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
 * SC_Helper_Purchase::cleanupSession()のテストクラス.
 *
 *
 * @author Hiroko Tamagawa
 * @version $Id$
 */
class SC_Helper_Purchase_cleanupSessionTest extends SC_Helper_Purchase_TestBase
{
  /** @var int */
  private $product_class_id1;
    /** @var int */
  private $product_class_id2;
  protected function setUp()
  {
    parent::setUp();
    $product_id1 = $this->objGenerator->createProduct(null, 3, PRODUCT_TYPE_NORMAL);
    $this->product_class_id1 = $this->objQuery->get('product_class_id', 'dtb_products_class', 'product_id = ? AND del_flg = 0', [$product_id1]);
    $product_id2 = $this->objGenerator->createProduct(null, 3, PRODUCT_TYPE_DOWNLOAD);
    $this->product_class_id2 = $this->objQuery->get('product_class_id', 'dtb_products_class', 'product_id = ? AND del_flg = 0', [$product_id2]);

  }

  protected function tearDown()
  {
    parent::tearDown();
  }

  /////////////////////////////////////////
  public function testCleanupSession__カートとセッションの配送情報が削除される()
  {
    // 引数の準備
    $helper = new SC_Helper_Purchase();
    $cartSession = new SC_CartSession();
    $customer = new SC_Customer();

    // 削除前のデータを設定
    $cartSession->addProduct($this->product_class_id1, 5);  // product_type_id=1
    $cartSession->addProduct($this->product_class_id2, 10); // product_type_id=2
    $_SESSION['site']['uniqid'] = '100001';

    $helper->cleanupSession(1001, $cartSession, $customer, PRODUCT_TYPE_NORMAL);

    $this->expected = array(
      'cart_max_deleted' => 0,
      'cart_max_notdeleted' => 1,
      'uniqid' => '',
      'shipping' => null,
      'multiple_temp' => null
    );

    $this->actual['cart_max_deleted'] = $cartSession->getMax(PRODUCT_TYPE_NORMAL);
    $this->actual['cart_max_notdeleted'] = $cartSession->getMax(PRODUCT_TYPE_DOWNLOAD);
    $this->actual['uniqid'] = $_SESSION['site']['uniqid'];
    $this->actual['shipping'] = $_SESSION['shipping'];
    $this->actual['multiple_temp'] = $_SESSION['multiple_temp'];

    $this->verify();  
  }
  
  //////////////////////////////////////////
}

