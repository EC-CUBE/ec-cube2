<?php

class SC_UploadFileTest extends Common_TestCase
{
    /** @var SC_UploadFile */
    protected $objUpFile;
    /** @var string */
    protected $saveDir;
    /** @var string */
    protected $tempDir;

    protected function setUp()
    {
        parent::setUp();
        $this->saveDir = sys_get_temp_dir().'/'.uniqid();
        $this->tempDir = sys_get_temp_dir().'/'.uniqid();
        foreach ([$this->saveDir, $this->tempDir] as $dir) {
            mkdir($dir, 0777, true);
        }
        $this->objUpFile = new SC_UploadFile($this->tempDir, $this->saveDir);
        copy(IMAGE_SAVE_REALDIR.'ice500.jpg', $this->tempDir.'/ice500.jpg');

        $_FILES = [
            'main_image' => [
                'name' => 'ice500.jpg',
                'tmp_name' => $this->tempDir.'/ice500.jpg',
                'error' => UPLOAD_ERR_OK
            ]
        ];
    }

    protected function tearDown()
    {
        foreach ([$this->saveDir, $this->tempDir] as $dir) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                /** @var SplFileInfo $file */
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getRealPath());
            }
            rmdir($dir);
        }
        parent::tearDown();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('SC_UploadFile', $this->objUpFile);
    }

    public function testAddFile()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE);
        $this->objUpFile->addFile('一覧-メイン画像', 'main_list_image', array('jpg', 'gif', 'png'), IMAGE_SIZE, false, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);

        $this->assertEquals(['詳細-メイン画像', '一覧-メイン画像'], $this->objUpFile->disp_name);
        $this->assertEquals(['main_image', 'main_list_image'], $this->objUpFile->keyname);
        $this->assertEquals([0, SMALL_IMAGE_WIDTH], $this->objUpFile->width);
        $this->assertEquals([0, SMALL_IMAGE_HEIGHT], $this->objUpFile->height);
        $this->assertEquals([['jpg'], ['jpg', 'gif', 'png']], $this->objUpFile->arrExt);
        $this->assertEquals([IMAGE_SIZE, IMAGE_SIZE], $this->objUpFile->size);
        $this->assertEquals([false, false], $this->objUpFile->necessary);
        $this->assertEquals([true, true], $this->objUpFile->image);
    }

    public function testMakeThumb()
    {
        $destFile = $this->objUpFile->makeThumb(
            $this->tempDir.'/ice500.jpg',
            SMALL_IMAGE_WIDTH,
            SMALL_IMAGE_HEIGHT,
            $this->saveDir.'/ice150'
        );
        $this->assertFileExists($this->saveDir.'/'.$destFile);
    }

    public function testMakeTempFileWithImage()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE);

        $this->expected = '';
        $this->actual = $this->objUpFile->makeTempFile('main_image');
        $this->verify();
    }

    public function testMakeTempFileWithImageWithNotRename()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE);

        $this->expected = '';
        $this->actual = $this->objUpFile->makeTempFile('main_image', false);
        $this->verify();
    }

    public function testMakeTempFileWithNotImage()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, false, 0, 0, false);
        $arrErr = $this->objUpFile->makeTempFile('main_image');

        $this->expected = '※ ファイルのアップロードに失敗しました。<br />';
        $this->actual = $this->objUpFile->makeTempFile('main_image');
        $this->verify('move_uploaded_file() が false になるため必ず失敗する');
    }

    public function testMakeTempFileWithRename()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, false, 0, 0, false);

        $this->expected = '※ ファイルのアップロードに失敗しました。<br />';
        $this->actual = $this->objUpFile->makeTempFile('main_image');
        $this->verify('move_uploaded_file() が false になるため必ず失敗する');

        $this->assertContains(date('mdHi').'_', $this->objUpFile->temp_file[0]);
    }

    public function testMakeTempFileWithNotRename()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, false, 0, 0, false);

        $this->expected = '※ ファイルのアップロードに失敗しました。<br />';
        $this->actual = $this->objUpFile->makeTempFile('main_image', false);
        $this->verify('move_uploaded_file() が false になるため必ず失敗する');

        $this->expected = 'ice500.jpg';
        $this->actual = $this->objUpFile->temp_file[0];
        $this->verify();
    }

    public function testDeleteFile()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, false, 0, 0, false);
        $this->objUpFile->makeTempFile('main_image', false);
        $this->objUpFile->deleteFile('main_image');

        $this->assertEquals([''], $this->objUpFile->temp_file);
        $this->assertEquals([''], $this->objUpFile->save_file);
        $this->assertFileNotExists($this->tempDir.'/ice500.jpg');
    }

    public function testGetTempFilePath()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, false, 0, 0, false);
        $this->objUpFile->makeTempFile('main_image', true); // rename

        $this->assertContains(date('mdHi').'_', $this->objUpFile->getTempFilePath('main_image'));
    }

    public function testMoveTempFile()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, false, 0, 0, false);
        $this->objUpFile->makeTempFile('main_image', false);
        $this->objUpFile->moveTempFile();

        $this->assertFileExists($this->saveDir.'/ice500.jpg');
    }

    public function testMoveTempDownloadFile()
    {
        $_FILES = [
            'down_file' => [
                'name' => 'ice500.jpg',
                'tmp_name' => $this->tempDir.'/ice500.jpg',
                'error' => UPLOAD_ERR_OK
            ]
        ];
        $this->objUpFile->addFile('ダウンロードファイル', 'down_file', array('jpg'), IMAGE_SIZE, false, 0, 0, false);

        $this->objUpFile->makeTempDownFile('down_file');
        $this->objUpFile->setDBDownFile(['down_realfilename' => 'ice500.jpg']);
        $this->objUpFile->moveTempDownFile();

        $this->assertFileNotExists($this->saveDir.'/ice500.jpg');
    }

    public function testMoveTempDownloadFileWithFileExists()
    {
        $_FILES = [
            'down_file' => [
                'name' => 'ice500.jpg',
                'tmp_name' => $this->tempDir.'/ice500.jpg',
                'error' => UPLOAD_ERR_OK
            ]
        ];
        $this->objUpFile->addFile('ダウンロードファイル', 'down_file', array('jpg'), IMAGE_SIZE, false, 0, 0, false);

        $this->objUpFile->makeTempDownFile('down_file');
        $this->objUpFile->moveTempDownFile();

        $this->assertFileNotExists($this->saveDir.'/ice500.jpg');
    }

    public function testMoveTempFileWithFileExists()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, false, 0, 0, false);
        $this->objUpFile->makeTempFile('main_image', false);
        $this->objUpFile->setDBFileList(['main_image' => 'ice500.jpg']); // file exists
        $this->objUpFile->moveTempFile();

        $this->assertFileNotExists($this->saveDir.'/ice500.jpg');
    }

    public function testSetHiddenFileList()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, false, 0, 0, false);

        $this->objUpFile->makeTempFile('main_image', false);
        $this->objUpFile->setDBFileList(['main_image' => 'ice500.jpg']); // file exists
        $this->objUpFile->setHiddenFileList(
            [
                'temp_main_image' => 'ice500.jpg',
                'save_main_image' => 'ice500.jpg'
            ]
        );

        $this->expected = [
            'temp_main_image' => 'ice500.jpg',
            'save_main_image' => 'ice500.jpg'
        ]; 
        $this->actual = $this->objUpFile->getHiddenFileList();
        $this->verify();
    }

    public function testGetFormFileList()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, false, 0, 0, false);

        $this->objUpFile->makeTempFile('main_image', false);
        $this->objUpFile->setDBFileList(['main_image' => 'ice500.jpg']); // file exists

        $this->expected = [
            'main_image' => [
                'filepath' => '/temp/ice500.jpg',
                'real_filepath' => $this->tempDir.'/ice500.jpg',
                'width' => 0,
                'height' => 0,
                'disp_name' => '詳細-メイン画像'
            ]
        ];
        $this->actual = $this->objUpFile->getFormFileList('/temp', '/save');
        $this->verify();

        $this->expected = ['main_image' => 'ice500.jpg'];
        $this->actual = $this->objUpFile->getDBFileList();
        $this->verify();
    }

    public function testGetFormFileWithSaveFile()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, false, 0, 0, false);

        $this->objUpFile->makeTempFile('main_image', false);
        $this->objUpFile->setDBFileList(['main_image' => 'ice500.jpg']); // file exists
        $this->objUpFile->temp_file = [];

        $this->expected = [
            'main_image' => [
                'filepath' => '/save/ice500.jpg',
                'real_filepath' => $this->saveDir.'/ice500.jpg',
                'width' => 0,
                'height' => 0,
                'disp_name' => '詳細-メイン画像'
            ]
        ];
        $this->actual = $this->objUpFile->getFormFileList('/temp', '/save');
        $this->verify();

        $this->expected = ['main_image' => 'ice500.jpg'];
        $this->actual = $this->objUpFile->getDBFileList();
        $this->verify();
    }

    public function testGetFormFileListWithRealSize()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, false, 0, 0, false);

        $this->objUpFile->makeTempFile('main_image', false);
        $this->objUpFile->setDBFileList(['main_image' => 'ice500.jpg']); // file exists

        $this->expected = [
            'main_image' => [
                'filepath' => '/temp/ice500.jpg',
                'real_filepath' => $this->tempDir.'/ice500.jpg',
                'width' => 500,
                'height' => 500,
                'disp_name' => '詳細-メイン画像'
            ]
        ];
        $this->actual = $this->objUpFile->getFormFileList('/temp', '/save', true);
        $this->verify();
    }

    public function testCheckExists()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, true, 0, 0, false);

        $this->objUpFile->makeTempFile('main_image', false);
        $this->objUpFile->setDBFileList(['main_image' => 'ice500.jpg']);

        $this->expected = [];
        $this->actual = $this->objUpFile->checkExists('main_image');
        $this->verify();
    }

    public function testCheckExistsWithNotupload()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, true, 0, 0, false);

        $this->expected = [
            'main_image' => '※ 詳細-メイン画像がアップロードされていません。<br>'
        ];
        $this->actual = $this->objUpFile->checkExists('main_image');
        $this->verify();
    }

    public function testCheckUploadErrorWithNoFile()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, true, 0, 0, false);
        $_FILES['main_image']['error'] = UPLOAD_ERR_NO_FILE;

        $this->expected = '※ 詳細-メイン画像が選択されていません。<br />';
        $this->actual = $this->objUpFile->makeTempFile('main_image');
        $this->verify();
    }

    public function testCheckUploadErrorWithIniSize()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, true, 0, 0, false);
        $_FILES['main_image']['error'] = UPLOAD_ERR_INI_SIZE;

        $this->expected = '※ 詳細-メイン画像のアップロードに失敗しました。(.htaccessファイルのphp_value upload_max_filesizeを調整してください)<br />';
        $this->actual = $this->objUpFile->makeTempFile('main_image');
        $this->verify();
    }

    public function testCheckUploadErrorWithAnyError()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, true, 0, 0, false);
        $_FILES['main_image']['error'] = UPLOAD_ERR_PARTIAL;

        $this->expected = '※ 詳細-メイン画像のアップロードに失敗しました。エラーコードは[3]です。<br />';
        $this->actual = $this->objUpFile->makeTempFile('main_image');
        $this->verify();
    }

    public function testMakeTempFileWithDownloadfile()
    {
        $_FILES = [
            'down_file' => [
                'name' => 'ice500.jpg',
                'tmp_name' => $this->tempDir.'/ice500.jpg',
                'error' => UPLOAD_ERR_OK
            ]
        ];
        $this->objUpFile->addFile('ダウンロードファイル', 'down_file', array('jpg'), IMAGE_SIZE, false, 0, 0, false);

        $this->expected = '';
        $this->actual = $this->objUpFile->makeTempDownFile('down_file');
        $this->verify();

        $this->assertFileExists($this->objUpFile->temp_dir . $this->objUpFile->temp_file[0]);
    }

    public function testDeleteKikakuFile()
    {
        $this->objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg'), IMAGE_SIZE, false, 0, 0, false);
        $this->objUpFile->makeTempFile('main_image', false);
        $this->objUpFile->deleteKikakuFile('main_image');

        $this->assertEquals([''], $this->objUpFile->temp_file);
        $this->assertNotEquals([''], $this->objUpFile->save_file);
        $this->assertFileNotExists($this->tempDir.'/ice500.jpg');
    }

    public function testGetFormDownloadFileList()
    {
        $_FILES = [
            'down_file' => [
                'name' => 'ice500.jpg',
                'tmp_name' => $this->tempDir.'/ice500.jpg',
                'error' => UPLOAD_ERR_OK
            ]
        ];
        $this->objUpFile->addFile('ダウンロードファイル', 'down_file', array('jpg'), IMAGE_SIZE, false, 0, 0, false);
        $this->objUpFile->makeTempDownFile('down_file');

        $this->assertContains(date('mdHi').'_', $this->objUpFile->getFormDownFile());
    }

    public function testGetFormDownloadFileWithSaveFile()
    {
        $_FILES = [
            'down_file' => [
                'name' => 'ice500.jpg',
                'tmp_name' => $this->tempDir.'/ice500.jpg',
                'error' => UPLOAD_ERR_OK
            ]
        ];
        $this->objUpFile->addFile('ダウンロードファイル', 'down_file', array('jpg'), IMAGE_SIZE, false, 0, 0, false);

        $this->objUpFile->setDBDownFile(['down_realfilename' => 'ice500.jpg']); // file exists
        $this->expected = 'ice500.jpg';
        $this->actual = $this->objUpFile->getFormDownFile();
        $this->verify();
    }

    public function testDeleteDownloadFile()
    {
        $_FILES = [
            'down_file' => [
                'name' => 'ice500.jpg',
                'tmp_name' => $this->tempDir.'/ice500.jpg',
                'error' => UPLOAD_ERR_OK
            ]
        ];
        $this->objUpFile->addFile('ダウンロードファイル', 'down_file', array('jpg'), IMAGE_SIZE, false, 0, 0, false);

        $this->objUpFile->makeTempDownFile('down_file');
        // $this->objUpFile->setDBDownFile(['down_realfilename' => 'ice500.jpg']); // file exists
        $this->objUpFile->deleteDBDownFile(['down_realfilename' => 'ice500.jpg']);

        $this->assertFileNotExists($this->saveDir.'/ice500.jpg');
    }
}

