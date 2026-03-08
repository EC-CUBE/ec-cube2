<?php

require_once __DIR__.'/SC_Helper_Session_TestBase.php';

/**
 * SC_Helper_Session セッションハンドラのテストクラス.
 */
class SC_Helper_Session_sfSessTest extends SC_Helper_Session_TestBase
{
    public function testSfSessOpen常にtrueを返す()
    {
        $result = $this->objHelper->sfSessOpen('/tmp', 'PHPSESSID');

        $this->assertTrue($result);
    }

    public function testSfSessClose常にtrueを返す()
    {
        $result = $this->objHelper->sfSessClose();

        $this->assertTrue($result);
    }

    public function testSfSessReadセッションデータを読み込める()
    {
        $this->createSessionData([
            'sess_id' => 'test123',
            'sess_data' => 'test_session_data',
        ]);

        $result = $this->objHelper->sfSessRead('test123');

        $this->assertEquals('test_session_data', $result);
    }

    public function testSfSessRead存在しないセッションIDは空文字列()
    {
        $result = $this->objHelper->sfSessRead('nonexistent');

        $this->assertEquals('', $result);
    }

    public function testSfSessWrite新規セッションを作成()
    {
        $result = $this->objHelper->sfSessWrite('new_session', 'new_data');

        $this->assertTrue($result);

        $session = $this->objQuery->getRow('*', 'dtb_session', 'sess_id = ?', ['new_session']);
        $this->assertEquals('new_session', $session['sess_id']);
        $this->assertEquals('new_data', $session['sess_data']);
    }

    public function testSfSessWrite既存セッションを更新()
    {
        $this->createSessionData([
            'sess_id' => 'existing',
            'sess_data' => 'old_data',
        ]);

        $result = $this->objHelper->sfSessWrite('existing', 'updated_data');

        $this->assertTrue($result);

        $session = $this->objQuery->getRow('*', 'dtb_session', 'sess_id = ?', ['existing']);
        $this->assertEquals('updated_data', $session['sess_data']);
    }

    public function testSfSessWrite空データの場合は新規作成しない()
    {
        $result = $this->objHelper->sfSessWrite('empty_session', '');

        $this->assertTrue($result);

        $session = $this->objQuery->getRow('*', 'dtb_session', 'sess_id = ?', ['empty_session']);
        $this->assertEmpty($session, '空データの場合はレコード作成しない');
    }

    public function testSfSessDestroyセッションを破棄()
    {
        $this->createSessionData([
            'sess_id' => 'destroy_test',
        ]);

        $result = $this->objHelper->sfSessDestroy('destroy_test');

        $this->assertTrue($result);

        $session = $this->objQuery->getRow('*', 'dtb_session', 'sess_id = ?', ['destroy_test']);
        $this->assertEmpty($session, 'セッションが削除されている');
    }

    public function testSfSessGc古いセッションを削除()
    {
        // 古いセッション
        $old_date = date('Y-m-d H:i:s', time() - MAX_LIFETIME - 3600);
        $this->objQuery->insert('dtb_session', [
            'sess_id' => 'old_session',
            'sess_data' => 'old',
            'create_date' => $old_date,
            'update_date' => $old_date,
        ]);

        // 新しいセッション
        $this->createSessionData([
            'sess_id' => 'new_session',
        ]);

        $result = $this->objHelper->sfSessGc(MAX_LIFETIME);

        $this->assertTrue($result);

        $old = $this->objQuery->getRow('*', 'dtb_session', 'sess_id = ?', ['old_session']);
        $this->assertEmpty($old, '古いセッションが削除されている');

        $new = $this->objQuery->getRow('*', 'dtb_session', 'sess_id = ?', ['new_session']);
        $this->assertNotEmpty($new, '新しいセッションは残っている');
    }
}
