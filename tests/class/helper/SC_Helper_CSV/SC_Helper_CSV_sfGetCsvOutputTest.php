<?php

require_once __DIR__.'/SC_Helper_CSV_TestBase.php';

/**
 * SC_Helper_CSV::sfGetCsvOutput()のテストクラス.
 */
class SC_Helper_CSV_sfGetCsvOutputTest extends SC_Helper_CSV_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用のCSV設定を作成
        $this->createCsvConfig(1, [
            [
                'no' => 1,
                'col' => 'product_id',
                'disp_name' => '商品ID',
                'rank' => 1,
                'status' => CSV_COLUMN_STATUS_FLG_ENABLE,
                'rw_flg' => CSV_COLUMN_RW_FLG_KEY_FIELD,
                'mb_convert_kana_option' => '',
                'size_const_type' => '',
                'error_check_types' => 'EXIST_CHECK,NUM_CHECK',
            ],
            [
                'no' => 2,
                'col' => 'product_name',
                'disp_name' => '商品名',
                'rank' => 2,
                'status' => CSV_COLUMN_STATUS_FLG_ENABLE,
                'rw_flg' => CSV_COLUMN_RW_FLG_READ_WRITE,
                'mb_convert_kana_option' => '',
                'size_const_type' => 'STEXT_LEN',
                'error_check_types' => 'EXIST_CHECK',
            ],
            [
                'no' => 3,
                'col' => 'price',
                'disp_name' => '価格',
                'rank' => 3,
                'status' => CSV_COLUMN_STATUS_FLG_DISABLE,
                'rw_flg' => CSV_COLUMN_RW_FLG_READ_WRITE,
                'mb_convert_kana_option' => '',
                'size_const_type' => '',
                'error_check_types' => '',
            ],
        ]);
    }

    public function testSfGetCsvOutputCSV設定を取得()
    {
        $result = $this->objHelper->sfGetCsvOutput(1);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    public function testSfGetCsvOutputランク順でソートされる()
    {
        $result = $this->objHelper->sfGetCsvOutput(1);

        $this->assertEquals('product_id', $result[0]['col']);
        $this->assertEquals('product_name', $result[1]['col']);
        $this->assertEquals('price', $result[2]['col']);
    }

    public function testSfGetCsvOutputステータスでフィルタリング()
    {
        $result = $this->objHelper->sfGetCsvOutput(1, 'status = '.CSV_COLUMN_STATUS_FLG_ENABLE);

        $this->assertCount(2, $result, '有効な項目のみ取得される');
        $this->assertEquals('product_id', $result[0]['col']);
        $this->assertEquals('product_name', $result[1]['col']);
    }

    public function testSfGetCsvOutput存在しないCSVIDの場合()
    {
        $result = $this->objHelper->sfGetCsvOutput(9999);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testSfGetCsvOutput必要なカラムが含まれる()
    {
        $result = $this->objHelper->sfGetCsvOutput(1);

        $this->assertArrayHasKey('no', $result[0]);
        $this->assertArrayHasKey('csv_id', $result[0]);
        $this->assertArrayHasKey('col', $result[0]);
        $this->assertArrayHasKey('disp_name', $result[0]);
        $this->assertArrayHasKey('rank', $result[0]);
        $this->assertArrayHasKey('status', $result[0]);
        $this->assertArrayHasKey('rw_flg', $result[0]);
    }
}
