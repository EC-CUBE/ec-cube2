<?php
// XXX E_NOTICE は除外しない方が良いが大量に出るので...
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED &~E_STRICT);
ini_set('display_errors', 1);
$HOME = realpath(dirname(__FILE__)) . "/../..";
// TODO PHPUnit 4.8 で動作しないため一旦コメントアウト
// require_once($HOME . "/tests/class/replace/SC_Display_Ex.php");
// require_once($HOME . "/tests/class/replace/SC_Response_Ex.php");
// require_once($HOME . "/tests/class/replace/SC_Utils_Ex.php");
require_once($HOME . "/tests/class/test/util/Test_Utils.php");
require_once($HOME . "/tests/class/test/util/User_Utils.php");

require_once($HOME . "/data/class/pages/LC_Page_Index.php");
/**
 * 全テストケースの基底クラスです。
 * SC_Queryのテスト以外は基本的にこのクラスを継承して作成してください。
 *
 */
class Common_TestCase extends PHPUnit_Framework_TestCase
{
    /** MailCatcher の URL. */
    const MAILCATCHER_URL = 'http://127.0.0.1:1080';

    /**
     * MDB2 をグローバル変数のバックアップ対象から除外する。
     *
     * @var array
     * @see PHPUnit_Framework_TestCase::$backupGlobals
     * @see PHPUnit_Framework_TestCase::$backupGlobalsBlacklist
     */
    protected $backupGlobalsBlacklist = array(
        '_MDB2_databases',
        '_MDB2_dsninfo_default',
    );

    /** @var SC_Query */
    protected $objQuery;

    /** @var \Eccube2\Tests\Fixture\Generator */
    protected $objGenerator;

    /** 期待値 */
    protected $expected;
    /** 実際の値 */
    protected $actual;

    protected function setUp()
    {
        $this->objQuery = SC_Query_Ex::getSingletonInstance('', true);
        $this->objQuery->begin();
        $this->objGenerator = new \Eccube2\Tests\Fixture\Generator($this->objQuery);
    }

    protected function tearDown()
    {
        $this->objQuery->rollback();
        $this->objQuery = null;
    }

    /**
     * 各テストfunctionの末尾で呼び出し、期待値と実際の値の比較を行います。
     * 呼び出す前に、$expectedに期待値を、$actualに実際の値を導入してください。
     */
    protected function verify($message = null)
    {
        $this->assertEquals($this->expected, $this->actual, $message);
    }

    /**
     * MailCatcher の起動状態をチェックする.
     *
     * MailCatcher が起動していない場合は, テストをスキップする.
     */
    protected function checkMailCatcherStatus()
    {
        try {
            $context = stream_context_create(array(
                'http' => array('ignore_errors' => true)
            ));
            $response = file_get_contents(self::MAILCATCHER_URL.'/messages', false, $context);

            $http_status = strpos($http_response_header[0], '200');
            if ($http_status === false) {
                $this->markTestSkipped('MailCatcher is not available');
            }
        } catch (Exception $e) {
            $this->markTestSkipped('MailCatcher is not available');
        }
    }

    /**
     * MailCatcher のメッセージをすべて削除する.
     */
    protected function resetEmails()
    {
        try {
            $context = stream_context_create(
                array(
                    'http' => array(
                        'method'=> 'DELETE'
                    )
                )
            );

            file_get_contents(self::MAILCATCHER_URL.'/messages', false, $context);

        } catch (\Exception $e) {
            // quiet
        }
    }

    /**
     * MailCatcher のメッセージをすべて取得する.
     *
     * @return array MailCatcher のメッセージの配列
     */
    protected function getMailCatcherMessages()
    {
        return json_decode(file_get_contents(self::MAILCATCHER_URL. '/messages'), true);
    }

    /**
     * MailCatcher のメッセージを ID を指定して取得する.
     *
     * @param int $id メッセージの ID
     * @return array MailCatcher のメッセージ
     */
    protected function getMailCatcherMessage($message)
    {
        $source = file_get_contents(self::MAILCATCHER_URL. '/messages/'.$message['id'].'.source');

        $message['source'] = quoted_printable_decode($source);
        $message['source'] = mb_convert_encoding($message['source'], 'UTF-8', 'JIS');
        return $message;
    }

    /**
     * MailCatcher の最後のメッセージ取得する.
     *
     * @return array MailCatcher のメッセージ
     */
    protected function getLastMailCatcherMessage()
    {
        $messages = $this->getMailCatcherMessages();
        if (empty($messages)) {
            $this->fail("No messages received");
        }

        $last = array_shift($messages);
        return $this->getMailCatcherMessage($last);
    }


    //////////////////////////////////////////////////////////////////
    // 以下はテスト用のユーティリティを使うためのサンプルです。
    // 実際に動作させる場合にはコメントアウトを外して下さい。

    /**
     * actionExit()呼び出しを書き換えてexit()させない例です。
     */
    /**
    public function testExit()
    {
        $resp = new SC_Response_Ex();
        $resp->actionExit();

        $this->expected = TRUE;
        $this->actual = $resp->isExited();
        $this->verify('exitしたかどうか');
    }
     */

    /**
     * 端末種別をテストケースから自由に設定する例です。
     */
    /**
    public function testDeviceType()
    {
        $this->expected = array(DEVICE_TYPE_MOBILE, DEVICE_TYPE_SMARTPHONE);
        $this->actual = array();

        // 端末種別を設定
        User_Utils::setDeviceType(DEVICE_TYPE_MOBILE);
        $this->actual[0] = SC_Display_Ex::detectDevice();
        User_Utils::setDeviceType(DEVICE_TYPE_SMARTPHONE);
        $this->actual[1] = SC_Display_Ex::detectDevice();

        $this->verify('端末種別');
    }
     */

    /**
     * ログイン状態をテストケースから自由に切り替える例です。
     */
    /**
    public function testLoginState()
    {
        $this->expected = array(FALSE, TRUE);
        $this->actual = array();

        $objCustomer = new SC_Customer_Ex();
        User_Utils::setLoginState(FALSE);
        $this->actual[0] = $objCustomer->isLoginSuccess();
        User_Utils::setLoginState(TRUE, null, $this->objQuery);
        $this->actual[1] = $objCustomer->isLoginSuccess();

        $this->verify('ログイン状態');
    }
     */
}
