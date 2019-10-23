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

class SC_CheckError_SPTAB_CHECKTest extends SC_CheckError_AbstractTestCase
{

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'SPTAB_CHECK';
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /////////////////////////////////////////

    public function testSPTAB_CHECK_タブのみの入力()
    {
        $this->arrForm = [self::FORM_NAME => "\t"];
        $this->scenario();

        $this->expected = '※ SPTAB_CHECKにスペース、タブ、改行のみの入力はできません。<br />';
        $this->verify();
    }

    public function testSPTAB_CHECK_半角スペースのみの入力()
    {
        $this->arrForm = [self::FORM_NAME => " "];
        $this->scenario();

        $this->expected = '※ SPTAB_CHECKにスペース、タブ、改行のみの入力はできません。<br />';
        $this->verify();
    }

    public function testSPTAB_CHECK_全角スペースのみの入力()
    {
        $this->arrForm = [self::FORM_NAME => "　"];
        $this->scenario();

        $this->expected = '※ SPTAB_CHECKにスペース、タブ、改行のみの入力はできません。<br />';
        $this->verify();
    }

    public function testSPTAB_CHECK_改行のみの入力()
    {
        $this->arrForm = [self::FORM_NAME => "\n"];
        $this->scenario();

        $this->expected = '※ SPTAB_CHECKにスペース、タブ、改行のみの入力はできません。<br />';
        $this->verify();
    }

    public function testSPTAB_CHECK_改行のみの入力2()
    {
        $this->arrForm = [self::FORM_NAME => "\r"];
        $this->scenario();

        $this->expected = '※ SPTAB_CHECKにスペース、タブ、改行のみの入力はできません。<br />';
        $this->verify();
    }

    public function testSPTAB_CHECK_スペース改行タブの混在()
    {
        $this->arrForm = [self::FORM_NAME => " 　\t\n\r"];
        $this->scenario();

        $this->expected = '※ SPTAB_CHECKにスペース、タブ、改行のみの入力はできません。<br />';
        $this->verify();
    }

    public function testSPTAB_CHECK_文字の先頭にスペース()
    {
        $this->arrForm = [self::FORM_NAME => " test"];
        $this->scenario();

        $this->expected = '';
        $this->verify();
    }

    public function testSPTAB_CHECK_文字の間にスペース()
    {
        $this->arrForm = [self::FORM_NAME => "te st"];
        $this->scenario();

        $this->expected = '';
        $this->verify();
    }

    public function testSPTAB_CHECK_文字の最後にスペース()
    {
        $this->arrForm = [self::FORM_NAME => "test "];
        $this->scenario();

        $this->expected = '';
        $this->verify();
    }
}
