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
 * ポイント設定 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_Basis_Point extends LC_Page_Admin_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'basis/point.tpl';
        $this->tpl_subno = 'point';
        $this->tpl_mainno = 'basis';
        $this->tpl_maintitle = '基本情報管理';
        $this->tpl_subtitle = 'ポイント設定';
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
        // パラメーター管理クラス
        $objFormParam = new SC_FormParam_Ex();
        // パラメーター情報の初期化
        $this->lfInitParam($objFormParam);
        // POST値の取得
        $objFormParam->setParam($_POST);

        $this->tpl_mode = 'update'; // 旧バージョンテンプレート互換

        if (!empty($_POST)) {
            // 入力値の変換
            $objFormParam->convParam();
            $this->arrErr = $objFormParam->checkError();

            if (count($this->arrErr) == 0) {
                switch ($this->getMode()) {
                    case 'update':
                        SC_Helper_DB_Ex::registerBasisData($objFormParam->getHashArray());

                        // 再表示
                        $this->tpl_onload = "window.alert('ポイント設定が完了しました。');";

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
        $objFormParam->addParam('ポイント付与率', 'point_rate', PERCENTAGE_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('会員登録時付与ポイント', 'welcome_point', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK']);
    }

    /**
     * @deprecated SC_Helper_DB_Ex::registerBasisData() を使う。
     */
    public function lfUpdateData($post)
    {
        SC_Helper_DB_Ex::registerBasisData($post);
    }

    /**
     * @deprecated SC_Helper_DB_Ex::registerBasisData() を使う。
     */
    public function lfInsertData($post)
    {
        SC_Helper_DB_Ex::registerBasisData($post);
    }
}
