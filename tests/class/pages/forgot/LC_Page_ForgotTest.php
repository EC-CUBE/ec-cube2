<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/Common_TestCase.php';

class LC_Page_ForgotTest extends Common_TestCase
{
    /** @var Faker\Generator */
    protected $faker;

    /** @var int */
    protected $customer_id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkMailCatcherStatus();
        $this->faker = Faker\Factory::create('ja_JP');

        // Create test customer
        $this->createTestCustomer();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Create test customer
     */
    private function createTestCustomer()
    {
        $email = $this->faker->safeEmail;
        $this->customer_id = $this->objGenerator->createCustomer($email, ['status' => 2]); // Active customer
    }

    /**
     * requestモードのバリデーションテスト: メールアドレス必須
     */
    public function testRequestModeメールアドレスが空の場合エラーになる()
    {
        $_POST = [
            'mode' => 'request',
            'email' => '',
        ];

        $objPage = new LC_Page_Forgot();
        $objPage->init();
        $objPage->action();

        // Check that there are validation errors
        $this->assertNotEmpty($objPage->arrErr['email']);
    }

    /**
     * requestモードのバリデーションテスト: 不正なメールアドレス
     */
    public function testRequestMode不正なメールアドレスの場合エラーになる()
    {
        $_POST = [
            'mode' => 'request',
            'email' => 'invalid-email',
        ];

        $objPage = new LC_Page_Forgot();
        $objPage->init();
        $objPage->action();

        // Check that there are validation errors
        $this->assertNotEmpty($objPage->arrErr['email']);
    }

    /**
     * requestモードのレート制限テスト
     */
    public function testRequestModeレート制限により4回目のリクエストが拒否される()
    {
        $customer = $this->objQuery->getRow('*', 'dtb_customer', 'customer_id = ?', [$this->customer_id]);
        $email = $customer['email'];

        // Create 3 password reset requests
        for ($i = 0; $i < 3; $i++) {
            SC_Helper_PasswordReset_Ex::createResetToken(
                $email,
                $this->customer_id,
                '192.168.1.1',
                'Test'
            );
        }

        $_POST = [
            'mode' => 'request',
            'email' => $email,
        ];
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Test';

        $objPage = new LC_Page_Forgot();
        $objPage->init();
        $objPage->action();

        // Should have rate limit error
        $this->assertNotEmpty($objPage->errmsg);
        $this->assertStringContainsString('制限', $objPage->errmsg);
    }

    /**
     * resetモードのバリデーションテスト: パスワード必須
     */
    public function testResetModeパスワードが空の場合エラーになる()
    {
        $customer = $this->objQuery->getRow('*', 'dtb_customer', 'customer_id = ?', [$this->customer_id]);
        $token = SC_Helper_PasswordReset_Ex::createResetToken(
            $customer['email'],
            $this->customer_id,
            '192.168.1.1',
            'Test'
        );

        $_POST = [
            'mode' => 'complete',
            'token' => $token,
            'password' => '',
            'password02' => '',
        ];

        $objPage = new LC_Page_Forgot();
        $objPage->init();
        $objPage->action();

        // Check that there are validation errors
        $this->assertNotEmpty($objPage->arrErr['password']);
    }

    /**
     * resetモードのバリデーションテスト: パスワード不一致
     */
    public function testResetModeパスワードが一致しない場合エラーになる()
    {
        $customer = $this->objQuery->getRow('*', 'dtb_customer', 'customer_id = ?', [$this->customer_id]);
        $token = SC_Helper_PasswordReset_Ex::createResetToken(
            $customer['email'],
            $this->customer_id,
            '192.168.1.1',
            'Test'
        );

        $_POST = [
            'mode' => 'complete',
            'token' => $token,
            'password' => 'password123',
            'password02' => 'different456',
        ];

        $objPage = new LC_Page_Forgot();
        $objPage->init();
        $objPage->action();

        // Check that there are validation errors
        $this->assertNotEmpty($objPage->errmsg);
    }

    /**
     * resetモードのテスト: 無効なトークン
     */
    public function testResetMode無効なトークンでエラー画面に遷移する()
    {
        $_GET['mode'] = 'reset';
        $_GET['token'] = 'invalid_token_1234567890abcdef1234567890abcdef12345678901234';

        $objPage = new LC_Page_Forgot();
        $objPage->init();
        $objPage->action();

        // Should redirect to error page
        $this->assertEquals('forgot/error.tpl', $objPage->tpl_mainpage);
    }

    /**
     * トークンの有効期限チェック
     */
    public function testValidateToken期限切れトークンは無効とみなされる()
    {
        $customer = $this->objQuery->getRow('*', 'dtb_customer', 'customer_id = ?', [$this->customer_id]);

        // Create expired token manually
        $token = SC_Helper_PasswordReset_Ex::generateToken();
        $token_hash = SC_Helper_PasswordReset_Ex::hashToken($token);
        $password_reset_id = $this->objQuery->nextVal('dtb_password_reset_password_reset_id');
        $this->objQuery->insert('dtb_password_reset', [
            'password_reset_id' => $password_reset_id,
            'email' => $customer['email'],
            'token_hash' => $token_hash,
            'customer_id' => $this->customer_id,
            'status' => 0,
            'expire_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test',
            'update_date' => 'CURRENT_TIMESTAMP',
        ]);

        $result = SC_Helper_PasswordReset_Ex::validateToken($token);

        $this->assertNull($result);
    }

    /**
     * メール送信テスト: リクエストメールが正しく送信される
     */
    public function testRequestModeリクエストメールが正しく送信される()
    {
        $customer = $this->objQuery->getRow('*', 'dtb_customer', 'customer_id = ?', [$this->customer_id]);

        $this->resetEmails();

        $_POST = [
            'mode' => 'request',
            'email' => $customer['email'],
        ];
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Test';
        $_SERVER['REQUEST_URI'] = '/forgot/';

        $objPage = new LC_Page_Forgot();
        $objPage->init();
        $objPage->process();

        // Check if email was sent
        $messages = $this->getMailCatcherMessages();
        $this->assertNotEmpty($messages);

        $message = $this->getMailCatcherMessage($messages[0]);

        // Check email content
        $this->assertStringContainsString($customer['email'], $message['recipients'][0]);
        $this->assertStringContainsString('パスワード再設定', $message['source']);
        $this->assertStringContainsString('forgot/index.php?mode=reset&token=', $message['source']);
    }

    /**
     * セキュリティテスト: 存在しないメールアドレスでも同じ成功メッセージ
     */
    public function testRequestMode存在しないメールアドレスでも成功メッセージを表示()
    {
        $this->resetEmails();

        $_POST = [
            'mode' => 'request',
            'email' => 'nonexistent@example.com',
        ];
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Test';

        $objPage = new LC_Page_Forgot();
        $objPage->init();
        $objPage->process();

        // Should redirect to request_complete even if email doesn't exist
        $this->assertEquals('request_complete', $objPage->tpl_mainpage);

        // No email should be sent
        $messages = $this->getMailCatcherMessages();
        $this->assertEmpty($messages);
    }
}
