<?php

require_once __DIR__.'/SC_Helper_CSV_TestBase.php';

/**
 * SC_Helper_CSV::fopen_for_output_csv()のテストクラス.
 */
class SC_Helper_CSV_fopenForOutputCsvTest extends SC_Helper_CSV_TestBase
{
    public function testFopenForOutputCsvファイルポインタが開かれる()
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'csv_');

        $fp = SC_Helper_CSV::fopen_for_output_csv($tmpfile);

        $this->assertIsResource($fp, 'ファイルポインタが返される');
        $this->assertTrue(fclose($fp));

        unlink($tmpfile);
    }

    public function testFopenForOutputCsvデフォルトはphpOutput()
    {
        ob_start();

        $fp = SC_Helper_CSV::fopen_for_output_csv();

        $this->assertIsResource($fp, 'ファイルポインタが返される');

        fwrite($fp, 'test');
        fclose($fp);

        $output = ob_get_clean();
        $this->assertEquals('test', $output, 'php://output に書き込まれる');
    }

    public function testFopenForOutputCsvCSVが書き込める()
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'csv_');

        $fp = SC_Helper_CSV::fopen_for_output_csv($tmpfile);

        $data = [['id', 'name'], ['1', '商品A']];
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }

        fclose($fp);

        $content = file_get_contents($tmpfile);
        $this->assertStringContainsString('id,name', $content);
        $this->assertStringContainsString('1,商品A', $content);

        unlink($tmpfile);
    }

    public function testFopenForOutputCsv改行コードがCRLFに変換される()
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'csv_');

        $fp = SC_Helper_CSV::fopen_for_output_csv($tmpfile);

        fputcsv($fp, ['test']);
        fclose($fp);

        $content = file_get_contents($tmpfile);

        // 改行コードがCRLFになっているか確認
        $this->assertStringContainsString("\r\n", $content, '改行コードがCRLFになっている');

        unlink($tmpfile);
    }
}
