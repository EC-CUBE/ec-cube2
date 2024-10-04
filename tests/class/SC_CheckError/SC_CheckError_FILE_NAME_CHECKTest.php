<?php

class SC_CheckError_FILE_NAME_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;
    /** @var string|null */
    protected $fileName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'FILE_NAME_CHECK';
    }

    public function testFILE_NAME_CHECK()
    {
        $this->fileName = 'test.jpeg';
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFILE_NAME_CHECKWithError()
    {
        $this->fileName = 'ア.JPG';
        $this->expected = '※ FILE_NAME_CHECKのファイル名には、英数字、記号（_ - .）のみを入力して下さい。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testFILE_NAME_CHECKWithEmpty()
    {
        $this->fileName = '';
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFILE_NAME_CHECKWithNull()
    {
        $this->fileName = null;
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    /**
     * {@inheritdoc}
     */
    public function scenario()
    {
        $_FILES = [
            self::FORM_NAME => [
                'name' => $this->fileName
            ]
        ];
        $this->arrForm = [];
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME], [$this->target_func]);
    }
}
