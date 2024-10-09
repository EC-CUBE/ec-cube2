<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_Maker/SC_Helper_Maker_TestBase.php';
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
 * SC_Helper_Maker::getList()のテストクラス.
 *
 * @author hiroshi kakuta
 */
class SC_Helper_Maker_getListTest extends SC_Helper_Maker_TestBase
{
    public $objHelperMaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objHelperMaker = new SC_Helper_Maker_Ex();
        $this->setUpMaker();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**　rankが存在しない場合、空を返す。
     */
    public function testGetList存在しない場合空を返す()
    {
        $this->deleteAllMaker();

        $this->expected = [];
        $this->actual = $this->objHelperMaker->getList();

        $this->verify();
    }

    public function testGetListデータがある場合想定した結果が返る()
    {
        $this->expected = [
            [
                'maker_id' => '1004',
                'name' => 'MEC',
            ],
            [
                'maker_id' => '1003',
                'name' => 'シャンプー',
            ],
            [
                'maker_id' => '1001',
                'name' => 'ソニン',
            ],
        ];

        $this->actual = $this->objHelperMaker->getList();
        $this->verify();
    }

    public function testGetList一覧取得hasDeleteをtrueにした場合削除済みデータも取得()
    {
        $this->expected = [
            [
                'maker_id' => '1004',
                'name' => 'MEC',
            ],
            [
                'maker_id' => '1003',
                'name' => 'シャンプー',
            ],
            [
                'maker_id' => '1002',
                'name' => 'パソナニック',
            ],
            [
                'maker_id' => '1001',
                'name' => 'ソニン',
            ],
        ];

        $this->actual = $this->objHelperMaker->getList(true);
        $this->verify();
    }
}
