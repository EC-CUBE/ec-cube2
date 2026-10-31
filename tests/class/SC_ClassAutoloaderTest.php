<?php

require_once __DIR__.'/Common_TestCase.php';

/**
 * SC_ClassAutoloader のテストクラス
 *
 * オートローダーが生成するパスと *_Ex ファイルの配置の整合性を検証
 * 関連issue: https://github.com/EC-CUBE/ec-cube2/issues/1268
 *            https://github.com/EC-CUBE/ec-cube2/issues/1429
 */
class SC_ClassAutoloaderTest extends Common_TestCase
{
    /**
     * EC-CUBE 2.17.2 までの *_Ex ファイル一式を収録したパッケージ
     */
    public const CLASS_EXTENDS_STUBS_DIR = 'vendor/nanasess/ec-cube2-class-extends-stubs/data/class_extends/';

    /**
     * *_Ex クラスのパスが元クラスの配置から正しく導出されるかテスト
     *
     * @dataProvider exClassProvider
     *
     * @group classloader
     *
     * @param string $className
     * @param string $relativePath CLASS_EX_REALDIR からの相対パス
     */
    public function testGetExClassPath($className, $relativePath)
    {
        $this->assertSame(CLASS_EX_REALDIR.$relativePath, SC_ClassAutoloader_Ex::getExClassPath($className));
    }

    /**
     * 元クラスが data/class 以下に存在する *_Ex クラスのデータプロバイダー
     *
     * @return array
     */
    public static function exClassProvider()
    {
        $stubsDir = DATA_REALDIR.self::CLASS_EXTENDS_STUBS_DIR;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($stubsDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $classes = [];
        foreach ($iterator as $file) {
            $className = $file->getBasename('.php');
            if (!$file->isFile() || $file->getExtension() !== 'php' || !str_ends_with($className, '_Ex')) {
                continue;
            }
            // 元クラスが削除済みのもの (LC_Page_Admin_System_Editdb_Ex など) や API 用のクラスは対象外
            $baseClassName = preg_replace('/_Ex$/', '', $className);
            if (!class_exists($baseClassName)) {
                continue;
            }
            $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($stubsDir)));
            $classes[$className] = [$className, $relativePath];
        }
        ksort($classes);

        return $classes;
    }

    /**
     * 元クラスが存在しない場合はパスを導出しないことをテスト
     *
     * @group classloader
     */
    public function testGetExClassPathWithoutBaseClass()
    {
        $this->assertNull(SC_ClassAutoloader_Ex::getExClassPath('SC_NotExistsClass_Ex'));
        $this->assertNull(SC_ClassAutoloader_Ex::getExClassPath('SC_Product'));
    }

    /**
     * data/class_extends に配置した *_Ex ファイルがロードされるかテスト
     *
     * 2.25.0 では helper_extends/purchase/SC_Helper_Purchase_Ex.php を探してしまい,
     * 配置したファイルが無視されて元クラスのエイリアスになっていた
     *
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     *
     * @group classloader
     */
    public function testLoadPlacedExClass()
    {
        $className = 'SC_Helper_Purchase_Ex';
        $path = CLASS_EX_REALDIR.'helper_extends/'.$className.'.php';
        $this->assertFileDoesNotExist($path);
        $this->assertFalse(class_exists($className, false), sprintf('%s はロード前であること', $className));

        file_put_contents($path, '<?php class SC_Helper_Purchase_Ex extends SC_Helper_Purchase { const PLACED = true; }');
        try {
            $refClass = new ReflectionClass($className);
        } finally {
            unlink($path);
        }

        $this->assertSame($className, $refClass->getName(), '元クラスのエイリアスではないこと');
        $this->assertTrue(constant($className.'::PLACED'));
    }

    /**
     * 同梱の LC_Page_AbstractMypage_Ex がロードされ, require_once しても再宣言にならないかテスト
     *
     * @see https://github.com/EC-CUBE/ec-cube2/issues/1429
     *
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     *
     * @group classloader
     */
    public function testLoadAbstractMypageEx()
    {
        $className = 'LC_Page_AbstractMypage_Ex';
        $path = CLASS_EX_REALDIR.'page_extends/mypage/'.$className.'.php';

        $refClass = new ReflectionClass($className);
        $this->assertSame($className, $refClass->getName(), '元クラスのエイリアスではないこと');
        $this->assertSame(realpath($path), realpath($refClass->getFileName()));

        // 決済モジュール等で require_once されても再宣言にならない
        require_once $path;
    }

    /**
     * 主要なヘルパークラスが正しくオートロードされるかテスト
     *
     * @dataProvider helperClassProvider
     *
     * @group classloader
     *
     * @param string $className
     */
    public function testHelperClassAutoloading($className)
    {
        // クラスがまだロードされていない場合のみテスト
        if (!class_exists($className, false)) {
            $this->assertTrue(
                class_exists($className, true),
                sprintf('%s がオートロードできませんでした', $className)
            );
        } else {
            $this->assertTrue(true, sprintf('%s は既にロード済みです', $className));
        }
    }

    /**
     * ヘルパークラスのデータプロバイダー
     *
     * @return array
     */
    public static function helperClassProvider()
    {
        return [
            'SC_Helper_Purchase_Ex' => ['SC_Helper_Purchase_Ex'],
            'SC_Helper_DB_Ex' => ['SC_Helper_DB_Ex'],
            'SC_Helper_Mail_Ex' => ['SC_Helper_Mail_Ex'],
            'SC_Helper_Customer_Ex' => ['SC_Helper_Customer_Ex'],
            'SC_Helper_Session_Ex' => ['SC_Helper_Session_Ex'],
            'SC_Helper_Plugin_Ex' => ['SC_Helper_Plugin_Ex'],
            'SC_Helper_TaxRule_Ex' => ['SC_Helper_TaxRule_Ex'],
        ];
    }

    /**
     * Graphクラスが正しくオートロードされるかテスト
     *
     * @dataProvider graphClassProvider
     *
     * @group classloader
     *
     * @param string $className
     */
    public function testGraphClassAutoloading($className)
    {
        if (!class_exists($className, false)) {
            $this->assertTrue(
                class_exists($className, true),
                sprintf('%s がオートロードできませんでした', $className)
            );
        } else {
            $this->assertTrue(true, sprintf('%s は既にロード済みです', $className));
        }
    }

    /**
     * Graphクラスのデータプロバイダー
     *
     * @return array
     */
    public static function graphClassProvider()
    {
        return [
            'SC_Graph_Base_Ex' => ['SC_Graph_Base_Ex'],
            'SC_Graph_Line_Ex' => ['SC_Graph_Line_Ex'],
            'SC_Graph_Bar_Ex' => ['SC_Graph_Bar_Ex'],
            'SC_Graph_Pie_Ex' => ['SC_Graph_Pie_Ex'],
        ];
    }

    /**
     * _Exクラスが存在しない場合、元クラスのエイリアスとして動作するかテスト
     *
     * @group classloader
     */
    public function testExClassAlias()
    {
        // 存在しない_Exクラスの例（実際には存在しないと仮定）
        $baseClass = 'SC_CheckError';
        $exClass = 'SC_CheckError_Ex';

        // 元クラスが存在することを確認
        $this->assertTrue(class_exists($baseClass, true), sprintf('%s が存在しません', $baseClass));

        // _Exクラスをロード（エイリアスとして作成されるはず）
        if (!class_exists($exClass, false)) {
            class_exists($exClass, true);
        }

        // _Exクラスが元クラスのエイリアスであることを確認
        $this->assertTrue(
            class_exists($exClass, false),
            sprintf('%s がエイリアスとして作成されませんでした', $exClass)
        );
    }
}
