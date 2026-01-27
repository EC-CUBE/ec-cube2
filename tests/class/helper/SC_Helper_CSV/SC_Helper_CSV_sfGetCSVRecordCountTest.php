<?php

require_once __DIR__.'/SC_Helper_CSV_TestBase.php';

/**
 * SC_Helper_CSV::sfGetCSVRecordCount()のテストクラス.
 */
class SC_Helper_CSV_sfGetCSVRecordCountTest extends SC_Helper_CSV_TestBase
{
    public function testSfGetCSVRecordCountCSVファイルの行数を取得()
    {
        $data = [
            ['id', 'name', 'price'],
            ['1', '商品A', '1000'],
            ['2', '商品B', '2000'],
            ['3', '商品C', '3000'],
        ];

        $tmpfile = tmpfile();
        foreach ($data as $row) {
            fputcsv($tmpfile, $row);
        }
        rewind($tmpfile);

        $result = $this->objHelper->sfGetCSVRecordCount($tmpfile);

        $this->assertEquals(4, $result, 'ヘッダー含めて4行');

        fclose($tmpfile);
    }

    public function testSfGetCSVRecordCountヘッダーのみの場合()
    {
        $data = [
            ['id', 'name', 'price'],
        ];

        $tmpfile = tmpfile();
        foreach ($data as $row) {
            fputcsv($tmpfile, $row);
        }
        rewind($tmpfile);

        $result = $this->objHelper->sfGetCSVRecordCount($tmpfile);

        $this->assertEquals(1, $result);

        fclose($tmpfile);
    }

    public function testSfGetCSVRecordCount空ファイルの場合()
    {
        $tmpfile = tmpfile();
        rewind($tmpfile);

        $result = $this->objHelper->sfGetCSVRecordCount($tmpfile);

        $this->assertEquals(0, $result, '空ファイルは0行');

        fclose($tmpfile);
    }

    public function testSfGetCSVRecordCountファイルポインタがリセットされる()
    {
        $data = [
            ['id', 'name'],
            ['1', '商品A'],
        ];

        $tmpfile = tmpfile();
        foreach ($data as $row) {
            fputcsv($tmpfile, $row);
        }
        rewind($tmpfile);

        $this->objHelper->sfGetCSVRecordCount($tmpfile);

        // ファイルポインタが先頭に戻っているか確認
        $firstLine = fgetcsv($tmpfile);
        $this->assertEquals(['id', 'name'], $firstLine, 'ファイルポインタが先頭に戻っている');

        fclose($tmpfile);
    }
}
