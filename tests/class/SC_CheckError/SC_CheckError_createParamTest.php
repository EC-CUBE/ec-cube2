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


class SC_CheckError_createParamTest extends SC_CheckError_AbstractTestCase
{
    protected $old_reporting_level;

    protected function setUp()
    {
        parent::setUp();
        $this->old_reporting_level = error_reporting();
        error_reporting($this->old_reporting_level ^ (E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE));
        $this->target_func = 'EXIST_CHECK';
        $this->arrForm = array(self::FORM_NAME => array(0 => 'A', 1 => "B", 2 => 'C'));
        $this->objErr = new SC_CheckError_Ex($this->arrForm);

    }

    protected function tearDown()
    {
        parent::tearDown();
        error_reporting($this->old_reporting_level);
    }

    /////////////////////////////////////////

    public function testArrParamIsCaracter()
    {
        $this->objErr->doFunc(array('EXIST_CHECK', "aabbcc_1234"), array('EXIST_CHECK'));

        $this->expected = array(self::FORM_NAME => array (0 => 'A',1 => 'B', 2 => 'C'),
                                'aabbcc_1234' => '');
        $this->actual = $this->objErr->arrParam;
        $this->assertEquals($this->expected, $this->actual);
    }

    public function testArrParamIsIllegalCaracter()
    {
        $this->objErr->doFunc(array('EXIST_CHECK', "aabbcc_1234-"), array('EXIST_CHECK'));

        $this->expected = array(self::FORM_NAME => array (0 => 'A',1 => 'B', 2 => 'C'));
        $this->actual = $this->objErr->arrParam;
        $this->assertEquals($this->expected, $this->actual, 'arrParam is Illegal character');
    }


    public function testArrParamIsIllegalValue()
    {

        $this->arrForm = array(self::FORM_NAME => '/../\\\.');
        $this->scenario();

        $this->expected = "※ EXIST_CHECKに禁止された記号の並びまたは制御文字が入っています。<br />";
        $this->verify('arrParam is Illegal value');
    }

    public function testArrParamIsIllegalValue2()
    {
        $this->arrForm = array(self::FORM_NAME => "\x00");
        $this->scenario();

        $this->expected = "※ EXIST_CHECKに禁止された記号の並びまたは制御文字が入っています。<br />";
        $this->verify('arrParam is Illegal value2');
    }
}
