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
 * SC_Utils::sfGetHashString()のテストクラス (AUTH_TYPE = PLAIN).
 *
 * @see SC_Utils_AuthTypePlain_TestBase
 *
 * @author Hiroko Tamagawa
 *
 * @group auth_type_plain
 *
 * @runTestsInSeparateProcesses
 *
 * @preserveGlobalState disabled
 */
class SC_Utils_sfGetHashString_authTypePlainTest extends SC_Utils_AuthTypePlain_TestBase
{
    public function testSfGetHashString暗号化なしの設定になっている場合文字列が変換されない()
    {
        $input = 'hello, world';

        $this->expected = $input;
        $this->actual = SC_Utils::sfGetHashString($input);

        $this->verify();
    }
}
