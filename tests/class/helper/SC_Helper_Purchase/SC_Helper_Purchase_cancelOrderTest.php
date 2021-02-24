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
 * SC_Helper_Purchase::cancelOrder()のテストクラス.
 *
 * @author Hiroko Tamagawa
 * @version $Id$
 */
class SC_Helper_Purchase_cancelOrderTest extends SC_Helper_Purchase_TestBase
{
  /** @var array */
  private $arrProductsClasses;
  private $helper;

  protected function setUp()
  {
    parent::setUp();
    $product_id = $this->objGenerator->createProduct(null, 2);
    $this->objQuery->setOrder('product_class_id');
    $this->arrProductsClasses = $this->objQuery->select('product_class_id, stock', 'dtb_products_class', 'product_id = ? AND del_flg = 0', [$product_id]);
    $this->helper = new SC_Helper_Purchase_cancelOrderMock();
  }

  protected function tearDown()
  {
    parent::tearDown();
  }

  /////////////////////////////////////////
  public function testCancelOrder_デフォルトの引数で呼び出した場合_製品クラスのデータが更新される()
  {
    $order_id = '1001';
    $this->objQuery->begin();

    $this->helper->cancelOrder($order_id);

    $this->actual['testResult'] = $_SESSION['testResult'];
    $this->actual['productClass'] = $this->objQuery->select(
      'stock', 'dtb_products_class',
      'product_class_id in (?, ?)', array_map(function ($productsClass) {
        return $productsClass['product_class_id'];
      }, $this->arrProductsClasses)
    );
    $this->expected = array(
      'testResult' => array(
        'registerOrder' => array(
          'order_id' => '1001',
          'params' => array(
            'status' => ORDER_CANCEL
          )
        ),
        'getOrderDetail' => array(
          'order_id' => '1001'
        )
      ),
      'productClass' => array_map(function ($productsClass) {
        return ['stock' => $productsClass['stock']];
      }, $this->arrProductsClasses
      )
    );
    $this->verify();
  }

  // 実際にトランザクションを開始したかどうかはテストできないが、
  // 問題なく処理が完了することのみ確認
  public function testCancelOrder_トランザクションが開始していない場合_内部で開始する()
  {
    $order_id = '1001';

    $this->helper->cancelOrder($order_id, ORDER_NEW);

    $this->actual['testResult'] = $_SESSION['testResult'];
    $this->actual['productClass'] = $this->objQuery->select(
      'stock', 'dtb_products_class',
      'product_class_id in (?, ?)',  array_map(function ($productsClass) {
        return $productsClass['product_class_id'];
      }, $this->arrProductsClasses)
    );
    $this->expected = array(
      'testResult' => array(
        'registerOrder' => array(
          'order_id' => '1001',
          'params' => array(
            'status' => ORDER_NEW
          )
        ),
        'getOrderDetail' => array(
          'order_id' => '1001'
        )
      ),
      'productClass' => array_map(function ($productsClass) {
        return ['stock' => $productsClass['stock']];
      }, $this->arrProductsClasses
      )
    );

    $this->verify();
  }

  public function testCancelOrder_削除フラグが立っている場合_DB更新時に削除フラグが立てられる()
  {
    $order_id = '1001';
    $this->objQuery->begin();

    $this->helper->cancelOrder($order_id, ORDER_DELIV, true);

    $this->actual['testResult'] = $_SESSION['testResult'];
    $this->actual['productClass'] = $this->objQuery->select(
      'stock', 'dtb_products_class',
      'product_class_id in (?, ?)', array_map(function ($productsClass) {
        return $productsClass['product_class_id'];
      }, $this->arrProductsClasses)
    );
    $this->expected = array(
      'testResult' => array(
        'registerOrder' => array(
          'order_id' => '1001',
          'params' => array(
            'status' => ORDER_DELIV,
            'del_flg' => '1'
          )
        ),
        'getOrderDetail' => array(
          'order_id' => '1001'
        )
      ),
      'productClass' => array_map(function ($productsClass) {
        return ['stock' => $productsClass['stock']];
      }, $this->arrProductsClasses
      )
    );

    $this->verify();
  }

  //////////////////////////////////////////
}

class SC_Helper_Purchase_cancelOrderMock extends SC_Helper_Purchase
{

  static function registerOrder($order_id, $params)
  {
    $_SESSION['testResult']['registerOrder'] = array(
      'order_id' => $order_id,
      'params' => $params
    );
  }

  public static function getOrderDetail($order_id, $has_order_status = true)
  {
    $_SESSION['testResult']['getOrderDetail'] = array(
      'order_id' => $order_id
    );

    return array(
      array(
        'product_class_id' => '1001',
        'quantity' => '5'
      ),
      array(
        'product_class_id' => '1002',
        'quantity' => '1'
      )
    );
  }
}
