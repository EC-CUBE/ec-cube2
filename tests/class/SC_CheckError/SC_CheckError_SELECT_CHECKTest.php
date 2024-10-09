<?php

class SC_CheckError_SELECT_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'SELECT_CHECK';
    }

    public function testSELECTCHECK()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = "※ {$this->target_func}が選択されていません。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testSELECTCHECKWithExists()
    {
        $this->arrForm = [self::FORM_NAME => 'a'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }
}
