<?php

class SC_CheckError_FILE_MIMETYPE_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;
    /** @var string */
    protected $validMimeType;
    /** @var string|null */
    protected $fileName;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'FILE_MIMETYPE_CHECK';
        $this->validMimeType = 'image/.*';
    }

    public function testFILE_EXT_CHECKWithJPEG()
    {
        $this->fileName = __DIR__.'/../fixtures/files/dummy.jpg';
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFILE_EXT_CHECKWithPNG()
    {
        $this->fileName = __DIR__.'/../fixtures/files/dummy.png';
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFILE_EXT_CHECKWithGIF()
    {
        $this->fileName = __DIR__.'/../fixtures/files/dummy.gif';
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFILE_EXT_CHECKWithCSV()
    {
        $this->fileName = __DIR__.'/../fixtures/files/dummy.csv';
        $this->expected = '';
        $this->validMimeType = 'text/plain';

        $this->scenario();
        $this->verify();
    }

    public function testFILE_EXT_CHECKWithTAR()
    {
        $this->fileName = __DIR__.'/../fixtures/files/dummy.tar';
        $this->expected = '';
        $this->validMimeType = 'application/(x-)?(tar|gzip)';

        $this->scenario();
        $this->verify();
    }

    public function testFILE_EXT_CHECKWithTARGZ()
    {
        $this->fileName = __DIR__.'/../fixtures/files/dummy.tar.gz';
        $this->expected = '';
        $this->validMimeType = 'application/(x-)?(tar|gzip)';

        $this->scenario();
        $this->verify();
    }

    public function testFILE_EXT_CHECKWithZIP()
    {
        $this->fileName = __DIR__.'/../fixtures/files/dummy.zip';
        $this->expected = '';
        $this->validMimeType = 'application/zip';

        $this->scenario();
        $this->verify();
    }

    public function testFILE_EXT_CHECKWithEvil()
    {
        $this->fileName = __DIR__.'/../fixtures/files/evil.gif';
        $this->expected = '※ FILE_MIMETYPE_CHECKで許可されていない形式のファイルです。<br />';

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
                'tmp_name' => $this->fileName
            ]
        ];
        $this->arrForm = [];
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME, $this->validMimeType], [$this->target_func]);
    }
}
