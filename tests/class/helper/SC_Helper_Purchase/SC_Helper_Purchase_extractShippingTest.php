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
 * SC_Helper_Purchase::extractShipping()のテストクラス.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Helper_Purchase_extractShippingTest extends SC_Helper_Purchase_TestBase
{
    protected function setUp(): void
    {
        // parent::setUp();
    }

    protected function tearDown(): void
    {
        // parent::tearDown();
    }

    // ///////////////////////////////////////
    public function testExtractShipping予め指定されたキーだけが抽出される()
    {
        $helper = new SC_Helper_Purchase_Ex();
        $helper->arrShippingKey = ['id', 'name', 'code'];
        $arrSrc = [
            'shipping_id' => '1001',
            'shipping_code' => 'cd1001',
            'shipping_detail' => 'dt1001', // 無視される
            'shipping_name' => '名称1001',
        ];

        $this->expected = [
            'shipping_id' => '1001',
            'shipping_name' => '名称1001',
            'shipping_code' => 'cd1001',
        ];
        $this->actual = $helper->extractShipping($arrSrc);

        $this->verify();
    }

    // ////////////////////////////////////////
}
