<?php

class SC_SendMailTest extends Common_TestCase
{
    /**
     * @var SC_SendMail
     */
    protected $objSendMail;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkMailCatcherStatus();
        $this->objSendMail = new SC_SendMail_Ex();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('SC_SendMail', $this->objSendMail);
    }

    public function testSendMail()
    {
        $this->resetEmails();

        $this->objSendMail->setItem('to@example.com', '件名', '本文', 'from@example.com', '差出人名', 'reply-to@example.com', 'return-path@example.com', 'error-to@example.com', 'bcc@example.com', 'cc@example.com');
        $result = $this->objSendMail->sendMail();
        $this->assertTrue($result);

        $messages = $this->getMailCatcherMessages();
        $this->assertCount(1, $messages);

        $message = $this->getLastMailCatcherMessage();
        $this->assertEquals('件名', $message['subject']);
        $this->assertStringContainsString('本文', $message['source']);

        $this->assertStringContainsString('text/plain', $message['source']);

        $this->assertStringContainsString('Return-Path: error-to@example.com', $message['source']);
    }

    public function testSendHtmlMail()
    {
        $this->resetEmails();

        $this->objSendMail->setItemHtml('to@example.com', '件名', '<p>本文</p>', 'from@example.com', '差出人名', 'reply-to@example.com', 'return-path@example.com', 'error-to@example.com', 'bcc@example.com', 'cc@example.com');
        $result = $this->objSendMail->sendHtmlMail();
        $this->assertTrue($result);

        $messages = $this->getMailCatcherMessages();
        $this->assertCount(1, $messages);

        $message = $this->getLastMailCatcherMessage();
        $this->assertEquals('件名', $message['subject']);
        $this->assertStringContainsString('<p>本文</p>', $message['source']);

        $this->assertStringContainsString('text/html', $message['source']);
    }

    public function testSetReturnPathToSendMail()
    {
        $this->resetEmails();

        $this->objSendMail->setBase('to@example.com', '件名', '本文', 'from@example.com', '差出人名', 'reply-to@example.com');

        $this->objSendMail->setReturnPath('return-path@example.com');
        $result = $this->objSendMail->sendMail();
        $this->assertTrue($result);

        $messages = $this->getMailCatcherMessages();
        $this->assertCount(1, $messages);

        $message = $this->getLastMailCatcherMessage();
        $this->assertEquals('件名', $message['subject']);
        $this->assertStringContainsString('本文', $message['source']);
        $this->assertStringContainsString('text/plain', $message['source']);

        $this->assertStringContainsString('Return-Path: return-path@example.com', $message['source']);
    }

    public function testUnsetErrorToSendMail()
    {
        $this->resetEmails();

        $this->objSendMail->setItem('to@example.com', '件名', '本文', 'from@example.com', '差出人名', 'reply-to@example.com', 'return-path@example.com');
        $result = $this->objSendMail->sendMail();
        $this->assertTrue($result);

        $messages = $this->getMailCatcherMessages();
        $this->assertCount(1, $messages);

        $message = $this->getLastMailCatcherMessage();
        $this->assertEquals('件名', $message['subject']);
        $this->assertStringContainsString('本文', $message['source']);

        $this->assertStringContainsString('text/plain', $message['source']);

        $this->assertStringContainsString('Return-Path: return-path@example.com', $message['source']);
    }

    public function testUnsetReturnPathToSendMail()
    {
        $this->resetEmails();

        $this->objSendMail->setItem('to@example.com', '件名', '本文', 'from@example.com', '差出人名', 'reply-to@example.com');
        $result = $this->objSendMail->sendMail();
        $this->assertTrue($result);

        $messages = $this->getMailCatcherMessages();
        $this->assertCount(1, $messages);

        $message = $this->getLastMailCatcherMessage();
        $this->assertEquals('件名', $message['subject']);
        $this->assertStringContainsString('本文', $message['source']);

        $this->assertStringContainsString('text/plain', $message['source']);

        $this->assertStringContainsString('Return-Path: from@example.com', $message['source']);
    }

    public function testGetRecip()
    {
        $this->objSendMail->setItem('to@example.com', '件名', '本文', 'from@example.com', '差出人名', 'reply-to@example.com');

        $this->objSendMail->backend = 'mail';
        $this->expected = 'to@example.com';
        $this->actual = $this->objSendMail->getRecip();
        $this->verify();

        $this->objSendMail->backend = 'smtp';
        $this->expected = [
            'To' => 'to@example.com',
        ];
        $this->actual = $this->objSendMail->getRecip();
        $this->verify();

        $this->objSendMail->backend = 'sendmail';
        $this->expected = [
            'To' => 'to@example.com',
        ];
        $this->actual = $this->objSendMail->getRecip();
        $this->verify();
    }

    /**
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     */
    public function testGetBackendParams()
    {
        $objDb = new SC_Helper_DB_Ex();

        SC_Helper_DB_Ex::registerBasisData(['email04' => 'test@example.com']);

        $objSite = $objDb->sfGetBasisData();
        $this->expected = [
            '-f '.$objSite['email04'],
        ];
        $this->actual = $this->objSendMail->getBackendParams('mail');
        $this->verify();

        $this->expected = [
            'sendmail_path' => '/usr/bin/sendmail',
            'sendmail_args' => '-i',
        ];
        $this->actual = $this->objSendMail->getBackendParams('sendmail');
        $this->verify();

        $this->expected = [
            'host' => SMTP_HOST,
            'port' => SMTP_PORT,
        ];
        $this->actual = $this->objSendMail->getBackendParams('smtp');
        $this->verify();
    }
}
