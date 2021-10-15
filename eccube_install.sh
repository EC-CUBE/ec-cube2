#!/bin/sh

######################################################################
#
# EC-CUBE のインストールを行う shell スクリプト
#
#
# #処理内容
# 1. パーミッション変更
# 2. html/install/sql 配下の SQL を実行
# 3. 管理者権限をアップデート
# 4. data/config/config.php を生成
#
# 使い方
# Configurationの内容を自分の環境に併せて修正
# PostgreSQLの場合は、DBユーザーを予め作成しておいて
# # ./ec_cube_install.sh pgsql
# MySQLはMYSQLのRoot以外のユーザーで実行する場合は、128行目をコメントアウトして
# # ./ec_cube_install.sh mysql
#
#
# 開発コミュニティの関連スレッド
# http://xoops.ec-cube.net/modules/newbb/viewtopic.php?topic_id=4918&forum=14&post_id=23090#forumpost23090
#
#######################################################################

#######################################################################
# Configuration
#-- Shop Configuration
CONFIG_PHP="data/config/config.php"
ADMIN_MAIL=${ADMIN_MAIL:-"admin@example.com"}
SHOP_NAME=${SHOP_NAME:-"EC-CUBE SHOP"}
HTTP_URL=${HTTP_URL:-"http://localhost:8000/"}
HTTPS_URL=${HTTPS_URL:-"http://localhost:8000/"}
ROOT_URLPATH=${ROOT_URLPATH:-"/"}
DOMAIN_NAME=${DOMAIN_NAME:-""}
ADMIN_DIR=${ADMIN_DIR:-"admin/"}

DBSERVER=${DBSERVER-"127.0.0.1"}
DBNAME=${DBNAME:-"eccube_db"}
DBUSER=${DBUSER:-"eccube_db_user"}
DBPASS=${DBPASS:-"password"}

MAIL_BACKEND=${MAIL_BACKEND-"smtp"}
SMTP_HOST=${SMTP_HOST-"127.0.0.1"}
SMTP_PORT=${SMTP_PORT-"1025"}
SMTP_USER=${SMTP_USER-""}
SMTP_PASSWORD=${SMTP_PASSWORD-""}

ADMINPASS="f6b126507a5d00dbdbb0f326fe855ddf84facd57c5603ffdf7e08fbb46bd633c"
AUTH_MAGIC="droucliuijeanamiundpnoufrouphudrastiokec"

DBTYPE=$1;

case "${DBTYPE}" in
"heroku" )
    PSQL=psql
    export PGPASSWORD=${PGPASSWORD-$DBPASS}
    PGUSER=${PGUSER-"postgres"}
    DBPORT=${DBPORT:-"5432"}
;;
"appveyor" )
    PSQL=psql
    export PGPASSWORD=${PGPASSWORD-$DBPASS}
    PGUSER=postgres
    DBPORT=${DBPORT:-"5432"}
;;
"pgsql" )
    PSQL=psql
    export PGPASSWORD=${PGPASSWORD-$DBPASS}
    PGUSER=postgres
    DBPORT=${DBPORT:-"5432"}
    DB=$DBTYPE;
;;
"mysql" | "mysqli" )
    MYSQL=mysql
    ROOTUSER=root
    ROOTPASS=${ROOTPASS-$DBPASS}
    DBPORT=${DBPORT:-"3306"}
    DB=mysqli;
;;
* ) echo "ERROR:: argument is invaid"
exit
;;
esac


#######################################################################
# Functions

adjust_directory_permissions()
{
    chmod -R go+w "./html"
    chmod go+w "./data"
    chmod -R go+w "./data/Smarty"
    chmod -R go+w "./data/cache"
    chmod -R go+w "./data/class"
    chmod -R go+w "./data/class_extends"
    chmod go+w "./data/config"
    chmod -R go+w "./data/download"
    chmod -R go+w "./data/downloads"
    chmod go+w "./data/fonts"
    chmod go+w "./data/include"
    chmod go+w "./data/logs"
    chmod -R go+w "./data/module"
    chmod go+w "./data/smarty_extends"
    chmod go+w "./data/upload"
    chmod go+w "./data/upload/csv"
}

create_sequence_tables()
{
    SEQUENCES="
dtb_best_products_best_id_seq
dtb_bloc_bloc_id_seq
dtb_category_category_id_seq
dtb_class_class_id_seq
dtb_classcategory_classcategory_id_seq
dtb_csv_no_seq
dtb_csv_sql_sql_id_seq
dtb_customer_customer_id_seq
dtb_deliv_deliv_id_seq
dtb_holiday_holiday_id_seq
dtb_kiyaku_kiyaku_id_seq
dtb_mail_history_send_id_seq
dtb_maker_maker_id_seq
dtb_member_member_id_seq
dtb_module_update_logs_log_id_seq
dtb_news_news_id_seq
dtb_order_order_id_seq
dtb_order_detail_order_detail_id_seq
dtb_other_deliv_other_deliv_id_seq
dtb_pagelayout_page_id_seq
dtb_payment_payment_id_seq
dtb_products_class_product_class_id_seq
dtb_products_product_id_seq
dtb_review_review_id_seq
dtb_send_history_send_id_seq
dtb_mailmaga_template_template_id_seq
dtb_plugin_plugin_id_seq
dtb_plugin_hookpoint_plugin_hookpoint_id_seq
dtb_api_config_api_config_id_seq
dtb_api_account_api_account_id_seq
dtb_tax_rule_tax_rule_id_seq
"

    comb_sql="";
    for S in $SEQUENCES; do
        case ${DBTYPE} in
            heroku | appveyor | pgsql )
                sql=$(echo "CREATE SEQUENCE ${S} START 10000;")
            ;;
            mysql | mysqli )
                sql=$(echo "CREATE TABLE ${S} (
                        sequence int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (sequence)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                    LOCK TABLES ${S} WRITE;
                    INSERT INTO ${S} VALUES (10000);
                    UNLOCK TABLES;")
            ;;
        esac

        comb_sql=${comb_sql}${sql}
    done;

    case ${DBTYPE} in
        heroku | appveyor | pgsql )
            echo ${comb_sql} | ${PSQL} -h ${DBSERVER} -p ${DBPORT} -U ${DBUSER} ${DBNAME}
        ;;
        mysql)
            echo ${comb_sql} | ${MYSQL} -h ${DBSERVER} -P ${DBPORT} -u ${DBUSER} ${PASSOPT} ${DBNAME}
        ;;
    esac
}

get_optional_sql()
{
    echo "INSERT INTO dtb_member (member_id, login_id, password, name, salt, work, del_flg, authority, creator_id, rank, update_date) VALUES (2, 'admin', '${ADMINPASS}', '管理者', '${AUTH_MAGIC}', '1', '0', '0', '0', '1', current_timestamp);"
    echo "INSERT INTO dtb_baseinfo (id, shop_name, email01, email02, email03, email04, top_tpl, product_tpl, detail_tpl, mypage_tpl, update_date) VALUES (1, '${SHOP_NAME}', '${ADMIN_MAIL}', '${ADMIN_MAIL}', '${ADMIN_MAIL}', '${ADMIN_MAIL}', 'default1', 'default1', 'default1', 'default1', current_timestamp);"
}

create_config_php()
{
    cat > "./${CONFIG_PHP}" <<__EOF__
<?php
defined('ECCUBE_INSTALL') or define('ECCUBE_INSTALL', 'ON');
defined('HTTP_URL') or define('HTTP_URL', '${HTTP_URL}');
defined('HTTPS_URL') or define('HTTPS_URL', '${HTTPS_URL}');
defined('ROOT_URLPATH') or define('ROOT_URLPATH', '${ROOT_URLPATH}');
defined('DOMAIN_NAME') or define('DOMAIN_NAME', '${DOMAIN_NAME}');
defined('DB_TYPE') or define('DB_TYPE', '${DB}');
defined('DB_USER') or define('DB_USER', '${DBUSER}');
defined('DB_PASSWORD') or define('DB_PASSWORD', '${CONFIGPASS:-$DBPASS}');
defined('DB_SERVER') or define('DB_SERVER', '${DBSERVER}');
defined('DB_NAME') or define('DB_NAME', '${DBNAME}');
defined('DB_PORT') or define('DB_PORT', '${DBPORT}');
defined('ADMIN_DIR') or define('ADMIN_DIR', '${ADMIN_DIR}');
defined('ADMIN_FORCE_SSL') or define('ADMIN_FORCE_SSL', FALSE);
defined('ADMIN_ALLOW_HOSTS') or define('ADMIN_ALLOW_HOSTS', 'a:0:{}');
defined('AUTH_MAGIC') or define('AUTH_MAGIC', '${AUTH_MAGIC}');
defined('PASSWORD_HASH_ALGOS') or define('PASSWORD_HASH_ALGOS', 'sha256');
defined('MAIL_BACKEND') or define('MAIL_BACKEND', '${MAIL_BACKEND}');
defined('SMTP_HOST') or define('SMTP_HOST', '${SMTP_HOST}');
defined('SMTP_PORT') or define('SMTP_PORT', '${SMTP_PORT}');
defined('SMTP_USER') or define('SMTP_USER', '${SMTP_USER}');
defined('SMTP_PASSWORD') or define('SMTP_PASSWORD', '${SMTP_PASSWORD}');

__EOF__

    cat "./${CONFIG_PHP}"
}


#######################################################################
# Install

#-- Update Permissions
echo "update permissions..."
adjust_directory_permissions

#-- Setup Database
SQL_DIR="./html/install/sql"

case "${DBTYPE}" in
"heroku" )
    # PostgreSQL
    echo "create table..."
    ${PSQL} -h ${DBSERVER} -U ${DBUSER} -p ${DBPORT} -f ${SQL_DIR}/create_table_pgsql.sql ${DBNAME}
    echo "insert data..."
    ${PSQL} -h ${DBSERVER} -U ${DBUSER} -p ${DBPORT} -f ${SQL_DIR}/insert_data.sql ${DBNAME}
    echo "create sequence table..."
    create_sequence_tables
    echo "execute optional SQL..."
    get_optional_sql | ${PSQL} -h ${DBSERVER} -U ${DBUSER} -p ${DBPORT} ${DBNAME}
    DBTYPE="pgsql"
;;
"appveyor" | "pgsql" )
   # PostgreSQL
    echo "dropdb..."
    ${PSQL} -h ${DBSERVER} -U ${DBUSER} -p ${DBPORT} -c "DROP DATABASE ${DBNAME};"
    echo "createdb..."
    ${PSQL} -h ${DBSERVER} -U ${DBUSER} -p ${DBPORT} -c "CREATE DATABASE ${DBNAME};"
    echo "create table..."
    ${PSQL} -h ${DBSERVER} -U ${DBUSER} -p ${DBPORT} -f ${SQL_DIR}/create_table_pgsql.sql ${DBNAME}
    echo "insert data..."
    ${PSQL} -h ${DBSERVER} -U ${DBUSER} -p ${DBPORT} -f ${SQL_DIR}/insert_data.sql ${DBNAME}
    echo "create sequence table..."
    create_sequence_tables
    echo "execute optional SQL..."
    get_optional_sql | ${PSQL} -h ${DBSERVER} -U ${DBUSER} -p ${DBPORT} ${DBNAME}
    DBTYPE="pgsql"
;;
"mysql" )
    DBPASS=`echo $DBPASS | tr -d " "`
    if [ -n ${DBPASS} ]; then
        PASSOPT="--password=$DBPASS"
        CONFIGPASS=$DBPASS
    fi
    # MySQL
    echo "dropdb..."
    ${MYSQL} -u ${ROOTUSER} -h ${DBSERVER} -P ${DBPORT} ${PASSOPT} -e "DROP DATABASE \`${DBNAME}\`"
    echo "createdb..."
    ${MYSQL} -u ${ROOTUSER} -h ${DBSERVER} -P ${DBPORT} ${PASSOPT} -e "CREATE DATABASE \`${DBNAME}\` DEFAULT COLLATE=utf8_general_ci;"
    #echo "grant user..."
    #${MYSQL} -u ${ROOTUSER} -h ${DBSERVER} -P ${DBPORT} ${PASSOPT} -e "GRANT ALL ON \`${DBNAME}\`.* TO '${DBUSER}'@'%' IDENTIFIED BY '${DBPASS}'"
    echo "create table..."
    echo "SET SESSION default_storage_engine = InnoDB; SET sql_mode = 'NO_ENGINE_SUBSTITUTION';" |
        cat - ${SQL_DIR}/create_table_mysqli.sql |
        ${MYSQL} -h ${DBSERVER} -u ${DBUSER} -h ${DBSERVER} -P ${DBPORT} ${PASSOPT} ${DBNAME}
    echo "insert data..."
    echo "SET CHARACTER SET 'utf8';" |
        cat - ${SQL_DIR}/insert_data.sql |
        ${MYSQL} -u ${DBUSER} -h ${DBSERVER} -P ${DBPORT} ${PASSOPT} ${DBNAME}
    echo "create sequence table..."
    create_sequence_tables
    echo "execute optional SQL..."
    get_optional_sql | ${MYSQL} -u ${DBUSER} -h ${DBSERVER} -P ${DBPORT} ${PASSOPT} ${DBNAME}
;;
esac

#-- Setup Initial Data
echo "copy images..."
cp -rv "./html/install/save_image" "./html/upload/"

echo "creating ${CONFIG_PHP}..."
create_config_php

echo "Finished Successful!"
