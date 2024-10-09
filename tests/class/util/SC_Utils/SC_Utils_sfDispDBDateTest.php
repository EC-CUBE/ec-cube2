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
 * SC_Utils::sfDispDBDate()のテストクラス.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Utils_sfDispDBDateTest extends Common_TestCase
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
    public function testSfDispDBDate年が指定されていない場合0バイト文字列が返る()
    {
        $dbdate = '-01-23 01:12:24';

        $this->expected = '';
        $this->actual = SC_Utils::sfDispDBDate($dbdate);

        $this->verify();
    }

    public function testSfDispDBDate月が指定されていない場合0バイト文字列が返る()
    {
        $dbdate = '2012--23 01:12:24';

        $this->expected = '';
        $this->actual = SC_Utils::sfDispDBDate($dbdate);

        $this->verify();
    }

    public function testSfDispDBDate日が指定されていない場合0バイト文字列が返る()
    {
        $dbdate = '2012-01- 01:12:24';

        $this->expected = '';
        $this->actual = SC_Utils::sfDispDBDate($dbdate);

        $this->verify();
    }

    public function testSfDispDBDate年月日全て存在する場合フォーマット済み文字列が返る()
    {
        $dbdate = '2012-1-23 1:12:24';

        $this->expected = '2012/01/23 01:12';
        $this->actual = SC_Utils::sfDispDBDate($dbdate);

        $this->verify();
    }

    public function testSfDispDBDate時刻表示フラグがOFFの場合時刻なしのフォーマット済み文字列が返る()
    {
        $dbdate = '2012-1-23 1:12:24';

        $this->expected = '2012/01/23';
        $this->actual = SC_Utils::sfDispDBDate($dbdate, false);

        $this->verify();
    }

    // ////////////////////////////////////////
}
