<?php
class SC_Helper_FileManagerTest extends Common_TestCase
{
    public function testConvertToAbsolutePath()
    {
        $path = USER_DIR;
        $this->expected = realpath(USER_REALDIR).'/';
        $this->actual = SC_Helper_FileManager_Ex::convertToAbsolutePath($path);
        $this->verify($path.' -> '.$this->expected);

        $path = USER_DIR.'packages';
        $this->expected = realpath(USER_REALDIR).'/packages';
        $this->actual = SC_Helper_FileManager_Ex::convertToAbsolutePath($path);
        $this->verify($path.' -> '.$this->expected);

        $path = USER_REALDIR.'packages';
        $this->expected = realpath(USER_REALDIR).'/packages';
        $this->actual = SC_Helper_FileManager_Ex::convertToAbsolutePath($path);
        $this->verify($path.' -> '.$this->expected);

        $path = rtrim(USER_DIR, '/');
        $this->expected = realpath(USER_REALDIR);
        $this->actual = SC_Helper_FileManager_Ex::convertToAbsolutePath($path);
        $this->verify($path.' -> '.$this->expected);

        $path = USER_REALDIR.'packages/default/../';
        $this->expected = realpath(USER_REALDIR).'/packages/';
        $this->actual = SC_Helper_FileManager_Ex::convertToAbsolutePath($path);
        $this->verify($path.' -> '.$this->expected);

        $path = USER_REALDIR.'packages/default/../../../';
        $this->expected = realpath(USER_REALDIR).'/';
        $this->actual = SC_Helper_FileManager_Ex::convertToAbsolutePath($path);
        $this->verify($path.' -> '.$this->expected);

        if (DIRECTORY_SEPARATOR === '\\') {
            $path = 'c:/';
        } else {
            $path = '/';
        }
        $this->expected = realpath(USER_REALDIR).'/';
        $this->actual = SC_Helper_FileManager_Ex::convertToAbsolutePath($path);
        $this->verify($path.' -> '.$this->expected);

        $path = USER_REALDIR.'packages/gXGCwWZ4Nx/';
        $this->expected = realpath(USER_REALDIR).'/';
        $this->actual = SC_Helper_FileManager_Ex::convertToAbsolutePath($path);
        $this->verify($path.' -> '.$this->expected);
    }

    public function testConvertToRelativePath()
    {
        $path = USER_DIR;
        $this->expected = USER_DIR;
        $this->actual = SC_Helper_FileManager_Ex::convertToRelativePath($path);
        $this->verify($path.' -> '.$this->expected);

        $path = USER_DIR.'packages';
        $this->expected = USER_DIR.'packages';
        $this->actual = SC_Helper_FileManager_Ex::convertToRelativePath($path);
        $this->verify($path.' -> '.$this->expected);

        $path = USER_REALDIR.'packages';
        $this->expected = USER_DIR.'packages';
        $this->actual = SC_Helper_FileManager_Ex::convertToRelativePath($path);
        $this->verify($path.' -> '.$this->expected);

        $path = rtrim(USER_DIR, '/');
        $this->expected = rtrim(USER_DIR, '/');
        $this->actual = SC_Helper_FileManager_Ex::convertToRelativePath($path);
        $this->verify($path.' -> '.$this->expected);

        $path = USER_REALDIR.'packages/default/../';
        $this->expected = USER_DIR.'packages/';
        $this->actual = SC_Helper_FileManager_Ex::convertToRelativePath($path);
        $this->verify($path.' -> '.$this->expected);

        $path = USER_REALDIR.'packages/default/../../../';
        $this->expected = USER_DIR;
        $this->actual = SC_Helper_FileManager_Ex::convertToRelativePath($path);
        $this->verify($path.' -> '.$this->expected);

        if (DIRECTORY_SEPARATOR === '\\') {
            $path = 'c:/';
        } else {
            $path = '/';
        }
        $this->expected = USER_DIR;
        $this->actual = SC_Helper_FileManager_Ex::convertToRelativePath($path);
        $this->verify($path.' -> '.$this->expected);

        $path = USER_REALDIR.'packages/gXGCwWZ4Nx/';
        $this->expected = USER_DIR;
        $this->actual = SC_Helper_FileManager_Ex::convertToRelativePath($path);
        $this->verify($path.' -> '.$this->expected);
    }
}
