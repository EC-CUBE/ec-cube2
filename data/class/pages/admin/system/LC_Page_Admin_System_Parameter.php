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
 * パラメーター設定 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_System_Parameter extends LC_Page_Admin_Ex
{
    /** 定数キーとなる配列 */
    public $arrKeys;

    /** 定数コメントとなる配列 */
    public $arrComments;

    /** 定数値となる配列 */
    public $arrValues;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'system/parameter.tpl';
        $this->tpl_subno = 'parameter';
        $this->tpl_mainno = 'system';
        $this->tpl_maintitle = 'システム設定';
        $this->tpl_subtitle = 'パラメーター設定';
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
        $masterData = new SC_DB_MasterData_Ex();

        // キーの配列を生成
        $this->arrKeys = $this->getParamKeys($masterData);

        switch ($this->getMode()) {
            case 'update':
                // データの引き継ぎ
                $this->arrForm = $_POST;

                // エラーチェック
                $this->arrErr = $this->errorCheck($this->arrKeys, $this->arrForm);
                // エラーの無い場合は update
                if (empty($this->arrErr)) {
                    $this->update($this->arrKeys, $this->arrForm);
                    $this->tpl_onload = "window.alert('パラメーターの設定が完了しました。');";
                } else {
                    $this->arrValues = SC_Utils_Ex::getHash2Array($this->arrForm, $this->arrKeys);
                    $this->tpl_onload = "window.alert('エラーが発生しました。入力内容をご確認下さい。');";
                }
                break;
            default:
                break;
        }

        if (empty($this->arrErr)) {
            $this->arrValues = SC_Utils_Ex::getHash2Array($masterData->getDBMasterData('mtb_constants'));
        }

        // コメント, 値の配列を生成
        $this->arrComments = SC_Utils_Ex::getHash2Array($masterData->getDBMasterData(
            'mtb_constants',
            ['id', 'remarks', 'rank']
        ));
    }

    /**
     * パラメーター情報を更新する.
     *
     * 画面の設定値で mtb_constants テーブルの値とキャッシュを更新する.
     *
     * @return void
     */
    public function update(&$arrKeys, &$arrForm)
    {
        $data = [];
        $masterData = new SC_DB_MasterData_Ex();
        foreach ($arrKeys as $key) {
            $data[$key] = $arrForm[$key];
        }

        // DBのデータを更新
        $masterData->updateMasterData('mtb_constants', [], $data);

        // キャッシュを生成
        $masterData->createCache('mtb_constants', [], true, ['id', 'remarks']);
    }

    /**
     * エラーチェックを行う.
     *
     * @param  array $arrForm $_POST 値
     *
     * @return array
     */
    public function errorCheck(&$arrKeys, &$arrForm)
    {
        $objErr = new SC_CheckError_Ex($arrForm);
        foreach ($arrKeys as $key) {
            $objErr->doFunc([$key, $arrForm[$key]], ['EXIST_CHECK', 'EVAL_CHECK']);
        }

        return $objErr->arrErr;
    }

    /**
     * パラメーターのキーを配列で返す.
     *
     * @param SC_DB_MasterData_Ex $masterData
     *
     * @return array パラメーターのキーの配列
     */
    public function getParamKeys(&$masterData)
    {
        $keys = [];
        $i = 0;
        foreach ($masterData->getDBMasterData('mtb_constants') as $key => $val) {
            $keys[$i] = $key;
            $i++;
        }

        return $keys;
    }
}
