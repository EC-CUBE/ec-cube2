<?php

$HOME = realpath(__DIR__).'/../../../../..';
require_once $HOME.'/tests/class/Common_TestCase.php';

class LC_Page_Admin_Products_UploadCSVTest extends Common_TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if ('\\' !== DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Windows only');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // ロケールを初期化する
        $initial = new SC_Initial_Ex();
        $initial->phpconfigInit();
    }

    public function test日本語ロケールで商品csvアップロード()
    {
        // 日本語版 Windows のロケールを設定する
        setlocale(LC_ALL, 'Japanese_Japan.932');

        $HOME = realpath(__DIR__).'/../../../../..';
        $filepath = $HOME.'/tests/product.csv';
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
            if (PHP_VERSION_ID >= 70000) {
                $this->assertNotCount($this->expected, $arrCSV, '日本語のロケールでは '.$line_count.' 列目の行数が不正になるはず');
            } else {
                $this->assertCount($this->expected, $arrCSV, 'PHP5系は日本語のロケールでもアップロード可能');
            }

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

    public function testSCInitialのロケールで商品csvアップロード()
    {
        $HOME = realpath(__DIR__).'/../../../../..';
        $filepath = $HOME.'/tests/product.csv';
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
            $this->assertCount($this->expected, $arrCSV, $line_count.' 列目の行数は一致する');

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

    public function test英語のロケールで商品csvアップロード()
    {
        // 英語版 Windows のロケールを設定する
        setlocale(LC_ALL, 'English_United States.1252');

        $HOME = realpath(__DIR__).'/../../../../..';
        $filepath = $HOME.'/tests/product.csv';
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
            $this->assertCount($this->expected, $arrCSV, $line_count.' 列目の行数は一致する');

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
