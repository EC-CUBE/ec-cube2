<?php

$HOME = realpath(dirname(__FILE__)) . "/../../../..";
require_once($HOME . "/tests/class/helper/SC_Helper_Purchase/SC_Helper_Purchase_TestBase.php");
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
 * SC_Helper_Purchase::getOrderTemp()のテストクラス.
 *
 *
 * @author Hiroko Tamagawa
 * @version $Id$
 */
class SC_Helper_Purchase_getOrderTempTest extends SC_Helper_Purchase_TestBase
{
  /** @var array */
  private $customer_ids = [];
  /** @var array */
  private $order_ids = [];
  /** @var array */
  private $order_temp_ids = [];

  protected function setUp()
  {
    parent::setUp();
    $this->customer_ids = $this->setUpCustomer();
    $this->order_ids = $this->setUpOrder($this->customer_ids);
    $this->order_temp_ids = $this->setUpOrderTemp($this->order_ids);
  }

  protected function tearDown()
  {
    parent::tearDown();
  }

  /////////////////////////////////////////
  public function testGetOrderTemp_存在しない受注IDを指定した場合_結果が空になる()
  {
    $order_id = '9999';

    $this->expected = null;
    $this->actual = SC_Helper_Purchase::getOrderTemp($order_id);

    $this->verify();
  }

  public function testGetOrderTemp_存在する受注IDを指定した場合_対応する結果が取得できる()
  {
    $order_temp_id = $this->order_temp_ids[0];
    $arrCustomer = $this->objQuery->getRow('*', 'dtb_customer', 'customer_id = ?', [$this->customer_ids[0]]);

    $this->expected = array(
      'order_temp_id' => $order_temp_id,
      'customer_id' => $this->customer_ids[0],
      'order_name01' => $arrCustomer['name01']
    );
    $result = SC_Helper_Purchase::getOrderTemp($order_temp_id);

    $this->actual = Test_Utils::mapArray($result, array('order_temp_id', 'customer_id', 'order_name01'));

    $this->verify();
  }

  //////////////////////////////////////////
}

