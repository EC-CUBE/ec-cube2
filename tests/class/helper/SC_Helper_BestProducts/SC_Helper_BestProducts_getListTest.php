<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_BestProducts/SC_Helper_BestProducts_TestBase.php';
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
 * SC_Helper_BestProducts::getList()のテストクラス.
 *
 * @author hiroshi kakuta
 */
class SC_Helper_BestProducts_getListTest extends SC_Helper_BestProducts_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**　rankが存在しない場合、空を返す。
     */
    public function testGetList存在しない場合空を返す()
    {
        $this->deleteAllBestProducts();

        $this->expected = [];
        $this->actual = SC_Helper_BestProducts_Ex::getList();

        $this->verify();
    }

    public function testGetListデータがある場合想定した結果が返る()
    {
        $this->setUpBestProducts();

        $this->expected = [
            0 => [
                'best_id' => '1001',
                'product_id' => '2',
                'category_id' => '0',
                'rank' => '1',
                'title' => 'タイトルですよ',
                'comment' => 'コメントですよ',
                'creator_id' => '1',
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
                'del_flg' => '0',
            ],
            1 => [
                'best_id' => '1003',
                'product_id' => '3',
                'category_id' => '1',
                'rank' => '3',
                'title' => 'タイトルですよ3',
                'comment' => 'コメントですよ3',
                'creator_id' => '3',
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
                'del_flg' => '0',
            ],
        ];

        $this->actual = SC_Helper_BestProducts_Ex::getList();
        $this->verify();
    }

    public function testGetList一覧取得hasDeleteをtrueにした場合削除済みデータも取得()
    {
        $this->setUpBestProducts();

        $this->expected = [
            0 => [
                'best_id' => '1001',
                'product_id' => '2',
                'category_id' => '0',
                'rank' => '1',
                'title' => 'タイトルですよ',
                'comment' => 'コメントですよ',
                'creator_id' => '1',
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
                'del_flg' => '0',
            ],
            1 => [
                'best_id' => '1002',
                'product_id' => '1',
                'category_id' => '0',
                'rank' => '2',
                'title' => 'タイトルですよ',
                'comment' => 'コメントですよ',
                'creator_id' => '1',
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
                'del_flg' => '1',
            ],
            2 => [
                'best_id' => '1003',
                'product_id' => '3',
                'category_id' => '1',
                'rank' => '3',
                'title' => 'タイトルですよ3',
                'comment' => 'コメントですよ3',
                'creator_id' => '3',
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
                'del_flg' => '0',
            ],
        ];

        $this->actual = SC_Helper_BestProducts_Ex::getList(0, 0, true);
        $this->verify();
    }

    public function testGetListページングが想定した結果が返る表示件数1ページ番号2()
    {
        $this->setUpBestProducts();

        $this->expected = [
            0 => [
                'best_id' => '1003',
                'product_id' => '3',
                'category_id' => '1',
                'rank' => '3',
                'title' => 'タイトルですよ3',
                'comment' => 'コメントですよ3',
                'creator_id' => '3',
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
                'del_flg' => '0',
            ],
        ];

        $this->actual = SC_Helper_BestProducts_Ex::getList(1, 2);
        $this->verify();
    }

    public function testGetListページングが想定した結果が返る表示件数1ページ番号0()
    {
        $this->setUpBestProducts();

        $this->expected = [
            0 => [
                'best_id' => '1001',
                'product_id' => '2',
                'category_id' => '0',
                'rank' => '1',
                'title' => 'タイトルですよ',
                'comment' => 'コメントですよ',
                'creator_id' => '1',
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
                'del_flg' => '0',
            ],
        ];

        $this->actual = SC_Helper_BestProducts_Ex::getList(1, 0);
        $this->verify();
    }
}
