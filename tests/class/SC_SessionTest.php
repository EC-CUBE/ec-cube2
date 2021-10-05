
<?php

class SC_SessionTest extends Common_TestCase
{
    /**
     * @var SC_Session
     */
    protected $objSession;

    protected function setUp()
    {
        parent::setUp();
        $_SESSION['cert'] = CERT_STRING;
        $_SESSION['login_id'] = 'admin';
        $_SESSION['authority'] = 0;
        $_SESSION['member_id'] = 1;
        $_SESSION['uniq_id'] = 'uniqid'; // XXX コンストラクタで使用している未使用変数
        $this->objSession = new SC_Session_Ex();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('SC_Session', $this->objSession);
        $this->assertNotNull($this->objSession->uniqid, 'コンストラクタで使用している未使用変数');
    }

    public function testIsSuccess()
    {
        $_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../../html/admin/system/index.php';
        $_SESSION['authority'] = 0;
        $this->expected = SUCCESS;
        $this->actual = $this->objSession->IsSuccess();
        $this->verify();
    }

    public function testIsSuccessWithBC()
    {
        // 下位互換チェック. 2.17.1 までのパスを登録する
        $masterData = new SC_DB_MasterData_Ex();
        $masterData->registMasterData('mtb_permission', ['id', 'name', 'rank'], ['/admin/system/index.php' => '0']);
        $masterData->clearCache('mtb_permission');
        $masterData->createCache('mtb_permission');

        $_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../../html/admin/system/index.php';
        $_SESSION['authority'] = 0;
        $this->expected = SUCCESS;
        $this->actual = $this->objSession->IsSuccess();
        $this->verify();
    }

    public function testIsSuccessWithChangeAdminDir()
    {
        // ADMIN_DIR = 'manager/' でアクセス権を検証する
        $admin_dir = 'manager/';

        // ADMIN_DIR を除いたパスを登録する
        $masterData = new SC_DB_MasterData_Ex();
        $masterData->registMasterData('mtb_permission', ['id', 'name', 'rank'], ['/system/index.php' => '0']);
        $masterData->clearCache('mtb_permission');
        $masterData->createCache('mtb_permission');

        $_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../../html/manager/system/index.php';
        $_SESSION['authority'] = 0;
        $this->objSession = new SC_Session_Ex();
        $this->expected = SUCCESS;
        $this->actual = $this->objSession->IsSuccess($admin_dir);
        $this->verify();
    }

    public function testIsSuccessWithAdminDir()
    {
        // ADMIN_DIR = 'manager/' でアクセス権を検証する
        $admin_dir = 'manager/';

        // ADMIN_DIR を含めたパスを登録する
        $masterData = new SC_DB_MasterData_Ex();
        $masterData->registMasterData('mtb_permission', ['id', 'name', 'rank'], ['/'.$admin_dir.'/system/index.php' => '0']);
        $masterData->clearCache('mtb_permission');
        $masterData->createCache('mtb_permission');

        $_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../../html/manager/system/index.php';
        $_SESSION['authority'] = 0;
        $this->objSession = new SC_Session_Ex();
        $this->expected = SUCCESS;
        $this->actual = $this->objSession->IsSuccess($admin_dir);
        $this->verify();
    }

    public function testIsSuccessWithAccessError()
    {
        $_SESSION['cert'] = 'bad';
        $this->objSession = new SC_Session_Ex();
        $this->expected = ACCESS_ERROR;
        $this->actual = $this->objSession->IsSuccess();
        $this->verify();
    }

    public function testIsSuccessWithPermissionError()
    {
        $_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../../html/admin/system/index.php';
        $_SESSION['authority'] = 1;
        $this->objSession = new SC_Session_Ex();
        $this->expected = ACCESS_ERROR;
        $this->actual = $this->objSession->IsSuccess();
        $this->verify();
    }

    public function testIsSuccessWithPermissionErrorBC()
    {
        // 下位互換チェック. 2.17.1 までのパスを登録する
        $masterData = new SC_DB_MasterData_Ex();
        $masterData->registMasterData('mtb_permission', ['id', 'name', 'rank'], ['/admin/system/index.php' => '0']);
        $masterData->clearCache('mtb_permission');
        $masterData->createCache('mtb_permission');

        $_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../../html/admin/system/index.php';
        $_SESSION['authority'] = 1;
        $this->objSession = new SC_Session_Ex();
        $this->expected = ACCESS_ERROR;
        $this->actual = $this->objSession->IsSuccess();
        $this->verify();
    }

    public function testIsSuccessWithChangeAdminDirPermissionError()
    {
        // ADMIN_DIR = 'manager/' でアクセス権を検証する
        $admin_dir = 'manager/';

        // ADMIN_DIR を除いたパスを登録する
        $masterData = new SC_DB_MasterData_Ex();
        $masterData->registMasterData('mtb_permission', ['id', 'name', 'rank'], ['/system/index.php' => '0']);
        $masterData->clearCache('mtb_permission');
        $masterData->createCache('mtb_permission');

        $_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../../html/manager/system/index.php';
        $_SESSION['authority'] = 1;
        $this->objSession = new SC_Session_Ex();
        $this->expected = ACCESS_ERROR;
        $this->actual = $this->objSession->IsSuccess($admin_dir);
        $this->verify();
    }

    public function testIsSuccessWithAdminDirPermissionError()
    {
        // ADMIN_DIR = 'manager/' でアクセス権を検証する
        $admin_dir = 'manager/';

        // ADMIN_DIR を含めたパスを登録する
        $masterData = new SC_DB_MasterData_Ex();
        $masterData->registMasterData('mtb_permission', ['id', 'name', 'rank'], ['/'.$admin_dir.'/system/index.php' => '0']);
        $masterData->clearCache('mtb_permission');
        $masterData->createCache('mtb_permission');

        $_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../../html/manager/system/index.php';
        $_SESSION['authority'] = 1;
        $this->objSession = new SC_Session_Ex();
        $this->expected = ACCESS_ERROR;
        $this->actual = $this->objSession->IsSuccess($admin_dir);
        $this->verify();
    }


    public function testSetSession()
    {
        $this->objSession->SetSession('test', 'value');
        $this->assertEquals($_SESSION['test'], $this->objSession->GetSession('test'));
    }

    public function testGetSID()
    {
        $this->expected = substr(sha1(session_id()), 0, 8);
        $this->actual = $this->objSession->GetSID();
        $this->assertNotNull($this->actual);
        $this->verify();
    }

    public function testGetUniqId()
    {
        $_SESSION['uniqid'] = 'uniqid';
        $this->expected = 'uniqid';
        $this->actual = $this->objSession->getUniqId();
        $this->verify();
    }

    public function testGetUniqIdWithGenerate()
    {
        $this->actual = $this->objSession->getUniqId();
        $this->expected = $_SESSION['uniqid'];
        $this->verify();
    }

    public function testLogout()
    {
        $this->objSession->logout();
        $this->assertNull($_SESSION[TRANSACTION_ID_NAME]);
        $this->assertNull($_SESSION['cert']);
        $this->assertNull($_SESSION['login_id']);
        $this->assertNull($_SESSION['authority']);
        $this->assertNull($_SESSION['member_id']);
        $this->assertNull($_SESSION['uniqid']);
    }

    public function testRegenerateSID()
    {
        $this->expected = session_id();
        $result = $this->objSession->regenerateSID();
        $this->actual = session_id();

        if ($result === false) {
            $this->markTestSkipped('Can not regenerateSID');
            $this->assertEquals($this->expected, $this->actual);
        } else {
            $this->assertNotEquals($this->expected, $this->actual);
        }
    }
}
