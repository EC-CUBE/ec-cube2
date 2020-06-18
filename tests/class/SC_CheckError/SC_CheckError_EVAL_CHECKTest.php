<?php

class SC_CheckError_EVAL_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var string */
    protected $evaluation;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'EVAL_CHECK';
    }

    public function testEVAL_CHECK()
    {
        $this->evaluation = "define('AAA', 'BBB')";
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }


    public function testEVAL_CHECKWithInvalid()
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('ArgumentCountError in PHP8');

        }
        $this->evaluation = "define('AAA')";
        $this->expected = '※ form の形式が不正です。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testEVAL_CHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEVAL_CHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEVAL_CHECKWithErrorExists()
    {
        $this->arrForm = [
            self::FORM_NAME => 'a',
        ];
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc(
            [self::FORM_NAME, self::FORM_NAME],
            [
                'NUM_CHECK',
                $this->target_func
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
        $this->objErr->doFunc([self::FORM_NAME, $this->evaluation], [$this->target_func]);
    }
}
