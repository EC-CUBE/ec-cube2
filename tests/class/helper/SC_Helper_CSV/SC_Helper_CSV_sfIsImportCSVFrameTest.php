<?php

require_once __DIR__.'/SC_Helper_CSV_TestBase.php';

/**
 * SC_Helper_CSV::sfIsImportCSVFrame()のテストクラス.
 */
class SC_Helper_CSV_sfIsImportCSVFrameTest extends SC_Helper_CSV_TestBase
{
    public function testSfIsImportCSVFrameインポート可能な設定()
    {
        $arrCSVFrame = [
            [
                'status' => CSV_COLUMN_STATUS_FLG_ENABLE,
                'rw_flg' => CSV_COLUMN_RW_FLG_KEY_FIELD,
                'error_check_types' => 'EXIST_CHECK,NUM_CHECK',
            ],
            [
                'status' => CSV_COLUMN_STATUS_FLG_ENABLE,
                'rw_flg' => CSV_COLUMN_RW_FLG_READ_WRITE,
                'error_check_types' => 'EXIST_CHECK',
            ],
        ];

        $result = $this->objHelper->sfIsImportCSVFrame($arrCSVFrame);

        $this->assertTrue($result, 'インポート可能');
    }

    public function testSfIsImportCSVFrame必須フィールドが無効な場合()
    {
        $arrCSVFrame = [
            [
                'status' => CSV_COLUMN_STATUS_FLG_ENABLE,
                'rw_flg' => CSV_COLUMN_RW_FLG_READ_WRITE,
                'error_check_types' => 'EXIST_CHECK',
            ],
            [
                'status' => CSV_COLUMN_STATUS_FLG_DISABLE,  // 無効
                'rw_flg' => CSV_COLUMN_RW_FLG_READ_WRITE,
                'error_check_types' => 'EXIST_CHECK',  // 必須チェックあり
            ],
        ];

        $result = $this->objHelper->sfIsImportCSVFrame($arrCSVFrame);

        $this->assertFalse($result, '必須フィールドが無効な場合はインポート不可');
    }

    public function testSfIsImportCSVFrame無効だが必須チェックがない場合()
    {
        $arrCSVFrame = [
            [
                'status' => CSV_COLUMN_STATUS_FLG_ENABLE,
                'rw_flg' => CSV_COLUMN_RW_FLG_READ_WRITE,
                'error_check_types' => '',
            ],
            [
                'status' => CSV_COLUMN_STATUS_FLG_DISABLE,
                'rw_flg' => CSV_COLUMN_RW_FLG_READ_WRITE,
                'error_check_types' => '',  // 必須チェックなし
            ],
        ];

        $result = $this->objHelper->sfIsImportCSVFrame($arrCSVFrame);

        $this->assertTrue($result, '必須チェックがない場合はインポート可能');
    }

    public function testSfIsImportCSVFrame空配列の場合()
    {
        $arrCSVFrame = [];

        $result = $this->objHelper->sfIsImportCSVFrame($arrCSVFrame);

        $this->assertTrue($result, '空配列の場合はインポート可能');
    }
}
