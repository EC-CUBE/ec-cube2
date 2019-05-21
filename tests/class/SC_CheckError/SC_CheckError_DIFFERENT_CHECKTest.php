<?php

class SC_CheckError_DIFFERENT_CHECKTest extends SC_CheckError_AbstractTestCase
{
    const FORM_NAME_REPEAT = 'form_repeat';

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'DIFFERENT_CHECK';
    }

    public function testDIFFERENT_CHECK()
    {
        $this->arrForm = [self::FORM_NAME => 'a', self::FORM_NAME_REPEAT => 'a'];
        $this->expected = "※ label1とlabel2は、同じ値を使用できません。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testDIFFERENT_CHECKWithNoError()
    {
        $this->arrForm = [self::FORM_NAME => 'a', self::FORM_NAME_REPEAT => 'b'];
        $this->expected = "";

        $this->scenario();
        $this->verify();
    }

    public function testDIFFERENT_CHECKWithNotType()
    {
        $this->arrForm = [self::FORM_NAME => 1, self::FORM_NAME_REPEAT => '1'];
        $this->expected = "※ label1とlabel2は、同じ値を使用できません。<br />";

        $this->scenario();
        $this->verify();
    }

    /**
     * {@inheritdoc}
     */
    public function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc(['label1', 'label2', self::FORM_NAME, self::FORM_NAME_REPEAT], [$this->target_func]);
        // XXX エラーハンドリングが不十分. 判定対象キー1が一致するとエラーになってしまう
        $this->objErr->doFunc(['label1', 'label2', 'dummy1', self::FORM_NAME], [$this->target_func]);
    }
}
