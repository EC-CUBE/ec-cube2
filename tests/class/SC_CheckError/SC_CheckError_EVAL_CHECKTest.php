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

    /**
     * シングルクォートのみの入力でFatal Errorが発生しないことを確認 (Issue #1297)
     *
     * PHP 8.3で構文エラーが発生する入力値でもバリデーションエラーとして
     * 処理され、Fatal Errorにならないことを確認
     */
    public function testEVALCHECKDoesNotCauseFatalErrorWithSingleQuote()
    {
        $this->arrForm = [self::FORM_NAME => "'"];
        $this->expected = '※ form の形式が不正です。<br />';

        $this->scenario();
        $this->verify('シングルクォートのみの入力でもFatal Errorにならず、バリデーションエラーになる');
    }

    /**
     * 構文エラーを引き起こす値でFatal Errorが発生しないことを確認
     */
    public function testEVALCHECKDoesNotCauseFatalErrorWithUnclosedString()
    {
        $this->arrForm = [self::FORM_NAME => '"unclosed string'];
        $this->expected = '※ form の形式が不正です。<br />';

        $this->scenario();
        $this->verify('閉じられていない文字列でもFatal Errorにならず、バリデーションエラーになる');
    }

    /**
     * 複数のシングルクォートでFatal Errorが発生しないことを確認
     */
    public function testEVALCHECKDoesNotCauseFatalErrorWithMultipleSingleQuotes()
    {
        $this->arrForm = [self::FORM_NAME => "'''"];
        $this->expected = '※ form の形式が不正です。<br />';

        $this->scenario();
        $this->verify('複数のシングルクォートでもFatal Errorにならず、バリデーションエラーになる');
    }
}
