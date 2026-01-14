<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/Common_TestCase.php';

class SC_Helper_PasswordResetTest extends Common_TestCase
{
    /** @var Faker\Generator */
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker\Factory::create('ja_JP');

        // Create dtb_password_reset table for testing
        $this->createPasswordResetTable();
    }

    protected function tearDown(): void
    {
        // Drop test table
        $this->objQuery->query('DROP TABLE IF EXISTS dtb_password_reset');

        parent::tearDown();
    }

    /**
     * Create dtb_password_reset table for testing
     */
    private function createPasswordResetTable()
    {
        $dbFactory = SC_DB_DBFactory_Ex::getInstance();
        $db_type = DB_TYPE;

        if ($db_type === 'mysql' || $db_type === 'mysqli') {
            $sql = '
                CREATE TABLE IF NOT EXISTS dtb_password_reset (
                    password_reset_id int NOT NULL,
                    email text NOT NULL,
                    token_hash text NOT NULL,
                    customer_id int,
                    status smallint NOT NULL DEFAULT 0,
                    expire_date timestamp NOT NULL,
                    ip_address text,
                    user_agent text,
                    used_date timestamp NULL,
                    create_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    update_date timestamp NOT NULL,
                    PRIMARY KEY (password_reset_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8
            ';
        } else {
            // PostgreSQL
            $sql = '
                CREATE TABLE IF NOT EXISTS dtb_password_reset (
                    password_reset_id int NOT NULL,
                    email text NOT NULL,
                    token_hash text NOT NULL,
                    customer_id int,
                    status smallint NOT NULL DEFAULT 0,
                    expire_date timestamp NOT NULL,
                    ip_address text,
                    user_agent text,
                    used_date timestamp,
                    create_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    update_date timestamp NOT NULL,
                    PRIMARY KEY (password_reset_id)
                )
            ';
            $this->objQuery->query($sql);

            return;
        }

        $this->objQuery->query($sql);
    }

    /**
     * トークン生成のテスト: 一意性とフォーマット
     */
    public function testGenerateToken生成されたトークンは64文字のHEX文字列である()
    {
        $token = SC_Helper_PasswordReset_Ex::generateToken();

        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);
    }

    /**
     * トークン生成のテスト: 一意性確認
     */
    public function testGenerateToken複数回呼び出しても常に異なるトークンが生成される()
    {
        $tokens = [];
        for ($i = 0; $i < 10; $i++) {
            $tokens[] = SC_Helper_PasswordReset_Ex::generateToken();
        }

        $unique_tokens = array_unique($tokens);
        $this->assertEquals(10, count($unique_tokens));
    }

    /**
     * トークンハッシュ化のテスト
     */
    public function testHashToken同じトークンは常に同じハッシュを生成する()
    {
        $token = 'test_token_123';
        $hash1 = SC_Helper_PasswordReset_Ex::hashToken($token);
        $hash2 = SC_Helper_PasswordReset_Ex::hashToken($token);

        $this->assertEquals($hash1, $hash2);
        $this->assertEquals(64, strlen($hash1)); // SHA-256 is 64 hex characters
    }

    /**
     * トークンハッシュ化のテスト: 異なるトークンは異なるハッシュ
     */
    public function testHashToken異なるトークンは異なるハッシュを生成する()
    {
        $hash1 = SC_Helper_PasswordReset_Ex::hashToken('token1');
        $hash2 = SC_Helper_PasswordReset_Ex::hashToken('token2');

        $this->assertNotEquals($hash1, $hash2);
    }

    /**
     * トークンレコード作成のテスト
     */
    public function testCreateResetTokenトークンレコードが正常に作成される()
    {
        $email = $this->faker->safeEmail;
        $customer_id = 123;
        $ip_address = '192.168.1.1';
        $user_agent = 'Mozilla/5.0';

        $token = SC_Helper_PasswordReset_Ex::createResetToken(
            $email,
            $customer_id,
            $ip_address,
            $user_agent
        );

        // Check token format
        $this->assertEquals(64, strlen($token));

        // Verify record was created
        $token_hash = SC_Helper_PasswordReset_Ex::hashToken($token);
        $result = $this->objQuery->select(
            '*',
            'dtb_password_reset',
            'token_hash = ?',
            [$token_hash]
        );

        $this->assertCount(1, $result);
        $this->assertEquals($email, $result[0]['email']);
        $this->assertEquals($customer_id, $result[0]['customer_id']);
        $this->assertEquals(0, $result[0]['status']); // Unused
        $this->assertEquals($ip_address, $result[0]['ip_address']);
        $this->assertEquals($user_agent, $result[0]['user_agent']);
    }

    /**
     * トークン検証のテスト: 有効なトークン
     */
    public function testValidateToken有効なトークンはデータを返す()
    {
        $email = $this->faker->safeEmail;
        $customer_id = 123;

        $token = SC_Helper_PasswordReset_Ex::createResetToken(
            $email,
            $customer_id,
            '192.168.1.1',
            'Test User Agent'
        );

        $result = SC_Helper_PasswordReset_Ex::validateToken($token);

        $this->assertNotNull($result);
        $this->assertEquals($email, $result['email']);
        $this->assertEquals($customer_id, $result['customer_id']);
        $this->assertEquals(0, $result['status']);
    }

    /**
     * トークン検証のテスト: 無効なトークン
     */
    public function testValidateToken無効なトークンはnullを返す()
    {
        $invalid_token = 'invalid_token_that_does_not_exist_in_database_1234567890abc';

        $result = SC_Helper_PasswordReset_Ex::validateToken($invalid_token);

        $this->assertNull($result);
    }

    /**
     * トークン検証のテスト: 使用済みトークン
     */
    public function testValidateToken使用済みトークンはnullを返す()
    {
        $email = $this->faker->safeEmail;
        $token = SC_Helper_PasswordReset_Ex::createResetToken(
            $email,
            123,
            '192.168.1.1',
            'Test'
        );

        $token_hash = SC_Helper_PasswordReset_Ex::hashToken($token);
        SC_Helper_PasswordReset_Ex::markTokenAsUsed($token_hash);

        $result = SC_Helper_PasswordReset_Ex::validateToken($token);

        $this->assertNull($result);
    }

    /**
     * トークン検証のテスト: 期限切れトークン
     */
    public function testValidateToken期限切れトークンはnullを返す()
    {
        $email = $this->faker->safeEmail;
        $token = SC_Helper_PasswordReset_Ex::generateToken();
        $token_hash = SC_Helper_PasswordReset_Ex::hashToken($token);

        // Create expired token (expired 1 hour ago)
        $password_reset_id = $this->objQuery->nextVal('dtb_password_reset_password_reset_id');
        $this->objQuery->insert('dtb_password_reset', [
            'password_reset_id' => $password_reset_id,
            'email' => $email,
            'token_hash' => $token_hash,
            'customer_id' => 123,
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
     * レート制限のテスト: メールアドレス制限
     */
    public function testCheckRateLimit同一メールで3回目まで許可される()
    {
        $email = $this->faker->safeEmail;

        for ($i = 0; $i < 3; $i++) {
            SC_Helper_PasswordReset_Ex::createResetToken(
                $email,
                123,
                "192.168.1.$i",
                'Test'
            );
        }

        $result = SC_Helper_PasswordReset_Ex::checkRateLimit($email, '192.168.1.100');

        $this->assertFalse($result['allowed']);
        $this->assertEquals('email', $result['reason']);
    }

    /**
     * レート制限のテスト: IPアドレス制限
     */
    public function testCheckRateLimit同一IPで3回目まで許可される()
    {
        $ip_address = '192.168.1.1';

        for ($i = 0; $i < 3; $i++) {
            SC_Helper_PasswordReset_Ex::createResetToken(
                "user{$i}@example.com",
                123 + $i,
                $ip_address,
                'Test'
            );
        }

        $result = SC_Helper_PasswordReset_Ex::checkRateLimit('newuser@example.com', $ip_address);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('ip', $result['reason']);
    }

    /**
     * レート制限のテスト: 制限内
     */
    public function testCheckRateLimit時間内であれば許可される()
    {
        $email = $this->faker->safeEmail;

        SC_Helper_PasswordReset_Ex::createResetToken(
            $email,
            123,
            '192.168.1.1',
            'Test'
        );

        $result = SC_Helper_PasswordReset_Ex::checkRateLimit($email, '192.168.1.1');

        $this->assertTrue($result['allowed']);
    }

    /**
     * トークン使用済みマークのテスト
     */
    public function testMarkTokenAsUsedトークンが使用済みになる()
    {
        $email = $this->faker->safeEmail;
        $token = SC_Helper_PasswordReset_Ex::createResetToken(
            $email,
            123,
            '192.168.1.1',
            'Test'
        );

        $token_hash = SC_Helper_PasswordReset_Ex::hashToken($token);
        SC_Helper_PasswordReset_Ex::markTokenAsUsed($token_hash);

        $result = $this->objQuery->select(
            '*',
            'dtb_password_reset',
            'token_hash = ?',
            [$token_hash]
        );

        $this->assertEquals(1, $result[0]['status']);
        $this->assertNotNull($result[0]['used_date']);
    }

    /**
     * 全トークン無効化のテスト
     */
    public function testInvalidateAllTokensForCustomer顧客の全トークンが無効化される()
    {
        $customer_id = 123;

        // Create multiple tokens for the same customer
        for ($i = 0; $i < 3; $i++) {
            SC_Helper_PasswordReset_Ex::createResetToken(
                "user{$i}@example.com",
                $customer_id,
                "192.168.1.$i",
                'Test'
            );
        }

        SC_Helper_PasswordReset_Ex::invalidateAllTokensForCustomer($customer_id);

        $result = $this->objQuery->select(
            '*',
            'dtb_password_reset',
            'customer_id = ? AND status = 0',
            [$customer_id]
        );

        $this->assertCount(0, $result);

        // Verify all are marked as used
        $result = $this->objQuery->select(
            '*',
            'dtb_password_reset',
            'customer_id = ? AND status = 1',
            [$customer_id]
        );

        $this->assertCount(3, $result);
    }

    /**
     * 期限切れトークンクリーンアップのテスト
     */
    public function testCleanupExpiredTokens期限切れトークンがクリーンアップされる()
    {
        $email = $this->faker->safeEmail;

        // Create expired token
        $token = SC_Helper_PasswordReset_Ex::generateToken();
        $token_hash = SC_Helper_PasswordReset_Ex::hashToken($token);
        $password_reset_id = $this->objQuery->nextVal('dtb_password_reset_password_reset_id');
        $this->objQuery->insert('dtb_password_reset', [
            'password_reset_id' => $password_reset_id,
            'email' => $email,
            'token_hash' => $token_hash,
            'customer_id' => 123,
            'status' => 0,
            'expire_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test',
            'update_date' => 'CURRENT_TIMESTAMP',
        ]);

        $count = SC_Helper_PasswordReset_Ex::cleanupExpiredTokens();

        $this->assertEquals(1, $count);

        // Verify status changed to 2 (expired)
        $result = $this->objQuery->select(
            '*',
            'dtb_password_reset',
            'token_hash = ?',
            [$token_hash]
        );

        $this->assertEquals(2, $result[0]['status']);
    }
}
