<?php

$HOME = realpath(dirname(__FILE__)) . "/../../../../..";
require_once($HOME . "/tests/class/Common_TestCase.php");
/**
 *
 */
class LC_Page_Admin_Products_UploadCSVTest extends Common_TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function test商品csvアップロード()
    {
        $HOME = realpath(dirname(__FILE__)) . "/../../../../..";
        $filepath = $HOME . "/tests/product.csv";
        $enc_filepath = SC_Utils_Ex::sfEncodeFile($filepath, CHAR_CODE, CSV_TEMP_REALDIR);

        $fp = fopen($enc_filepath, 'r');

        $line_count = 0;
        while (!feof($fp)) {
            $arrCSV = fgetcsv($fp, CSV_LINE_MAX);
            // 空行はスキップ
            if (empty($arrCSV)) {
                continue;
            }
            // 行カウント
            $line_count++;

            $this->expected = 71;
            $this->actual = count($arrCSV);
            $this->verify($line_count.'列目の行数');


            // ヘッダ行はスキップ
            if ($line_count == 1) {
                continue;
            }
        }
        fclose($fp);

        $this->expected = 10;
        $this->actual = $line_count;

        $this->verify('列数');
    }
}
