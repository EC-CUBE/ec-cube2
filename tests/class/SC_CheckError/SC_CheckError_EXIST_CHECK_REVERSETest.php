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

class SC_CheckError_EXIST_CHECK_REVERSETest extends SC_CheckError_AbstractTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'EXIST_CHECK_REVERSE';
    }

    public function testEXIST_CHECK_REVERSE_formが空()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = "※ {$this->target_func}が入力されていません。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_REVERSE_formがNULL()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '※ EXIST_CHECK_REVERSEが入力されていません。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_REVERSE_formがint0()
    {
        $this->arrForm = [self::FORM_NAME => 0];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEXIST_CHECK_REVERSE_formがstring0()
    {
        $this->arrForm = [self::FORM_NAME => '0'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    /**
     * {@inheritdoc}
     */
    protected function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([self::FORM_NAME, $this->target_func], [$this->target_func]);
        $this->objErr->doFunc([self::FORM_NAME, 'dummy'], [$this->target_func]);
    }
}
