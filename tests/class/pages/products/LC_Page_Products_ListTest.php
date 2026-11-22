<?php

class LC_Page_Products_ListTest extends Common_TestCase
{
    /**
     * @dataProvider displayNumberProvider
     */
    public function testLfGetDisplayNum($displayNumber, $productListMax, $expected)
    {
        $page = new LC_Page_Products_List_Ex();
        $page->arrPRODUCTLISTMAX = $productListMax;

        $this->assertSame($expected, $page->lfGetDisplayNum($displayNumber));
    }

    public function displayNumberProvider()
    {
        return [
            '整数値はそのまま返す' => [20, [10 => '10件', 20 => '20件'], 20],
            '数値文字列はそのまま返す' => ['20', [10 => '10件', 20 => '20件'], '20'],
            '空文字は先頭キーを返す' => ['', [10 => '10件', 20 => '20件'], 10],
            '非数値は先頭キーを返す' => ['invalid', [10 => '10件', 20 => '20件'], 10],
            'キーが連番でなくても先頭キーを返す' => ['', [25 => '25件', 50 => '50件'], 25],
        ];
    }
}
