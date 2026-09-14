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
 * AUTH_TYPE = PLAIN 前提のテストの基底クラス.
 *
 * AUTH_TYPE は定数のため, bootstrap (tests/require.php) で HMAC に定義された後は変更できない.
 * そのためサブクラスには `@runTestsInSeparateProcesses` と `@preserveGlobalState disabled` を付与し,
 * テストメソッドごとに子プロセスを起動する (クラスアノテーションは継承されないため各サブクラスに記述が必要).
 *
 * 子プロセスは親プロセスの環境変数を引き継ぐため, setUpBeforeClass() で設定した
 * ECCUBE_TEST_AUTH_TYPE=PLAIN を bootstrap が読み取り, AUTH_TYPE = PLAIN として定義する.
 * (`@preserveGlobalState disabled` が無いと親プロセスの定数 (AUTH_TYPE = HMAC) が子プロセスに複製される)
 *
 * @see tests/require.php
 */
abstract class SC_Utils_AuthTypePlain_TestBase extends Common_TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        putenv('ECCUBE_TEST_AUTH_TYPE=PLAIN');
    }

    public static function tearDownAfterClass(): void
    {
        putenv('ECCUBE_TEST_AUTH_TYPE');
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        // DB は使用しないため parent::setUp() は呼ばない.
        // アノテーション漏れ等で AUTH_TYPE が切り替わっていない場合はここで検出する.
        $this->assertSame(
            'PLAIN',
            AUTH_TYPE,
            'AUTH_TYPE が PLAIN として定義されていません. '
            .'テストクラスに @runTestsInSeparateProcesses と @preserveGlobalState disabled が付与されているか確認してください.'
        );
    }

    protected function tearDown(): void
    {
        // parent::setUp() を呼んでいないため parent::tearDown() も呼ばない
    }
}
