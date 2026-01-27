<?php

require_once __DIR__.'/SC_Helper_CSV_TestBase.php';

/**
 * SC_Helper_CSV::sfArrayToCsv()のテストクラス.
 *
 * @deprecated このメソッドは非推奨だが、既存コードとの互換性のためテストする
 */
class SC_Helper_CSV_sfArrayToCsvTest extends SC_Helper_CSV_TestBase
{
    public function testSfArrayToCsv基本的なCSV変換()
    {
        $fields = ['id', 'name', 'price'];

        $result = $this->objHelper->sfArrayToCsv($fields);

        $this->assertEquals('id,name,price', $result);
    }

    public function testSfArrayToCsvカンマを含むフィールドは囲まれる()
    {
        $fields = ['id', 'name,with,comma', 'price'];

        $result = $this->objHelper->sfArrayToCsv($fields);

        $this->assertEquals('id,"name,with,comma",price', $result);
    }

    public function testSfArrayToCsv引用符を含むフィールド()
    {
        $fields = ['id', 'name"with"quote', 'price'];

        $result = $this->objHelper->sfArrayToCsv($fields);

        $this->assertEquals('id,"name""with""quote",price', $result, '引用符がエスケープされる');
    }

    public function testSfArrayToCsv改行を含むフィールド()
    {
        $fields = ['id', "name\nwith\nnewline", 'price'];

        $result = $this->objHelper->sfArrayToCsv($fields);

        $this->assertStringContainsString('"name', $result, '改行を含むフィールドは囲まれる');
    }

    public function testSfArrayToCsv配列フィールドはパイプ区切りに変換()
    {
        $fields = ['id', ['item1', 'item2', 'item3'], 'price'];

        $result = $this->objHelper->sfArrayToCsv($fields);

        $this->assertEquals('id,item1|item2|item3,price', $result);
    }

    public function testSfArrayToCsv空配列()
    {
        $fields = [];

        $result = $this->objHelper->sfArrayToCsv($fields);

        $this->assertEquals('', $result);
    }

    public function testSfArrayToCsvカスタム区切り文字()
    {
        $fields = ['id', 'name', 'price'];

        $result = $this->objHelper->sfArrayToCsv($fields, ';');

        $this->assertEquals('id;name;price', $result);
    }

    public function testSfArrayToCsvカスタム囲み文字()
    {
        $fields = ['id', 'name,with,comma', 'price'];

        $result = $this->objHelper->sfArrayToCsv($fields, ',', "'");

        $this->assertEquals("id,'name,with,comma',price", $result);
    }
}
