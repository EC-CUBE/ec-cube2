<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * DBに依存した処理を抽象化するファクトリークラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class SC_DB_DBFactory
{
    public const ISOLATION_LEVEL_READ_UNCOMMITTED = 10;
    public const ISOLATION_LEVEL_READ_COMMITTED = 20;
    public const ISOLATION_LEVEL_REPEATABLE_READ = 30;
    public const ISOLATION_LEVEL_SERIALIZABLE = 40;

    /**
     * DB_TYPE に応じた DBFactory インスタンスを生成する.
     *
     * @param  string $db_type 任意のインスタンスを返したい場合は DB_TYPE 文字列を指定
     *
     * @return SC_DB_DBFactory  DBFactory インスタンス
     */
    public static function getInstance($db_type = DB_TYPE)
    {
        switch ($db_type) {
            case 'mysql':
            case 'mysqli':
                return new SC_DB_DBFactory_MYSQL();

            case 'pgsql':
                return new SC_DB_DBFactory_PGSQL();

            default:
                return new self();
        }
    }

    /**
     * データソース名を取得する.
     *
     * 引数 $dsn が空でデータソースが定義済みの場合はDB接続パラメータの連想配列を返す
     * DEFAULT_DSN が未定義の場合は void となる.
     * $dsn が空ではない場合は, $dsn の値を返す.
     *
     * @param  string $dsn データソース名
     *
     * @return string データソース名またはDB接続パラメータの連想配列
     */
    public function getDSN($dsn = '')
    {
        if (empty($dsn)) {
            if (defined('DEFAULT_DSN')) {
                $dsn = ['phptype' => DB_TYPE,
                    'username' => DB_USER,
                    'password' => DB_PASSWORD,
                    'protocol' => 'tcp',
                    'hostspec' => DB_SERVER,
                    'port' => DB_PORT,
                    'database' => DB_NAME,
                ];
            } else {
                return '';
            }
        }

        return $dsn;
    }

    /**
     * DBのバージョンを取得する.
     *
     * @param  string $dsn データソース名
     *
     * @return string データベースのバージョン
     */
    public function sfGetDBVersion($dsn = '')
    {
        return null;
    }

    /**
     * MySQL 用の SQL 文に変更する.
     *
     * @param  string $sql SQL 文
     *
     * @return string MySQL 用に置換した SQL 文
     */
    public function sfChangeMySQL($sql)
    {
        return null;
    }

    /**
     * 昨日の売上高・売上件数を算出する SQL を返す.
     *
     * @param  string $method SUM または COUNT
     *
     * @return string 昨日の売上高・売上件数を算出する SQL
     */
    public function getOrderYesterdaySql($method)
    {
        return null;
    }

    /**
     * 当月の売上高・売上件数を算出する SQL を返す.
     *
     * @param  string $method SUM または COUNT
     *
     * @return string 当月の売上高・売上件数を算出する SQL
     */
    public function getOrderMonthSql($method)
    {
        return null;
    }

    /**
     * 昨日のレビュー書き込み件数を算出する SQL を返す.
     *
     * @return string 昨日のレビュー書き込み件数を算出する SQL
     */
    public function getReviewYesterdaySql()
    {
        return null;
    }

    /**
     * メール送信履歴の start_date の検索条件の SQL を返す.
     *
     * @return string 検索条件の SQL
     */
    public function getSendHistoryWhereStartdateSql()
    {
        return null;
    }

    /**
     * ダウンロード販売の検索条件の SQL を返す.
     *
     * @return string 検索条件の SQL
     */
    public function getDownloadableDaysWhereSql()
    {
        return null;
    }

    /**
     * 文字列連結を行う.
     *
     * @param  string[]  $columns 連結を行うカラム名
     *
     * @return string 連結後の SQL 文
     */
    public function concatColumn($columns)
    {
        return null;
    }

    /**
     * テーブルを検索する.
     *
     * 引数に部分一致するテーブル名を配列で返す.
     *
     * @deprecated SC_Query::listTables() を使用してください
     *
     * @param  string $expression 検索文字列
     *
     * @return array  テーブル名の配列
     */
    public function findTableNames($expression = '')
    {
        return [];
    }

    /**
     * インデックス作成の追加定義を取得する
     *
     * 引数に部分一致するテーブル名を配列で返す.
     *
     * @param  string $table 対象テーブル名
     * @param  string $name  対象カラム名
     *
     * @return array  インデックス設定情報配列
     */
    public function sfGetCreateIndexDefinition($table, $name, $definition)
    {
        return $definition;
    }

    /**
     * 各 DB に応じた SC_Query での初期化を行う
     *
     * @param  SC_Query $objQuery SC_Query インスタンス
     *
     * @return void
     */
    public function initObjQuery(SC_Query &$objQuery)
    {
    }

    /**
     * テーブル一覧を取得する
     *
     * @return array テーブル名の配列
     */
    public function listTables(SC_Query &$objQuery)
    {
        $objManager = &$objQuery->conn->loadModule('Manager');

        return $objManager->listTables();
    }

    /**
     * SQL 文に OFFSET, LIMIT を付加する。
     *
     * @param string 元の SQL 文
     * @param int|string|null $limit LIMIT 句に設定する値
     * @param int|string|null $offset OFFSET 句に設定する値
     *
     * @return string 付加後の SQL 文
     */
    public function addLimitOffset($sql, $limit = null, $offset = null)
    {
        // 以下の is_numeric() は、`!is_null()` と `!== ''` の評価と、SQL インジェクション対策を兼ねる。
        if (is_numeric($limit)) {
            $sql .= " LIMIT $limit";
        }
        if (is_numeric($offset)) {
            $sql .= " OFFSET $offset";
        }

        return $sql;
    }

    /**
     * 商品詳細の SQL を取得する.
     *
     * @param  string $where_products_class 商品規格情報の WHERE 句
     * @param array $product_ids 商品IDの配列
     *
     * @return string 商品詳細の SQL
     */
    public function alldtlSQL($where_products_class = '', $product_ids = [])
    {
        if (!SC_Utils_Ex::isBlank($where_products_class)) {
            $where_products_class = 'AND ('.$where_products_class.')';
        }

        $dtb_products_table = 'dtb_products';
        $product_id_cause = '';

        // dtb_products_class の Full scan を防ぐため,
        // 商品IDが特定できている場合は, 先に product_id で対象を絞り込む
        if (count($product_ids) > 0) {
            $in = SC_Utils_Ex::repeatStrWithSeparator('?', count($product_ids));
            $product_id_cause = ' AND product_id IN ('.$in.')';
            $dtb_products_table = ' ( SELECT * FROM dtb_products WHERE product_id IN ('.$in.') ) AS dtb_products';
        }
        /*
         * point_rate, deliv_fee は商品規格(dtb_products_class)ごとに保持しているが,
         * 商品(dtb_products)ごとの設定なので MAX のみを取得する.
         */
        $sql = <<< __EOS__
            (
                SELECT
                        dtb_products.*
                    ,T4.product_code_min
                    ,T4.product_code_max
                    ,T4.price01_min
                    ,T4.price01_max
                    ,T4.price02_min
                    ,T4.price02_max
                    ,T4.stock_min
                    ,T4.stock_max
                    ,T4.stock_unlimited_min
                    ,T4.stock_unlimited_max
                    ,T4.point_rate
                    ,T4.deliv_fee
                    ,dtb_maker.name AS maker_name
                FROM $dtb_products_table
                    INNER JOIN (
                        SELECT product_id
                            ,MIN(product_code) AS product_code_min
                            ,MAX(product_code) AS product_code_max
                            ,MIN(price01) AS price01_min
                            ,MAX(price01) AS price01_max
                            ,MIN(price02) AS price02_min
                            ,MAX(price02) AS price02_max
                            ,MIN(stock) AS stock_min
                            ,MAX(stock) AS stock_max
                            ,MIN(stock_unlimited) AS stock_unlimited_min
                            ,MAX(stock_unlimited) AS stock_unlimited_max
                            ,MAX(point_rate) AS point_rate
                            ,MAX(deliv_fee) AS deliv_fee
                        FROM dtb_products_class
                        WHERE del_flg = 0 $where_products_class $product_id_cause
                        GROUP BY product_id
                    ) AS T4
                        ON dtb_products.product_id = T4.product_id
                    LEFT JOIN dtb_maker
                        ON dtb_products.maker_id = dtb_maker.maker_id
            ) AS alldtl
            __EOS__;

        return $sql;
    }

    /**
     * トランザクション分離レベルを取得する。
     *
     * @return int
     */
    public function getTransactionIsolationLevel()
    {
        // TODO: 一般的な DBMS のデフォルトを返している。実際のレベルを返すのが望ましい。しかし、毎回 `SHOW transaction_isolation` などを実行するのは避けたい。インストーラーで実行環境のレベルを退避して設定ファイルに記録したり、設定ファイルで別のレベルを設定できるよう改善できそう。
        return static::ISOLATION_LEVEL_READ_COMMITTED;
    }

    /**
     * 削除時に対象行が存在しない場合に削除をスキップする必要があるかを返す。
     *
     * @return bool
     */
    public function isSkipDeleteIfNotExists()
    {
        return false;
    }
}
