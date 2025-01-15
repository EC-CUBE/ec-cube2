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
     * @return array()
     */
    public function registAddress($sqlval)
    {
        if (self::delivErrorCheck($sqlval)) {
            return false;
        }

        $objQuery = SC_Query_Ex::getSingletonInstance();
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
     * @return array()
     */
    public function getAddress($other_deliv_id, $customer_id = '')
    {
        if (self::delivErrorCheck(['customer_id' => $customer_id, 'other_deliv_id' => $other_deliv_id])) {
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
     * @return array
     */
    public function getList($customer_id, $startno = '')
    {
        if (self::delivErrorCheck(['customer_id' => $customer_id])) {
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
     * @return void
     */
    public function deleteAddress($other_deliv_id, $customer_id = '')
    {
        if (self::delivErrorCheck(['customer_id' => $customer_id, 'other_deliv_id' => $other_deliv_id])) {
            return false;
        }

        $objQuery = SC_Query_Ex::getSingletonInstance();

        $from = 'dtb_other_deliv';
        $where = 'customer_id = ? AND other_deliv_id = ?';
        $arrVal = [$customer_id, $other_deliv_id];

        return $objQuery->delete($from, $where, $arrVal);
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
     * @param array $arrParam
     *
     * @return bool / false
     */
    public function delivErrorCheck($arrParam)
    {
        // customer_id 要素は必須
        if (!isset($arrParam['customer_id'])) {
            return true; // エラー
        }

        if (!SC_Utils_Ex::isValidIntId($arrParam['customer_id'])) {
            return true; // エラー
        }

        // other_deliv_id 要素は任意だが、NULL・空文字はエラーと扱う。
        if (array_key_exists('other_deliv_id', $arrParam)) {
            if (!SC_Utils_Ex::isValidIntId($arrParam['other_deliv_id'])) {
                return true; // エラー
            }
        }

        return false; // 正常
    }
}
