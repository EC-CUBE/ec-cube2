<?php

class SC_Helper_MailmagaTest extends Common_TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用テーブルのクリーンアップ
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->delete('dtb_mailmaga_unsubscribe_token');
    }

    public function testGenerateUnsubscribeToken()
    {
        $token = SC_Helper_Mailmaga::generateUnsubscribeToken(1, 1, 'test@example.com');

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token));

        // トークンがDBに保存されているか確認
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrToken = $objQuery->getRow('*', 'dtb_mailmaga_unsubscribe_token', 'token = ?', [$token]);

        $this->assertNotEmpty($arrToken);
        $this->assertEquals(1, $arrToken['customer_id']);
        $this->assertEquals(1, $arrToken['send_id']);
        $this->assertEquals('test@example.com', $arrToken['email']);
        $this->assertEquals(0, $arrToken['used_flag']);
    }

    public function testGetUnsubscribeUrl()
    {
        $token = 'test-token-123';
        $url = SC_Helper_Mailmaga::getUnsubscribeUrl($token);

        $this->assertStringContainsString('mailmaga/unsubscribe/index.php', $url);
        $this->assertStringContainsString('token=test-token-123', $url);
        $this->assertStringStartsWith('https://', $url);
    }

    public function testValidateTokenValidToken()
    {
        // トークンを生成
        $token = SC_Helper_Mailmaga::generateUnsubscribeToken(1, 1, 'test@example.com');

        // トークンを検証
        $arrToken = SC_Helper_Mailmaga::validateToken($token);

        $this->assertIsArray($arrToken);
        $this->assertEquals(1, $arrToken['customer_id']);
        $this->assertEquals('test@example.com', $arrToken['email']);
    }

    public function testValidateTokenInvalidToken()
    {
        $result = SC_Helper_Mailmaga::validateToken('invalid-token');

        $this->assertFalse($result);
    }

    public function testValidateTokenUsedToken()
    {
        // トークンを生成
        $token = SC_Helper_Mailmaga::generateUnsubscribeToken(1, 1, 'test@example.com');

        // トークンを使用済みにする
        SC_Helper_Mailmaga::markTokenAsUsed($token);

        // 使用済みトークンは無効
        $result = SC_Helper_Mailmaga::validateToken($token);

        $this->assertFalse($result);
    }

    public function testValidateTokenExpiredToken()
    {
        // トークンを生成
        $token = SC_Helper_Mailmaga::generateUnsubscribeToken(1, 1, 'test@example.com');

        // トークンを期限切れにする
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->update(
            'dtb_mailmaga_unsubscribe_token',
            ['expire_date' => date('Y-m-d H:i:s', strtotime('-1 day'))],
            'token = ?',
            [$token]
        );

        // 期限切れトークンは無効
        $result = SC_Helper_Mailmaga::validateToken($token);

        $this->assertFalse($result);
    }

    public function testMarkTokenAsUsed()
    {
        // トークンを生成
        $token = SC_Helper_Mailmaga::generateUnsubscribeToken(1, 1, 'test@example.com');

        // トークンを使用済みにする
        $result = SC_Helper_Mailmaga::markTokenAsUsed($token);

        $this->assertTrue($result);

        // DBで used_flag が 1 になっているか確認
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrToken = $objQuery->getRow('*', 'dtb_mailmaga_unsubscribe_token', 'token = ?', [$token]);

        $this->assertEquals(1, $arrToken['used_flag']);
        $this->assertNotNull($arrToken['used_date']);
    }

    public function testUnsubscribeMailmaga()
    {
        // テスト用顧客を作成
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $customer_id = $objQuery->nextVal('dtb_customer_customer_id');

        $sqlval = [
            'customer_id' => $customer_id,
            'name01' => 'テスト',
            'name02' => '太郎',
            'email' => 'test@example.com',
            'secret_key' => SC_Helper_Customer_Ex::sfGetUniqSecretKey(),
            'status' => 2,
            'mailmaga_flg' => 1, // HTML
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
        ];
        $objQuery->insert('dtb_customer', $sqlval);

        // メルマガ登録解除
        $result = SC_Helper_Mailmaga::unsubscribeMailmaga($customer_id);

        $this->assertTrue($result);

        // mailmaga_flg が 3 になっているか確認
        $arrCustomer = $objQuery->getRow('*', 'dtb_customer', 'customer_id = ?', [$customer_id]);
        $this->assertEquals(3, $arrCustomer['mailmaga_flg']);
    }

    public function testCleanupExpiredTokens()
    {
        // 有効なトークンを生成
        $validToken = SC_Helper_Mailmaga::generateUnsubscribeToken(1, 1, 'valid@example.com');

        // 期限切れトークンを生成
        $expiredToken = SC_Helper_Mailmaga::generateUnsubscribeToken(2, 2, 'expired@example.com');
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->update(
            'dtb_mailmaga_unsubscribe_token',
            ['expire_date' => date('Y-m-d H:i:s', strtotime('-1 day'))],
            'token = ?',
            [$expiredToken]
        );

        // 使用済みトークンを生成
        $usedToken = SC_Helper_Mailmaga::generateUnsubscribeToken(3, 3, 'used@example.com');
        SC_Helper_Mailmaga::markTokenAsUsed($usedToken);

        // クリーンアップを実行
        $deletedCount = SC_Helper_Mailmaga::cleanupExpiredTokens();

        // 期限切れトークンと使用済みトークンが削除される
        $this->assertEquals(2, $deletedCount);

        // 有効なトークンは残っているか確認
        $arrValidToken = $objQuery->getRow('*', 'dtb_mailmaga_unsubscribe_token', 'token = ?', [$validToken]);
        $this->assertNotEmpty($arrValidToken);

        // 期限切れトークンは削除されているか確認
        $arrExpiredToken = $objQuery->getRow('*', 'dtb_mailmaga_unsubscribe_token', 'token = ?', [$expiredToken]);
        $this->assertEmpty($arrExpiredToken);

        // 使用済みトークンは削除されているか確認
        $arrUsedToken = $objQuery->getRow('*', 'dtb_mailmaga_unsubscribe_token', 'token = ?', [$usedToken]);
        $this->assertEmpty($arrUsedToken);
    }
}
