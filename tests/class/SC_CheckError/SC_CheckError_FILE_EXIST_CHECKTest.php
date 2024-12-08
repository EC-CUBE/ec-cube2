<?php

class SC_CheckError_FILE_EXIST_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var int */
    protected $fileSize;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'FILE_EXIST_CHECK';
    }

    public function testFILEEXISTCHECK()
    {
        $this->fileSize = 1;
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFILEEXISTCHECKWithZero()
    {
        $this->fileSize = 0;
        $this->expected = '※ FILE_EXIST_CHECKをアップロードして下さい。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testFILEEXISTCHECKWithEmpty()
    {
        $this->fileSize = '';
        $this->expected = '※ FILE_EXIST_CHECKをアップロードして下さい。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testFILEEXISTCHECKWithMinus()
    {
        $this->fileSize = -1;
        $this->expected = '※ FILE_EXIST_CHECKをアップロードして下さい。<br />';

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
                'size' => $this->fileSize,
            ],
        ];
        $this->arrForm = [];
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME], [$this->target_func]);
    }
}
