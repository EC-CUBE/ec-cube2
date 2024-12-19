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
 * SC_Helper_Purchase::setShipmentItemTempForSole()のテストクラス.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Helper_Purchase_setShipmentItemTempForSoleTest extends SC_Helper_Purchase_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $_SESSION['testResult'] = null;
    }

    // ///////////////////////////////////////
    public function testSetShipmentItemTempForSoleいったん配送情報がクリアされたあと改めて指定のものが設定される()
    {
        $helper = new SC_Helper_Purchase_setShipmentItemTempForSoleMock();
        $cartSession = new SC_CartSession_setShipmentItemTempForSoleMock();
        $shipping_id = '1001';

        $helper->setShipmentItemTempForSole($cartSession, $shipping_id);

        $this->expected = [
            'clearShipmentItemTemp' => true,
            'shipmentItemTemp' => [
                ['shipping_id' => '1001', 'id' => '1', 'quantity' => '10'],
                ['shipping_id' => '1001', 'id' => '2', 'quantity' => '5'],
            ],
        ];
        $this->actual = $_SESSION['testResult'];

        $this->verify();
    }

    // ////////////////////////////////////////
}

class SC_Helper_Purchase_setShipmentItemTempForSoleMock extends SC_Helper_Purchase
{
    public function clearShipmentItemTemp($shipping_id = null)
    {
        $_SESSION['testResult']['clearShipmentItemTemp'] = true;
    }

    public function setShipmentItemTemp($shipping_id, $id, $quantity)
    {
        $_SESSION['testResult']['shipmentItemTemp'][] =
      ['shipping_id' => $shipping_id, 'id' => $id, 'quantity' => $quantity];
    }
}

class SC_CartSession_setShipmentItemTempForSoleMock extends SC_CartSession
{
    public function getCartList($key, $pref_id = 0, $country_id = 0)
    {
        return [
            ['id' => '1', 'quantity' => '10'],
            ['id' => '2', 'quantity' => '5'],
            ['id' => '3', 'quantity' => '0'],
        ];
    }
}
