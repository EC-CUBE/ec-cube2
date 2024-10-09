<?php

$HOME = realpath(__DIR__).'/../../../..';
// 商品別税率機能無効
define('OPTION_PRODUCT_TAX_RULE', 0);
require_once $HOME.'/tests/class/helper/SC_Helper_TaxRule/SC_Helper_TaxRule_TestBase.php';

class SC_Helper_TaxRule_getTaxRuleTest extends SC_Helper_TaxRule_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->objTaxRule = new SC_Helper_TaxRule_Ex();
        $this->setUpTax();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test定数が正しく設定されているかのテスト()
    {
        $this->expected = 0;
        $this->actual = constant('OPTION_PRODUCT_TAX_RULE');
        $this->verify();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test引数が空の場合税率設定で設定かつ適用日時内の最新の値が返される()
    {
        $this->expected = [
            'apply_date' => '2014-01-01 00:00:00',
            'tax_rate' => '5',
            'product_id' => '0',
            'product_class_id' => '0',
            'del_flg' => '0',
        ];

        $return = $this->objTaxRule->getTaxRule();
        $this->actual = [
            'apply_date' => $return['apply_date'],
            'tax_rate' => $return['tax_rate'],
            'product_id' => $return['product_id'],
            'product_class_id' => $return['product_class_id'],
            'del_flg' => $return['del_flg'],
        ];

        $this->verify();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test商品idを指定した場合税率設定で設定かつ適用日時内の最新の値が返される()
    {
        $this->expected = [
            'apply_date' => '2014-01-01 00:00:00',
            'tax_rate' => '5',
            'product_id' => '0',
            'product_class_id' => '0',
            'del_flg' => '0',
        ];

        $return = $this->objTaxRule->getTaxRule(1000);
        $this->actual = [
            'apply_date' => $return['apply_date'],
            'tax_rate' => $return['tax_rate'],
            'product_id' => $return['product_id'],
            'product_class_id' => $return['product_class_id'],
            'del_flg' => $return['del_flg'],
        ];

        $this->verify();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test商品規格idを指定した場合税率設定で設定かつ適用日時内の最新の値が返される()
    {
        $this->expected = [
            'apply_date' => '2014-01-01 00:00:00',
            'tax_rate' => '5',
            'product_id' => '0',
            'product_class_id' => '0',
            'del_flg' => '0',
        ];

        $return = $this->objTaxRule->getTaxRule(1000, 2000);
        $this->actual = [
            'apply_date' => $return['apply_date'],
            'tax_rate' => $return['tax_rate'],
            'product_id' => $return['product_id'],
            'product_class_id' => $return['product_class_id'],
            'del_flg' => $return['del_flg'],
        ];

        $this->verify();
    }
}
