<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/util/SC_Utils/SC_Utils_AuthTypePlain_TestBase.php';
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
 * SC_Utils::sfNeedsReHash() のテストクラス (AUTH_TYPE = PLAIN).
 *
 * @see SC_Utils_AuthTypePlain_TestBase
 *
 * @group auth_type_plain
 *
 * @runTestsInSeparateProcesses
 *
 * @preserveGlobalState disabled
 */
class SC_Utils_sfNeedsReHash_authTypePlainTest extends SC_Utils_AuthTypePlain_TestBase
{
    public function testSfNeedsReHashAuthTypePlainの場合常にFalseが返る()
    {
        $hashpass = 'ec-cube';
        $salt = 'salt';

        $this->assertFalse(SC_Utils::sfNeedsReHash($hashpass, $salt));
    }

    public function testSfNeedsReHashAuthTypePlainでSalt空の場合もFalseが返る()
    {
        $hashpass = 'ec-cube';
        $salt = '';

        $this->assertFalse(SC_Utils::sfNeedsReHash($hashpass, $salt));
    }
}
