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
 * マスターデータ管理 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_System_Masterdata extends LC_Page_Admin_Ex
{
    /** @var array */
    public $arrMasterDataName;
    /** @var string */
    public $masterDataName;
    /** @var string */
    public $errorMessage;
    /** @var array */
    public $arrMasterData;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'system/masterdata.tpl';
        $this->tpl_subno = 'masterdata';
        $this->tpl_mainno = 'system';
        $this->tpl_maintitle = 'システム設定';
        $this->tpl_subtitle = 'マスターデータ管理';
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
        $this->arrMasterDataName = $this->getMasterDataNames(['mtb_pref', 'mtb_zip', 'mtb_constants']);
        $masterData = new SC_DB_MasterData_Ex();

        switch ($this->getMode()) {
            case 'edit':
                // POST 文字列の妥当性チェック
                $this->masterDataName = $this->checkMasterDataName($_POST, $this->arrMasterDataName);
                $this->errorMessage = $this->checkUniqueID($_POST);

                if (empty($this->errorMessage)) {
                    // 取得したデータからマスターデータを生成
                    $this->registMasterData($_POST, $masterData, $this->masterDataName);
                    $this->tpl_onload = "window.alert('マスターデータの設定が完了しました。');";
                }
                // FIXME break 入れ忘れと思われる。そうでないなら、要コメント。

                // no break
            case 'show':
                // POST 文字列の妥当性チェック
                $this->masterDataName = $this->checkMasterDataName($_POST, $this->arrMasterDataName);

                // DB からマスターデータを取得
                $this->arrMasterData =
                    $masterData->getDbMasterData($this->masterDataName);
                break;

            default:
                break;
        }
    }

    /**
     * マスターデータ名チェックを行う
     *
     * @param  array  $arrMasterDataName マスターデータテーブル名のリスト
     *
     * @return string $master_data_name 選択しているマスターデータのテーブル名
     */
    public function checkMasterDataName(&$arrParams, &$arrMasterDataName)
    {
        if (in_array($arrParams['master_data_name'], $arrMasterDataName)) {
            $master_data_name = $arrParams['master_data_name'];

            return $master_data_name;
        } else {
            SC_Utils_Ex::sfDispError('');
        }

        return null;
    }

    /**
     * マスターデータ名を配列で取得する.
     *
     * @param  string[] $ignores 取得しないマスターデータ名の配列
     *
     * @return array マスターデータ名の配列
     */
    public function getMasterDataNames($ignores = [])
    {
        $dbFactory = SC_DB_DBFactory_Ex::getInstance();
        $arrMasterDataName = $dbFactory->findTableNames('mtb_');

        $i = 0;
        foreach ($arrMasterDataName as $val) {
            foreach ($ignores as $ignore) {
                if ($val == $ignore) {
                    unset($arrMasterDataName[$i]);
                }
            }
            $i++;
        }

        return $arrMasterDataName;
    }

    /**
     * ID の値がユニークかチェックする.
     *
     * 重複した値が存在する場合はエラーメッセージを表示する.
     *
     * @return void|string エラーが発生した場合はエラーメッセージを返す.
     */
    public function checkUniqueID(&$arrParams)
    {
        $arrId = $arrParams['id'];
        for ($i = 0; $i < count($arrId); $i++) {
            $id = $arrId[$i];
            // 空の値は無視
            if ($arrId[$i] != '') {
                for ($j = $i + 1; $j < count($arrId); $j++) {
                    if ($id == $arrId[$j]) {
                        return $id.' が重複しているため登録できません.';
                    }
                }
            }
        }
    }

    /**
     * マスターデータの登録.
     *
     * @param  array  $arrParams        $_POST値
     * @param  SC_DB_MasterData_Ex $masterData       SC_DB_MasterData_Ex()
     * @param  string $master_data_name 登録対象のマスターデータのテーブル名
     *
     * @return void
     */
    public function registMasterData($arrParams, &$masterData, $master_data_name)
    {
        $arrTmp = [];
        foreach ($arrParams['id'] as $key => $val) {
            // ID が空のデータは生成しない
            if ($val != '') {
                $arrTmp[$val] = $arrParams['name'][$key];
            }
        }

        // マスターデータを更新
        $masterData->objQuery = SC_Query_Ex::getSingletonInstance();
        $masterData->objQuery->begin();
        $masterData->deleteMasterData($master_data_name, false);
        // TODO カラム名はメタデータから取得した方が良い
        $masterData->registMasterData($master_data_name, ['id', 'name', 'rank'], $arrTmp, false);
        $masterData->objQuery->commit();
    }
}
