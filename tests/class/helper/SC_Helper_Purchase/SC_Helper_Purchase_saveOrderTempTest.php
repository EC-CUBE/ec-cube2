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
 * SC_Helper_Purchase::saveOrderTemp()のテストクラス.
 *
 *
 * @author Hiroko Tamagawa
 * @version $Id$
 */
class SC_Helper_Purchase_saveOrderTempTest extends SC_Helper_Purchase_TestBase
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
    $this->objQuery->delete('dtb_order_temp');
    $this->customer_ids = $this->setUpCustomer();
    $this->order_ids = $this->setUpOrder($this->customer_ids);
    $this->order_temp_ids = $this->setUpOrderTemp($this->order_ids);
    $this->helper = new SC_Helper_Purchase_saveOrderTempMock();
  }

  protected function tearDown()
  {
    parent::tearDown();
  }

  /////////////////////////////////////////
  public function testSaveOrderTemp_受注一時情報IDが空の場合_何もしない()
  {
    $this->helper->saveOrderTemp(null,
      array(
        'customer_id' => '1003',
        'order_name01' => '受注情報03',
        'update_date' => 'CURRENT_TIMESTAMP'
      )
    );

    $this->expected = 2;
    $this->actual = $this->objQuery->count('dtb_order_temp');

    $this->verify('件数が変わっていない');
  }

  public function testSaveOrderTemp_既存の情報がない場合_情報が新規登録される()
  {
    $this->helper->saveOrderTemp('1003',
      array(
        'customer_id' => '1003',
        'order_name01' => '受注情報03',
        'update_date' => 'CURRENT_TIMESTAMP'
      )
    );

    $this->expected['count'] = '3';
    $this->expected['content'] = array(
        array(
          'order_temp_id' => '1003',
          'customer_id' => '1003',
          'order_name01' => '受注情報03'
        )
      );
    $this->actual['count'] = $this->objQuery->count('dtb_order_temp');    
    $this->actual['content'] = $this->objQuery->select(
      'order_temp_id, customer_id, order_name01',
      'dtb_order_temp', 'order_temp_id = ?', array('1003'));

    $this->verify('件数が一件増える');
  }

  public function testSaveOrderTemp_既存の情報がある場合_情報が更新される()
  {
    $this->helper->saveOrderTemp($this->order_temp_ids[0],
      array(
        'customer_id' => '2002',
        'order_name01' => '受注情報92',
        'update_date' => 'CURRENT_TIMESTAMP'
      )
    );

    $this->expected['count'] = '2';
    $this->expected['content'] = array(
        array(
          'order_temp_id' => $this->order_temp_ids[0],
          'customer_id' => '2002',
          'order_name01' => '受注情報92'
        )
      );
    $this->actual['count'] = $this->objQuery->count('dtb_order_temp');
    $this->actual['content'] = $this->objQuery->select(
      'order_temp_id, customer_id, order_name01',
      'dtb_order_temp', 'order_temp_id = ?', array($this->order_temp_ids[0]));

    $this->verify('件数が変わらず更新される');
  }

  public function testSaveOrderTemp_注文者情報がある場合_情報がコピーされる()
  {
    $this->helper->saveOrderTemp('1003',
      array(
        'order_temp_id' => '1003',
        'customer_id' => '1003',
        'order_name01' => '受注情報03',
        'update_date' => 'CURRENT_TIMESTAMP'
      ),
      new SC_Customer_Ex()
    );

    // function呼び出しを確認
    $this->expectOutputString('COPY_FROM_CUSTOMER');

    $this->expected = 3;
    $this->actual = $this->objQuery->count('dtb_order_temp');    

    $this->verify('件数が一件増える'); // 詳細な中身については他のテストで確認
  }

  //////////////////////////////////////////
}

class SC_Helper_Purchase_saveOrderTempMock extends SC_Helper_Purchase
{
  public static function copyFromCustomer(&$sqlval, &$objCustomer, $prefix = 'order',
                            $keys = array())
  {
    echo('COPY_FROM_CUSTOMER');
  }
}

