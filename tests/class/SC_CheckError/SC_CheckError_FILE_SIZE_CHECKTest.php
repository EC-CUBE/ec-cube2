<?php

class SC_CheckError_FILE_SIZE_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var int */
    protected $fileSize;
    /** @var int */
    protected $maxFileSize;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'FILE_SIZE_CHECK';
    }

    public function testFILESIZECHECK()
    {
        $this->maxFileSize = 1;
        $this->fileSize = 1024 * 1; // 1KB
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFILESIZECHECKWithOver()
    {
        $this->maxFileSize = 1;
        $this->fileSize = 1024 * 2; // 2KB
        $this->expected = '※ FILE_SIZE_CHECKのファイルサイズは1KB以下のものを使用してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testFILESIZECHECKWithOverToMB()
    {
        $this->maxFileSize = 1024;         // 1MB
        $this->fileSize = 1024 * 1024 * 2; // 2MB
        $this->expected = '※ FILE_SIZE_CHECKのファイルサイズは1MB以下のものを使用してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testFILESIZECHECKWithEmpty()
    {
        $this->maxFileSize = 1;
        $this->fileSize = '';
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFILESIZECHECKWithMinus()
    {
        $this->maxFileSize = 1;
        $this->fileSize = -1;
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
                'size' => $this->fileSize,
            ],
        ];
        $this->arrForm = [];
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME, $this->maxFileSize], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME, $this->maxFileSize], [$this->target_func]);
    }
}
