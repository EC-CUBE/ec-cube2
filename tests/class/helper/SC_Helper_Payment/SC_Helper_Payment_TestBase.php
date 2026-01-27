<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/Common_TestCase.php';
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
 * SC_Helper_Paymentのテストの基底クラス.
 */
class SC_Helper_Payment_TestBase extends Common_TestCase
{
    /** @var SC_Helper_Payment */
    protected $objHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objHelper = new SC_Helper_Payment_Ex();

        // テーブルをクリア（トランザクション内なので自動的にロールバックされる）
        $this->objQuery->delete('dtb_payment');
    }

    /**
     * テスト用の支払方法データを作成
     *
     * @param array $override 上書きするフィールド
     *
     * @return array
     */
    protected function createPaymentData($override = [])
    {
        // payment_idが指定されていない場合のみnextValを使用
        if (!isset($override['payment_id'])) {
            $payment_id = $this->objQuery->nextVal('dtb_payment_payment_id');
            $override['payment_id'] = $payment_id;
        } else {
            $payment_id = $override['payment_id'];
        }

        $data = array_merge([
            'payment_method' => 'テスト支払_'.$payment_id,
            'payment_image' => '',
            'charge' => 0,
            'rule_max' => null,
            'upper_rule' => null,
            'note' => '',
            'fix' => 1,
            'status' => 1,
            'del_flg' => 0,
            'creator_id' => 1,
            'rank' => 1,
            'charge_flg' => 1,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
        ], $override);

        $this->objQuery->insert('dtb_payment', $data);

        return $data;
    }
}
