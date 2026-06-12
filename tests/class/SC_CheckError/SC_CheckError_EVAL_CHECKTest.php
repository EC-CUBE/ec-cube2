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
            self::FORM_NAME => '"BBB"',
        ];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    /**
     * スカラ定数として妥当な各種の値が許可されること.
     *
     * mtb_constants の実値は リテラル / 定数 / それらの連結式 のいずれかで、
     * シングルクォート文字列も含まれる (#ffffdf 等).
     *
     * @dataProvider validValueProvider
     */
    public function testEVALCHECKWithValidValues($value)
    {
        $this->arrForm = [self::FORM_NAME => $value];
        $this->expected = '';

        $this->scenario();
        $this->verify("値 [{$value}] は許可されるべき");
    }

    public function validValueProvider()
    {
        return [
            'ダブルクォート文字列' => ['"BBB"'],
            'シングルクォート文字列' => ["'BBB'"],
            'カラーコード(シングルクォート)' => ["'#ffffdf'"],
            '整数' => ['7200'],
            '負の整数' => ['-1'],
            '真偽値' => ['false'],
            '定数参照' => ['SMTEXT_LEN'],
            '定数の連結式' => ['HTML_REALDIR . USER_DIR'],
            '文字列と定数の連結' => ['DATA_REALDIR . "cache/"'],
        ];
    }

    /**
     * 関数呼び出し・文の連結・変数等を含む値が拒否されること.
     *
     * mtb_constants の値はスカラ定数式 (リテラル / 定数 / それらの連結) に限られるため、
     * 関数呼び出しや複数の文を含む値はバリデーションエラーとなる.
     *
     * @dataProvider invalidValueProvider
     */
    public function testEVALCHECKWithInvalidValues($value)
    {
        $this->arrForm = [self::FORM_NAME => $value];
        $this->expected = '※ form の形式が不正です。<br />';

        $this->scenario();
        $this->verify("値 [{$value}] は拒否されるべき");
    }

    public function invalidValueProvider()
    {
        return [
            '関数呼び出し(define)' => ["define('AAA', 'BBB')"],
            '引数不足のdefine' => ["define('AAA')"],
            '括弧の不整合と文の連結' => ['0) || system($_GET[c]); //'],
            '複数の文' => ['1; phpinfo()'],
            '関数の呼び出し' => ["system('id')"],
            'バッククォート式' => ['`whoami`'],
            '変数参照' => ['$_SERVER'],
            'カンマ区切り' => ['1,2'],
        ];
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
