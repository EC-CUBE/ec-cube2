<?php

$HOME = realpath(__DIR__).'/../../../..';
// このテスト専用の定数の設定
defined('AUTH_TYPE') || define('AUTH_TYPE', 'PLAIN');
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
 * SC_Utils::sfNeedsReHash() のテストクラス (AUTH_TYPE = PLAIN).
 * AUTH_TYPE は定数のためまとめて実行できない. 個別実行が必要:
 * data/vendor/bin/phpunit tests/class/util/SC_Utils/SC_Utils_sfNeedsReHash_authTypePlainTest.php
 *
 * @group auth_type_plain
 */
class SC_Utils_sfNeedsReHash_authTypePlainTest extends Common_TestCase
{
    protected function setUp(): void
    {
        // parent::setUp();
    }

    protected function tearDown(): void
    {
        // parent::tearDown();
    }

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
