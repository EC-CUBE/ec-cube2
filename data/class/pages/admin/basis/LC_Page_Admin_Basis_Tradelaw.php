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
 * 特定商取引法 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_Basis_Tradelaw extends LC_Page_Admin_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'basis/tradelaw.tpl';
        $this->tpl_subno = 'tradelaw';
        $this->tpl_mainno = 'basis';
        $masterData = new SC_DB_MasterData_Ex();
        $this->arrPref = $masterData->getMasterData('mtb_pref');
        $this->arrTAXRULE = $masterData->getMasterData('mtb_taxrule');
        $this->tpl_maintitle = '基本情報管理';
        $this->tpl_subtitle = '特定商取引法';
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

        $this->tpl_mode = 'update'; // 旧バージョンテンプレート互換

        if (!empty($_POST)) {
            // 入力値の変換
            $objFormParam->convParam();
            $this->arrErr = $this->lfCheckError($objFormParam);

            if (count($this->arrErr) == 0) {
                switch ($this->getMode()) {
                    case 'update':
                        SC_Helper_DB_Ex::registerBasisData($objFormParam->getHashArray());

                        // 再表示
                        $this->tpl_onload = "window.alert('特定商取引法の登録が完了しました。');";

                        break;
                    default:
                        break;
                }
            }
        } else {
            $arrRet = $objDb->sfGetBasisData();
            $objFormParam->setParam($arrRet);
        }
        $this->arrForm = $objFormParam->getFormParamList();
    }

    /* パラメーター情報の初期化 */

    /**
     * @param SC_FormParam_Ex $objFormParam
     */
    public function lfInitParam(&$objFormParam)
    {
        $objFormParam->addParam('販売業者', 'law_company', STEXT_LEN, 'KVa', ['EXIST_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('運営責任者', 'law_manager', STEXT_LEN, 'KVa', ['EXIST_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('郵便番号1', 'law_zip01', ZIP01_LEN, 'n', ['EXIST_CHECK', 'NUM_CHECK', 'NUM_COUNT_CHECK']);
        $objFormParam->addParam('郵便番号2', 'law_zip02', ZIP02_LEN, 'n', ['EXIST_CHECK', 'NUM_CHECK', 'NUM_COUNT_CHECK']);
        $objFormParam->addParam('都道府県', 'law_pref', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('所在地1', 'law_addr01', MTEXT_LEN, 'KVa', ['EXIST_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('所在地2', 'law_addr02', MTEXT_LEN, 'KVa', ['EXIST_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('電話番号1', 'law_tel01', TEL_ITEM_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('電話番号2', 'law_tel02', TEL_ITEM_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('電話番号3', 'law_tel03', TEL_ITEM_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('FAX番号1', 'law_fax01', TEL_ITEM_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('FAX番号2', 'law_fax02', TEL_ITEM_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('FAX番号3', 'law_fax03', TEL_ITEM_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('メールアドレス', 'law_email', null, 'KVa', ['EXIST_CHECK', 'EMAIL_CHECK', 'EMAIL_CHAR_CHECK']);
        $objFormParam->addParam('URL', 'law_url', STEXT_LEN, 'KVa', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'URL_CHECK']);
        $objFormParam->addParam('必要料金', 'law_term01', MLTEXT_LEN, 'KVa', ['EXIST_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('注文方法', 'law_term02', MLTEXT_LEN, 'KVa', ['EXIST_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('支払方法', 'law_term03', MLTEXT_LEN, 'KVa', ['EXIST_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('支払期限', 'law_term04', MLTEXT_LEN, 'KVa', ['EXIST_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('引き渡し時期', 'law_term05', MLTEXT_LEN, 'KVa', ['EXIST_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('返品・交換について', 'law_term06', MLTEXT_LEN, 'KVa', ['EXIST_CHECK', 'MAX_LENGTH_CHECK']);
    }

    /**
     * @deprecated SC_Helper_DB_Ex::registerBasisData() を使う。
     */
    public function lfUpdateData($sqlval)
    {
        SC_Helper_DB_Ex::registerBasisData($sqlval);
    }

    /**
     * @deprecated SC_Helper_DB_Ex::registerBasisData() を使う。
     */
    public function lfInsertData($sqlval)
    {
        SC_Helper_DB_Ex::registerBasisData($sqlval);
    }

    /* 入力内容のチェック */

    /**
     * @param SC_FormParam_Ex $objFormParam
     */
    public function lfCheckError(&$objFormParam)
    {
        // 入力データを渡す。
        $arrRet = $objFormParam->getHashArray();
        $objErr = new SC_CheckError_Ex($arrRet);
        $objErr->arrErr = $objFormParam->checkError();

        // 電話番号チェック
        $objErr->doFunc(['TEL', 'law_tel01', 'law_tel02', 'law_tel03'], ['TEL_CHECK']);
        $objErr->doFunc(['FAX', 'law_fax01', 'law_fax02', 'law_fax03'], ['TEL_CHECK']);
        $objErr->doFunc(['郵便番号', 'law_zip01', 'law_zip02'], ['ALL_EXIST_CHECK']);

        return $objErr->arrErr;
    }
}
