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

class SC_CheckError_EXIST_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'EXIST_CHECK';
    }

    public function testEXISTCHECKFormが空文字の場合エラー()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = "※ {$this->target_func}が入力されていません。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testEXISTCHECKFormがnullの場合エラー()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = "※ {$this->target_func}が入力されていません。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testEXISTCHECKFormがfalseの場合エラー()
    {
        $this->arrForm = [self::FORM_NAME => false];
        $this->expected = "※ {$this->target_func}が入力されていません。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testEXISTCHECKFormがint0の場合エラーではない()
    {
        $this->arrForm = [self::FORM_NAME => 0];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXISTCHECKFormがfloat0の場合エラーではない()
    {
        $this->arrForm = [self::FORM_NAME => 0.0];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXISTCHECKFormがstring0の場合エラーではない()
    {
        $this->arrForm = [self::FORM_NAME => '0'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXISTCHECKFormが普通の文字列の場合エラーではない()
    {
        $this->arrForm = [self::FORM_NAME => '普通のテスト文字列'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXISTCHECKFormが空の配列の場合エラー()
    {
        $this->arrForm = [self::FORM_NAME => []];
        $this->expected = "※ {$this->target_func}が選択されていません。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testEXISTCHECKFormが空文字の配列の場合エラーではない()
    {
        $this->arrForm = [self::FORM_NAME => ['']];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXISTCHECKFormが0しか含まない配列の場合エラーではない()
    {
        $this->arrForm = [self::FORM_NAME => [0]];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXISTCHECKFormが配列の場合エラーではない()
    {
        $this->arrForm = ['form' => [1, 2, 3]];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXISTCHECKFormが連想配列の場合エラーではない()
    {
        $this->arrForm = ['form' => [0 => 'A', 1 => 'B', 2 => 'C']];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    /**
     * 日本語を含む表示名でも正常に動作することを確認
     *
     * PR #1157 によるデグレ対策テスト
     * 引数の順序が [表示名, 判定対象配列キー] の場合、
     * 表示名に日本語を含んでも正常に動作することを確認
     */
    public function testEXISTCHECK日本語を含む表示名でも正常に動作()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $disp_name = '市区町村名 (例：千代田区神田神保町)';

        $objErr = new SC_CheckError_Ex($this->arrForm);
        $objErr->doFunc([$disp_name, self::FORM_NAME], [$this->target_func]);

        $this->expected = "※ {$disp_name}が入力されていません。<br />";
        $this->actual = $objErr->arrErr[self::FORM_NAME] ?? null;

        $this->verify('日本語を含む表示名でも正常にエラーメッセージが生成される');
    }

    /**
     * 日本語・記号を含む表示名が値として正しく処理されることを確認
     */
    public function testEXISTCHECK日本語と記号を含む表示名で正常に動作()
    {
        $this->arrForm = [self::FORM_NAME => 'test value'];
        $disp_name = '都道府県名 (例：東京都) #必須項目';

        $objErr = new SC_CheckError_Ex($this->arrForm);
        $objErr->doFunc([$disp_name, self::FORM_NAME], [$this->target_func]);

        $this->expected = '';
        $this->actual = $objErr->arrErr[self::FORM_NAME] ?? null;

        $this->verify('日本語・記号を含む表示名でも値がある場合はエラーにならない');
    }
}
