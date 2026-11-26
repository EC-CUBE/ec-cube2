<?php

$HOME = realpath(__DIR__).'/../../../../..';
require_once $HOME.'/tests/class/Common_TestCase.php';

class LC_Page_Admin_Products_ProductClassTest extends Common_TestCase
{
    /** @var LC_Page_Admin_Products_ProductClass_Ex */
    protected $objPage;

    /** @var SC_FormParam_Ex */
    protected $objFormParam;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objPage = new LC_Page_Admin_Products_ProductClass_Ex();
        $this->objFormParam = new SC_FormParam_Ex();
        $this->objPage->initParam($this->objFormParam);
    }

    public function testPadRowValues未設定の行の値は空文字の配列になる()
    {
        $this->objFormParam->setParam([
            'total' => '3',
            'product_class_id' => ['10', '11', '12'],
        ]);
        $this->objFormParam->setValue('check', '');

        $this->objPage->padRowValues($this->objFormParam);

        $this->assertSame(['', '', ''], $this->objFormParam->getValue('check'));
        $this->assertSame(['', '', ''], $this->objFormParam->getValue('down_realfilename'));
        $this->assertSame(['10', '11', '12'], $this->objFormParam->getValue('product_class_id'));
    }

    public function testPadRowValuesチェックボックスは選択されていない行を補う()
    {
        $this->objFormParam->setParam([
            'total' => '4',
            'check' => [1 => '1', 3 => '1'],
            'stock_unlimited' => [2 => '1'],
        ]);

        $this->objPage->padRowValues($this->objFormParam);

        $this->assertSame(['', '1', '', '1'], $this->objFormParam->getValue('check'));
        $this->assertSame(['', '', '1', ''], $this->objFormParam->getValue('stock_unlimited'));
    }

    public function testPadRowValues行ごとに参照しても警告が出ない()
    {
        $this->objFormParam->setParam(['total' => '2']);
        $this->objPage->padRowValues($this->objFormParam);
        $arrValues = $this->objFormParam->getHashArray();

        $warnings = [];
        set_error_handler(function ($errno, $errstr) use (&$warnings) {
            $warnings[] = $errstr;

            return true;
        }, E_WARNING);
        try {
            $arrRows = [];
            for ($i = 0; $i < 2; $i++) {
                $arrRows[] = [$arrValues['check'][$i], $arrValues['stock_unlimited'][$i], $arrValues['down_realfilename'][$i]];
            }
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $warnings);
        $this->assertSame([['', '', ''], ['', '', '']], $arrRows);
    }
}
