<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_Mail/SC_Helper_Mail_TestBase.php';

class SC_Helper_Mail_sfSendRegistMailTest extends SC_Helper_Mail_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
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

        $tpl_realpath = DATA_REALDIR.'Smarty/templates/default/mail_templates/customer_regist_mail.tpl';
        $needle = str_contains(file_get_contents($tpl_realpath), '<!--{$name01}--><!--{$name02}--> 様')
            // 旧テンプレート (.github/workflows/unit-tests.yml で定義されている「Run to Email-template compatibility tests」向け)
            ? $this->arrCustomer['name01'].$this->arrCustomer['name02'].' 様'
            // 最新テンプレート
            : $this->arrCustomer['name01'].' '.$this->arrCustomer['name02'].' 様'
        ;
        $message = $this->getLastMailCatcherMessage();

        $this->assertStringContainsString($needle, $message['source']);
    }

    public function test会員登録依頼メールの宛名と登録リンクのidが正しい()
    {
        // 仮会員を作成
        $this->setUpCustomer(['status' => 1]);

        $this->resetEmails();
        $this->objHelperMail->sfSendRegistMail($this->arrCustomer['secret_key'], '', false, true);

        $message = $this->getLastMailCatcherMessage();
        $this->assertStringContainsString($this->arrCustomer['name01'].' '.$this->arrCustomer['name02'].' 様', $message['source']);
        $this->assertStringContainsString('&id='.$this->arrCustomer['secret_key'], $message['source']);
    }
}
