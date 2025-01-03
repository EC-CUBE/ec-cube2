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
 * メーカー登録 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_Products_Maker extends LC_Page_Admin_Ex
{
    /** @var int */
    public $tpl_maker_id;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'products/maker.tpl';
        $this->tpl_subno = 'maker';
        $this->tpl_maintitle = '商品管理';
        $this->tpl_subtitle = 'メーカー登録';
        $this->tpl_mainno = 'products';
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
        $objMaker = new SC_Helper_Maker_Ex();
        $objFormParam = new SC_FormParam_Ex();

        // パラメーター情報の初期化
        $this->lfInitParam($objFormParam);

        // POST値をセット
        $objFormParam->setParam($_POST);

        // POST値の入力文字変換
        $objFormParam->convParam();

        // maker_idを変数にセット
        $maker_id = $objFormParam->getValue('maker_id');

        // モードによる処理切り替え
        switch ($this->getMode()) {
            // 編集処理
            case 'edit':
                // エラーチェック
                $this->arrErr = $this->lfCheckError($objFormParam, $objMaker);
                if (!SC_Utils_Ex::isBlank($this->arrErr['maker_id'])) {
                    trigger_error('', E_USER_ERROR);

                    return;
                }

                if (count($this->arrErr) <= 0) {
                    // POST値の引き継ぎ
                    $arrParam = $objFormParam->getHashArray();
                    // 登録実行
                    $res_maker_id = $this->doRegist($maker_id, $arrParam, $objMaker);
                    if ($res_maker_id !== false) {
                        // 完了メッセージ
                        $this->tpl_onload = "alert('登録が完了しました。');";
                        SC_Response_Ex::reload();
                    } else {
                        $this->arrErr['maker_id'] = '登録に失敗しました。';
                    }
                }
                break;

                // 編集前処理
            case 'pre_edit':
                $maker = $objMaker->getMaker($maker_id);
                $objFormParam->setParam($maker);

                // POSTデータを引き継ぐ
                $this->tpl_maker_id = $maker_id;
                break;

                // メーカー順変更
            case 'up':
                $objMaker->rankUp($maker_id);

                // リロード
                SC_Response_Ex::reload();
                break;

            case 'down':
                $objMaker->rankDown($maker_id);

                // リロード
                SC_Response_Ex::reload();
                break;

                // 削除
            case 'delete':
                $objMaker->delete($maker_id);

                // リロード
                SC_Response_Ex::reload();
                break;

            default:
                break;
        }

        $this->arrForm = $objFormParam->getFormParamList();

        // メーカー情報読み込み
        $this->arrMaker = $objMaker->getList();
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
        $objFormParam->addParam('メーカーID', 'maker_id', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('メーカー名', 'name', SMTEXT_LEN, 'KVa', ['EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
    }

    /**
     * 登録処理を実行.
     *
     * @param  int  $maker_id
     * @param  array    $sqlval
     * @param  SC_Helper_Maker_Ex   $objMaker
     *
     * @return multiple
     */
    public function doRegist($maker_id, $sqlval, SC_Helper_Maker_Ex $objMaker)
    {
        $sqlval['maker_id'] = $maker_id;
        $sqlval['creator_id'] = $_SESSION['member_id'];

        return $objMaker->saveMaker($sqlval);
    }

    /**
     * 入力エラーチェック.
     *
     * @param SC_FormParam $objFormParam
     *
     * @return array $objErr->arrErr エラー内容
     */
    public function lfCheckError(&$objFormParam, SC_Helper_Maker_Ex &$objMaker)
    {
        $arrErr = $objFormParam->checkError();
        $arrForm = $objFormParam->getHashArray();

        // maker_id の正当性チェック
        if (!empty($arrForm['maker_id'])) {
            if (!SC_Utils_Ex::sfIsInt($arrForm['maker_id'])
                || SC_Utils_Ex::sfIsZeroFilling($arrForm['maker_id'])
                || !$objMaker->getMaker($arrForm['maker_id'])
            ) {
                // maker_idが指定されていて、且つその値が不正と思われる場合はエラー
                $arrErr['maker_id'] = '※ メーカーIDが不正です<br />';
            }
        }
        if (!isset($arrErr['name'])) {
            $arrMaker = $objMaker->getByName($arrForm['name']);

            // 編集中のレコード以外に同じ名称が存在する場合
            if (
                !SC_Utils_Ex::isBlank($arrMaker)
                && $arrMaker['maker_id'] != $arrForm['maker_id']
                && $arrMaker['name'] == $arrForm['name']
            ) {
                $arrErr['name'] = '※ 既に同じ内容の登録が存在します。<br />';
            }
        }

        return $arrErr;
    }
}
