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
 * SC_Helper_Purchase::unsetOneShipping()のテストクラス.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Helper_Purchase_unsetOneShippingTempTest extends SC_Helper_Purchase_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////
    public function testUnsetOneShippingTemp指定したIDの配送情報のみが破棄される()
    {
        $this->setUpShipping($this->getMultipleShipping());

        SC_Helper_Purchase::unsetOneShippingTemp('00002');

        $this->expected = [
      '00001' => [
        'shipment_id' => '00001',
        'shipment_item' => ['商品1'],
        'shipping_pref' => '東京都', ],
      '00003' => [
        'shipment_id' => '00003',
        'shipment_item' => [],
        'shipping_pref' => '埼玉県', ],
    ];
        $this->actual = $_SESSION['shipping'];

        $this->verify('配送情報');
    }
}
