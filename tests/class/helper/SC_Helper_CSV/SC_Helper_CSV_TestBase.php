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
 * SC_Helper_CSVのテストの基底クラス.
 */
class SC_Helper_CSV_TestBase extends Common_TestCase
{
    /** @var SC_Helper_CSV */
    protected $objHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objHelper = new SC_Helper_CSV_Ex();

        // dtb_csv テーブルをクリア（トランザクション内なので自動的にロールバックされる）
        $this->objQuery->delete('dtb_csv');
    }

    /**
     * テスト用のCSV設定データを作成
     *
     * @param int $csv_id CSV ID
     * @param array $columns カラム設定の配列
     */
    protected function createCsvConfig($csv_id, $columns = [])
    {
        if (empty($columns)) {
            // デフォルトのカラム設定
            $columns = [
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
                    'error_check_types' => 'EXIST_CHECK,SPTAB_CHECK,MAX_LENGTH_CHECK',
                ],
            ];
        }

        foreach ($columns as $column) {
            $sqlval = array_merge([
                'csv_id' => $csv_id,
                'create_date' => 'CURRENT_TIMESTAMP',
                'update_date' => 'CURRENT_TIMESTAMP',
            ], $column);

            $this->objQuery->insert('dtb_csv', $sqlval);
        }
    }

    /**
     * テスト用の一時CSVファイルを作成
     *
     * @param array $data CSV データ（2次元配列）
     *
     * @return string 一時ファイルのパス
     */
    protected function createTempCsvFile($data)
    {
        $tmpfile = tmpfile();
        $tmpfile_path = stream_get_meta_data($tmpfile)['uri'];

        foreach ($data as $row) {
            fputcsv($tmpfile, $row);
        }

        rewind($tmpfile);

        // ファイルハンドルを保持して、テスト終了時に自動削除されるようにする
        $this->tmpFiles[] = $tmpfile;

        return $tmpfile_path;
    }

    /** @var resource[] 一時ファイルハンドル */
    private $tmpFiles = [];

    protected function tearDown(): void
    {
        // 一時ファイルを閉じる（自動削除される）
        foreach ($this->tmpFiles as $tmpfile) {
            if (is_resource($tmpfile)) {
                fclose($tmpfile);
            }
        }
        $this->tmpFiles = [];

        parent::tearDown();
    }
}
