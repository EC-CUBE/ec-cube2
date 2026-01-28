<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/Common_TestCase.php';

/**
 * SC_Helper_Sessionのテストの基底クラス.
 */
class SC_Helper_Session_TestBase extends Common_TestCase
{
    /** @var SC_Helper_Session */
    protected $objHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objHelper = new SC_Helper_Session_Ex();

        // dtb_session テーブルをクリア
        $this->objQuery->delete('dtb_session');
    }

    /**
     * テスト用のセッションデータを作成
     *
     * @param array $override 上書きする値の配列
     *
     * @return array 作成したセッションデータ
     */
    protected function createSessionData($override = [])
    {
        $sess_id = $override['sess_id'] ?? 'test_session_'.uniqid();

        $data = array_merge([
            'sess_id' => $sess_id,
            'sess_data' => 'test_data',
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
        ], $override);

        $this->objQuery->insert('dtb_session', $data);

        return $data;
    }
}
