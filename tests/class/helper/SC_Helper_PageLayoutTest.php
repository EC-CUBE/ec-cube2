<?php

class SC_Helper_PageLayoutTest extends Common_TestCase
{
    public function testIsResponsive()
    {
        $this->assertFalse(SC_Helper_PageLayout::isResponsive());
    }
}
