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
 * SC_Helper_Deliveryのテストの基底クラス.
 */
class SC_Helper_Delivery_TestBase extends Common_TestCase
{
    /** @var SC_Helper_Delivery */
    protected $objHelper;

    /** @var array テスト前のデータバックアップ */
    protected $backupData = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->objHelper = new SC_Helper_Delivery_Ex();

        // テストで変更される可能性のあるテーブルをバックアップ
        $this->backupData['dtb_deliv'] = $this->objQuery->select('*', 'dtb_deliv');
        $this->backupData['dtb_delivtime'] = $this->objQuery->select('*', 'dtb_delivtime');
        $this->backupData['dtb_delivfee'] = $this->objQuery->select('*', 'dtb_delivfee');
        $this->backupData['dtb_payment_options'] = $this->objQuery->select('*', 'dtb_payment_options');

        // テーブルをクリア
        $this->objQuery->delete('dtb_payment_options');
        $this->objQuery->delete('dtb_delivfee');
        $this->objQuery->delete('dtb_delivtime');
        $this->objQuery->delete('dtb_deliv');
    }

    protected function tearDown(): void
    {
        // バックアップからデータを復元
        $this->objQuery->delete('dtb_payment_options');
        foreach ($this->backupData['dtb_payment_options'] as $row) {
            $this->objQuery->insert('dtb_payment_options', $row);
        }

        $this->objQuery->delete('dtb_delivfee');
        foreach ($this->backupData['dtb_delivfee'] as $row) {
            $this->objQuery->insert('dtb_delivfee', $row);
        }

        $this->objQuery->delete('dtb_delivtime');
        foreach ($this->backupData['dtb_delivtime'] as $row) {
            $this->objQuery->insert('dtb_delivtime', $row);
        }

        $this->objQuery->delete('dtb_deliv');
        foreach ($this->backupData['dtb_deliv'] as $row) {
            $this->objQuery->insert('dtb_deliv', $row);
        }

        parent::tearDown();
    }

    /**
     * テスト用の配送方法データを作成
     *
     * @param array $override 上書きするフィールド
     * @return array
     */
    protected function createDelivData($override = [])
    {
        // deliv_idが指定されていない場合のみnextValを使用
        if (!isset($override['deliv_id'])) {
            $deliv_id = $this->objQuery->nextVal('dtb_deliv_deliv_id');
            $override['deliv_id'] = $deliv_id;
        } else {
            $deliv_id = $override['deliv_id'];
        }

        $data = array_merge([
            'product_type_id' => 1,
            'service_name' => 'テスト配送_'.$deliv_id,
            'confirm_url' => '',
            'rank' => 1,
            'status' => 1,
            'del_flg' => 0,
            'creator_id' => 1,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
        ], $override);

        $this->objQuery->insert('dtb_deliv', $data);

        return $data;
    }

    /**
     * テスト用の配送時間データを作成
     *
     * @param int $deliv_id 配送ID
     * @param array $delivTimes 配送時間の配列（time_id => deliv_time）
     */
    protected function createDelivTimeData($deliv_id, $delivTimes = [])
    {
        if (empty($delivTimes)) {
            $delivTimes = [
                1 => '午前中',
                2 => '12-14時',
                3 => '14-16時',
            ];
        }

        foreach ($delivTimes as $time_id => $deliv_time) {
            $this->objQuery->insert('dtb_delivtime', [
                'deliv_id' => $deliv_id,
                'time_id' => $time_id,
                'deliv_time' => $deliv_time,
            ]);
        }
    }

    /**
     * テスト用の配送料金データを作成
     *
     * @param int $deliv_id 配送ID
     * @param array $delivFees 配送料金の配列（pref => fee）
     */
    protected function createDelivFeeData($deliv_id, $delivFees = [])
    {
        if (empty($delivFees)) {
            // デフォルトで東京(13)と大阪(27)の配送料を設定
            $delivFees = [
                13 => 500,  // 東京
                27 => 800,  // 大阪
            ];
        }

        $fee_id = 1;
        foreach ($delivFees as $pref => $fee) {
            $this->objQuery->insert('dtb_delivfee', [
                'deliv_id' => $deliv_id,
                'fee_id' => $fee_id++,
                'pref' => $pref,
                'fee' => $fee,
            ]);
        }
    }

    /**
     * テスト用の支払方法オプションデータを作成
     *
     * @param int $deliv_id 配送ID
     * @param array $payment_ids 支払方法IDの配列
     */
    protected function createPaymentOptionsData($deliv_id, $payment_ids = [])
    {
        if (empty($payment_ids)) {
            $payment_ids = [1, 2];  // デフォルトで支払方法1と2を設定
        }

        $rank = 1;
        foreach ($payment_ids as $payment_id) {
            $this->objQuery->insert('dtb_payment_options', [
                'deliv_id' => $deliv_id,
                'payment_id' => $payment_id,
                'rank' => $rank++,
            ]);
        }
    }
}
