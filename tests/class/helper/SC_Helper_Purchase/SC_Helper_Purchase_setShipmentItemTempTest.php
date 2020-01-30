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
 * SC_Helper_Purchase::setShipmentItemTemp()のテストクラス.
 * 【注意】dtb_baseinfoはインストール時に入るデータをそのまま使用
 *
 * @author Hiroko Tamagawa
 * @version $Id$
 */
class SC_Helper_Purchase_setShipmentItemTempTest extends SC_Helper_Purchase_TestBase
{
  /** @var int */
  private $product_id;
  /** @var array */
  private $productsClass;
  private $helper;

  protected function setUp()
  {
    parent::setUp();

    $this->product_id = $this->objGenerator->createProduct(null);
    $this->objQuery->setOrder('product_class_id');
    $this->productsClass = $this->objQuery->getRow('*', 'dtb_products_class', 'product_id = ?', [$this->product_id]);

    $_SESSION['shipping']['1001']['shipment_item'] = array(
      '1001' => array('productsClass' => array('price02' => 9000))
    );
    $this->helper = new SC_Helper_Purchase_Ex();
  }

  protected function tearDown()
  {
    parent::tearDown();
  }

  /////////////////////////////////////////
  public function testSetShipmentItemTemp_製品情報が既に存在する場合_存在する情報が価格に反映される()
  {
    $this->helper->setShipmentItemTemp('1001', '1001', 10);

    $this->expected = array(
      'shipping_id' => '1001',
      'product_class_id' => '1001',
      'quantity' => 10,
      'price' => 9000,
      'total_inctax' => SC_Helper_TaxRule_Ex::sfCalcIncTax(90000),
      'productsClass' => array('price02' => 9000)
    );
    $this->actual = $_SESSION['shipping']['1001']['shipment_item']['1001'];

    $this->verify();
  }

  public function testSetShipmentItemTemp_製品情報が存在しない場合_DBから取得した値が反映される()
  {
    $quantity = 10;
    $this->helper->setShipmentItemTemp('1001', $this->productsClass['product_class_id'], $quantity);

    $this->expected = array(
      'shipping_id' => '1001',
      'product_class_id' => $this->productsClass['product_class_id'],
      'quantity' => $quantity,
      'price' => $this->productsClass['price02'],
      'total_inctax' => SC_Helper_TaxRule_Ex::sfCalcIncTax($this->productsClass['price02']) * $quantity,
    );
    $result = $_SESSION['shipping']['1001']['shipment_item'][$this->productsClass['product_class_id']];
    unset($result['productsClass']);
    $this->actual = $result;

    $this->verify();
  }

  //////////////////////////////////////////
}

