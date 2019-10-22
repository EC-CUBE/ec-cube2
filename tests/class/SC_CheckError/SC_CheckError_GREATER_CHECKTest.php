<?php

class SC_CheckError_GREATER_CHECKTest extends SC_CheckError_AbstractTestCase
{
    const FORM_NAME_REPEAT = 'form_repeat';

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'GREATER_CHECK';
    }

    public function testGREATER_CHECK()
    {
        $this->arrForm = [self::FORM_NAME => 3, self::FORM_NAME_REPEAT => 2];
        $this->expected = "※ label1はlabel2より大きい値を入力できません。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testGREATER_CHECKWithEqual()
    {
        $this->arrForm = [self::FORM_NAME => 1, self::FORM_NAME_REPEAT => 1];
        $this->expected = "";

        $this->scenario();
        $this->verify();
    }

    public function testGREATER_CHECKWithLess()
    {
        $this->arrForm = [self::FORM_NAME => 2, self::FORM_NAME_REPEAT => 3];
        $this->expected = "";

        $this->scenario();
        $this->verify();
    }

    /**
     * 0 を含むチェックは未実装
     */
    public function testGREATER_CHECKWithZero()
    {
        $this->markTestIncomplete('Not implement to zero check');
        $this->arrForm = [self::FORM_NAME => 1, self::FORM_NAME_REPEAT => 0];
        $this->expected = "";

        $this->scenario();
        $this->verify();
    }

    public function testGREATER_CHECKWithNotType()
    {
        $this->arrForm = [self::FORM_NAME => 3, self::FORM_NAME_REPEAT => '2'];
        $this->expected = "※ label1はlabel2より大きい値を入力できません。<br />";

        $this->scenario();
        $this->verify();
    }

    public function testGREATER_CHECKWithString()
    {
        $this->arrForm = [self::FORM_NAME => 'a', self::FORM_NAME_REPEAT => 'b'];
        $this->expected = "";

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
