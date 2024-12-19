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
 * SC_Helper_Purchase::rollbackOrder()のテストクラス.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Helper_Purchase_rollbackOrderTest extends SC_Helper_Purchase_TestBase
{
    private $helper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->helper = new SC_Helper_Purchase_rollbackOrderMock();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////
    public function testRollbackOrderデフォルトの引数で呼び出した場合カートの状態をロールバックして元に戻る()
    {
        $this->objQuery->begin();
        $order_id = '1001';

        $uniqid = $this->helper->rollbackOrder($order_id);

        $this->actual['testResult'] = $_SESSION['testResult'];
        $this->actual['siteRegist'] = $_SESSION['site']['regist_success'];
        $this->expected = [
            'testResult' => [
                'cancelOrder' => [
                    'order_id' => '1001',
                    'orderStatus' => ORDER_CANCEL,
                    'is_delete' => false,
                ],
                'getOrderTempByOrderId' => [
                    'order_id' => '1001',
                ],
                'saveOrderTemp' => [
                    'uniqid' => $uniqid,
                    'arrOrderTemp' => [
                        'customer_id' => '2001',
                        'del_flg' => '0',
                    ],
                ],
                'verifyChangeCart' => [
                    'uniqid' => $uniqid,
                ],
            ],
            'siteRegist' => true,
        ];
        $this->verify();
    }

    /**
     * 実際にトランザクションを開始したかどうかはテストできないが、
     * 問題なく処理が完了することのみ確認
     */
    public function testRollbackOrderトランザクションが開始していない場合内部で開始する()
    {
        $order_id = '1001';

        $uniqid = $this->helper->rollbackOrder($order_id, ORDER_DELIV, true);

        $this->actual['testResult'] = $_SESSION['testResult'];
        $this->actual['siteRegist'] = $_SESSION['site']['regist_success'];
        $this->expected = [
            'testResult' => [
                'cancelOrder' => [
                    'order_id' => '1001',
                    'orderStatus' => ORDER_DELIV,
                    'is_delete' => true,
                ],
                'getOrderTempByOrderId' => [
                    'order_id' => '1001',
                ],
                'saveOrderTemp' => [
                    'uniqid' => $uniqid,
                    'arrOrderTemp' => [
                        'customer_id' => '2001',
                        'del_flg' => '0',
                    ],
                ],
                'verifyChangeCart' => [
                    'uniqid' => $uniqid,
                ],
            ],
            'siteRegist' => true,
        ];
        $this->verify();
    }
    // ////////////////////////////////////////
}

class SC_Helper_Purchase_rollbackOrderMock extends SC_Helper_Purchase
{
    public $testResult = [];

    public static function cancelOrder($order_id, $orderStatus = ORDER_CANCEL, $is_delete = false)
    {
        $_SESSION['testResult']['cancelOrder'] = [
            'order_id' => $order_id,
            'orderStatus' => $orderStatus,
            'is_delete' => $is_delete,
        ];
    }

    public static function getOrderTempByOrderId($order_id)
    {
        $_SESSION['testResult']['getOrderTempByOrderId'] = [
            'order_id' => $order_id,
        ];

        return [
            'customer_id' => '2001',
        ];
    }

    public static function saveOrderTemp($uniqid, $arrOrderTemp, &$objCustomer = null)
    {
        $_SESSION['testResult']['saveOrderTemp'] = [
            'uniqid' => $uniqid,
            'arrOrderTemp' => $arrOrderTemp,
        ];
    }

    public static function verifyChangeCart($uniqid, &$objCartSession)
    {
        $_SESSION['testResult']['verifyChangeCart'] = [
            'uniqid' => $uniqid,
        ];
    }
}
