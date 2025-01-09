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
 * SC_Utils::sfNoImageMainList()のテストクラス.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Utils_sfNoImageMainListTest extends Common_TestCase
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
    public function testSfNoImageMainListファイル名が空の場合デフォルトのファイル名が返る()
    {
        $this->expected = 'noimage_main_list.jpg';
        $this->actual = SC_Utils::sfNoImageMainList('');

        $this->verify();
    }

    public function testSfNoImageMainListディレクトリが指定されている場合そのディレクトリ以下でデフォルトのファイル名が返る()
    {
        $this->expected = 'aaa/bbb/noimage_main_list.jpg';
        $this->actual = SC_Utils::sfNoImageMainList('aaa/bbb/');

        $this->verify();
    }

    public function testSfNoImageMainListファイル名が指定されている場合指定されたファイル名が返る()
    {
        $this->expected = 'aaa/bbb/ccc.png';
        $this->actual = SC_Utils::sfNoImageMainList('aaa/bbb/ccc.png');

        $this->verify();
    }

    // ////////////////////////////////////////
}
