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
 * SC_Helper_Purchase::isAddPoint()のテストクラス.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Helper_Purchase_isAddPointTest extends SC_Helper_Purchase_TestBase
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
    public function testIsAddPoint新規注文の場合FALSEが返る()
    {
        $this->expected = false;
        $this->actual = SC_Helper_Purchase::isAddPoint(ORDER_NEW);

        $this->verify('ポイント加算するかどうか');
    }

    public function testIsAddPoint入金待ちの場合FALSEが返る()
    {
        $this->expected = false;
        $this->actual = SC_Helper_Purchase::isAddPoint(ORDER_PAY_WAIT);

        $this->verify('ポイント加算するかどうか');
    }

    public function testIsAddPoint入金済みの場合FALSEが返る()
    {
        $this->expected = false;
        $this->actual = SC_Helper_Purchase::isAddPoint(ORDER_PRE_END);

        $this->verify('ポイント加算するかどうか');
    }

    public function testIsAddPointキャンセルの場合FALSEが返る()
    {
        $this->expected = false;
        $this->actual = SC_Helper_Purchase::isAddPoint(ORDER_CANCEL);

        $this->verify('ポイント加算するかどうか');
    }

    public function testIsAddPoint取り寄せ中の場合FALSEが返る()
    {
        $this->expected = false;
        $this->actual = SC_Helper_Purchase::isAddPoint(ORDER_BACK_ORDER);

        $this->verify('ポイント加算するかどうか');
    }

    public function testIsAddPoint発送済みの場合TRUEが返る()
    {
        $this->expected = true;
        $this->actual = SC_Helper_Purchase::isAddPoint(ORDER_DELIV);

        $this->verify('ポイント加算するかどうか');
    }

    public function testIsAddPointその他の場合FALSEが返る()
    {
        $this->expected = false;
        $this->actual = SC_Helper_Purchase::isAddPoint(ORDER_PENDING);

        $this->verify('ポイント加算するかどうか');
    }
}
