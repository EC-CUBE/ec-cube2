<?php

class SC_SessionFactoryTest extends Common_TestCase
{
    public function testGetInstance()
    {
        $sessionFactory = SC_SessionFactory_Ex::getInstance();
        $sessionFactory->initSession();

        $this->assertInstanceOf('SC_SessionFactory_UseCookie', $sessionFactory);
        $this->assertTrue($sessionFactory->useCookie());

        $refClass = new ReflectionClass($sessionFactory);
        $refMethod = $refClass->getMethod('getSecureOption');
        if (PHP_VERSION_ID < 80100) {
            $refMethod->setAccessible(true);
        }
        if (str_contains(HTTP_URL, 'https')) {
            $this->assertTrue($refMethod->invoke($sessionFactory));
        } else {
            $this->markTestIncomplete();
        }
    }
}
