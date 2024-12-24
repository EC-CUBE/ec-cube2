<?php

abstract class SC_CheckError_AbstractTestCase extends Common_TestCase
{
    /**
     * Form のパラメータ名.
     *
     * @var string
     */
    public const FORM_NAME = 'form';

    /** @var string */
    protected $target_func;

    /** @var array */
    protected $arrForm;

    /**
     * @var SC_CheckError
     */
    protected $objErr;

    /**
     * Initialize to SC_CheckError
     */
    protected function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME], [$this->target_func]);
    }

    /**
     * {@inheritdoc}
     */
    protected function verify($message = '')
    {
        $this->actual = $this->objErr->arrErr[self::FORM_NAME] ?? null;
        parent::verify($message);
    }
}
