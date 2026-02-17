<?php

/**
 * @backupGlobals disabled
 */
class SC_Batch_Update_parseDistInfoTest extends PHPUnit_Framework_TestCase
{
    /** @var SC_Batch_Update */
    private $batch;

    /** @var string */
    private $tmpDir;

    protected function setUp(): void
    {
        if (!defined('MODULE_REALDIR') || !defined('HTML_REALDIR')) {
            $this->markTestSkipped('EC-CUBE constants are not defined.');
        }
        $this->batch = new SC_Batch_Update();
        $this->tmpDir = sys_get_temp_dir().'/sc_batch_update_test_'.uniqid();
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        // テンポラリファイルを削除
        array_map('unlink', glob($this->tmpDir.'/*'));
        rmdir($this->tmpDir);
        if (file_exists('/tmp/pwned.txt')) {
            unlink('/tmp/pwned.txt');
        }
    }

    /**
     * オーナーズストアが生成する distinfo.php のフォーマットをパースできること
     */
    public function testオーナーズストア形式の定数連結をパースできる()
    {
        $content = <<<'PHP'
            <?php
            $distinfo = array(
            'da39a3ee5e6b4b0d3255bfef95601890afd80709' => MODULE_REALDIR . 'mdl_example/example.php',
            'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3' => MODULE_REALDIR . 'mdl_example/config.php',
            );
            ?>
            PHP;
        $path = $this->tmpDir.'/distinfo.php';
        file_put_contents($path, $content);

        $result = $this->batch->parseDistInfo($path);

        $this->assertCount(2, $result);
        $this->assertSame(
            MODULE_REALDIR.'mdl_example/example.php',
            $result['da39a3ee5e6b4b0d3255bfef95601890afd80709']
        );
        $this->assertSame(
            MODULE_REALDIR.'mdl_example/config.php',
            $result['a94a8fe5ccb19ba61c4c0873d391e987982fbbd3']
        );
    }

    /**
     * makeDistInfo() が生成するバックアップ用フォーマットをパースできること
     */
    public function testバックアップ形式の文字列リテラルをパースできる()
    {
        $content = <<<'PHP'
            <?php
            $distinfo = array(
            'da39a3ee5e6b4b0d3255bfef95601890afd80709' => '/var/www/app/data/class/example.php',
            'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3' => '/var/www/app/html/index.php',
            );
            ?>
            PHP;
        $path = $this->tmpDir.'/distinfo.php';
        file_put_contents($path, $content);

        $result = $this->batch->parseDistInfo($path);

        $this->assertCount(2, $result);
        $this->assertSame(
            '/var/www/app/data/class/example.php',
            $result['da39a3ee5e6b4b0d3255bfef95601890afd80709']
        );
        $this->assertSame(
            '/var/www/app/html/index.php',
            $result['a94a8fe5ccb19ba61c4c0873d391e987982fbbd3']
        );
    }

    /**
     * HTML_REALDIR 定数の連結をパースできること
     */
    public function testHTMLREALDIR定数の連結をパースできる()
    {
        $content = <<<'PHP'
            <?php
            $distinfo = array(
            'da39a3ee5e6b4b0d3255bfef95601890afd80709' => HTML_REALDIR . 'js/example.js',
            );
            ?>
            PHP;
        $path = $this->tmpDir.'/distinfo.php';
        file_put_contents($path, $content);

        $result = $this->batch->parseDistInfo($path);

        $this->assertCount(1, $result);
        $this->assertSame(
            HTML_REALDIR.'js/example.js',
            $result['da39a3ee5e6b4b0d3255bfef95601890afd80709']
        );
    }

    /**
     * 存在しないファイルの場合は空配列を返すこと
     */
    public function test存在しないファイルは空配列を返す()
    {
        $result = $this->batch->parseDistInfo($this->tmpDir.'/nonexistent.php');

        $this->assertSame([], $result);
    }

    /**
     * 空のファイルの場合は空配列を返すこと
     */
    public function test空ファイルは空配列を返す()
    {
        $path = $this->tmpDir.'/distinfo.php';
        file_put_contents($path, '<?php ?>');

        $result = $this->batch->parseDistInfo($path);

        $this->assertSame([], $result);
    }

    /**
     * 悪意あるPHPコードを含む distinfo.php が実行されないこと
     */
    public function test悪意あるコードが実行されない()
    {
        $content = <<<'PHP'
            <?php
            system('echo PWNED > /tmp/pwned.txt');
            $distinfo = array(
            'da39a3ee5e6b4b0d3255bfef95601890afd80709' => MODULE_REALDIR . 'mdl_example/example.php',
            );
            ?>
            PHP;
        $path = $this->tmpDir.'/distinfo.php';
        file_put_contents($path, $content);

        $result = $this->batch->parseDistInfo($path);

        // パースは正常に動作する
        $this->assertCount(1, $result);
        // 悪意あるコードは実行されていない
        $this->assertFileDoesNotExist('/tmp/pwned.txt');
    }

    /**
     * 未定義の定数が使用されている場合はスキップされること
     */
    public function test未定義の定数はスキップされる()
    {
        $content = <<<'PHP'
            <?php
            $distinfo = array(
            'da39a3ee5e6b4b0d3255bfef95601890afd80709' => UNKNOWN_CONST . 'path/to/file.php',
            );
            ?>
            PHP;
        $path = $this->tmpDir.'/distinfo.php';
        file_put_contents($path, $content);

        $result = $this->batch->parseDistInfo($path);

        $this->assertSame([], $result);
    }
}
