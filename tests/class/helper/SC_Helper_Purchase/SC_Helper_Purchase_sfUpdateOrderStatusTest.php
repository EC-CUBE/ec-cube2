<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_Purchase/SC_Helper_Purchase_TestBase.php';
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
 * SC_Helper_Purchase::sfUpdateOrderStatus()のテストクラス.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Helper_Purchase_sfUpdateOrderStatusTest extends SC_Helper_Purchase_TestBase
{
    /** @var array */
    private $customer_ids = [];
    /** @var array */
    private $order_ids = [];
    private $helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer_ids = $this->setUpCustomer();
        $this->order_ids = $this->setUpOrder($this->customer_ids);
        $this->helper = new SC_Helper_Purchase_sfUpdateOrderStatusMock();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////
    // オプションの引数：対応状況、使用ポイント、加算ポイント
    public function testSfUpdateOrderStatusオプションの引数が未指定の場合DBの値が引き継がれる()
    {
        $order_id = $this->order_ids[0];
        $old_update_date = $this->objQuery->get('update_date', 'dtb_order', 'order_id = ?', [$order_id]);
        $customer_point = $this->objQuery->get('point', 'dtb_customer', 'customer_id = ?', [$this->customer_ids[0]]);

        SC_Helper_Purchase_sfUpdateOrderStatusMock::$usePoint = false;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::$addPoint = false;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::sfUpdateOrderStatus($order_id); // 引数は最低限だけ指定

        $this->expected = [
            'order' => [
                'status' => '3',
                'add_point' => '20',
                'use_point' => '10',
            ],
            'customer' => [
                'point' => $customer_point,
            ],
        ];
        $this->actual['order'] = array_shift($this->objQuery->select(
            'status, use_point, add_point',
            'dtb_order', 'order_id = ?', [$order_id]));
        $this->actual['customer'] = array_shift($this->objQuery->select(
            'point', 'dtb_customer', 'customer_id = ?', $this->customer_ids[0]));

        $this->verify();

        $update_date = $this->objQuery->get('update_date', 'dtb_order', 'order_id = ?', [$order_id]);
        $this->assertTrue($update_date > $old_update_date, '受注情報が更新されている');
    }

    // TODO 定数を変更できないためテスト不可
    /**
     * public function testSfUpdateOrderStatus_ポイント使用しない設定の場合_ポイントに関する処理が行われない()
     * {
     *
     * $this->verify();
     * }
     */
    public function testSfUpdateOrderStatus対応状況が発送済みに変更された場合発送日が更新される()
    {
        $order_id = $this->order_ids[0];
        $old_dates = $this->objQuery->select(
            'update_date, commit_date, payment_date',
            'dtb_order', 'order_id = ?', [$order_id]);
        $customer_point = $this->objQuery->get('point', 'dtb_customer', 'customer_id = ?', [$this->customer_ids[0]]);

        SC_Helper_Purchase_sfUpdateOrderStatusMock::$usePoint = false;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::$addPoint = false;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::sfUpdateOrderStatus($order_id, ORDER_DELIV, 50, 45);

        $this->expected = [
            'order' => [
                'status' => ORDER_DELIV,
                'add_point' => '50',  // 引数の設定どおりになる
                'use_point' => '45', // 引数の設定どおりになる
            ],
            'customer' => [
                'point' => $customer_point, // ポイントを使わない
            ],
        ];
        $this->actual['order'] = array_shift($this->objQuery->select(
            'status, use_point, add_point',
            'dtb_order', 'order_id = ?', [$order_id]));
        $this->actual['customer'] = array_shift($this->objQuery->select(
            'point', 'dtb_customer', 'customer_id = ?', $this->customer_ids[0]));

        $this->verify();

        $new_dates = $this->objQuery->select(
            'update_date, commit_date, payment_date',
            'dtb_order', 'order_id = ?', [$order_id]);
        $this->assertUpdate($new_dates, $old_dates, 'update_date', '受注情報');
        $this->assertUpdate($new_dates, $old_dates, 'commit_date', '発送日');
        $this->assertUpdate($new_dates, $old_dates, 'payment_date', '入金日', false);
    }

    public function testSfUpdateOrderStatus対応状況が入金済みに変更された場合入金日が更新される()
    {
        $order_id = $this->order_ids[1];
        $old_dates = $this->objQuery->select(
            'update_date, commit_date, payment_date',
            'dtb_order', 'order_id = ?', [$order_id]);

        SC_Helper_Purchase_sfUpdateOrderStatusMock::$usePoint = false;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::$addPoint = false;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::sfUpdateOrderStatus($order_id, ORDER_PRE_END, 50, 45);

        $this->expected = [
            'order' => [
                'status' => ORDER_PRE_END,
                'add_point' => '50',  // 引数の設定どおりになる
                'use_point' => '45', // 引数の設定どおりになる
            ],
        ];
        $this->actual['order'] = array_shift($this->objQuery->select(
            'status, use_point, add_point',
            'dtb_order', 'order_id = ?', [$order_id]));

        $this->verify();

        $new_dates = $this->objQuery->select(
            'update_date, commit_date, payment_date',
            'dtb_order', 'order_id = ?', [$order_id]);
        $this->assertUpdate($new_dates, $old_dates, 'update_date', '受注情報');
        $this->assertUpdate($new_dates, $old_dates, 'commit_date', '発送日', false);
        $this->assertUpdate($new_dates, $old_dates, 'payment_date', '入金日');
    }

    public function testSfUpdateOrderStatus変更前の対応状況が利用対象の場合変更前の使用ポイントを戻す()
    {
        $order_id = $this->order_ids[1];
        $old_dates = $this->objQuery->select(
            'update_date, commit_date, payment_date',
            'dtb_order', 'order_id = ?', [$order_id]);
        $customer_point = $this->objQuery->get('point', 'dtb_customer', 'customer_id = ?', [$this->customer_ids[1]]);

        SC_Helper_Purchase_sfUpdateOrderStatusMock::$usePoint = null;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::$addPoint = false; // 加算は強制的にfalseにしておく
        SC_Helper_Purchase_sfUpdateOrderStatusMock::sfUpdateOrderStatus($order_id, ORDER_CANCEL, 0, 45);

        $this->expected = [
            'order' => [
                'status' => ORDER_CANCEL,
                'add_point' => '0',  // 引数の設定どおりになる
                'use_point' => '45', // 引数の設定どおりになる
            ],
            'customer' => [
                'point' => '210', // 元々200pt+10pt戻す
            ],
        ];
        $this->actual['order'] = array_shift($this->objQuery->select(
            'status, use_point, add_point',
            'dtb_order', 'order_id = ?', [$order_id]));
        $this->actual['customer'] = array_shift($this->objQuery->select(
            'point', 'dtb_customer', 'customer_id = ?', [$this->customer_ids[1]]));

        $this->verify();
    }

    public function testSfUpdateOrderStatus変更後の対応状況が利用対象の場合変更後の使用ポイントを引く()
    {
        $order_id = $this->order_ids[0];
        $old_dates = $this->objQuery->select(
            'update_date, commit_date, payment_date',
            'dtb_order', 'order_id = ?', [$order_id]);

        SC_Helper_Purchase_sfUpdateOrderStatusMock::$usePoint = null;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::$addPoint = null;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::sfUpdateOrderStatus($order_id, ORDER_NEW, 50, 45);

        $this->expected = [
            'order' => [
                'status' => ORDER_NEW,
                'add_point' => '50',  // 引数の設定どおりになる
                'use_point' => '45', // 引数の設定どおりになる
            ],
            'customer' => [
                'point' => '55', // 元々100pt→45pt引く
            ],
        ];
        $this->actual['order'] = array_shift($this->objQuery->select(
            'status, use_point, add_point',
            'dtb_order', 'order_id = ?', [$order_id]));
        $this->actual['customer'] = array_shift($this->objQuery->select(
            'point', 'dtb_customer', 'customer_id = ?', [$this->customer_ids[0]]));

        $this->verify();
    }

    public function testSfUpdateOrderStatus変更前の対応状況が加算対象の場合変更前の加算ポイントを戻す()
    {
        $order_id = $this->order_ids[1];
        $old_dates = $this->objQuery->select(
            'update_date, commit_date, payment_date',
            'dtb_order', 'order_id = ?', [$order_id]);

        SC_Helper_Purchase_sfUpdateOrderStatusMock::$addPoint = null;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::$usePoint = false; // 使用対象は強制的にfalseにしておく
        SC_Helper_Purchase_sfUpdateOrderStatusMock::sfUpdateOrderStatus($order_id, ORDER_CANCEL, 50, 45);

        $this->expected = [
            'order' => [
                'status' => ORDER_CANCEL,
                'add_point' => '50',  // 引数の設定どおりになる
                'use_point' => '45', // 引数の設定どおりになる
            ],
            'customer' => [
                'point' => '180', // 元々200pt→20pt引く
            ],
        ];
        $this->actual['order'] = array_shift($this->objQuery->select(
            'status, use_point, add_point',
            'dtb_order', 'order_id = ?', [$order_id]));
        $this->actual['customer'] = array_shift($this->objQuery->select(
            'point', 'dtb_customer', 'customer_id = ?', [$this->customer_ids[1]]));

        $this->verify();
    }

    public function testSfUpdateOrderStatus変更後の対応状況が加算対象の場合変更後の加算ポイントを足す()
    {
        $order_id = $this->order_ids[0];
        $old_dates = $this->objQuery->select(
            'update_date, commit_date, payment_date',
            'dtb_order', 'order_id = ?', [$order_id]);

        SC_Helper_Purchase_sfUpdateOrderStatusMock::$addPoint = null;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::$usePoint = null;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::sfUpdateOrderStatus($order_id, ORDER_DELIV, 50, 0);

        $this->expected = [
            'order' => [
                'status' => ORDER_DELIV,
                'add_point' => '50',  // 引数の設定どおりになる
                'use_point' => '0', // 引数の設定どおりになる
            ],
            'customer' => [
                'point' => '150', // 元々100pt→50pt足す
            ],
        ];
        $this->actual['order'] = array_shift($this->objQuery->select(
            'status, use_point, add_point',
            'dtb_order', 'order_id = ?', [$order_id]));
        $this->actual['customer'] = array_shift($this->objQuery->select(
            'point', 'dtb_customer', 'customer_id = ?', [$this->customer_ids[0]]));

        $this->verify();
    }

    public function testSfUpdateOrderStatus加算ポイントがプラスの場合会員テーブルが更新される()
    {
        $order_id = $this->order_ids[0];
        $old_dates = $this->objQuery->select(
            'update_date, commit_date, payment_date',
            'dtb_order', 'order_id = ?', [$order_id]);

        SC_Helper_Purchase_sfUpdateOrderStatusMock::$usePoint = true;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::$addPoint = true;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::sfUpdateOrderStatus($order_id, ORDER_PRE_END, 40, 25);

        $this->expected = [
            'order' => [
                'status' => ORDER_PRE_END,
                'add_point' => '40',  // 引数の設定どおりになる
                'use_point' => '25', // 引数の設定どおりになる
            ],
            'customer' => [
                'point' => '105', // 変更前の状態で-10pt,変更後の状態で+15pt
            ],
        ];
        $this->actual['order'] = array_shift($this->objQuery->select(
            'status, use_point, add_point',
            'dtb_order', 'order_id = ?', [$order_id]));
        $this->actual['customer'] = array_shift($this->objQuery->select(
            'point', 'dtb_customer', 'customer_id = ?', $this->customer_ids[0]));

        $this->verify();
    }

    public function testSfUpdateOrderStatus加算ポイントが負でポイントが足りている場合会員テーブルが更新される()
    {
        $order_id = $this->order_ids[0];
        $old_dates = $this->objQuery->select(
            'update_date, commit_date, payment_date',
            'dtb_order', 'order_id = ?', [$order_id]);

        SC_Helper_Purchase_sfUpdateOrderStatusMock::$usePoint = true;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::$addPoint = true;
        SC_Helper_Purchase_sfUpdateOrderStatusMock::sfUpdateOrderStatus($order_id, ORDER_PRE_END, 0, 50);

        $this->expected = [
            'order' => [
                'status' => ORDER_PRE_END,
                'add_point' => '0',  // 引数の設定どおりになる
                'use_point' => '50', // 引数の設定どおりになる
            ],
            'customer' => [
                'point' => '40', // 変更前の状態で-10pt,変更後の状態で-50pt
            ],
        ];
        $this->actual['order'] = array_shift($this->objQuery->select(
            'status, use_point, add_point',
            'dtb_order', 'order_id = ?', [$order_id]));
        $this->actual['customer'] = array_shift($this->objQuery->select(
            'point', 'dtb_customer', 'customer_id = ?', $this->customer_ids[0]));
        $this->verify();
    }

    // TODO ロールバックされる場合はexitするためテスト不可.
    /**
     * public function testSfUpdateOrderStatus_加算ポイントが負でポイントが足りていない場合_会員テーブルがロールバックされエラーとなる()
     * {
     * }
     */

    // ////////////////////////////////////////

    public function assertUpdate($new_dates, $old_dates, $key, $message, $is_update = true)
    {
        $new_date = $new_dates[0][$key];
        $old_date = $old_dates[0][$key];
        if (empty($new_date)) {
            $new_date = '2000-01-01 00:00:00';
        }
        if (empty($old_date)) {
            $old_date = '2000-01-01 00:00:00';
        }

        if ($is_update) {
            $this->assertTrue($new_date > $old_date, $message.'が更新されている');
        } else {
            $this->assertEquals($new_date, $old_date, $message.'が更新されていない');
        }
    }
}

class SC_Helper_Purchase_sfUpdateOrderStatusMock extends SC_Helper_Purchase
{
    public static $usePoint;
    public static $addPoint;

    public static function isUsePoint($status)
    {
        if (static::$usePoint === null) {
            return parent::isUsePoint($status);
        }

        return static::$usePoint;
    }

    public static function isAddPoint($status)
    {
        if (static::$addPoint === null) {
            return parent::isAddPoint($status);
        }

        return static::$addPoint;
    }
}
