<?php

$HOME = realpath(dirname(__FILE__)) . "/../../../..";
require_once($HOME . "/tests/class/helper/SC_Helper_TaxRule/SC_Helper_TaxRule_TestBase.php");

class SC_Helper_TaxRule_setTaxRuleTest extends SC_Helper_TaxRule_TestBase
{

    protected function setUp()
    {
        parent::setUp();
        $this->objTaxRule = new SC_Helper_TaxRule_Ex();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /////////////////////////////////////////

    /**
     * @test
     */
    public function 新規登録が出来る()
    {
        // postgresとmysqlでmember_idのカラムに差がある
        $_SESSION['member_id'] = 1;
        $this->expected = array(
            'apply_date' => '2000-10-10 10:10:10',
            'calc_rule' => '1',
            'tax_rate' => '5',
        );
        $this->objTaxRule->setTaxRule(
            $this->expected['calc_rule'],
            $this->expected['tax_rate'],
            $this->expected['apply_date']);

        $result = $this->objQuery->select(
            'apply_date, calc_rule, tax_rate',
            'dtb_tax_rule',
            'apply_date = ?',
            array($this->expected['apply_date'])
        );

        $this->actual = $result[0];
        $this->verify();
    }

    /**
     * del_flg を考慮する
     * @see https://github.com/EC-CUBE/eccube-2_13/issues/304
     */
    public function testSetDelflgOfSetTaxRule()
    {
        $apply_date = date('Y-m-d H:i:s');
        $_SESSION['member_id'] = 1;
        $this->objTaxRule->setTaxRule(1, 10, $apply_date);
        $arrTaxRule = $this->objQuery->getRow('*', 'dtb_tax_rule', 'apply_date = ?', [$apply_date]);
        // del_flg を立てる
        SC_Helper_TaxRule_Ex::deleteTaxRuleData($arrTaxRule['tax_rule_id']);

        $this->objTaxRule->setTaxRule(1, 10, $apply_date);
        $actualTaxRule = $this->objQuery->getRow('*', 'dtb_tax_rule', 'del_flg = 0 AND apply_date = ?', [$apply_date]);

        $this->assertNotEquals($arrTaxRule['tax_rule_id'], $actualTaxRule['tax_rule_id']);
    }
}
