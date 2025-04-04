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
 * 会員の登録配送先を管理するヘルパークラス.
 *
 * @author pineray
 *
 * @version $Id$
 */
class SC_Helper_Address
{
    /**
     * お届け先を登録
     *
     * @param  array   $sqlval
     *
     * @return bool 登録したか
     */
    public function registAddress($sqlval)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        if (!SC_Utils_Ex::isValidIntId($sqlval['customer_id'])) {
            return false;
        }

        $customer_id = $sqlval['customer_id'];
        $other_deliv_id = $sqlval['other_deliv_id'];

        // 追加
        if ((int) $other_deliv_id === 0) {
            // 別のお届け先最大登録数に達している場合、エラー
            $from = 'dtb_other_deliv';
            $where = 'customer_id = ?';
            $arrVal = [$customer_id];
            $deliv_count = $objQuery->count($from, $where, $arrVal);
            if ($deliv_count >= DELIV_ADDR_MAX) {
                return false;
            }

            // 別のお届け先を追加
            $sqlval['other_deliv_id'] = $objQuery->nextVal('dtb_other_deliv_other_deliv_id');
            $objQuery->insert($from, $sqlval);

        // 変更
        } else {
            $from = 'dtb_other_deliv';
            $where = 'customer_id = ? AND other_deliv_id = ?';
            $arrVal = [$customer_id, $other_deliv_id];
            $deliv_count = $objQuery->count($from, $where, $arrVal);
            if ($deliv_count != 1) {
                return false;
            }

            // 別のお届け先を変更
            $objQuery->update($from, $sqlval, $where, $arrVal);
        }

        return true;
    }

    /**
     * お届け先を取得
     *
     * @param int $other_deliv_id
     *
     * @return array|false|null 受け取り側は false を不正と判定している様子。しかし、型を比較せず、一致なしの null も同一視する箇所もありそう。
     */
    public function getAddress($other_deliv_id, $customer_id)
    {
        if (!SC_Utils_Ex::isValidIntId($other_deliv_id)) {
            return false;
        }
        if (!SC_Utils_Ex::isValidIntId($customer_id)) {
            return false;
        }

        $objQuery = SC_Query_Ex::getSingletonInstance();

        $col = '*';
        $from = 'dtb_other_deliv';
        $where = 'customer_id = ? AND other_deliv_id = ?';
        $arrVal = [$customer_id, $other_deliv_id];
        $address = $objQuery->getRow($col, $from, $where, $arrVal);

        return $address;
    }

    /**
     * お届け先の一覧を取得
     *
     * @param  int $customer_id
     * @param  int $startno
     *
     * @return array|false
     *     - XXX: 不正時に false を返しているが、受け取り側は考慮していなそう。
     */
    public function getList($customer_id, $startno = '')
    {
        if (!SC_Utils_Ex::isValidIntId($customer_id)) {
            return false;
        }

        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->setOrder('other_deliv_id DESC');
        // スマートフォン用の処理
        if ($startno != '') {
            $objQuery->setLimitOffset(SEARCH_PMAX, $startno);
        }

        $col = '*';
        $from = 'dtb_other_deliv';
        $where = 'customer_id = ?';
        $arrVal = [$customer_id];

        return $objQuery->select($col, $from, $where, $arrVal);
    }

    /**
     * お届け先の削除
     *
     * @return bool 削除したか。空削除も true を返す。
     */
    public function deleteAddress($other_deliv_id, $customer_id)
    {
        if (!SC_Utils_Ex::isValidIntId($other_deliv_id)) {
            return false;
        }
        if (!SC_Utils_Ex::isValidIntId($customer_id)) {
            return false;
        }

        $objQuery = SC_Query_Ex::getSingletonInstance();

        $from = 'dtb_other_deliv';
        $where = 'customer_id = ? AND other_deliv_id = ?';
        $arrVal = [$customer_id, $other_deliv_id];

        $objQuery->delete($from, $where, $arrVal);

        return true;
    }

    /**
     * お届け先フォーム初期化
     *
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     *
     * @return void
     */
    public function setFormParam(&$objFormParam)
    {
        SC_Helper_Customer_Ex::sfCustomerCommonParam($objFormParam);
        $objFormParam->addParam('', 'other_deliv_id');
    }

    /**
     * お届け先フォームエラーチェック
     *
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     *
     * @return void
     */
    public function errorCheck(&$objFormParam)
    {
        $objErr = SC_Helper_Customer_Ex::sfCustomerCommonErrorCheck($objFormParam);

        return $objErr->arrErr;
    }

    /**
     * お届け先エラーチェック
     *
     * @deprecated 本体で使用していない。
     *
     * @param array $arrParam
     *
     * @return bool / false
     */
    public function delivErrorCheck($arrParam)
    {
        $error_flg = false;

        if (is_null($arrParam['customer_id']) || !is_numeric($arrParam['customer_id']) || !preg_match("/^\d+$/", $arrParam['customer_id'])) {
            $error_flg = true;
        }

        if (isset($arrParam['other_deliv_id']) && $arrParam['other_deliv_id'] !== ''
            && (!is_numeric($arrParam['other_deliv_id']) || !preg_match("/^\d+$/", $arrParam['other_deliv_id']))) {
            $error_flg = true;
        }

        return $error_flg;
    }
}
