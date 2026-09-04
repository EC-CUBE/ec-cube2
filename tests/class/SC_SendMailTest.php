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

    public function testAddCustomHeader()
    {
        $this->resetEmails();

        $this->objSendMail->setItem(
            'to@example.com',
            '件名',
            '本文',
            'from@example.com',
            '差出人名'
        );

        // カスタムヘッダーを追加
        $this->objSendMail->addCustomHeader('List-Unsubscribe', '<https://example.com/unsubscribe>');
        $this->objSendMail->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');

        $result = $this->objSendMail->sendMail();
        $this->assertTrue($result);

        $message = $this->getLastMailCatcherMessage();

        // ヘッダーに List-Unsubscribe が含まれているか確認
        $this->assertStringContainsString('List-Unsubscribe: <https://example.com/unsubscribe>', $message['source']);
        $this->assertStringContainsString('List-Unsubscribe-Post: List-Unsubscribe=One-Click', $message['source']);
    }

    public function testClearCustomHeaders()
    {
        $this->objSendMail->addCustomHeader('X-Test', 'test');
        $this->objSendMail->clearCustomHeaders();

        $header = $this->objSendMail->getBaseHeader();

        $this->assertArrayNotHasKey('X-Test', $header);
    }

    public function testAddCustomHeaderPreventHeaderInjection()
    {
        // 改行文字を含むヘッダーは追加されない
        $this->objSendMail->addCustomHeader("X-Test\r\n", 'test');
        $header = $this->objSendMail->getBaseHeader();
        $this->assertArrayNotHasKey("X-Test\r\n", $header);

        // 値に改行文字を含む場合も追加されない
        $this->objSendMail->addCustomHeader('X-Test', "test\r\nvalue");
        $header = $this->objSendMail->getBaseHeader();
        $this->assertArrayNotHasKey('X-Test', $header);
    }

    public function testAddCustomHeaderPreventProtectedHeaderOverride()
    {
        $this->objSendMail->setItem(
            'to@example.com',
            '件名',
            '本文',
            'from@example.com',
            '差出人名'
        );

        // 保護されたヘッダーは上書きできない
        $this->objSendMail->addCustomHeader('From', 'attacker@example.com');
        $header = $this->objSendMail->getBaseHeader();

        // From ヘッダーは元の値のまま
        $this->assertStringContainsString('from@example.com', $header['From']);
        $this->assertStringNotContainsString('attacker@example.com', $header['From']);
    }
}
