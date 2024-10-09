<?php

class SC_CheckError_DIR_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var string */
    protected $dirName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'DIR_CHECK';
        $this->dirName = sys_get_temp_dir().'/'.uniqid();
        mkdir($this->dirName);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->dirName)) {
            rmdir($this->dirName);
        }
        parent::tearDown();
    }

    public function testDIRCHECK()
    {
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testDIRCHECKWithNotfound()
    {
        $this->dirName = sys_get_temp_dir().'/dir';
        $this->expected = '※ 指定したDIR_CHECKは存在しません。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testDIRCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testDIRCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    /**
     * {@inheritdoc}
     */
    public function scenario()
    {
        $this->arrForm = [self::FORM_NAME => $this->dirName];
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME], [$this->target_func]);
    }
}
