<?php

$HOME = realpath(__DIR__).'/../../../..';
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
 * SC_Utils::formatName() のテストクラス
 *
 * @author Seasoft 塚田将久 (新規作成)
 */
class SC_Utils_formatNameTest extends Common_TestCase
{
    public function test典型パターン()
    {
        $this->assertSame('山田 太郎', SC_Utils::formatName([
            'name01' => '山田',
            'name02' => '太郎',
        ]));
    }

    public function test受注情報フリガナ()
    {
        $this->assertSame('ヤマダ タロウ', SC_Utils::formatName([
            'order_kana01' => 'ヤマダ',
            'order_kana02' => 'タロウ',
        ], 'order_kana'));
    }

    public function testGetFormParamList形式()
    {
        $this->assertSame('山田 太郎', SC_Utils::formatName([
            'name01' => ['value' => '山田'],
            'name02' => ['value' => '太郎'],
        ]));
    }

    public function test姓のみ()
    {
        $this->assertSame('山田', SC_Utils::formatName([
            'name01' => '山田',
        ]));
    }

    public function test名のみ()
    {
        $this->assertSame('太郎', SC_Utils::formatName([
            'name02' => '太郎',
        ]));
    }

    public function testNull()
    {
        $this->assertSame('', SC_Utils::formatName(null));
    }

    public function testNull要素()
    {
        $this->assertSame('', SC_Utils::formatName([
            'name01' => null,
            'name02' => null,
        ]));
    }

    public function test空配列()
    {
        $this->assertSame('', SC_Utils::formatName([]));
    }

    public function test空文字列()
    {
        $this->assertSame('', SC_Utils::formatName([
            'name01' => '',
            'name02' => '',
        ]));
    }

    public function test異常()
    {
        $this->assertSame('', SC_Utils::formatName([
            'name01' => ['山田'],
            'name02' => ['value' => ['太郎']],
        ]));
    }
}
