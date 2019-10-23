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

require_once CLASS_EX_REALDIR . 'page_extends/admin/LC_Page_Admin_Ex.php';

/**
 * 定休日管理のページクラス.
 *
 * @package Page
 * @author EC-CUBE CO.,LTD.
 * @version $Id$
 */
class LC_Page_Admin_Basis_Holiday extends LC_Page_Admin_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'basis/holiday.tpl';
        $this->tpl_subno = 'holiday';
        $this->tpl_maintitle = '基本情報管理';
        $this->tpl_subtitle = '定休日管理';
        $this->tpl_mainno = 'basis';
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
        $objHoliday = new SC_Helper_Holiday_Ex();

        $objDate = new SC_Date_Ex();
        $this->arrMonth = $objDate->getMonth();
        $this->arrDay = $objDate->getDay();

        $mode = $this->getMode();

        $objFormParam = new SC_FormParam_Ex();
        $this->lfInitParam($mode, $objFormParam);
        $objFormParam->setParam($_POST);
        $objFormParam->convParam();

        $holiday_id = $objFormParam->getValue('holiday_id');

        // 要求判定
        switch ($mode) {
            // 編集処理
            case 'edit':
                $this->arrErr = $this->lfCheckError($objFormParam, $objHoliday);
                if (!SC_Utils_Ex::isBlank($this->arrErr['holiday_id'])) {
                    trigger_error('', E_USER_ERROR);

                    return;
                }

                if (count($this->arrErr) <= 0) {
                    // POST値の引き継ぎ
                    $arrParam = $objFormParam->getHashArray();
                    // 登録実行
                    $res_holiday_id = $this->doRegist($holiday_id, $arrParam, $objHoliday);
                    if ($res_holiday_id !== FALSE) {
                        // 完了メッセージ
                        $holiday_id = $res_holiday_id;
                        $this->tpl_onload = "alert('登録が完了しました。');";
                    }
                }
                // POSTデータを引き継ぐ
                $this->tpl_holiday_id = $holiday_id;

                break;
            // 削除
            case 'delete':
                $objHoliday->delete($holiday_id);
                break;
            // 編集前処理
            case 'pre_edit':
                // 編集項目を取得する。
                $arrHolidayData = $objHoliday->get($holiday_id);
                $objFormParam->setParam($arrHolidayData);

                // POSTデータを引き継ぐ
                $this->tpl_holiday_id = $holiday_id;
                break;
            case 'down':
                $objHoliday->rankDown($holiday_id);

                // 再表示
                $this->objDisplay->reload();
                break;
            case 'up':
                $objHoliday->rankUp($holiday_id);

                // 再表示
                $this->objDisplay->reload();
                break;
            default:
                break;
        }

        $this->arrForm = $objFormParam->getFormParamList();

        $this->arrHoliday = $objHoliday->getList();
    }

    /**
     * 登録処理を実行.
     *
     * @param  integer  $holiday_id
     * @param  array    $sqlval
     * @param  SC_Helper_Holiday_Ex   $objHoliday
     * @return multiple
     */
    public function doRegist($holiday_id, $sqlval, SC_Helper_Holiday_Ex $objHoliday)
    {
        $sqlval['holiday_id'] = $holiday_id;
        $sqlval['creator_id'] = $_SESSION['member_id'];

        return $objHoliday->save($sqlval);
    }

    /**
     * @param string|null $mode
     * @param SC_FormParam_Ex $objFormParam
     */
    public function lfInitParam($mode, &$objFormParam)
    {
        switch ($mode) {
            case 'edit':
            case 'pre_edit':
                $objFormParam->addParam('タイトル', 'title', STEXT_LEN, 'KVa', array('EXIST_CHECK','SPTAB_CHECK','MAX_LENGTH_CHECK'));
                $objFormParam->addParam('月', 'month', INT_LEN, 'n', array('SELECT_CHECK','SPTAB_CHECK','MAX_LENGTH_CHECK'));
                $objFormParam->addParam('日', 'day', INT_LEN, 'n', array('SELECT_CHECK','SPTAB_CHECK','MAX_LENGTH_CHECK'));
                $objFormParam->addParam('定休日ID', 'holiday_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
                break;
            case 'delete':
            case 'down':
            case 'up':
            default:
                $objFormParam->addParam('定休日ID', 'holiday_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
                break;
        }
    }

    /**
     * 入力エラーチェック
     *
     * @param  SC_FormParam_Ex $objFormParam
     * @param  SC_Helper_Holiday_Ex $objHoliday
     * @return array
     */
    public function lfCheckError(&$objFormParam, SC_Helper_Holiday_Ex &$objHoliday)
    {
        $arrErr = $objFormParam->checkError();
        $arrForm = $objFormParam->getHashArray();

        // 日付の妥当性チェック
        // 閏年への対応.
        if ($arrForm['month'] == 2 && $arrForm['day'] == 29) {
            $valid_date = true;
        } else {
            $valid_date = checkdate($arrForm['month'], $arrForm['day'], date('Y'));
        }
        if (!$valid_date) {
            $arrErr['date'] = '※ 妥当な日付ではありません。<br />';
        }

        // 編集中のレコード以外に同じ日付が存在する場合
        if ($objHoliday->isDateExist($arrForm['month'], $arrForm['day'], $arrForm['holiday_id'])) {
            $arrErr['date'] = '※ 既に同じ日付の登録が存在します。<br />';
        }

        return $arrErr;
    }
}
