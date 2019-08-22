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
 * SC_Helper_Purchase::sfUpdateOrderNameCol()のテストクラス.
 *
 *
 * @author Hiroko Tamagawa
 * @version $Id$
 */
class SC_Helper_Purchase_sfUpdateOrderNameColTest extends SC_Helper_Purchase_TestBase
{
  var $helper;
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

    $this->helper = new SC_Helper_Purchase();
  }

  protected function tearDown()
  {
    parent::tearDown();
  }

  /////////////////////////////////////////
  public function testSfUpdateOrderNameCol_TEMPフラグがOFFの場合_受注テーブルと発送テーブルが更新される()
  {
    $order_id = $this->order_ids[1];
    $this->helper->saveOrderTemp($this->order_temp_ids[1], ['payment_method' => '支払方法0002']);
    $arrOrder = $this->objQuery->getRow('*', 'dtb_order', 'order_id = ?', [$order_id]);
    $arrShipping = $this->objQuery->getRow('*', 'dtb_shipping', 'order_id = ? AND shipping_id = 0', [$order_id]);
    $arrDelivTime = $this->objQuery->getRow('*', 'dtb_delivtime', 'deliv_id = ? AND time_id = ?', [$arrOrder['deliv_id'], $arrShipping['time_id']]);
    $arrPayment = $this->objQuery->getRow('*', 'dtb_payment', 'payment_id = ?', [$arrOrder['payment_id']]);

    $this->helper->sfUpdateOrderNameCol($order_id);

    $this->expected['shipping'] = array(array('shipping_time' => $arrDelivTime['deliv_time']));
    $this->expected['order'] = array(array('payment_method' => $arrPayment['payment_method']));
    $this->expected['order_temp'] = array(array('payment_method' => '支払方法0002')); // 変更されていない

    $this->actual['shipping'] = $this->objQuery->select(
      'shipping_time', 'dtb_shipping', 'order_id = ?', array($order_id)
    );

    $this->actual['order'] = $this->objQuery->select(
      'payment_method', 'dtb_order', 'order_id = ?', array($order_id)
    );
    $this->actual['order_temp'] = $this->objQuery->select(
      'payment_method', 'dtb_order_temp', 'order_id = ?', array($order_id)
    );
    $this->verify();
  }

  public function testSfUpdateOrderNameCol_TEMPフラグがONの場合_一時テーブルが更新される()
  {
    $order_id = $this->order_ids[1];
    $this->helper->saveOrderTemp($this->order_temp_ids[1], ['payment_method' => '支払方法0002']);
    $arrOrder = $this->objQuery->getRow('*', 'dtb_order', 'order_id = ?', [$order_id]);
    $arrShipping = $this->objQuery->getRow('*', 'dtb_shipping', 'order_id = ? AND shipping_id = 0', [$order_id]);
    $arrDelivTime = $this->objQuery->getRow('*', 'dtb_delivtime', 'deliv_id = ? AND time_id = ?', [$arrOrder['deliv_id'], $arrShipping['time_id']]);
    $arrPayment = $this->objQuery->getRow('*', 'dtb_payment', 'payment_id = ?', [$arrOrder['payment_id']]);

    $this->helper->sfUpdateOrderNameCol($this->order_temp_ids[1], true);

    $this->expected['shipping'] = array(array('shipping_time' => $arrDelivTime['deliv_time']));
    $this->expected['order'] = array(array('payment_method' => $arrPayment['payment_method']));
    $this->expected['order_temp'] = array(array('payment_method' => $arrPayment['payment_method'])); // 変更されている

    $this->actual['shipping'] = $this->objQuery->select(
      'shipping_time', 'dtb_shipping', 'order_id = ?', array($order_id)
    );
    $this->actual['order'] = $this->objQuery->select(
      'payment_method', 'dtb_order', 'order_id = ?', array($order_id)
    );
    $this->actual['order_temp'] = $this->objQuery->select(
      'payment_method', 'dtb_order_temp', 'order_temp_id = ?', array($this->order_temp_ids[1])
    );
    $this->verify();
  }

  //////////////////////////////////////////
}

