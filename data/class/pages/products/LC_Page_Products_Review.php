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
 * お客様の声投稿のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Products_Review extends LC_Page_Ex
{
    /** おすすめレベル */
    public $arrRECOMMEND;

    /** 性別 */
    public $arrSex;

    /** 入力禁止URL */
    public $arrReviewDenyURL;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        $this->skip_load_page_layout = true;
        parent::init();

        $masterData = new SC_DB_MasterData_Ex();
        $this->arrRECOMMEND = $masterData->getMasterData('mtb_recommend');
        $this->arrSex = $masterData->getMasterData('mtb_sex');
        $this->arrReviewDenyURL = $masterData->getMasterData('mtb_review_deny_url');
        $this->tpl_mainpage = 'products/review.tpl';
        $this->httpCacheControl('nocache');
    }

    /**
     * Page のプロセス.
     */
    public function process()
    {
        parent::process();
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のAction.
     *
     * @return void
     */
    public function action()
    {
        $objFormParam = new SC_FormParam_Ex();
        $this->lfInitParam($objFormParam);
        $objFormParam->setParam($_POST);
        $objFormParam->convParam();

        switch ($this->getMode()) {
            case 'confirm':
                $this->arrErr = $this->lfCheckError($objFormParam);

                // エラーチェック
                if (empty($this->arrErr)) {
                    $this->tpl_mainpage = 'products/review_confirm.tpl';
                }
                break;

            case 'return':
                break;

            case 'complete':
                $this->arrErr = $this->lfCheckError($objFormParam);
                // エラーチェック
                if (empty($this->arrErr)) {
                    // 登録実行
                    $this->lfRegistRecommendData($objFormParam);

                    // レビュー書き込み完了ページへ
                    SC_Response_Ex::sendRedirect('review_complete.php');
                    SC_Response_Ex::actionExit();
                }
                break;

            default:
                // 最初のproduct_idは、$_GETで渡ってくる。
                $objFormParam->setParam($_GET);
                break;
        }

        $this->arrForm = $objFormParam->getHashArray();

        // 商品名の取得
        $this->arrForm['name'] = $this->lfGetProductName($this->arrForm['product_id']);
        if (empty($this->arrForm['name'])) {
            SC_Utils_Ex::sfDispSiteError(PRODUCT_NOT_FOUND);
        }

        $this->setTemplate($this->tpl_mainpage);
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
        $objFormParam->addParam('レビューID', 'review_id', INT_LEN, 'aKV');
        $objFormParam->addParam('商品ID', 'product_id', INT_LEN, 'n', ['NUM_CHECK', 'EXIST_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('投稿者名', 'reviewer_name', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('投稿者URL', 'reviewer_url', MTEXT_LEN, 'a', ['NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK', 'URL_CHECK']);
        $objFormParam->addParam('性別', 'sex', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('おすすめレベル', 'recommend_level', INT_LEN, 'n', ['EXIST_CHECK', 'SELECT_CHECK']);
        $objFormParam->addParam('タイトル', 'title', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('コメント', 'comment', LTEXT_LEN, 'aKV', ['EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
    }

    /**
     * 入力内容のチェックを行う.
     *
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     *
     * @return array        エラーメッセージの配列
     */
    public function lfCheckError(&$objFormParam)
    {
        $arrErr = $objFormParam->checkError();

        if (REVIEW_ALLOW_URL == false) {
            $objErr = new SC_CheckError_Ex($objFormParam->getHashArray());
            // コメント欄へのURLの入力を禁止
            $objErr->doFunc(['URL', 'comment', $this->arrReviewDenyURL], ['PROHIBITED_STR_CHECK']);
            $arrErr += $objErr->arrErr;
        }

        return $arrErr;
    }

    /**
     * 商品名を取得
     *
     * @param  int $product_id 商品ID
     *
     * @return string  $product_name 商品名
     */
    public function lfGetProductName($product_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        return $objQuery->get('name', 'dtb_products', 'product_id = ? AND '.SC_Product_Ex::getProductDispConditions(), [$product_id]);
    }

    // 登録実行
    public function lfRegistRecommendData(&$objFormParam)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objCustomer = new SC_Customer_Ex();

        $arrRegist = $objFormParam->getDbArray();

        $arrRegist['create_date'] = 'CURRENT_TIMESTAMP';
        $arrRegist['update_date'] = 'CURRENT_TIMESTAMP';
        $arrRegist['creator_id'] = '0';

        if ($objCustomer->isLoginSuccess(true)) {
            $arrRegist['customer_id'] = $objCustomer->getValue('customer_id');
        }

        // -- 登録実行
        $objQuery->begin();
        $arrRegist['review_id'] = $objQuery->nextVal('dtb_review_review_id');
        $objQuery->insert('dtb_review', $arrRegist);
        $objQuery->commit();
    }
}
