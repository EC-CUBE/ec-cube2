<?php

$HOME = realpath(__DIR__).'/../../../..';
// このテスト専用の定数の設定
defined('AUTH_TYPE') || define('AUTH_TYPE', 'HMAC');
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
 * SC_Utils::sfNeedsReHash() 及び関連メソッドのテストクラス (AUTH_TYPE = HMAC).
 */
class SC_Utils_sfNeedsReHash_authTypeHmacTest extends Common_TestCase
{
    protected function setUp(): void
    {
        // parent::setUp();
    }

    protected function tearDown(): void
    {
        // parent::tearDown();
    }

    // =============================================
    // EC-CUBE 2.11未満互換テスト (SHA1, saltなし)
    // =============================================

    public function testSfNeedsReHash_Saltが空でSha256の場合_Trueが返る()
    {
        // PASSWORD_HASH_ALGOS = 'sha256' の場合、saltが空(SHA1)は再ハッシュ必要
        $pass = 'ec-cube';
        $hashpass = sha1($pass.':'.AUTH_MAGIC);
        $salt = '';

        $this->assertTrue(SC_Utils::sfNeedsReHash($hashpass, $salt));
    }

    public function testSfIsMatchHashPassword_SHA1ハッシュでReHash後にHMACSHA256で認証成功する()
    {
        $pass = 'ec-cube';
        $hashpass = sha1($pass.':'.AUTH_MAGIC);

        // SHA1で認証成功することを確認
        $this->assertTrue(SC_Utils::sfIsMatchHashPassword($pass, $hashpass, ''));

        // 再ハッシュ実行
        $arrNewHash = SC_Utils::sfReHashPassword($pass);
        $this->assertNotEmpty($arrNewHash['salt']);
        $this->assertNotEquals($hashpass, $arrNewHash['password']);

        // 再ハッシュ後のパスワードで認証成功することを確認
        $this->assertTrue(SC_Utils::sfIsMatchHashPassword($pass, $arrNewHash['password'], $arrNewHash['salt']));
    }

    // =============================================
    // EC-CUBE 2.11以降テスト (HMAC-SHA256, saltあり)
    // =============================================

    public function testSfNeedsReHash_Saltが存在しSha256の場合_Falseが返る()
    {
        $pass = 'ec-cube';
        $salt = 'salt';
        $hashpass = SC_Utils_Ex::sfGetHashString($pass, $salt);

        $this->assertFalse(SC_Utils::sfNeedsReHash($hashpass, $salt));
    }

    // =============================================
    // password_hash() 形式テスト (bcrypt/Argon2id)
    // =============================================

    public function testSfIsMatchHashPassword_Bcryptハッシュで認証成功する()
    {
        $pass = 'ec-cube';
        $hashpass = password_hash($pass, PASSWORD_BCRYPT);

        $this->assertTrue(SC_Utils::sfIsMatchHashPassword($pass, $hashpass, ''));
    }

    public function testSfIsMatchHashPassword_Bcryptハッシュで不一致の場合Falseが返る()
    {
        $pass = 'ec-cube';
        $hashpass = password_hash($pass, PASSWORD_BCRYPT);

        $this->assertFalse(SC_Utils::sfIsMatchHashPassword('wrong-password', $hashpass, ''));
    }

    public function testSfIsMatchHashPassword_Argon2idハッシュで認証成功する()
    {
        if (!defined('PASSWORD_ARGON2ID')) {
            $this->markTestSkipped('PASSWORD_ARGON2ID is not available');
        }
        $pass = 'ec-cube';
        $hashpass = password_hash($pass, PASSWORD_ARGON2ID);

        $this->assertTrue(SC_Utils::sfIsMatchHashPassword($pass, $hashpass, ''));
    }

    public function testSfNeedsReHash_Bcryptハッシュで同一アルゴリズムの場合_Falseが返る()
    {
        $pass = 'ec-cube';
        $hashpass = password_hash($pass, PASSWORD_BCRYPT);

        // PASSWORD_HASH_ALGOS = 'sha256' なので、bcryptハッシュに対してはfalse
        // (password_hash対応アルゴではないため)
        $this->assertFalse(SC_Utils::sfNeedsReHash($hashpass, ''));
    }

    public function testSfReHashPassword_Sha256の場合_Saltが生成される()
    {
        $pass = 'ec-cube';
        $arrNewHash = SC_Utils::sfReHashPassword($pass);

        $this->assertNotEmpty($arrNewHash['salt']);
        $this->assertNotEmpty($arrNewHash['password']);
        $this->assertTrue(SC_Utils::sfIsMatchHashPassword($pass, $arrNewHash['password'], $arrNewHash['salt']));
    }

    public function testSfIsPasswordHashAlgos_Sha256の場合_Falseが返る()
    {
        // PASSWORD_HASH_ALGOS = 'sha256' はpassword_hash()対応ではない
        $this->assertFalse(SC_Utils::sfIsPasswordHashAlgos());
    }
}
