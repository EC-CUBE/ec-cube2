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
 * SC_Utils::jsonEncode()のテストクラス.
 * 環境によるfunctionの変更まではカバーできないため、簡単な出力のみテスト.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Utils_jsonEncodeTest extends Common_TestCase
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
    public function testJsonEncodeJSON形式にエンコードされた文字列が返る()
    {
        $input = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];
        $this->expected = '{"a":1,"b":2,"c":3,"d":4,"e":5}';
        $this->actual = SC_Utils::jsonEncode($input);

        $this->verify();
    }

    public function testJsonEncodeHTMLの特殊文字はエスケープされる()
    {
        $input = ['name' => '</script><b>"A&B\'</b>'];
        $this->expected = '{"name":"\u003C\/script\u003E\u003Cb\u003E\u0022A\u0026B\u0027\u003C\/b\u003E"}';
        $this->actual = SC_Utils::jsonEncode($input);

        $this->verify();
        $this->assertSame($input, json_decode($this->actual, true));
    }

    // ////////////////////////////////////////
}
