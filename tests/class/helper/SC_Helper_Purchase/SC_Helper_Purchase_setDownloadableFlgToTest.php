<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_Purchase/SC_Helper_Purchase_TestBase.php';
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
 * SC_Helper_Purchase::setDownloadableFlgTo()のテストクラス.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Helper_Purchase_setDownloadableFlgToTest extends SC_Helper_Purchase_TestBase
{
    protected function setUp(): void
    {
        // parent::setUp();
    }

    protected function tearDown(): void
    {
        // parent::tearDown();
    }

    // ///////////////////////////////////////
    public function testSetDownloadableFlgTo販売価格が0円の場合フラグがONになる()
    {
        $input = [
            '1001' => ['price' => 0],
        ];

        $this->expected = true;
        SC_Helper_Purchase::setDownloadableFlgTo($input);
        $this->actual = $input['1001']['is_downloadable'];

        $this->verify('ダウンロード可能フラグ設定結果');
    }

    public function testSetDownloadableFlgToダウンロード期限内かつ入金日ありの場合フラグがONになる()
    {
        $input = [
            '1001' => ['price' => 1000, 'effective' => '1', 'payment_date' => '2012-12-12'],
        ];

        $this->expected = true;
        SC_Helper_Purchase::setDownloadableFlgTo($input);
        $this->actual = $input['1001']['is_downloadable'];

        $this->verify('ダウンロード可能フラグ設定結果');
    }

    public function testSetDownloadableFlgToダウンロード期限内かつ入金日なしの場合フラグがOFFになる()
    {
        $input = [
            '1001' => ['price' => 1000, 'effective' => '1', 'payment_date' => null],
        ];

        $this->expected = false;
        SC_Helper_Purchase::setDownloadableFlgTo($input);
        $this->actual = $input['1001']['is_downloadable'];

        $this->verify('ダウンロード可能フラグ設定結果');
    }

    public function testSetDownloadableFlgToダウンロード期限外かつ入金日ありの場合フラグがOFFになる()
    {
        $input = [
            '1001' => ['price' => 1000, 'effective' => '0', 'payment_date' => '2012-12-12'],
        ];

        $this->expected = false;
        SC_Helper_Purchase::setDownloadableFlgTo($input);
        $this->actual = $input['1001']['is_downloadable'];

        $this->verify('ダウンロード可能フラグ設定結果');
    }

    public function testSetDownloadableFlgToダウンロード期限外かつ入金日なしの場合フラグがOFFになる()
    {
        $input = [
            '1001' => ['price' => 1000, 'effective' => '0', 'payment_date' => null],
        ];

        $this->expected = false;
        SC_Helper_Purchase::setDownloadableFlgTo($input);
        $this->actual = $input['1001']['is_downloadable'];

        $this->verify('ダウンロード可能フラグ設定結果');
    }

    // ////////////////////////////////////////
}
