<?php

$HOME = realpath(dirname(__FILE__)) . "/../../../..";
require_once($HOME . "/tests/class/Common_TestCase.php");
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
 * SC_Helper_Purchaseのテストの基底クラス.
 *
 *
 * @author Hiroko Tamagawa
 * @version $Id$
 */
class SC_Helper_Purchase_TestBase extends Common_TestCase
{
  /** @var FixtureGenerator */
  protected $objGenerator;

  protected function setUp()
  {
    parent::setUp();
    $this->objGenerator = new FixtureGenerator($this->objQuery);
  }

  protected function tearDown()
  {
    parent::tearDown();
  }

  /////////////////////////////////////////
  /**
   * セッションに配送情報を設定します。
   *
   * @param array $shipping 単一配送情報
   */
  protected function setUpShipping($shipping)
  {
    if (!$shipping) {
      $shipping = $this->getSingleShipping();
    }

    $_SESSION['shipping'] = $shipping;
  }

  protected function getSingleShipping()
  {
    return array(
      '00001' => array(
        'shipment_id' => '00001',
        'shipment_item' => '商品1',
        'shipping_pref' => '東京都')
    );
  }

  protected function getMultipleShipping()
  {
    return array(
      '00001' => array(
        'shipment_id' => '00001',
        'shipment_item' => array('商品1'),
        'shipping_pref' => '東京都'),
      '00002' => array(
        'shipment_id' => '00002',
        'shipment_item' => array('商品2'),
        'shipping_pref' => '沖縄県'),
      '00003' => array(
        'shipment_id' => '00003',
        'shipment_item' => array(),
        'shipping_pref' => '埼玉県')
    );
  }

  /**
   * DBに配送情報を設定します。
   */
  protected function setUpShippingOnDb()
  {
    $shippings = array(
      array(
        'update_date' => '2000-01-01 00:00:00',
        'shipping_id' => '1',
        'order_id' => '1001',
        'shipping_name01' => '配送情報01',
        'shipping_date' => '2012-01-12'
      ),
      array(
        'update_date' => '2000-01-01 00:00:00',
        'shipping_id' => '2',
        'order_id' => '2',
        'shipping_name01' => '配送情報02',
        'shipping_date' => '2011-10-01'
      ),
      array(
        'update_date' => '2000-01-01 00:00:00',
        'shipping_id' => '1002',
        'order_id' => '1002',
        'shipping_time' => '午後',
        'time_id' => '1'
      )
    );

    $this->objQuery->delete('dtb_shipping');
    foreach ($shippings as $key => $item) {
      $this->objQuery->insert('dtb_shipping', $item);
    }
  }

 /**
  * DBに受注情報を設定します.
  */
  protected function setUpOrder($customer_ids = [], $product_class_ids = [])
  {
    $orders = array(
      array(
        'update_date' => '2000-01-01 00:00:00',
        'customer_id' => $customer_ids[0],
        'order_name01' => '受注情報01',
        'status' => '3',
        'payment_date' => '2032-12-31 01:20:30', // 日付が変わっても良いように、遠い未来に設定
        'use_point' => '10',
        'add_point' => '20'
      ),
      array(
        'update_date' => '2000-01-01 00:00:00',
        'customer_id' => $customer_ids[1],
        'order_name01' => '受注情報02',
        'status' => '5',
        'use_point' => '10',
        'add_point' => '20'
      )
    );

    $this->objQuery->delete('dtb_order');
    return array_map(function ($properties) use ($product_class_ids) {
      $order_id = $this->objGenerator->createOrder($properties['customer_id'], $product_class_ids);
      $this->objQuery->update('dtb_order', $properties, 'order_id = ?', [$order_id]);

      return $order_id;
    }, $orders);
  }

 /**
  * setUpOrder() で生成した一時情報を返す.
  */
  protected function setUpOrderTemp($order_ids)
  {
    return array_map(function ($order_id) {
      return $this->objQuery->get('order_temp_id', 'dtb_order_temp', 'order_id = ?', [$order_id]);
    } , $order_ids);
  }
 /**
  * DBに顧客情報を設定します。
  */
 protected function setUpCustomer()
 {
   $this->objQuery->delete('dtb_customer');
   return [
     $this->objGenerator->createCustomer(null, ['point' => 100]),
     $this->objGenerator->createCustomer(null, ['point' => 200])
   ];
 }
}

