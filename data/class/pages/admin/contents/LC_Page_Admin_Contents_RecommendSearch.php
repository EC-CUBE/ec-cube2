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
 * おすすめ商品管理 商品検索のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_Contents_RecommendSearch extends LC_Page_Admin_Ex
{
    /** @var int */
    public $rank;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainno = 'contents';
        $this->tpl_subno = '';

        $this->tpl_subtitle = '商品検索';
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
        $this->lfInitParam($objFormParam);
        $objFormParam->setParam($_POST);
        $objFormParam->convParam();

        $rank = (int) ($_GET['rank'] ?? 0);

        switch ($this->getMode()) {
            case 'search':
                // POST値の引き継ぎ
                $this->arrErr = $this->lfCheckError($objFormParam);
                $arrPost = $objFormParam->getHashArray();
                // 入力された値にエラーがない場合、検索処理を行う。
                // 検索結果の数に応じてページャの処理も入れる。
                if (SC_Utils_Ex::isBlank($this->arrErr)) {
                    $objProduct = new SC_Product_Ex();

                    $wheres = $this->createWhere($objFormParam, $objDb);
                    $this->tpl_linemax = $this->getLineCount($wheres, $objProduct);

                    $page_max = SC_Utils_Ex::sfGetSearchPageMax($arrPost['search_page_max'] ?? 0);

                    // ページ送りの取得
                    $objNavi = new SC_PageNavi_Ex($arrPost['search_pageno'], $this->tpl_linemax, $page_max, 'eccube.moveSearchPage', NAVI_PMAX);
                    $this->tpl_strnavi = $objNavi->strnavi;      // 表示文字列
                    $startno = $objNavi->start_row;

                    $arrProduct_id = $this->getProducts($wheres, $objProduct, $page_max, $startno);
                    $this->arrProducts = $this->getProductList($arrProduct_id, $objProduct);
                    $this->arrForm = $arrPost;
                }
                break;
            default:
                break;
        }

        // カテゴリ取得
        $this->arrCatList = $objDb->sfGetCategoryList();
        $this->rank = $rank;
        $this->setTemplate('contents/recommend_search.tpl');
    }

    /**
     * パラメーターの初期化を行う
     *
     * @param SC_FormParam_Ex $objFormParam
     */
    public function lfInitParam(&$objFormParam)
    {
        $objFormParam->addParam('商品ID', 'search_name', LTEXT_LEN, 'KVa', ['MAX_LENGTH_CHECK']);
        $objFormParam->addParam('商品ID', 'search_category_id', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('商品コード', 'search_product_code', LTEXT_LEN, 'KVa', ['MAX_LENGTH_CHECK']);
        $objFormParam->addParam('商品ステータス', 'search_status', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('ページ番号', 'search_pageno', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
    }

    /**
     * 入力されたパラメーターのエラーチェックを行う。
     *
     * @param  SC_FormParam_Ex $objFormParam
     *
     * @return array  エラー内容
     */
    public function lfCheckError(&$objFormParam)
    {
        $objErr = new SC_CheckError_Ex($objFormParam->getHashArray());
        $objErr->arrErr = $objFormParam->checkError();

        return $objErr->arrErr;
    }

    /**
     * POSTされた値からSQLのWHEREとBINDを配列で返す。
     *
     * @param  SC_FormParam $objFormParam
     * @param SC_Helper_DB_Ex $objDb
     *
     * @return array        ('where' => where string, 'bind' => databind array)
     */
    public function createWhere(&$objFormParam, &$objDb)
    {
        $arrForm = $objFormParam->getHashArray();
        $where = 'alldtl.del_flg = 0';
        $bind = [];
        foreach ($arrForm as $key => $val) {
            if ($val == '') {
                continue;
            }

            switch ($key) {
                case 'search_name':
                    $where .= ' AND name ILIKE ?';
                    $bind[] = '%'.$val.'%';
                    break;
                case 'search_category_id':
                    [$tmp_where, $tmp_bind] = $objDb->sfGetCatWhere($val);
                    if ($tmp_where != '') {
                        $where .= ' AND EXISTS (SELECT * FROM dtb_product_categories WHERE dtb_product_categories.product_id = alldtl.product_id AND '.$tmp_where.')';
                        $bind = array_merge((array) $bind, (array) $tmp_bind);
                    }
                    break;
                case 'search_product_code':
                    $where .= ' AND EXISTS (SELECT * FROM dtb_products_class WHERE dtb_products_class.product_id = alldtl.product_id AND product_code LIKE ?)';
                    $bind[] = '%'.$val.'%';
                    break;
                case 'search_status':
                    $where .= ' AND alldtl.status = ?';
                    $bind[] = $val;
                    break;
                default:
                    break;
            }
        }

        return [
            'where' => $where,
            'bind' => $bind,
        ];
    }

    /**
     * 検索結果対象となる商品の数を返す。
     *
     * @param array      $whereAndBind
     * @param SC_Product $objProduct
     */
    public function getLineCount($whereAndBind, &$objProduct)
    {
        $where = $whereAndBind['where'];
        $bind = $whereAndBind['bind'];
        // 検索結果対象となる商品の数を取得
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->setWhere($where);
        $linemax = $objProduct->findProductCount($objQuery, $bind);

        return $linemax;   // 何件が該当しました。表示用
    }

    /**
     * 検索結果の取得
     *
     * @param array      $whereAndBind string whereと array bindの連想配列
     * @param SC_Product $objProduct
     * @param int $page_max
     */
    public function getProducts($whereAndBind, &$objProduct, $page_max, $startno)
    {
        $where = $whereAndBind['where'];
        $bind = $whereAndBind['bind'];
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->setWhere($where);
        // 取得範囲の指定(開始行番号、行数のセット)
        $objQuery->setLimitOffset($page_max, $startno);

        // 検索結果の取得
        return $objProduct->findProductIdsOrder($objQuery, $bind);
    }

    /**
     * 商品取得
     *
     * @param array      $arrProductId
     * @param SC_Product $objProduct
     */
    public function getProductList($arrProductId, &$objProduct)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        // 表示順序
        $order = 'update_date DESC, product_id DESC';
        $objQuery->setOrder($order);

        return $objProduct->getListByProductIds($objQuery, $arrProductId);
    }
}
