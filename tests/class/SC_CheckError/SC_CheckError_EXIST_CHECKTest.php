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

    public function testEXIST_CHECK_formが空文字の場合_エラー()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = "※ {$this->target_func}が入力されていません。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_formがnullの場合_エラー()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = "※ {$this->target_func}が入力されていません。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_formがfalseの場合_エラー()
    {
        $this->arrForm = [self::FORM_NAME => false];
        $this->expected = "※ {$this->target_func}が入力されていません。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_formがint0の場合_エラーではない()
    {
        $this->arrForm = [self::FORM_NAME => 0];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_formがfloat0の場合_エラーではない()
    {
        $this->arrForm = [self::FORM_NAME => 0.0];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_formがstring0の場合_エラーではない()
    {
        $this->arrForm = [self::FORM_NAME => '0'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_formが普通の文字列の場合_エラーではない()
    {
        $this->arrForm = [self::FORM_NAME => '普通のテスト文字列'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_formが空の配列の場合_エラー()
    {
        $this->arrForm = [self::FORM_NAME => []];
        $this->expected = "※ {$this->target_func}が選択されていません。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_formが空文字の配列の場合_エラーではない()
    {
        $this->arrForm =[self::FORM_NAME => ['']];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_formが0しか含まない配列の場合_エラーではない()
    {
        $this->arrForm = [self::FORM_NAME => [0]];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_formが配列の場合_エラーではない()
    {
        $this->arrForm = ['form' => [1, 2, 3]];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_formが連想配列の場合_エラーではない()
    {
        $this->arrForm = ['form' => [0 => 'A', 1 => 'B', 2 => 'C']];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }
}

