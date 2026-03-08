<?php

require_once __DIR__.'/SC_Helper_Session_TestBase.php';

/**
 * SC_Helper_Session トランザクショントークンのテストクラス.
 */
class SC_Helper_Session_tokenTest extends SC_Helper_Session_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // トランザクショントークンをクリア
        unset($_SESSION[TRANSACTION_ID_NAME]);
        unset($_REQUEST[TRANSACTION_ID_NAME]);
    }

    protected function tearDown(): void
    {
        unset($_SESSION[TRANSACTION_ID_NAME]);
        unset($_REQUEST[TRANSACTION_ID_NAME]);

        parent::tearDown();
    }

    public function testCreateToken予測困難な文字列を生成()
    {
        $token1 = SC_Helper_Session_Ex::createToken();
        $token2 = SC_Helper_Session_Ex::createToken();

        $this->assertIsString($token1);
        $this->assertEquals(40, strlen($token1), 'SHA1ハッシュは40文字');
        $this->assertNotEquals($token1, $token2, '毎回異なるトークンが生成される');
    }

    public function testGetTokenトークンを生成して取得()
    {
        $token = SC_Helper_Session_Ex::getToken();

        $this->assertIsString($token);
        $this->assertEquals(40, strlen($token));
        $this->assertEquals($token, $_SESSION[TRANSACTION_ID_NAME], 'セッションに保存される');
    }

    public function testGetToken既存トークンがある場合は同じトークンを返す()
    {
        $token1 = SC_Helper_Session_Ex::getToken();
        $token2 = SC_Helper_Session_Ex::getToken();

        $this->assertEquals($token1, $token2, '同じトークンが返される');
    }

    public function testIsValidToken正しいトークンでtrue()
    {
        $token = SC_Helper_Session_Ex::getToken();
        $_REQUEST[TRANSACTION_ID_NAME] = $token;

        $result = SC_Helper_Session_Ex::isValidToken();

        $this->assertTrue($result);
    }

    public function testIsValidToken異なるトークンでfalse()
    {
        SC_Helper_Session_Ex::getToken();
        $_REQUEST[TRANSACTION_ID_NAME] = 'invalid_token';

        $result = SC_Helper_Session_Ex::isValidToken();

        $this->assertFalse($result);
    }

    public function testIsValidTokenトークンが未設定でfalse()
    {
        $result = SC_Helper_Session_Ex::isValidToken();

        $this->assertFalse($result);
    }

    public function testIsValidTokenリクエストトークンが空でfalse()
    {
        SC_Helper_Session_Ex::getToken();
        $_REQUEST[TRANSACTION_ID_NAME] = '';

        $result = SC_Helper_Session_Ex::isValidToken();

        $this->assertFalse($result);
    }

    public function testIsValidToken検証後にトークンを破棄()
    {
        $token = SC_Helper_Session_Ex::getToken();
        $_REQUEST[TRANSACTION_ID_NAME] = $token;

        $result = SC_Helper_Session_Ex::isValidToken(true);

        $this->assertTrue($result);
        $this->assertArrayNotHasKey(TRANSACTION_ID_NAME, $_SESSION, 'トークンが破棄されている');
    }

    public function testIsValidToken検証失敗時は自動的にトークンを破棄()
    {
        SC_Helper_Session_Ex::getToken();
        $_REQUEST[TRANSACTION_ID_NAME] = 'invalid';

        $result = SC_Helper_Session_Ex::isValidToken(false);

        $this->assertFalse($result);
        $this->assertArrayNotHasKey(TRANSACTION_ID_NAME, $_SESSION, '失敗時はトークンが破棄される');
    }

    public function testDestroyTokenトークンを破棄()
    {
        SC_Helper_Session_Ex::getToken();
        $this->assertArrayHasKey(TRANSACTION_ID_NAME, $_SESSION);

        SC_Helper_Session_Ex::destroyToken();

        $this->assertArrayNotHasKey(TRANSACTION_ID_NAME, $_SESSION);
    }
}
