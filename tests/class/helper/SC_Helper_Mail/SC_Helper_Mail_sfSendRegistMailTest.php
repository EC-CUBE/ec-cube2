<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_Mail/SC_Helper_Mail_TestBase.php';

class SC_Helper_Mail_sfSendRegistMailTest extends SC_Helper_Mail_TestBase
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////

    public function test会員登録メールの宛名が正しい()
    {
        // 本会員を作成
        $this->setUpCustomer();

        $this->resetEmails();
        $this->objHelperMail->sfSendRegistMail($this->arrCustomer['secret_key']);

        $message = $this->getLastMailCatcherMessage();
        $this->assertContains($this->arrCustomer['name01'].$this->arrCustomer['name02'].' 様', $message['source']);
    }

    public function test会員登録依頼メールの宛名と登録リンクのidが正しい()
    {
        // 仮会員を作成
        $this->setUpCustomer(['status' => 1]);

        $this->resetEmails();
        $this->objHelperMail->sfSendRegistMail($this->arrCustomer['secret_key'], '', false, true);

        $message = $this->getLastMailCatcherMessage();
        $this->assertContains($this->arrCustomer['name01'].' '.$this->arrCustomer['name02'].' 様', $message['source']);
        $this->assertContains('&id='.$this->arrCustomer['secret_key'], $message['source']);
    }
}
