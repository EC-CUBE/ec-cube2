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
 * SC_Helper_Purchase::registerOrderComplete()のテストクラス.
 * TODO 在庫の減少処理はエラー表示⇒exit呼び出しとなるためテスト不可.
 *
 * @author Hiroko Tamagawa
 * @version $Id$
 */

class SC_Helper_Purchase_registerOrderCompleteTest extends SC_Helper_Purchase_TestBase
{
  /** @var array */
  private $customer_ids = [];
  /** @var array */
  private $order_ids = [];
  /** @var array */
  private $order_temp_ids = [];
  private $helper;

  protected function setUp()
  {
    parent::setUp();
    $this->customer_ids = $this->setUpCustomer();
    $this->order_ids = $this->setUpOrder($this->customer_ids);
    $this->order_temp_ids = $this->setUpOrderTemp($this->order_ids);
    $this->helper = new SC_Helper_Purchase_registerOrderCompleteMock();
  }

  protected function tearDown()
  {
    parent::tearDown();
  }

  /////////////////////////////////////////
  public function testRegisterOrderComplete_不要な変数が含まれている場合_登録前に除外される()
  {

    // 引数の準備
    $orderParams = array(
      'order_id' => $this->order_ids[0],
      'status' => ORDER_PAY_WAIT,
      'mail_maga_flg' => '1',
      'order_tax_rate' => '5',
      'order_tax_rule' => '1'
    );
    $cartSession = new SC_CartSession_registerOrderCompleteMock();
    $_SESSION['site']['uniqid'] = $this->order_temp_ids[0];

    $this->helper->registerOrderComplete($orderParams, $cartSession, '1');

    $this->expected = array(
      'registerOrder' => array(
        'order_id' => $this->order_ids[0],
        'status' => ORDER_PAY_WAIT,
        'mailmaga_flg' => null
      ),
      'registerOrderDetail' => array(
        'order_id' => $this->order_ids[0],
        'params' => array(
          array(
            'order_id' => $this->order_ids[0],
            'product_id' => '1002',
            'product_class_id' => '1002',
            'product_name' => '製品02',
            'product_code' => 'cd1002',
            'classcategory_name1' => 'cat01',
            'classcategory_name2' => 'cat02',
            'point_rate' => '5',
            'price' => '1000',
            'quantity' => '10',
            'tax_rate' => null,
            'tax_rule' => null,
            'tax_adjust' => null
          )
        )
      ),
      'del_flg' => '1'
    );

    $this->actual = $_SESSION['testResult'];
    $this->actual['del_flg'] = $this->objQuery->get('del_flg', 'dtb_order_temp', 'order_temp_id = ?', $this->order_temp_ids[0]);
    $this->verify();
  }

  public function testRegisterOrderComplete_ステータスの指定がない場合_新規受付扱いとなる()
  {
    // 引数の準備
    $orderParams = array(
      'order_id' => '1001',
    //  'status' => ORDER_PAY_WAIT,
      'order_tax_rate' => '5',
      'order_tax_rule' => '1'
    );
    $cartSession = new SC_CartSession_registerOrderCompleteMock();
    $_SESSION['site']['uniqid'] = '1001';

    $this->helper->registerOrderComplete($orderParams, $cartSession, '1');

    // 上の条件と重複する部分は確認を省略
    $this->expected = array(
      'registerOrder' => array(
        'order_id' => '1001',
        'status' => ORDER_NEW,
        'mailmaga_flg' => null
      )
    );

    $this->actual['registerOrder'] = $_SESSION['testResult']['registerOrder'];
    $this->verify();
  }

  //////////////////////////////////////////
}

class SC_Helper_Purchase_registerOrderCompleteMock extends SC_Helper_Purchase
{

  public static function registerOrder($order_id, $params)
  {
    $_SESSION['testResult']['registerOrder'] = array(
      'order_id' => $order_id,
      'status' => $params['status'],
      'mailmaga_flg' => $params['mailmaga_flg']
    );
  }

  public static function registerOrderDetail($order_id, $params)
  {
    $_SESSION['testResult']['registerOrderDetail'] = array(
      'order_id' => $order_id,
      'params' => $params
    );
  }

  function setUniqId()
  {}
}

class SC_CartSession_registerOrderCompleteMock extends SC_CartSession
{

  // カートの内容を取得
  function getCartList($cartKey, $pref_id = 0, $country_id = 0)
  {
    return array(
      array(
        'productsClass' => array(
          'product_id' => '1002',
          'product_class_id' => '1002',
          'name' => '製品02',
          'product_code' => 'cd1002',
          'classcategory_name1' => 'cat01',
          'classcategory_name2' => 'cat02'
        ),
        'point_rate' => '5',
        'price' => '1000',
        'quantity' => '10'
      )
    );
  }
}
