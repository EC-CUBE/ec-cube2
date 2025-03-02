<?php
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

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/Common_TestCase.php';

class SC_Session_isPrepageTest extends Common_TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->objSiteSession = new SC_SiteSession_Ex();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////

    public function testIsPrepageSessionが空の場合False()
    {
        $this->expected = false;
        $this->actual = $this->objSiteSession->isPrepage();
        $this->verify('ページ判定');
    }

    public function testIsPrepagePrepageとnowpageが違う場合False()
    {
        $this->expected = false;
        $_SESSION['site']['pre_page'] = 'test.php';
        $this->actual = $this->objSiteSession->isPrepage();
        $this->verify('ページ判定');
    }

    public function testIsPrepagePrepageとnowpageが同じの場合True()
    {
        $this->expected = true;
        $_SESSION['site']['pre_page'] = $_SERVER['SCRIPT_NAME'];
        $this->actual = $this->objSiteSession->isPrepage();
        $this->verify('ページ判定');
    }

    public function testIsPrepagePreRegistSuccessがtrueの場合True()
    {
        $this->expected = true;
        $_SESSION['site']['pre_page'] = 'test.php';
        $_SESSION['site']['pre_regist_success'] = true;
        $this->actual = $this->objSiteSession->isPrepage();
        $this->verify('ページ判定');
    }
}
