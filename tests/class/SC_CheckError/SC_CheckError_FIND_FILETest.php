<?php

class SC_CheckError_FIND_FILETest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;
    /** @var string|null */
    protected $fileName;
    /** @var string */
    protected $targetDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'FIND_FILE';
        $this->targetDir = IMAGE_SAVE_REALDIR;
    }

    public function testFINDFILE()
    {
        $this->fileName = 'ice130.jpg';
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFINDFILEWithNotfound()
    {
        $this->fileName = 'test.JPG';
        $this->expected = '※ '.$this->target_func.'が見つかりません。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testFINDFILEWithEmpty()
    {
        $this->fileName = '';
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFINDFILEWithNull()
    {
        $this->fileName = null;
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFINDFILEWithDefaultTargetDir()
    {
        $this->targetDir = '';
        $this->fileName = 'ice130.jpg';
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    /**
     * {@inheritdoc}
     */
    public function scenario()
    {
        $this->arrForm = [self::FORM_NAME => $this->fileName];
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME, $this->targetDir], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME, $this->targetDir], [$this->target_func]);
    }
}
