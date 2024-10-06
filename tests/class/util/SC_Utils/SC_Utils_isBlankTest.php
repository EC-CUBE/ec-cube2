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
 * SC_Utils::isBlank()のテストクラス.
 * 元々test/class/以下にあったテストを移行しています.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Utils_isBlankTest extends Common_TestCase
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
    public function testIsBlank0バイト文字列の場合Trueが返る()
    {
        $input = '';
        $this->assertTrue(SC_Utils::isBlank($input), $input);
    }

    public function testIsBlank全角スペースの場合Trueが返る()
    {
        $input = '　';
        $this->assertTrue(SC_Utils::isBlank($input), $input);
    }

    public function testIsBlankGreedy指定なしで全角スペースの場合Falseが返る()
    {
        $input = '　';
        $this->assertFalse(SC_Utils::isBlank($input, false), $input);
    }

    public function testIsBlank空の配列の場合Trueが返る()
    {
        $input = [];
        $this->assertTrue(SC_Utils::isBlank($input), $input);
    }

    public function testIsBlankネストした配列の場合Trueが返る()
    {
        $input = [[[]]];
        $this->assertTrue(SC_Utils::isBlank($input), $input);
    }

    public function testIsBlankGreedy指定なしでネストした配列の場合Falseが返る()
    {
        $input = [[[]]];
        $this->assertFalse(SC_Utils::isBlank($input, false), $input);
    }

    public function testIsBlank空でない配列の場合Falseが返る()
    {
        $input = [[['1']]];
        $this->assertFalse(SC_Utils::isBlank($input), $input);
    }

    public function testIsBlankGreedy指定なしで空でない配列の場合Falseが返る()
    {
        $input = [[['1']]];
        $this->assertFalse(SC_Utils::isBlank($input, false), $input);
    }

    public function testIsBlank全角スペースと空白の組み合わせの場合Trueが返る()
    {
        $input = "　\n　";
        $this->assertTrue(SC_Utils::isBlank($input), $input);
    }

    public function testIsBlankGreedy指定なしで全角スペースと空白の組み合わせの場合Falseが返る()
    {
        $input = "　\n　";
        $this->assertFalse(SC_Utils::isBlank($input, false), $input);
    }

    public function testIsBlank全角スペースと非空白の組み合わせの場合Falseが返る()
    {
        $input = '　A　';
        $this->assertFalse(SC_Utils::isBlank($input), $input);
    }

    public function testIsBlankGreedy指定なしで全角スペースと非空白の組み合わせの場合Falseが返る()
    {
        $input = '　A　';
        $this->assertFalse(SC_Utils::isBlank($input, false), $input);
    }

    public function testIsBlank数値のゼロを入力した場合Falseが返る()
    {
        $input = 0;
        $this->assertFalse(SC_Utils::isBlank($input), $input);
    }

    public function testIsBlank値が空の配列を入力した場合Trueが返る()
    {
        $input = [''];
        $this->assertTrue(SC_Utils::isBlank($input), $input);
    }

    public function testIsBlank全てのホワイトスペースを並べた場合Trueが返る()
    {
        $input = " \t　\n\r\x0B\0";
        $this->assertTrue(SC_Utils::isBlank($input), $input);
    }

    public function testIsBlank通常の文字が含まれている場合Falseが返る()
    {
        $input = " AB \n\t";
        $this->assertFalse(SC_Utils::isBlank($input), $input);
    }

    // ////////////////////////////////////////
}
