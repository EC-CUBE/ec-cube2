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

/*  [名称] SC_CustomerList
 *  [概要] 会員検索用クラス
 */
class SC_CustomerList extends SC_SelectSql_Ex
{
    public $arrColumnCSV;

    public function __construct($array, $mode = '')
    {
        if (is_array($array)) {
            $this->arrSql = $array;
        }
        $objDb = new SC_Helper_DB_Ex();
        $dbFactory = SC_DB_DBFactory_Ex::getInstance();

        if (!isset($this->arrSql['search_buy_product_name'])) $this->arrSql['search_buy_product_name'] = '';
        if (!isset($this->arrSql['search_buy_product_code'])) $this->arrSql['search_buy_product_code'] = '';
        if (!isset($this->arrSql['search_category_id'])) $this->arrSql['search_category_id'] = '';
        // 購入商品コード
        // 購入商品名称
        // 購入商品カテゴリ
        if (
            strlen($this->arrSql['search_buy_product_name']) > 0 or
            strlen($this->arrSql['search_buy_product_code']) > 0 or
            strlen($this->arrSql['search_category_id']) > 0
        ) {
            $tmp = array();
            // 購入商品名称
            if (strlen($this->arrSql['search_buy_product_name']) > 0) {
                $this->arrVal[] = $this->addSearchStr($this->arrSql['search_buy_product_name']);
                $tmp[] = 'od.product_name LIKE ? ';
            }
            // 購入商品コード
            if (strlen($this->arrSql['search_buy_product_code']) > 0) {
                $this->arrVal[] = $this->addSearchStr($this->arrSql['search_buy_product_code']);
                $tmp[] = 'od.product_code LIKE ? ';
            }
            // 購入商品カテゴリ
            if (strlen($this->arrSql['search_category_id']) > 0) {
                list($tmp_where, $tmp_arrval) = $objDb->sfGetCatWhere($this->arrSql['search_category_id']);
                if ($tmp_where != '') {
                    $tmp[] = 'EXISTS (SELECT product_id FROM dtb_product_categories WHERE '.$tmp_where.' AND product_id = od.product_id)';
                    $this->arrVal = array_merge((array) $this->arrVal, (array) $tmp_arrval);
                }
            }
            //検索して一致した物を基準にINNER JOINで結合した方が早いので、商品コード・商品名称・商品カテゴリが選択されていた場合には
            //JOINのクエリを追加。
            $this->setInnerJoin = '
                (
                    SELECT DISTINCT
                        o.customer_id
                    FROM
                        dtb_order_detail od INNER JOIN
                        dtb_order o ON ( od.order_id = o.order_id)
                    WHERE
                        '.implode(" AND ",$tmp).'
                ) as baseorder INNER JOIN
            ';
            $this->setInnerJoin2 = ' ON(baseorder.customer_id = dtb_customer.customer_id)';
        }else{
            $this->setInnerJoin = '';
        }

        if ($mode == '') {
            // 会員本登録会員で削除していない会員
            $this->setWhere('status = 2 AND del_flg = 0 ');
            // 登録日を示すカラム
            $regdate_col = 'dtb_customer.update_date';
        }

        if ($mode == 'customer') {
            $this->setWhere(' del_flg = 0 ');
            // 登録日を示すカラム
            $regdate_col = 'dtb_customer.update_date';
        }

        // 会員ID
        if (!isset($this->arrSql['search_customer_id'])) $this->arrSql['search_customer_id'] = '';
        if (strlen($this->arrSql['search_customer_id']) > 0) {
            $this->setWhere('dtb_customer.customer_id =  ?');
            $this->arrVal[] = $this->arrSql['search_customer_id'];
        }

        // 名前
        if (!isset($this->arrSql['search_name'])) $this->arrSql['search_name'] = '';
        if (strlen($this->arrSql['search_name']) > 0) {
            $this->setWhere('(' . $dbFactory->concatColumn(array('dtb_customer.name01', 'dtb_customer.name02')) . ' LIKE ?)');
            $searchName = $this->addSearchStr($this->arrSql['search_name']);
            $this->arrVal[] = preg_replace('/[ 　]+/u', '', $searchName);
        }

        // 名前(フリガナ)
        if (!isset($this->arrSql['search_kana'])) $this->arrSql['search_kana'] = '';
        if (strlen($this->arrSql['search_kana']) > 0) {
            $this->setWhere('(' . $dbFactory->concatColumn(array('dtb_customer.kana01', 'dtb_customer.kana02')) . ' LIKE ?)');
            $searchKana = $this->addSearchStr($this->arrSql['search_kana']);
            $this->arrVal[] = preg_replace('/[ 　]+/u', '', $searchKana);
        }

        // 都道府県
        if (!isset($this->arrSql['search_pref'])) $this->arrSql['search_pref'] = '';
        if (strlen($this->arrSql['search_pref']) > 0) {
            $this->setWhere('dtb_customer.pref = ?');
            $this->arrVal[] = $this->arrSql['search_pref'];
        }

        // 電話番号
        if (!isset($this->arrSql['search_tel'])) $this->arrSql['search_tel'] = '';
        if (is_numeric($this->arrSql['search_tel'])) {
            $this->setWhere('(' . $dbFactory->concatColumn(array('dtb_customer.tel01', 'dtb_customer.tel02', 'dtb_customer.tel03')) . ' LIKE ?)');
            $searchTel = $this->addSearchStr($this->arrSql['search_tel']);
            $this->arrVal[] = str_replace('-', '', $searchTel);
        }

        // 性別
        if (!isset($this->arrSql['search_sex'])) $this->arrSql['search_sex'] = '';
        if (is_array($this->arrSql['search_sex'])) {
            $arrSexVal = $this->setItemTerm($this->arrSql['search_sex'], 'dtb_customer.sex');
            foreach ($arrSexVal as $data) {
                $this->arrVal[] = $data;
            }
        }

        // 職業
        if (!isset($this->arrSql['search_job'])) $this->arrSql['search_job'] = '';
        if (is_array($this->arrSql['search_job'])) {
            if (in_array('不明', $this->arrSql['search_job'])) {
                $arrJobVal = $this->setItemTermWithNull($this->arrSql['search_job'], 'dtb_customer.job');
            } else {
                $arrJobVal = $this->setItemTerm($this->arrSql['search_job'], 'dtb_customer.job');
            }
            if (is_array($arrJobVal)) {
                foreach ($arrJobVal as $data) {
                    $this->arrVal[] = $data;
                }
            }
        }

        // E-MAIL
        if (!isset($this->arrSql['search_email'])) $this->arrSql['search_email'] = '';
        if (strlen($this->arrSql['search_email']) > 0) {
            //カンマ区切りで複数の条件指定可能に
            $this->arrSql['search_email'] = explode(',', $this->arrSql['search_email']);
            $sql_where = '';
            foreach ($this->arrSql['search_email'] as $val) {
                $val = trim($val);
                //検索条件を含まない
                if ($this->arrSql['not_emailinc'] == '1') {
                    if ($sql_where == '') {
                        $sql_where .= 'dtb_customer.email NOT ILIKE ? ';
                    } else {
                        $sql_where .= 'AND dtb_customer.email NOT ILIKE ? ';
                    }
                } else {
                    if ($sql_where == '') {
                        $sql_where .= 'dtb_customer.email ILIKE ? ';
                    } else {
                        $sql_where .= 'OR dtb_customer.email ILIKE ? ';
                    }
                }
                $searchEmail = $this->addSearchStr($val);
                $this->arrVal[] = $searchEmail;
            }
            $this->setWhere($sql_where);
        }

        // E-MAIL(mobile)
        if (!isset($this->arrSql['search_email_mobile'])) $this->arrSql['search_email_mobile'] = '';

        if (strlen($this->arrSql['search_email_mobile']) > 0) {
            //カンマ区切りで複数の条件指定可能に
            $this->arrSql['search_email_mobile'] = explode(',', $this->arrSql['search_email_mobile']);
            $sql_where = '';
            foreach ($this->arrSql['search_email_mobile'] as $val) {
                $val = trim($val);
                //検索条件を含まない
                if ($this->arrSql['not_email_mobileinc'] == '1') {
                    if ($sql_where == '') {
                        $sql_where .= 'dtb_customer.email_mobile NOT ILIKE ? ';
                    } else {
                        $sql_where .= 'AND dtb_customer.email_mobile NOT ILIKE ? ';
                    }
                } else {
                    if ($sql_where == '') {
                        $sql_where .= 'dtb_customer.email_mobile ILIKE ? ';
                    } else {
                        $sql_where .= 'OR dtb_customer.email_mobile ILIKE ? ';
                    }
                }
                $searchemail_mobile = $this->addSearchStr($val);
                $this->arrVal[] = $searchemail_mobile;
            }
            $this->setWhere($sql_where);
        }

        // メールマガジンの場合
        if ($mode == 'customer') {
            // メルマガ受け取りの選択項目がフォームに存在する場合
            if (isset($this->arrSql['search_htmlmail'])) {
                $this->setWhere('dtb_customer.status = 2');
                if (SC_Utils_Ex::sfIsInt($this->arrSql['search_htmlmail'])) {
                    // メルマガ拒否している会員も含む場合は、条件を付加しない
                    if ($this->arrSql['search_htmlmail'] != 99) {
                        $this->setWhere('dtb_customer.mailmaga_flg = ?');
                        $this->arrVal[] = $this->arrSql['search_htmlmail'];
                    }
                } else {
                    //　メルマガ購読拒否は省く
                    $this->setWhere('dtb_customer.mailmaga_flg <> 3');
                }
            }
        }

        // 配信メールアドレス種別
        if ($mode == 'customer') {
            if (isset($this->arrSql['search_mail_type'])) {
                $sqlEmailMobileIsEmpty = "(dtb_customer.email_mobile IS NULL OR dtb_customer.email_mobile = '')";
                switch ($this->arrSql['search_mail_type']) {
                    // PCメールアドレス
                    case 1:
                        $this->setWhere("(dtb_customer.email <> dtb_customer.email_mobile OR $sqlEmailMobileIsEmpty)");
                        break;
                    // 携帯メールアドレス
                    case 2:
                        $this->setWhere("NOT $sqlEmailMobileIsEmpty");
                        break;
                    // PCメールアドレス (携帯メールアドレスを登録している会員は除外)
                    case 3:
                        $this->setWhere($sqlEmailMobileIsEmpty);
                        break;
                    // 携帯メールアドレス (PCメールアドレスを登録している会員は除外)
                    case 4:
                        $this->setWhere('dtb_customer.email = dtb_customer.email_mobile');
                        break;
                }
            }
        }

        // 購入金額指定
        if (!isset($this->arrSql['search_buy_total_from'])) $this->arrSql['search_buy_total_from'] = '';
        if (!isset($this->arrSql['search_buy_total_to'])) $this->arrSql['search_buy_total_to'] = '';
        if (is_numeric($this->arrSql['search_buy_total_from']) || is_numeric($this->arrSql['search_buy_total_to'])) {
            $arrBuyTotal = $this->selectRange($this->arrSql['search_buy_total_from'], $this->arrSql['search_buy_total_to'], 'buy_total');
            foreach ($arrBuyTotal as $data) {
                $this->arrVal[] = $data;
            }
        }

        // 購入回数指定
        if (!isset($this->arrSql['search_buy_times_from'])) $this->arrSql['search_buy_times_from'] = '';
        if (!isset($this->arrSql['search_buy_times_to'])) $this->arrSql['search_buy_times_to'] = '';
        if (is_numeric($this->arrSql['search_buy_times_from']) || is_numeric($this->arrSql['search_buy_times_to'])) {
            $arrBuyTimes = $this->selectRange($this->arrSql['search_buy_times_from'], $this->arrSql['search_buy_times_to'], 'buy_times');
            foreach ($arrBuyTimes as $data) {
                $this->arrVal[] = $data;
            }
        }

        // 誕生日期間指定
        if (!isset($this->arrSql['search_b_start_year'])) $this->arrSql['search_b_start_year'] = '';
        if (!isset($this->arrSql['search_b_start_month'])) $this->arrSql['search_b_start_month'] = '';
        if (!isset($this->arrSql['search_b_start_day'])) $this->arrSql['search_b_start_day'] = '';
        if (!isset($this->arrSql['search_b_end_year'])) $this->arrSql['search_b_end_year'] = '';
        if (!isset($this->arrSql['search_b_end_month'])) $this->arrSql['search_b_end_month'] = '';
        if (!isset($this->arrSql['search_b_end_day'])) $this->arrSql['search_b_end_day'] = '';
        if ((strlen($this->arrSql['search_b_start_year']) > 0 && strlen($this->arrSql['search_b_start_month']) > 0 && strlen($this->arrSql['search_b_start_day']) > 0)
            || strlen($this->arrSql['search_b_end_year']) > 0 && strlen($this->arrSql['search_b_end_month']) > 0 && strlen($this->arrSql['search_b_end_day']) > 0) {
            $arrBirth = $this->selectTermRange($this->arrSql['search_b_start_year'], $this->arrSql['search_b_start_month'], $this->arrSql['search_b_start_day'],
                                               $this->arrSql['search_b_end_year'], $this->arrSql['search_b_end_month'], $this->arrSql['search_b_end_day'], 'birth');
            foreach ($arrBirth as $data) {
                $this->arrVal[] = $data;
            }
        }

        // 誕生月の検索
        if (!isset($this->arrSql['search_birth_month'])) $this->arrSql['search_birth_month'] = '';
        if (is_numeric($this->arrSql['search_birth_month'])) {
            $this->setWhere(' EXTRACT(month from birth) = ?');
            $this->arrVal[] = $this->arrSql['search_birth_month'];
        }

        // 登録期間指定
        if (!isset($this->arrSql['search_start_year'])) $this->arrSql['search_start_year'] = '';
        if (!isset($this->arrSql['search_start_month'])) $this->arrSql['search_start_month'] = '';
        if (!isset($this->arrSql['search_start_day'])) $this->arrSql['search_start_day'] = '';
        if (!isset($this->arrSql['search_end_year'])) $this->arrSql['search_end_year'] = '';
        if (!isset($this->arrSql['search_end_month'])) $this->arrSql['search_end_month'] = '';
        if (!isset($this->arrSql['search_end_day'])) $this->arrSql['search_end_day'] = '';
        if ( (strlen($this->arrSql['search_start_year']) > 0 && strlen($this->arrSql['search_start_month']) > 0 && strlen($this->arrSql['search_start_day']) > 0) ||
                (strlen($this->arrSql['search_end_year']) > 0 && strlen($this->arrSql['search_end_month']) >0 && strlen($this->arrSql['search_end_day']) > 0)) {
            $arrRegistTime = $this->selectTermRange($this->arrSql['search_start_year'], $this->arrSql['search_start_month'], $this->arrSql['search_start_day'],
                            $this->arrSql['search_end_year'], $this->arrSql['search_end_month'], $this->arrSql['search_end_day'], $regdate_col);
            foreach ($arrRegistTime as $data) {
                $this->arrVal[] = $data;
            }
        }

        // 最終購入日指定
        if (!isset($this->arrSql['search_buy_start_year'])) $this->arrSql['search_buy_start_year'] = '';
        if (!isset($this->arrSql['search_buy_start_month'])) $this->arrSql['search_buy_start_month'] = '';
        if (!isset($this->arrSql['search_buy_start_day'])) $this->arrSql['search_buy_start_day'] = '';
        if (!isset($this->arrSql['search_buy_end_year'])) $this->arrSql['search_buy_end_year'] = '';
        if (!isset($this->arrSql['search_buy_end_month'])) $this->arrSql['search_buy_end_month'] = '';
        if (!isset($this->arrSql['search_buy_end_day'])) $this->arrSql['search_buy_end_day'] = '';

        if ( (strlen($this->arrSql['search_buy_start_year']) > 0 && strlen($this->arrSql['search_buy_start_month']) > 0 && strlen($this->arrSql['search_buy_start_day']) > 0) ||
                (strlen($this->arrSql['search_buy_end_year']) > 0 && strlen($this->arrSql['search_buy_end_month']) >0 && strlen($this->arrSql['search_buy_end_day']) > 0)) {
            $arrRegistTime = $this->selectTermRange($this->arrSql['search_buy_start_year'], $this->arrSql['search_buy_start_month'], $this->arrSql['search_buy_start_day'],
                            $this->arrSql['search_buy_end_year'], $this->arrSql['search_buy_end_month'], $this->arrSql['search_buy_end_day'], 'last_buy_date');
            foreach ($arrRegistTime as $data) {
                $this->arrVal[] = $data;
            }
        }


        // 会員状態
        if (!isset($this->arrSql['search_status'])) $this->arrSql['search_status'] = '';
        if (is_array($this->arrSql['search_status'])) {
            $arrStatusVal = $this->setItemTerm($this->arrSql['search_status'], 'status');
            foreach ($arrStatusVal as $data) {
                $this->arrVal[] = $data;
            }
        }
        $this->setOrder('dtb_customer.customer_id DESC');
    }
    // 検索用SQL
    public function getList()
    {
        $this->select = 'SELECT dtb_customer.customer_id,dtb_customer.name01,dtb_customer.name02,dtb_customer.kana01,dtb_customer.kana02,dtb_customer.sex,
        dtb_customer.email,dtb_customer.email_mobile,dtb_customer.tel01,dtb_customer.tel02,dtb_customer.tel03,dtb_customer.pref,
        dtb_customer.status,dtb_customer.update_date,dtb_customer.mailmaga_flg FROM '.$this->setInnerJoin.' dtb_customer '.$this->setInnerJoin2;
        return $this->getSql(2);
    }

    public function getListMailMagazine($is_mobile = false)
    {
        $colomn = $this->getMailMagazineColumn($is_mobile);
        $colomn = 'dtb_customer.customer_id,dtb_customer.name01,dtb_customer.name02,dtb_customer.kana01,dtb_customer.kana02,dtb_customer.sex,
        dtb_customer.email,dtb_customer.email_mobile,dtb_customer.tel01,dtb_customer.tel02,dtb_customer.tel03,dtb_customer.pref,
        dtb_customer.status,dtb_customer.update_date,dtb_customer.mailmaga_flg';
        if($this->setInnerJoin != ''){
            $this->select = "
                SELECT
                    $colomn
                FROM
                    ".$this->setInnerJoin."
                    dtb_customer ".$this->setInnerJoin2;;
        }else{
            $this->select = "
                SELECT
                    $colomn
                FROM
                    dtb_customer";
        }
        return $this->getSql(0);
    }

    // 検索総数カウント用SQL
    public function getListCount()
    {
        $this->select = 'SELECT COUNT(dtb_customer.customer_id) FROM '.$this->setInnerJoin.' dtb_customer '.$this->setInnerJoin2;

        return $this->getSql(1);
    }

    // CSVダウンロード用SQL
    public function getListCSV($arrColumnCSV)
    {
        $this->arrColumnCSV = $arrColumnCSV;
        $i = 0;
        foreach ($this->arrColumnCSV as $val) {
            if ($i != 0) $state .= ', ';
            $state .= $val['sql'];
            $i ++;
        }

        $this->select = 'SELECT ' .$state. ' FROM '.$this->setInnerJoin.' dtb_customer '.$this->setInnerJoin2;

        return $this->getSql(2);
    }

    public function getWhere($with_where = false)
    {
        return array(parent::getWhere($with_where), $this->arrVal);
    }
}
