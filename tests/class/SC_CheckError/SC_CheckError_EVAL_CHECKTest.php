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
     * 日本語を含む表示名でも正常に動作することを確認
     *
     * PR #1157 によるデグレ対策テスト
     * 引数の順序が [表示名, 判定対象配列キー] の場合、
     * 表示名に日本語を含んでも正常に動作することを確認
     */
    public function testEVALCHECKWorksWithJapaneseDisplayName()
    {
        $this->arrForm = [self::FORM_NAME => 'invalid syntax ;;;'];
        $disp_name = '市区町村名 (例：千代田区神田神保町)';

        $objErr = new SC_CheckError_Ex($this->arrForm);
        $objErr->doFunc([$disp_name, self::FORM_NAME], [$this->target_func]);

        $this->expected = "※ {$disp_name} の形式が不正です。<br />";
        $this->actual = $objErr->arrErr[self::FORM_NAME] ?? null;

        $this->verify('日本語を含む表示名でも正常にエラーメッセージが生成される');
    }

    /**
     * 日本語を含む表示名で正常な値の場合はエラーにならないことを確認
     */
    public function testEVALCHECKDoesNotReturnErrorForValidValueWithJapaneseDisplayName()
    {
        $this->arrForm = [self::FORM_NAME => '"valid value"'];
        $disp_name = '都道府県名 (例：東京都) #設定項目';

        $objErr = new SC_CheckError_Ex($this->arrForm);
        $objErr->doFunc([$disp_name, self::FORM_NAME], [$this->target_func]);

        $this->expected = '';
        $this->actual = $objErr->arrErr[self::FORM_NAME] ?? null;

        $this->verify('日本語を含む表示名でも正常な値の場合はエラーにならない');
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
