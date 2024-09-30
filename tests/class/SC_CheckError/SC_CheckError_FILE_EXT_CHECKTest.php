<?php

class SC_CheckError_FILE_EXT_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;
    /** @var array */
    protected $validExtensions;
    /** @var string|null */
    protected $fileName;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'FILE_EXT_CHECK';
        $this->validExtensions = ['jpg', 'JPEG'];
    }

    public function testFILEEXTCHECK()
    {
        $this->fileName = 'test.jpeg';
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFILEEXTCHECKWithUpperCase()
    {
        $this->fileName = 'test.JPG';
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFILEEXTCHECKWithEmpty()
    {
        $this->fileName = '';
        $this->expected = '※ FILE_EXT_CHECKで許可されている形式は、jpg・JPEGです。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testFILEEXTCHECKWithNull()
    {
        $this->fileName = null;
        $this->expected = '※ FILE_EXT_CHECKで許可されている形式は、jpg・JPEGです。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testFILEEXTCHECKWithError()
    {
        $this->fileName = 'test.png';
        $this->expected = '※ FILE_EXT_CHECKで許可されている形式は、jpg・JPEGです。<br />';

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
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME, $this->validExtensions], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME, $this->validExtensions], [$this->target_func]);
    }
}
