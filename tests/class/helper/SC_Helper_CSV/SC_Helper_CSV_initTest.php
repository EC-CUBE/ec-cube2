<?php

require_once __DIR__.'/SC_Helper_CSV_TestBase.php';

/**
 * SC_Helper_CSV::init()のテストクラス.
 */
class SC_Helper_CSV_initTest extends SC_Helper_CSV_TestBase
{
    public function testInit項目英名が設定される()
    {
        $this->objHelper->init();

        $expected = [
            1 => 'product',
            2 => 'customer',
            3 => 'order',
            4 => 'review',
            5 => 'category',
        ];

        $this->assertEquals($expected, $this->objHelper->arrSubnavi);
    }

    public function testInit項目名が設定される()
    {
        $this->objHelper->init();

        $expected = [
            1 => '商品管理',
            2 => '会員管理',
            3 => '受注管理',
            4 => 'レビュー',
            5 => 'カテゴリ',
        ];

        $this->assertEquals($expected, $this->objHelper->arrSubnaviName);
    }

    public function testコンストラクタで自動的にinitが呼ばれる()
    {
        $objHelper = new SC_Helper_CSV_Ex();

        $this->assertNotEmpty($objHelper->arrSubnavi);
        $this->assertNotEmpty($objHelper->arrSubnaviName);
    }
}
