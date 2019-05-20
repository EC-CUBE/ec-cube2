<?php

class SC_CheckError_FIND_FILETest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;
    /** @var string|null */
    protected $fileName;
    /** @var string */
    protected $targetDir;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'FIND_FILE';
        $this->targetDir = IMAGE_SAVE_REALDIR;
    }

    public function testFIND_FILE()
    {
        $this->fileName = 'ice130.jpg';
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFIND_FILEWithNotfound()
    {
        $this->fileName = 'test.JPG';
        $this->expected = '※ '.$this->targetDir.'test.JPGが見つかりません。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testFIND_FILEWithEmpty()
    {
        $this->fileName = '';
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFIND_FILEWithNull()
    {
        $this->fileName = null;
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testFIND_FILEWithDefaultTargetDir()
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
