<?php
$HOME = realpath(dirname(__FILE__)) . "/../../../..";
require_once($HOME . "/tests/class/Common_TestCase.php");

/**
 * HTTP_RequestTest
 *
 * @copyright
 * @author Nobuhiko Kimoto <info@nob-log.info>
 * @license
 */
class HTTP_RequestTest extends Common_TestCase
{
    public function testConstructorSetsDefaults()
    {
        $url = 'http://www.example.com/foo';
        $req = new HTTP_Request($url);
        //$req->setMethod(HTTP_REQUEST_METHOD_POST);
        //$req->addPostData('Foo', 'bar');

        $this->assertSame($url, $req->getUrl());
        //$this->assertEquals(HTTP_REQUEST_METHOD_POST, $req->getMethod());
    }

}
