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

class SC_Session_checkUniqIdTest extends Common_TestCase
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

    public function testCheckUniqIdPOST値がない場合True()
    {
        $_POST = null;
        $this->expected = true;
        $this->actual = $this->objSiteSession->checkUniqId();
        $this->verify('ポスト値空');
    }

    public function testCheckUniqIdPOSTとセッションのUniqIDが一致する場合True()
    {
        $_POST['uniqid'] = '1234567890';
        $_SESSION['site']['uniqid'] = '1234567890';

        $this->expected = true;
        $this->actual = $this->objSiteSession->checkUniqId();
        $this->verify('ユニークID一致');
    }

    public function testCheckUniqIdPOSTとセッションのUniqIDが一致しない場合False()
    {
        $_POST['uniqid'] = '0987654321';
        $_SESSION['site']['uniqid'] = '1234567890';

        $this->expected = false;
        $this->actual = $this->objSiteSession->checkUniqId();
        $this->verify('ユニークID不一致');
    }
}
