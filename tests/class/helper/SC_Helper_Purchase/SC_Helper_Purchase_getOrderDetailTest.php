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
 * SC_Helper_Purchase::getOrderDetail()のテストクラス.
 *
 *
 * @author Hiroko Tamagawa
 * @version $Id$
 */
class SC_Helper_Purchase_getOrderDetailTest extends SC_Helper_Purchase_TestBase
{
  /** @var array */
  private $customer_ids = [];
  /** @var array */
  private $order_ids = [];

  protected function setUp()
  {
    parent::setUp();
    $this->customer_ids = $this->setUpCustomer();
    $this->order_ids = $this->setUpOrder($this->customer_ids, [1, 2, 3, 4, 5]);
  }

  protected function tearDown()
  {
    parent::tearDown();
  }

  /////////////////////////////////////////
  public function testGetOrderDetail_存在しない受注IDを指定した場合_結果が空になる()
  {
    $order_id = '9999';

    $this->expected = array();
    $this->actual = SC_Helper_Purchase::getOrderDetail($order_id);

    $this->verify();
  }

  public function testGetOrderDetail_存在する受注IDを指定した場合_対応する受注詳細情報が取得できる()
  {
    $properties = [
      'product_id', 'product_class_id', 'product_type_id', 'product_code',
      'product_name', 'classcategory_name1', 'classcategory_name2', 'price',
      'quantity', 'point_rate', 'status', 'payment_date', 'enable', 'effective',
      'tax_rate', 'tax_rule'];

    $this->objQuery->setOrder('order_detail_id');
    $arrOrderDetails = $this->objQuery->select('*', 'dtb_order_detail T1 JOIN dtb_order T2 ON T1.order_id = T2.order_id', 'T1.order_id = ?', [$this->order_ids[0]]);

    // 不足しているダミーの情報を付与する
    $arrOrderDetails = array_map(function ($orderDetail) {
      $orderDetail['product_type_id'] = (string) PRODUCT_TYPE_NORMAL;
      $orderDetail['enable'] = '1';
      $orderDetail['effective'] = '1';
      return $orderDetail;
    }, $arrOrderDetails);

    $this->expected = array_map(function ($orderDetail) use ($properties) {
      $expectedDetail = [];
      foreach ($properties as $key) {
        $expectedDetail[$key] = $orderDetail[$key];
      }

      return $expectedDetail;
    }, $arrOrderDetails);

    $this->actual = SC_Helper_Purchase::getOrderDetail($this->order_ids[0]);

    $this->verify();

    $this->assertNotEmpty($this->actual[0]['status']);
    $this->assertNotEmpty($this->actual[0]['payment_date']);
  }

  public function testGetOrderDetail_ステータス取得フラグがOFFのの場合_ステータス以外の情報が取得できる()
  {
    $properties = [
      'product_id', 'product_class_id', 'product_type_id', 'product_code',
      'product_name', 'classcategory_name1', 'classcategory_name2', 'price',
      'quantity', 'point_rate', 'enable', 'effective',
      'tax_rate', 'tax_rule'];

    $this->objQuery->setOrder('order_detail_id');
    $arrOrderDetails = $this->objQuery->select('*', 'dtb_order_detail T1 JOIN dtb_order T2 ON T1.order_id = T2.order_id', 'T1.order_id = ?', [$this->order_ids[0]]);
    // 不足しているダミーの情報を付与する
    $arrOrderDetails = array_map(function ($orderDetail) {
      $orderDetail['product_type_id'] = (string) PRODUCT_TYPE_NORMAL;
      $orderDetail['enable'] = '1';
      $orderDetail['effective'] = '1';
      return $orderDetail;
    }, $arrOrderDetails);

    $this->expected = array_map(function ($orderDetail) use ($properties) {
      $expectedDetail = [];
      foreach ($properties as $key) {
        $expectedDetail[$key] = $orderDetail[$key];
      }

      return $expectedDetail;
    }, $arrOrderDetails);

    $this->actual = SC_Helper_Purchase::getOrderDetail($this->order_ids[0], false);

    $this->verify();

    $this->assertEmpty($this->actual[0]['status']);
    $this->assertEmpty($this->actual[0]['payment_date']);
  }

  //////////////////////////////////////////
}

