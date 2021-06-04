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

class SC_CheckError_FILE_NAME_CHECK_BY_NOUPLOADTest extends SC_CheckError_AbstractTestCase
{

    public function setUp(): void {
        parent::setUp();
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new RuntimeException($errstr . " on line " . $errline . " in file " . $errfile);
        });
        $this->target_func = 'FILE_NAME_CHECK_BY_NOUPLOAD';
    }

    public function tearDown(): void {
        restore_error_handler();
        parent::tearDown();
    }

    public function validValueProvider()
    {
        return array(
            array('a'),
            array('012'),
            array('abc012'),
            array('a.txt'),
            array('a-b.zip'),
            array('a-b_c.tar.gz'),
        );
    }

    public function invalidValueProvider()
    {
        return array(
            array("line1\nline2"),
            array("a\x00b"),
            array('a/b'),
            array('a b'),
            array('日本語'),
            array('日 本 語'),
        );
    }

    public function testFILE_NAME_CHECK_BY_NOUPLOAD_空文字列の場合_エラーをセットしない()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->scenario();

        $this->assertArrayNotHasKey(self::FORM_NAME, $this->objErr->arrErr);
    }

    /**
     * @dataProvider validValueProvider
     */
    public function testFILE_NAME_CHECK_BY_NOUPLOAD_使用できない文字が含まれていない場合_エラーをセットしない($value)
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->scenario();

        $this->assertArrayNotHasKey(self::FORM_NAME, $this->objErr->arrErr);
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function testFILE_NAME_CHECK_BY_NOUPLOAD_使用できない文字が含まれている場合_エラーをセットする($value)
    {
        $this->arrForm = [self::FORM_NAME => $value];
        $this->scenario();

        $this->assertArrayHasKey(self::FORM_NAME, $this->objErr->arrErr);
    }

    /**
     * @depends testFILE_NAME_CHECK_BY_NOUPLOAD_使用できない文字が含まれている場合_エラーをセットする
     */
    public function testFILE_NAME_CHECK_BY_NOUPLOAD_他のエラーが既にセットされている場合_エラーを上書きしない()
    {
        $this->arrForm = [self::FORM_NAME => 'a/b'];
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->arrErr[self::FORM_NAME] = $other_error = 'Unknown error.';
        $this->objErr->doFunc(array('label', self::FORM_NAME) ,array('FILE_NAME_CHECK_BY_NOUPLOAD'));

        $this->expected = $other_error;
        $this->actual = $this->objErr->arrErr[self::FORM_NAME];
        $this->assertSame($this->expected, $this->actual);
    }
}
