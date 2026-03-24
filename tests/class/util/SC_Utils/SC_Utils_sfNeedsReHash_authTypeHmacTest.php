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
 * PASSWORD_HASH_ALGOS = PASSWORD_DEFAULT (bcrypt) を前提としたテスト.
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

    public function testSfNeedsReHashSaltが空の旧SHA1ハッシュの場合Trueが返る()
    {
        $pass = 'ec-cube';
        $hashpass = sha1($pass.':'.AUTH_MAGIC);
        $salt = '';

        $this->assertTrue(SC_Utils::sfNeedsReHash($hashpass, $salt));
    }

    public function testSfIsMatchHashPasswordSHA1ハッシュでReHash後にPasswordHash形式で認証成功する()
    {
        $pass = 'ec-cube';
        $hashpass = sha1($pass.':'.AUTH_MAGIC);

        // SHA1で認証成功することを確認
        $this->assertTrue(SC_Utils::sfIsMatchHashPassword($pass, $hashpass, ''));

        // 再ハッシュ実行 (PASSWORD_DEFAULT = bcrypt)
        $arrNewHash = SC_Utils::sfReHashPassword($pass);
        // password_hash()使用時はsaltは空(ハッシュに内包)
        $this->assertEmpty($arrNewHash['salt']);
        $this->assertNotEquals($hashpass, $arrNewHash['password']);
        // password_hash() 形式であることを確認
        $info = password_get_info($arrNewHash['password']);
        $this->assertNotNull($info['algo']);
        $this->assertNotEquals(0, $info['algo']);

        // 再ハッシュ後のパスワードで認証成功することを確認
        $this->assertTrue(SC_Utils::sfIsMatchHashPassword($pass, $arrNewHash['password'], $arrNewHash['salt']));
    }

    // =============================================
    // EC-CUBE 2.11以降互換テスト (HMAC-SHA256, saltあり)
    // =============================================

    public function testSfNeedsReHashHMACSHA256ハッシュでSaltが存在する場合Trueが返る()
    {
        // PASSWORD_HASH_ALGOS = PASSWORD_DEFAULT なので、旧HMAC-SHA256は再ハッシュ必要
        $pass = 'ec-cube';
        $salt = 'salt';
        $hashpass = hash_hmac('sha256', $pass.':'.AUTH_MAGIC, $salt);

        $this->assertTrue(SC_Utils::sfNeedsReHash($hashpass, $salt));
    }

    public function testSfIsMatchHashPasswordHMACSHA256ハッシュでReHash後にPasswordHash形式で認証成功する()
    {
        $pass = 'ec-cube';
        $salt = 'salt';
        $hashpass = hash_hmac('sha256', $pass.':'.AUTH_MAGIC, $salt);

        // HMAC-SHA256で認証成功することを確認
        $this->assertTrue(SC_Utils::sfIsMatchHashPassword($pass, $hashpass, $salt));

        // 再ハッシュ実行
        $arrNewHash = SC_Utils::sfReHashPassword($pass);
        $this->assertEmpty($arrNewHash['salt']);

        // 再ハッシュ後のパスワードで認証成功することを確認
        $this->assertTrue(SC_Utils::sfIsMatchHashPassword($pass, $arrNewHash['password'], $arrNewHash['salt']));
    }

    // =============================================
    // password_hash() 形式テスト (bcrypt/Argon2id)
    // =============================================

    public function testSfIsMatchHashPasswordBcryptハッシュで認証成功する()
    {
        $pass = 'ec-cube';
        $hashpass = password_hash($pass, PASSWORD_BCRYPT);

        $this->assertTrue(SC_Utils::sfIsMatchHashPassword($pass, $hashpass, ''));
    }

    public function testSfIsMatchHashPasswordBcryptハッシュで不一致の場合Falseが返る()
    {
        $pass = 'ec-cube';
        $hashpass = password_hash($pass, PASSWORD_BCRYPT);

        $this->assertFalse(SC_Utils::sfIsMatchHashPassword('wrong-password', $hashpass, ''));
    }

    public function testSfIsMatchHashPasswordArgon2idハッシュで認証成功する()
    {
        if (!defined('PASSWORD_ARGON2ID')) {
            $this->markTestSkipped('PASSWORD_ARGON2ID is not available');
        }
        $pass = 'ec-cube';
        $hashpass = password_hash($pass, PASSWORD_ARGON2ID);

        $this->assertTrue(SC_Utils::sfIsMatchHashPassword($pass, $hashpass, ''));
    }

    public function testSfNeedsReHashBcryptハッシュで同一アルゴリズムの場合Falseが返る()
    {
        $pass = 'ec-cube';
        $hashpass = password_hash($pass, PASSWORD_BCRYPT);

        // PASSWORD_HASH_ALGOS = PASSWORD_DEFAULT (= bcrypt) なので再ハッシュ不要
        $this->assertFalse(SC_Utils::sfNeedsReHash($hashpass, ''));
    }

    public function testSfReHashPasswordPasswordHash形式でSaltが空で返る()
    {
        $pass = 'ec-cube';
        $arrNewHash = SC_Utils::sfReHashPassword($pass);

        // password_hash()使用時はsaltは空
        $this->assertEmpty($arrNewHash['salt']);
        $this->assertNotEmpty($arrNewHash['password']);
        // password_hash() 形式であることを確認
        $info = password_get_info($arrNewHash['password']);
        $this->assertNotNull($info['algo']);
        $this->assertNotEquals(0, $info['algo']);
        // 認証成功することを確認
        $this->assertTrue(SC_Utils::sfIsMatchHashPassword($pass, $arrNewHash['password'], $arrNewHash['salt']));
    }

    public function testSfIsPasswordHashAlgosPasswordDefaultの場合Trueが返る()
    {
        // PASSWORD_HASH_ALGOS = PASSWORD_DEFAULT はpassword_hash()対応
        $this->assertTrue(SC_Utils::sfIsPasswordHashAlgos());
    }

    public function testSfGetHashStringPasswordHash形式で返る()
    {
        $pass = 'ec-cube';
        $hash = SC_Utils_Ex::sfGetHashString($pass);

        // password_hash() 形式であることを確認
        $info = password_get_info($hash);
        $this->assertNotNull($info['algo']);
        $this->assertNotEquals(0, $info['algo']);
        $this->assertTrue(password_verify($pass, $hash));
    }
}
