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
 * 商品管理 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_Products extends LC_Page_Admin_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'products/index.tpl';
        $this->tpl_mainno = 'products';
        $this->tpl_subno = 'index';
        $this->tpl_pager = 'pager.tpl';
        $this->tpl_maintitle = '商品管理';
        $this->tpl_subtitle = '商品マスター';

        $masterData = new SC_DB_MasterData_Ex();
        $this->arrPageMax = $masterData->getMasterData('mtb_page_max');
        $this->arrDISP = $masterData->getMasterData('mtb_disp');
        $this->arrSTATUS = $masterData->getMasterData('mtb_status');
        $this->arrPRODUCTSTATUS_COLOR = $masterData->getMasterData('mtb_product_status_color');

        $objDate = new SC_Date_Ex();
        // 登録・更新検索開始年
        $objDate->setStartYear(RELEASE_YEAR);
        $objDate->setEndYear(date('Y'));
        $this->arrStartYear = $objDate->getYear();
        $this->arrStartMonth = $objDate->getMonth();
        $this->arrStartDay = $objDate->getDay();
        // 登録・更新検索終了年
        $objDate->setStartYear(RELEASE_YEAR);
        $objDate->setEndYear(date('Y'));
        $this->arrEndYear = $objDate->getYear();
        $this->arrEndMonth = $objDate->getMonth();
        $this->arrEndDay = $objDate->getDay();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    public function action()
    {
        $objDb = new SC_Helper_DB_Ex();
        $objFormParam = new SC_FormParam_Ex();
        $objProduct = new SC_Product_Ex();
        $objQuery = SC_Query_Ex::getSingletonInstance();

        // パラメーター情報の初期化
        $this->lfInitParam($objFormParam);
        $objFormParam->setParam($_POST);
        $this->arrHidden = $objFormParam->getSearchArray();
        $this->arrForm = $objFormParam->getFormParamList();

        switch ($this->getMode()) {
            case 'delete':
                // 商品、子テーブル(商品規格)、会員お気に入り商品の削除
                $this->doDelete('product_id = ?', [$objFormParam->getValue('product_id')]);
                // 件数カウントバッチ実行
                $objDb->sfCountCategory($objQuery);
                $objDb->sfCountMaker($objQuery);
                // 削除後に検索結果を表示するため breakしない

                // 検索パラメーター生成後に処理実行するため breakしない
                // no break
            case 'csv':
            case 'delete_all':
            case 'search':
                $objFormParam->convParam();
                $objFormParam->trimParam();
                $this->arrErr = $this->lfCheckError($objFormParam);
                $arrParam = $objFormParam->getHashArray();

                if (count($this->arrErr) == 0) {
                    $where = 'del_flg = 0';
                    $arrWhereVal = [];
                    foreach ($arrParam as $key => $val) {
                        if ($val == '') {
                            continue;
                        }
                        $this->buildQuery($key, $where, $arrWhereVal, $objFormParam, $objDb);
                    }

                    $order = 'update_date DESC';

                    /* -----------------------------------------------
                     * 処理を実行
                     * ----------------------------------------------- */
                    switch ($this->getMode()) {
                        // CSVを送信する。
                        case 'csv':
                            $objCSV = new SC_Helper_CSV_Ex();
                            // CSVを送信する。正常終了の場合、終了。
                            $objCSV->sfDownloadCsv(1, $where, $arrWhereVal, $order, true);
                            SC_Response_Ex::actionExit();

                            // 全件削除(ADMIN_MODE)
                            // no break
                        case 'delete_all':
                            $this->doDelete($where, $arrWhereVal);
                            break;

                            // 検索実行
                        default:
                            // 行数の取得
                            $this->tpl_linemax = $this->getNumberOfLines($where, $arrWhereVal);
                            // ページ送りの処理
                            $page_max = SC_Utils_Ex::sfGetSearchPageMax($objFormParam->getValue('search_page_max'));
                            // ページ送りの取得
                            $objNavi = new SC_PageNavi_Ex(
                                $this->arrHidden['search_pageno'],
                                $this->tpl_linemax,
                                $page_max,
                                'eccube.moveNaviPage',
                                NAVI_PMAX
                            );
                            $this->arrPagenavi = $objNavi->arrPagenavi;

                            // 検索結果の取得
                            $this->arrProducts = $this->findProducts(
                                $where,
                                $arrWhereVal,
                                $page_max,
                                $objNavi->start_row,
                                $order,
                                $objProduct
                            );

                            // 各商品ごとのカテゴリIDを取得
                            if (count($this->arrProducts) > 0) {
                                foreach ($this->arrProducts as $key => $val) {
                                    $this->arrProducts[$key]['categories'] = $objProduct->getCategoryIds($val['product_id'], true);
                                    $objDb->g_category_on = false;
                                }
                            }
                    }
                }
                break;
        }

        // カテゴリの読込
        [$this->arrCatKey, $this->arrCatVal] = $objDb->sfGetLevelCatList(false);
        $this->arrCatList = $this->lfGetIDName($this->arrCatKey, $this->arrCatVal);
    }

    /**
     * パラメーター情報の初期化を行う.
     *
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     *
     * @return void
     */
    public function lfInitParam(&$objFormParam)
    {
        // POSTされる値
        $objFormParam->addParam('商品ID', 'product_id', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('カテゴリID', 'category_id', STEXT_LEN, 'n', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('ページ送り番号', 'search_pageno', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('表示件数', 'search_page_max', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);

        // 検索条件
        $objFormParam->addParam('商品ID', 'search_product_id', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('商品コード', 'search_product_code', STEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('商品名', 'search_name', STEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('カテゴリ', 'search_category_id', STEXT_LEN, 'n', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('種別', 'search_status', INT_LEN, 'n', ['MAX_LENGTH_CHECK']);
        // 登録・更新日
        $objFormParam->addParam('開始年', 'search_startyear', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('開始月', 'search_startmonth', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('開始日', 'search_startday', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('終了年', 'search_endyear', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('終了月', 'search_endmonth', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('終了日', 'search_endday', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);

        $objFormParam->addParam('商品ステータス', 'search_product_statuses', INT_LEN, 'n', ['MAX_LENGTH_CHECK']);
    }

    /**
     * 入力内容のチェックを行う.
     *
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     *
     * @return array
     */
    public function lfCheckError(&$objFormParam)
    {
        $objErr = new SC_CheckError_Ex($objFormParam->getHashArray());
        $objErr->arrErr = $objFormParam->checkError();

        $objErr->doFunc(['開始日', '終了日', 'search_startyear', 'search_startmonth', 'search_startday', 'search_endyear', 'search_endmonth', 'search_endday'], ['CHECK_SET_TERM']);

        return $objErr->arrErr;
    }

    // カテゴリIDをキー、カテゴリ名を値にする配列を返す。
    public function lfGetIDName($arrCatKey, $arrCatVal)
    {
        $max = count($arrCatKey);
        $arrRet = [];
        for ($cnt = 0; $cnt < $max; $cnt++) {
            $key = $arrCatKey[$cnt] ?? '';
            $val = $arrCatVal[$cnt] ?? '';
            $arrRet[$key] = $val;
        }

        return $arrRet;
    }

    /**
     * 商品、子テーブル(商品規格)、お気に入り商品の削除
     *
     * @param  string $where    削除対象の WHERE 句
     * @param  array  $arrParam 削除対象の値
     *
     * @return void
     */
    public function doDelete($where, $arrParam = [])
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $product_ids = $objQuery->getCol('product_id', 'dtb_products', $where, $arrParam);

        $sqlval['del_flg'] = 1;
        $sqlval['update_date'] = 'CURRENT_TIMESTAMP';
        $objQuery->begin();
        $objQuery->update('dtb_products_class', $sqlval, "product_id IN (SELECT product_id FROM dtb_products WHERE $where)", $arrParam);
        $objQuery->delete('dtb_customer_favorite_products', "product_id IN (SELECT product_id FROM dtb_products WHERE $where)", $arrParam);

        $objRecommend = new SC_Helper_BestProducts_Ex();
        $objRecommend->deleteByProductIDs($product_ids);

        $objQuery->update('dtb_products', $sqlval, $where, $arrParam);
        $objQuery->commit();
    }

    /**
     * クエリを構築する.
     *
     * 検索条件のキーに応じた WHERE 句と, クエリパラメーターを構築する.
     * クエリパラメーターは, SC_FormParam の入力値から取得する.
     *
     * 構築内容は, 引数の $where 及び $arrValues にそれぞれ追加される.
     *
     * @param  string       $key          検索条件のキー
     * @param  string       $where        構築する WHERE 句
     * @param  array        $arrValues    構築するクエリパラメーター
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     * @param  SC_Helper_DB_Ex $objDb        SC_Helper_DB_Ex インスタンス
     *
     * @return void
     */
    public function buildQuery($key, &$where, &$arrValues, &$objFormParam, &$objDb)
    {
        $dbFactory = SC_DB_DBFactory_Ex::getInstance();
        switch ($key) {
            // 商品ID
            case 'search_product_id':
                $where .= ' AND product_id = ?';
                $arrValues[] = sprintf('%d', $objFormParam->getValue($key));
                break;
                // 商品コード
            case 'search_product_code':
                $where .= ' AND product_id IN (SELECT product_id FROM dtb_products_class WHERE product_code ILIKE ? AND del_flg = 0)';
                $arrValues[] = sprintf('%%%s%%', $objFormParam->getValue($key));
                break;
                // 商品名
            case 'search_name':
                $where .= ' AND name LIKE ?';
                $arrValues[] = sprintf('%%%s%%', $objFormParam->getValue($key));
                break;
                // カテゴリ
            case 'search_category_id':
                [$tmp_where, $tmp_Values] = $objDb->sfGetCatWhere($objFormParam->getValue($key));
                if ($tmp_where != '') {
                    $where .= ' AND product_id IN (SELECT product_id FROM dtb_product_categories WHERE '.$tmp_where.')';
                    $arrValues = array_merge((array) $arrValues, (array) $tmp_Values);
                }
                break;
                // 種別
            case 'search_status':
                $tmp_where = '';
                foreach ($objFormParam->getValue($key) as $element) {
                    if ($element != '') {
                        if (SC_Utils_Ex::isBlank($tmp_where)) {
                            $tmp_where .= ' AND (status = ?';
                        } else {
                            $tmp_where .= ' OR status = ?';
                        }
                        $arrValues[] = $element;
                    }
                }

                if (!SC_Utils_Ex::isBlank($tmp_where)) {
                    $tmp_where .= ')';
                    $where .= " $tmp_where ";
                }
                break;
                // 登録・更新日(開始)
            case 'search_startyear':
                $date = SC_Utils_Ex::sfGetTimestamp(
                    $objFormParam->getValue('search_startyear'),
                    $objFormParam->getValue('search_startmonth'),
                    $objFormParam->getValue('search_startday')
                );
                $where .= ' AND update_date >= ?';
                $arrValues[] = $date;
                break;
                // 登録・更新日(終了)
            case 'search_endyear':
                $date = SC_Utils_Ex::sfGetTimestamp(
                    $objFormParam->getValue('search_endyear'),
                    $objFormParam->getValue('search_endmonth'),
                    $objFormParam->getValue('search_endday'),
                    true
                );
                $where .= ' AND update_date <= ?';
                $arrValues[] = $date;
                break;
                // 商品ステータス
            case 'search_product_statuses':
                $arrPartVal = $objFormParam->getValue($key);
                $count = count($arrPartVal);
                if ($count >= 1) {
                    $where .= ' '
                        .'AND product_id IN ('
                        .'    SELECT product_id FROM dtb_product_status WHERE product_status_id IN ('.SC_Utils_Ex::repeatStrWithSeparator('?', $count).')'
                        .')';
                    $arrValues = array_merge($arrValues, $arrPartVal);
                }
                break;
            default:
                break;
        }
    }

    /**
     * 検索結果の行数を取得する.
     *
     * @param  string  $where     検索条件の WHERE 句
     * @param  array   $arrValues 検索条件のパラメーター
     *
     * @return int 検索結果の行数
     */
    public function getNumberOfLines($where, $arrValues)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        return $objQuery->count('dtb_products', $where, $arrValues);
    }

    /**
     * 商品を検索する.
     *
     * @param  string     $where      検索条件の WHERE 句
     * @param  array      $arrValues  検索条件のパラメーター
     * @param  int    $limit      表示件数
     * @param  int    $offset     開始件数
     * @param  string     $order      検索結果の並び順
     * @param  SC_Product $objProduct SC_Product インスタンス
     *
     * @return array      商品の検索結果
     */
    public function findProducts($where, $arrValues, $limit, $offset, $order, &$objProduct)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        // 読み込む列とテーブルの指定
        $col = 'product_id, name, main_list_image, status, product_code_min, product_code_max, price02_min, price02_max, stock_min, stock_max, stock_unlimited_min, stock_unlimited_max, update_date';
        $from = $objProduct->alldtlSQL();

        $objQuery->setLimitOffset($limit, $offset);
        $objQuery->setOrder($order);

        return $objQuery->select($col, $from, $where, $arrValues);
    }
}
