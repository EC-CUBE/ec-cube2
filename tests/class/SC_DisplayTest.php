<?php

class SC_DisplayTest extends Common_TestCase
{
    /**
     * @var SC_Display
     */
    protected $objDisplay;

    protected function setUp()
    {
        parent::setUp();

        $this->objDisplay = new SC_Display_Ex();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('SC_Display', $this->objDisplay);
        $this->assertInstanceOf('SC_Response', $this->objDisplay->response);
    }

    public function testDetectDevice()
    {
        $this->assertEquals(DEVICE_TYPE_PC, SC_Display_Ex::detectDevice());
        
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_1_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Mobile/15E148 Safari/604.1';
        $this->assertEquals(DEVICE_TYPE_SMARTPHONE, SC_Display_Ex::detectDevice(true));

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15';
        $this->assertEquals(DEVICE_TYPE_PC, SC_Display_Ex::detectDevice(true));
    }

    public function testSetPreviewURL()
    {
        $_SERVER['REQUEST_URI'] = '/cart';
        $this->objDisplay->setPrevURL();

        $objCartSession = new SC_CartSession();
        $this->assertEquals('/cart', $objCartSession->getPrevURL());
    }

    public function testSetDevice()
    {
        $this->objDisplay->setDevice(DEVICE_TYPE_ADMIN);
        $this->assertInstanceOf('SC_AdminView', $this->objDisplay->view);

        $this->objDisplay->setDevice(DEVICE_TYPE_MOBILE);
        $this->assertInstanceOf('SC_MobileView', $this->objDisplay->view);

        $this->objDisplay->setDevice(DEVICE_TYPE_SMARTPHONE);
        $this->assertInstanceOf('SC_SmartphoneView', $this->objDisplay->view);

        $this->objDisplay->setDevice();
        $this->assertInstanceOf('SC_SiteView', $this->objDisplay->view);
    }

    public function testPrepareWithAdmin()
    {
        $objPage = new LC_Page_Admin_Index();
        $objPage->setTemplate(__DIR__.'/../../data/Smarty/templates/admin/home.tpl');
        $this->objDisplay->prepare($objPage, true);

        $this->objDisplay->noAction(); // quiet
        $this->objDisplay->addHeader('test', 'value');

        $this->objDisplay->assign('test', 'value');
        $this->objDisplay->assignarray(['test2' => 'value']);

        $this->assertTrue($this->objDisplay->response->containsHeader('test'));

        $expected = ECCUBE_VERSION;
        $this->assertContains($expected, $this->objDisplay->response->body);
        $this->objDisplay->response->setHeader(['Content-Type' => 'text/html']);
        $this->objDisplay->response->headerForDownload('test.csv');
        $this->objDisplay->response->setStatusCode(200);

        $this->objDisplay->response->sendHeader();
        $this->objDisplay->response->write();

        $this->expectOutputRegex('/'.$expected.'/');
    }
}
