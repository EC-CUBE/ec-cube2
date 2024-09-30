<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/Common_TestCase.php';
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
 * SC_Utils::sfArrCombine()のテストクラス.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Utils_sfArrCombineTest extends Common_TestCase
{
    protected function setUp()
    {
        // parent::setUp();
    }

    protected function tearDown()
    {
        // parent::tearDown();
    }

    // ///////////////////////////////////////
    public function testSfArrCombine入力の配列が空の場合結果も空になる()
    {
        $keys = [];
        $values = [];

        $this->expected = [];
        $this->actual = SC_Utils::sfArrCombine($keys, $values);

        $this->verify();
    }

    // TODO 要確認　キーの方が短い場合はエラーで良いのでは？
    public function testSfArrCombine入力のキー配列の方が短い場合余った値の配列の要素は無視される()
    {
        $keys = ['apple', 'banana'];
        $values = ['りんご', 'バナナ', 'オレンジ', '梨'];

        $this->expected = [
      'apple' => 'りんご',
      'banana' => 'バナナ',
      null => '梨'
    ];
        $this->actual = SC_Utils::sfArrCombine($keys, $values);

        $this->verify();
    }

    public function testSfArrCombine入力のキー配列の方が長い場合余ったキーの配列の要素は空になる()
    {
        $keys = ['apple', 'banana', 'orange', 'pear'];
        $values = ['りんご', 'バナナ'];

        $this->expected = [
      'apple' => 'りんご',
      'banana' => 'バナナ',
      'orange' => null,
      'pear' => null
    ];
        $this->actual = SC_Utils::sfArrCombine($keys, $values);

        $this->verify();
    }

    // ////////////////////////////////////////
}
