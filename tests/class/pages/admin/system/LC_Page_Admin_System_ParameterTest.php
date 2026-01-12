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

$HOME = realpath(__DIR__).'/../../../../../';
require_once $HOME.'/tests/class/Common_TestCase.php';

class LC_Page_Admin_System_ParameterTest extends Common_TestCase
{
    /** @var LC_Page_Admin_System_Parameter */
    protected $objPage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objPage = new LC_Page_Admin_System_Parameter();
    }

    /**
     * errorCheck()メソッドが日本語を含むパラメータ値でも正常に動作することを確認
     *
     * PR #1157 によるデグレ対策テスト
     * 引数の順序が正しく、日本語を含む値でもFatal Errorを発生させないことを確認
     * （表示名には定数名が使われ、チェック対象は実際の値）
     */
    public function testErrorCheck_日本語を含むパラメータ値でFatalErrorが発生しない()
    {
        $arrKeys = ['SAMPLE_ADDRESS', 'SAMPLE_CITY'];
        $arrForm = [
            'SAMPLE_ADDRESS' => '市区町村名 (例：千代田区神田神保町)',
            'SAMPLE_CITY' => '都道府県名 (例：東京都)',
        ];

        $arrErr = $this->objPage->errorCheck($arrKeys, $arrForm);

        // 日本語を含む値でもFatal errorが発生せず、エラーが返されないことを確認
        $this->assertIsArray($arrErr);
        $this->assertEmpty($arrErr);
    }

    /**
     * errorCheck()メソッドが空の値に対して正しくエラーを返すことを確認
     */
    public function testErrorCheck_空の値に対してエラーを返す()
    {
        $arrKeys = ['EMPTY_PARAM'];
        $arrForm = [
            'EMPTY_PARAM' => '',
        ];

        $arrErr = $this->objPage->errorCheck($arrKeys, $arrForm);

        // EXIST_CHECKにより、空の値に対してエラーが返されることを確認
        $this->assertArrayHasKey('EMPTY_PARAM', $arrErr);
        $this->assertStringContainsString('が入力されていません', $arrErr['EMPTY_PARAM']);
    }

    /**
     * errorCheck()メソッドが正常な値に対してエラーを返さないことを確認
     */
    public function testErrorCheck_正常な値に対してエラーを返さない()
    {
        $arrKeys = ['VALID_PARAM'];
        $arrForm = [
            'VALID_PARAM' => '"valid value"',
        ];

        $arrErr = $this->objPage->errorCheck($arrKeys, $arrForm);

        // エラーが返されないことを確認
        $this->assertEmpty($arrErr);
    }

    /**
     * errorCheck()メソッドが不正なPHPコードに対してエラーを返すことを確認
     */
    public function testErrorCheck_不正なPHPコードに対してエラーを返す()
    {
        $arrKeys = ['INVALID_PHP_CODE'];
        $arrForm = [
            'INVALID_PHP_CODE' => 'invalid code with syntax error ;;;',
        ];

        $arrErr = $this->objPage->errorCheck($arrKeys, $arrForm);

        // EVAL_CHECKにより、不正なPHPコードに対してエラーが返されることを確認
        $this->assertArrayHasKey('INVALID_PHP_CODE', $arrErr);
        $this->assertStringContainsString('形式が不正です', $arrErr['INVALID_PHP_CODE']);
    }

    /**
     * errorCheck()メソッドが複数のパラメータを正しく処理することを確認
     */
    public function testErrorCheck_複数のパラメータを正しく処理()
    {
        $arrKeys = ['PARAM1', 'PARAM2', 'PARAM3'];
        $arrForm = [
            'PARAM1' => '"valid value 1"',
            'PARAM2' => '', // 空の値（エラー）
            'PARAM3' => '日本語を含む値 (例：東京都)',
        ];

        $arrErr = $this->objPage->errorCheck($arrKeys, $arrForm);

        // PARAM2 のみエラーが返されることを確認
        $this->assertCount(1, $arrErr);
        $this->assertArrayHasKey('PARAM2', $arrErr);
        $this->assertArrayNotHasKey('PARAM1', $arrErr);
        $this->assertArrayNotHasKey('PARAM3', $arrErr);
    }

    /**
     * errorCheck()メソッドが記号を含むパラメータ値でも正常に動作することを確認
     */
    public function testErrorCheck_記号を含むパラメータ値で正常に動作()
    {
        $arrKeys = ['PARAM_WITH_SYMBOLS'];
        $arrForm = [
            'PARAM_WITH_SYMBOLS' => '記号を含む値 (例：#1-2-3 @test)',
        ];

        $arrErr = $this->objPage->errorCheck($arrKeys, $arrForm);

        // エラーが返されないことを確認
        $this->assertEmpty($arrErr);
    }

    /**
     * errorCheck()メソッドがシングルクォートでFatal Errorを発生させないことを確認
     *
     * Issue #1297: PHP 8.3でシングルクォートを入力するとFatal errorが発生する問題
     */
    public function testErrorCheck_シングルクォートでFatalErrorが発生しない()
    {
        $arrKeys = ['PARAM_WITH_QUOTE'];
        $arrForm = [
            'PARAM_WITH_QUOTE' => "'",
        ];

        $arrErr = $this->objPage->errorCheck($arrKeys, $arrForm);

        // Fatal Errorにならず、バリデーションエラーが返されることを確認
        $this->assertArrayHasKey('PARAM_WITH_QUOTE', $arrErr);
        $this->assertStringContainsString('形式が不正です', $arrErr['PARAM_WITH_QUOTE']);
    }

    /**
     * errorCheck()メソッドが構文エラーを引き起こす値でFatal Errorを発生させないことを確認
     */
    public function testErrorCheck_構文エラーでFatalErrorが発生しない()
    {
        $arrKeys = ['PARAM_WITH_SYNTAX_ERROR'];
        $arrForm = [
            'PARAM_WITH_SYNTAX_ERROR' => '"unclosed string',
        ];

        $arrErr = $this->objPage->errorCheck($arrKeys, $arrForm);

        // Fatal Errorにならず、バリデーションエラーが返されることを確認
        $this->assertArrayHasKey('PARAM_WITH_SYNTAX_ERROR', $arrErr);
        $this->assertStringContainsString('形式が不正です', $arrErr['PARAM_WITH_SYNTAX_ERROR']);
    }
}
