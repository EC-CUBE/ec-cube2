<?php

class SC_Initial_setUmaskTest extends Common_TestCase
{
    /**
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     */
    public function testSetsConfiguredUmask()
    {
        define('UMASK', 0027);
        $originalUmask = umask(0002);

        try {
            (new SC_Initial())->setUmask();

            $this->assertSame(0027, umask());
        } finally {
            umask($originalUmask);
        }
    }

    /**
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     */
    public function testDoesNotChangeUmaskWhenDisabled()
    {
        define('UMASK', false);
        $originalUmask = umask(0002);

        try {
            (new SC_Initial())->setUmask();

            $this->assertSame(0002, umask());
        } finally {
            umask($originalUmask);
        }
    }

    /**
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     */
    public function testAddsGroupAndOtherWriteBitsToCurrentUmask()
    {
        $originalUmask = umask(0007);

        try {
            (new SC_Initial())->setUmask();

            $this->assertSame(0027, umask());
        } finally {
            umask($originalUmask);
        }
    }
}
