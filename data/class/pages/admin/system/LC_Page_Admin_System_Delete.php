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
 * メンバー削除 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_System_Delete extends LC_Page_Admin_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
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
        if ($this->getMode() !== 'delete') {
            SC_Utils_Ex::sfDispError(INVALID_MOVE_ERRORR);
            SC_Response_Ex::actionExit();
        }
        $objFormParam = new SC_FormParam_Ex();

        // パラメーターの初期化
        $this->initParam($objFormParam, $_GET);

        $id = 0;
        // パラメーターの検証
        if ($objFormParam->checkError()
            || !SC_Utils_Ex::sfIsInt($id = $objFormParam->getValue('id'))) {
            GC_Utils_Ex::gfPrintLog("error id=$id");
            SC_Utils_Ex::sfDispError(INVALID_MOVE_ERRORR);
        }

        $id = $objFormParam->getValue('id');

        // レコードの削除
        $this->deleteMember($id);

        // リダイレクト
        $url = $this->getLocation(ADMIN_SYSTEM_URLPATH)
             .'?pageno='.$objFormParam->getValue('pageno');

        SC_Response_Ex::sendRedirect($url);
    }

    /**
     * パラメーター初期化.
     *
     * @param  SC_FormParam_Ex $objFormParam
     * @param  array  $arrParams    $_GET値
     *
     * @return void
     */
    public function initParam(&$objFormParam, &$arrParams)
    {
        $objFormParam->addParam('pageno', 'pageno', INT_LEN, '', ['NUM_CHECK', 'MAX_LENGTH_CHECK', 'EXIST_CHECK']);
        $objFormParam->addParam('id', 'id', INT_LEN, '', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->setParam($arrParams);
    }

    /**
     * メンバー情報削除の為の制御.
     *
     * @param  int $id 削除対象のmember_id
     *
     * @return void
     */
    public function deleteMember($id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();

        $this->renumberRank($objQuery, $id);
        $this->deleteRecode($objQuery, $id);

        $objQuery->commit();
    }

    /**
     * ランキングの振り直し.
     *
     * @param  SC_Query      $objQuery
     * @param  int     $id       削除対象のmember_id
     *
     * @return void|UPDATE の結果フラグ
     */
    public function renumberRank(&$objQuery, $id)
    {
        // ランクの取得
        $where1 = 'member_id = ?';
        $rank = $objQuery->get('rank', 'dtb_member', $where1, [$id]);

        // Updateする値を作成する.
        $where2 = 'rank > ? AND del_flg <> 1';

        // UPDATEの実行 - 削除したレコードより上のランキングを下げてRANKの空きを埋める。
        return $objQuery->update('dtb_member', [], $where2, [$rank], ['rank' => 'rank-1']);
    }

    /**
     * レコードの削除(削除フラグをONにする).
     *
     * @param  SC_Query      $objQuery
     * @param  int     $id       削除対象のmember_id
     *
     * @return void|UPDATE の結果フラグ
     */
    public function deleteRecode(&$objQuery, $id)
    {
        // Updateする値を作成する.
        $sqlVal = [];
        $sqlVal['rank'] = 0;
        $sqlVal['del_flg'] = 1;
        $where = 'member_id = ?';

        // UPDATEの実行 - ランクを最下位にする、DELフラグON
        return $objQuery->update('dtb_member', $sqlVal, $where, [$id]);
    }
}
