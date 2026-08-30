<?php

/**
 * SC_Helper_LoginRateLimit のテストクラス
 *
 * Issue #1301: ログインエラー表示改善 + ブルートフォース攻撃対策
 */
class SC_Helper_LoginRateLimitTest extends Common_TestCase
{
    /**
     * @var SC_Query
     */
    protected $objQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objQuery = SC_Query_Ex::getSingletonInstance();

        // テストデータをクリア
        $this->objQuery->delete('dtb_login_attempt');
    }

    protected function tearDown(): void
    {
        // テストデータをクリア
        $this->objQuery->delete('dtb_login_attempt');
        parent::tearDown();
    }

    /**
     * Test checkRateLimit with no previous attempts
     *
     * 前回の試行がない場合、レート制限はallowedを返す
     */
    public function testCheckRateLimitNoPreviousAttemptsReturnsAllowed()
    {
        $result = SC_Helper_LoginRateLimit_Ex::checkRateLimit(
            'test@example.com',
            '192.168.1.1'
        );

        $this->assertTrue($result['allowed']);
        $this->assertEquals(0, $result['email_count']);
        $this->assertEquals(0, $result['ip_count']);
    }

    /**
     * Test checkRateLimit within email limit
     *
     * メールアドレスのレート制限内（5回未満）の場合、allowedを返す
     */
    public function testCheckRateLimitWithinEmailLimitReturnsAllowed()
    {
        $email = 'test@example.com';
        $ip = '192.168.1.1';

        // 3回の失敗を記録
        for ($i = 0; $i < 3; $i++) {
            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($email, $ip, 'TestAgent', 0);
        }

        $result = SC_Helper_LoginRateLimit_Ex::checkRateLimit($email, $ip);

        $this->assertTrue($result['allowed']);
        $this->assertEquals(3, $result['email_count']);
    }

    /**
     * Test checkRateLimit at email limit boundary
     *
     * メールアドレスのレート制限境界値（4回）の場合、allowedを返す
     */
    public function testCheckRateLimitAtEmailLimitBoundaryReturnsAllowed()
    {
        $email = 'test@example.com';
        $ip = '192.168.1.1';

        // 4回の失敗を記録（5回目の試行まで許可）
        for ($i = 0; $i < 4; $i++) {
            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($email, $ip, 'TestAgent', 0);
        }

        $result = SC_Helper_LoginRateLimit_Ex::checkRateLimit($email, $ip);

        $this->assertTrue($result['allowed']);
        $this->assertEquals(4, $result['email_count']);
    }

    /**
     * Test checkRateLimit exceeds email limit
     *
     * メールアドレスのレート制限超過（5回以上）の場合、blockedを返す
     */
    public function testCheckRateLimitExceedsEmailLimitReturnsBlocked()
    {
        $email = 'test@example.com';
        $ip = '192.168.1.1';

        // 5回の失敗を記録（5回目の失敗でブロック）
        for ($i = 0; $i < 5; $i++) {
            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($email, $ip, 'TestAgent', 0);
        }

        $result = SC_Helper_LoginRateLimit_Ex::checkRateLimit($email, $ip);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('email', $result['reason']);
        $this->assertEquals(5, $result['email_count']);
    }

    /**
     * Test checkRateLimit within IP limit
     *
     * IPアドレスのレート制限内（9回未満）の場合、allowedを返す
     */
    public function testCheckRateLimitWithinIPLimitReturnsAllowed()
    {
        $ip = '192.168.1.1';

        // 8回の失敗を異なるメールアドレスで記録
        for ($i = 0; $i < 8; $i++) {
            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt("test{$i}@example.com", $ip, 'TestAgent', 0);
        }

        $result = SC_Helper_LoginRateLimit_Ex::checkRateLimit('new@example.com', $ip);

        $this->assertTrue($result['allowed']);
        $this->assertEquals(8, $result['ip_count']);
    }

    /**
     * Test checkRateLimit at IP limit boundary
     *
     * IPアドレスのレート制限境界値（9回）の場合、allowedを返す
     */
    public function testCheckRateLimitAtIPLimitBoundaryReturnsAllowed()
    {
        $ip = '192.168.1.1';

        // 9回の失敗を異なるメールアドレスで記録（10回目の試行まで許可）
        for ($i = 0; $i < 9; $i++) {
            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt("test{$i}@example.com", $ip, 'TestAgent', 0);
        }

        $result = SC_Helper_LoginRateLimit_Ex::checkRateLimit('new@example.com', $ip);

        $this->assertTrue($result['allowed']);
        $this->assertEquals(9, $result['ip_count']);
    }

    /**
     * Test checkRateLimit exceeds IP limit
     *
     * IPアドレスのレート制限超過（10回以上）の場合、blockedを返す
     */
    public function testCheckRateLimitExceedsIPLimitReturnsBlocked()
    {
        $ip = '192.168.1.1';

        // 10回の失敗を異なるメールアドレスで記録（10回目の失敗でブロック）
        for ($i = 0; $i < 10; $i++) {
            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt("test{$i}@example.com", $ip, 'TestAgent', 0);
        }

        $result = SC_Helper_LoginRateLimit_Ex::checkRateLimit('new@example.com', $ip);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('ip', $result['reason']);
        $this->assertEquals(10, $result['ip_count']);
    }

    /**
     * Test checkRateLimit ignores successful attempts
     *
     * 成功した試行はレート制限のカウントに含まれない
     */
    public function testCheckRateLimitIgnoresSuccessfulAttemptsReturnsAllowed()
    {
        $email = 'test@example.com';
        $ip = '192.168.1.1';

        // 4回の失敗と5回の成功を記録
        for ($i = 0; $i < 4; $i++) {
            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($email, $ip, 'TestAgent', 0);
        }
        for ($i = 0; $i < 5; $i++) {
            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($email, $ip, 'TestAgent', 1);
        }

        $result = SC_Helper_LoginRateLimit_Ex::checkRateLimit($email, $ip);

        $this->assertTrue($result['allowed']);
        $this->assertEquals(4, $result['email_count']);
    }

    /**
     * Test checkRateLimit ignores old attempts
     *
     * 1時間以上前の試行はレート制限のカウントに含まれない
     */
    public function testCheckRateLimitIgnoresOldAttemptsReturnsAllowed()
    {
        $email = 'test@example.com';
        $ip = '192.168.1.1';

        // 古い試行を直接データベースに挿入
        for ($i = 0; $i < 10; $i++) {
            $login_attempt_id = $this->objQuery->nextVal('dtb_login_attempt_login_attempt_id');
            $this->objQuery->insert('dtb_login_attempt', [
                'login_attempt_id' => $login_attempt_id,
                'login_id' => $email,
                'ip_address' => $ip,
                'user_agent' => 'TestAgent',
                'result' => 0,
                'create_date' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            ]);
        }

        $result = SC_Helper_LoginRateLimit_Ex::checkRateLimit($email, $ip);

        $this->assertTrue($result['allowed']);
        $this->assertEquals(0, $result['email_count']);
        $this->assertEquals(0, $result['ip_count']);
    }

    /**
     * Test recordLoginAttempt creates record
     *
     * ログイン試行が正しく記録される
     */
    public function testRecordLoginAttemptCreatesRecord()
    {
        $email = 'test@example.com';
        $ip = '192.168.1.1';
        $userAgent = 'Mozilla/5.0 TestAgent';

        SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($email, $ip, $userAgent, 0);

        $count = $this->objQuery->count('dtb_login_attempt');
        $this->assertEquals(1, $count);

        $records = $this->objQuery->select('*', 'dtb_login_attempt');
        $this->assertEquals($email, $records[0]['login_id']);
        $this->assertEquals($ip, $records[0]['ip_address']);
        $this->assertEquals($userAgent, $records[0]['user_agent']);
        $this->assertEquals('0', $records[0]['result']);
    }

    /**
     * Test recordLoginAttempt records success
     *
     * 成功したログイン試行が正しく記録される
     */
    public function testRecordLoginAttemptRecordsSuccess()
    {
        $email = 'test@example.com';
        $ip = '192.168.1.1';

        SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($email, $ip, 'TestAgent', 1);

        $records = $this->objQuery->select('*', 'dtb_login_attempt');
        $this->assertEquals('1', $records[0]['result']);
    }

    /**
     * Test cleanupOldAttempts removes old records
     *
     * 古いログイン試行記録が削除される
     */
    public function testCleanupOldAttemptsRemovesOldRecords()
    {
        $email = 'test@example.com';
        $ip = '192.168.1.1';

        // 31日前の古いレコードを挿入
        $old_login_attempt_id = $this->objQuery->nextVal('dtb_login_attempt_login_attempt_id');
        $this->objQuery->insert('dtb_login_attempt', [
            'login_attempt_id' => $old_login_attempt_id,
            'login_id' => $email,
            'ip_address' => $ip,
            'user_agent' => 'TestAgent',
            'result' => 0,
            'create_date' => date('Y-m-d H:i:s', strtotime('-31 days')),
        ]);

        // 新しいレコードを挿入
        SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($email, $ip, 'TestAgent', 0);

        $count_before = $this->objQuery->count('dtb_login_attempt');
        $this->assertEquals(2, $count_before);

        // クリーンアップ実行
        $deleted = SC_Helper_LoginRateLimit_Ex::cleanupOldAttempts(30);

        $this->assertEquals(1, $deleted);

        $count_after = $this->objQuery->count('dtb_login_attempt');
        $this->assertEquals(1, $count_after);
    }

    /**
     * Test getAttemptStats returns correct statistics
     *
     * ログイン試行統計が正しく取得される
     */
    public function testGetAttemptStatsReturnsCorrectStatistics()
    {
        $email = 'test@example.com';
        $ip = '192.168.1.1';

        // 3回の失敗と2回の成功を記録
        for ($i = 0; $i < 3; $i++) {
            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($email, $ip, 'TestAgent', 0);
        }
        for ($i = 0; $i < 2; $i++) {
            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($email, $ip, 'TestAgent', 1);
        }

        $stats = SC_Helper_LoginRateLimit_Ex::getAttemptStats($email, 24);

        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(3, $stats['failed']);
        $this->assertEquals(2, $stats['success']);
    }

    /**
     * Test getIPAttemptStats returns correct statistics
     *
     * IP別のログイン試行統計が正しく取得される
     */
    public function testGetIPAttemptStatsReturnsCorrectStatistics()
    {
        $ip = '192.168.1.1';

        // 異なるメールアドレスで5回の失敗と3回の成功を記録
        for ($i = 0; $i < 5; $i++) {
            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt("test{$i}@example.com", $ip, 'TestAgent', 0);
        }
        for ($i = 0; $i < 3; $i++) {
            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt("user{$i}@example.com", $ip, 'TestAgent', 1);
        }

        $stats = SC_Helper_LoginRateLimit_Ex::getIPAttemptStats($ip, 24);

        $this->assertEquals(8, $stats['total']);
        $this->assertEquals(5, $stats['failed']);
        $this->assertEquals(3, $stats['success']);
    }

    /**
     * Test threshold defaults
     *
     * しきい値のデフォルト値が取得される
     */
    public function testGetThresholdReturnsDefaults()
    {
        $this->assertEquals(5, SC_Helper_LoginRateLimit_Ex::getEmailThreshold());
        $this->assertEquals(10, SC_Helper_LoginRateLimit_Ex::getIPThreshold());
    }

    /**
     * Test threshold override by environment variable
     *
     * 環境変数でしきい値を上書きできる
     */
    public function testGetThresholdIsOverridableByEnvironmentVariable()
    {
        putenv('LOGIN_RATE_LIMIT_EMAIL_THRESHOLD=7');
        putenv('LOGIN_RATE_LIMIT_IP_THRESHOLD=1000');
        try {
            $this->assertEquals(7, SC_Helper_LoginRateLimit_Ex::getEmailThreshold());
            $this->assertEquals(1000, SC_Helper_LoginRateLimit_Ex::getIPThreshold());
        } finally {
            putenv('LOGIN_RATE_LIMIT_EMAIL_THRESHOLD');
            putenv('LOGIN_RATE_LIMIT_IP_THRESHOLD');
        }
    }

    /**
     * Test non-numeric environment variable is ignored
     *
     * 数値でない環境変数は無視されデフォルト値が使用される
     */
    public function testGetThresholdIgnoresNonNumericEnvironmentVariable()
    {
        putenv('LOGIN_RATE_LIMIT_IP_THRESHOLD=invalid');
        try {
            $this->assertEquals(10, SC_Helper_LoginRateLimit_Ex::getIPThreshold());
        } finally {
            putenv('LOGIN_RATE_LIMIT_IP_THRESHOLD');
        }
    }

    /**
     * Test numeric constant overrides threshold
     *
     * 数値の定数はしきい値として使用される
     */
    public function testGetThresholdUsesNumericConstant()
    {
        if (!defined('TEST_LOGIN_RATE_LIMIT_NUMERIC')) {
            define('TEST_LOGIN_RATE_LIMIT_NUMERIC', '42');
        }

        $this->assertEquals(42, SC_Helper_LoginRateLimit_ThresholdStub::callGetThreshold('TEST_LOGIN_RATE_LIMIT_NUMERIC', 10));
    }

    /**
     * Test non-numeric constant is ignored
     *
     * 数値でない定数は無視されデフォルト値が使用される
     * （(int) 変換で 0 になり即ブロックとなる事故を防ぐ）
     */
    public function testGetThresholdIgnoresNonNumericConstant()
    {
        if (!defined('TEST_LOGIN_RATE_LIMIT_NON_NUMERIC')) {
            define('TEST_LOGIN_RATE_LIMIT_NON_NUMERIC', 'invalid');
        }

        $this->assertEquals(10, SC_Helper_LoginRateLimit_ThresholdStub::callGetThreshold('TEST_LOGIN_RATE_LIMIT_NON_NUMERIC', 10));
    }

    /**
     * Test checkRateLimit respects overridden IP threshold
     *
     * 上書きされたIPしきい値がレート制限判定に反映される
     */
    public function testCheckRateLimitRespectsOverriddenIPThreshold()
    {
        putenv('LOGIN_RATE_LIMIT_IP_THRESHOLD=15');
        try {
            $ip = '192.168.1.1';

            // デフォルトのしきい値（10回）を超える失敗を記録
            for ($i = 0; $i < 12; $i++) {
                SC_Helper_LoginRateLimit_Ex::recordLoginAttempt("test{$i}@example.com", $ip, 'TestAgent', 0);
            }

            $result = SC_Helper_LoginRateLimit_Ex::checkRateLimit('new@example.com', $ip);

            $this->assertTrue($result['allowed']);
            $this->assertEquals(12, $result['ip_count']);
        } finally {
            putenv('LOGIN_RATE_LIMIT_IP_THRESHOLD');
        }
    }
}

/**
 * getThreshold() をテストから呼び出すためのスタブ
 */
class SC_Helper_LoginRateLimit_ThresholdStub extends SC_Helper_LoginRateLimit
{
    public static function callGetThreshold($name, $default)
    {
        return self::getThreshold($name, $default);
    }
}
