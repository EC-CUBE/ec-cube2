<?php

require_once __DIR__.'/SC_Helper_CSV_TestBase.php';

/**
 * SC_Helper_CSV::sfIsUpdateCSVFrame()のテストクラス.
 */
class SC_Helper_CSV_sfIsUpdateCSVFrameTest extends SC_Helper_CSV_TestBase
{
    public function testSfIsUpdateCSVFrame更新可能な設定()
    {
        $arrCSVFrame = [
            [
                'status' => CSV_COLUMN_STATUS_FLG_ENABLE,
                'rw_flg' => CSV_COLUMN_RW_FLG_KEY_FIELD,
            ],
            [
                'status' => CSV_COLUMN_STATUS_FLG_ENABLE,
                'rw_flg' => CSV_COLUMN_RW_FLG_READ_WRITE,
            ],
        ];

        $result = $this->objHelper->sfIsUpdateCSVFrame($arrCSVFrame);

        $this->assertTrue($result, '更新可能');
    }

    public function testSfIsUpdateCSVFrameキーフィールドが無効な場合()
    {
        $arrCSVFrame = [
            [
                'status' => CSV_COLUMN_STATUS_FLG_DISABLE,  // 無効
                'rw_flg' => CSV_COLUMN_RW_FLG_KEY_FIELD,    // キーフィールド
            ],
            [
                'status' => CSV_COLUMN_STATUS_FLG_ENABLE,
                'rw_flg' => CSV_COLUMN_RW_FLG_READ_WRITE,
            ],
        ];

        $result = $this->objHelper->sfIsUpdateCSVFrame($arrCSVFrame);

        $this->assertFalse($result, 'キーフィールドが無効な場合は更新不可');
    }

    public function testSfIsUpdateCSVFrame読み書きフィールドが無効でも問題ない()
    {
        $arrCSVFrame = [
            [
                'status' => CSV_COLUMN_STATUS_FLG_ENABLE,
                'rw_flg' => CSV_COLUMN_RW_FLG_KEY_FIELD,
            ],
            [
                'status' => CSV_COLUMN_STATUS_FLG_DISABLE,    // 無効
                'rw_flg' => CSV_COLUMN_RW_FLG_READ_WRITE,     // 通常フィールド
            ],
        ];

        $result = $this->objHelper->sfIsUpdateCSVFrame($arrCSVFrame);

        $this->assertTrue($result, '読み書きフィールドが無効でも更新可能');
    }

    public function testSfIsUpdateCSVFrame空配列の場合()
    {
        $arrCSVFrame = [];

        $result = $this->objHelper->sfIsUpdateCSVFrame($arrCSVFrame);

        $this->assertTrue($result, '空配列の場合は更新可能');
    }
}
