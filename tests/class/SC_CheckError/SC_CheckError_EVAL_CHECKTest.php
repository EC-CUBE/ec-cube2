<?php

class SC_CheckError_EVAL_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'EVAL_CHECK';
    }

    public function testEVALCHECK()
    {
        $this->arrForm = [
            self::FORM_NAME => "define('AAA', 'BBB')",
        ];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEVALCHECKWithInvalid()
    {
        $this->arrForm = [
            self::FORM_NAME => "define('AAA')",
        ];
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('ArgumentCountError in PHP8');
        }
        $this->expected = '※ form の形式が不正です。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testEVALCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEVALCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEVALCHECKWithErrorExists()
    {
        $this->arrForm = [
            self::FORM_NAME => 'a',
        ];
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc(
            [self::FORM_NAME, self::FORM_NAME],
            [
                'NUM_CHECK',
                $this->target_func,
            ]
        );

        $this->expected = '※ formは数字で入力してください。<br />';

        $this->verify('既存のエラーがある場合はエラーチェックしない');
    }

    /**
     * {@inheritdoc}
     */
    public function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([self::FORM_NAME, self::FORM_NAME], [$this->target_func]);
    }
}
