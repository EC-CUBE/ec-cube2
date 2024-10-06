<?php

class SC_CheckError_ZERO_STARTTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'ZERO_START';
    }

    public function testZEROSTART()
    {
        $this->arrForm = [self::FORM_NAME => '0111'];
        $this->expected = "※ {$this->target_func}に0で始まる数値が入力されています。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testZEROSTARTwithNotZeroStart()
    {
        $this->arrForm = [self::FORM_NAME => 'a'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }
}
