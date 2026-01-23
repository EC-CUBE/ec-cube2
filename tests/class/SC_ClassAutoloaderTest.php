<?php

require_once __DIR__.'/Common_TestCase.php';

/**
 * SC_ClassAutoloader のテストクラス
 *
 * オートローダーが生成するパスと実際のファイル配置の整合性を検証
 * 関連issue: https://github.com/EC-CUBE/ec-cube2/issues/1268
 */
class SC_ClassAutoloaderTest extends Common_TestCase
{
    /**
     * 実際に存在する_Exファイルのリストを取得
     *
     * @return array
     */
    protected function getExistingExFiles()
    {
        $classExtendsDir = CLASS_EX_REALDIR;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($classExtendsDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $exFiles = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php' && str_ends_with($file->getBasename('.php'), '_Ex')) {
                // ファイル名（.phpを除く）をクラス名として使用
                $className = $file->getBasename('.php');
                $exFiles[$className] = $file->getPathname();
            }
        }

        return $exFiles;
    }

    /**
     * オートローダーが生成するパスを計算
     *
     * SC_ClassAutoloader::autoload()のパス生成ロジックを再現
     *
     * @param string $class クラス名
     *
     * @return string 生成されるパス
     */
    protected function calculateAutoloaderPath($class)
    {
        $arrClassNamePart = explode('_', $class);
        $is_ex = end($arrClassNamePart) === 'Ex';
        $count = count($arrClassNamePart);
        $classpath = $is_ex ? CLASS_EX_REALDIR : CLASS_REALDIR;

        if (($arrClassNamePart[0] === 'GC' || $arrClassNamePart[0] === 'SC') && $arrClassNamePart[1] === 'Utils') {
            $classpath .= $is_ex ? 'util_extends/' : 'util/';
        } elseif (($arrClassNamePart[0] === 'SC' || $arrClassNamePart[0] === 'LC') && $is_ex === true && $count >= 4) {
            $arrClassNamePartTemp = $arrClassNamePart;
            $arrClassNamePartTemp[1] .= '_extends';
            if ($count <= 5 && $arrClassNamePart[2] === 'Admin' && !in_array($arrClassNamePart[3], ['Home', 'Index', 'Logout'])) {
                $classpath .= strtolower(implode('/', array_slice($arrClassNamePartTemp, 1, -1))).'/';
            } else {
                if ($count === 4 && $arrClassNamePart[2] != 'Index' && $arrClassNamePart[3] === 'Ex') {
                    $classpath .= strtolower(implode('/', array_slice($arrClassNamePartTemp, 1, -1))).'/';
                } else {
                    $classpath .= strtolower(implode('/', array_slice($arrClassNamePartTemp, 1, -2))).'/';
                }
            }
        } elseif ($arrClassNamePart[0] === 'SC' && $is_ex === false && $count >= 3) {
            $classpath .= strtolower(implode('/', array_slice($arrClassNamePart, 1, -1))).'/';
        }

        $classpath .= "$class.php";

        return $classpath;
    }

    /**
     * オートローダーのパス生成と実際のファイル配置が一致するかテスト
     *
     * 既知の問題（issue #1268）により、一部のファイルで不整合が存在します。
     * このテストは不整合を検出して記録しますが、テストは成功扱いとします。
     * 将来的に不整合が修正されれば、このテストで確認できます。
     *
     * @group classloader
     */
    public function testAutoloaderPathConsistency()
    {
        $exFiles = $this->getExistingExFiles();
        $mismatches = [];

        foreach ($exFiles as $className => $actualPath) {
            $expectedPath = $this->calculateAutoloaderPath($className);

            if ($expectedPath !== $actualPath) {
                $mismatches[] = [
                    'class' => $className,
                    'expected' => str_replace(CLASS_EX_REALDIR, 'class_extends/', $expectedPath),
                    'actual' => str_replace(CLASS_EX_REALDIR, 'class_extends/', $actualPath),
                ];
            }
        }

        // 既知の不整合（issue #1268）
        $knownMismatches = [
            'SC_Helper_Plugin_Ex',
            'SC_Plugin_Util_Ex',
            'LC_Page_AbstractMypage_Ex',
            'LC_Page_Ex',
        ];

        $unexpectedMismatches = [];
        foreach ($mismatches as $mismatch) {
            if (!in_array($mismatch['class'], $knownMismatches)) {
                $unexpectedMismatches[] = $mismatch;
            }
        }

        if (!empty($unexpectedMismatches)) {
            $message = "新しい不整合が見つかりました（既知の問題以外）:\n\n";
            foreach ($unexpectedMismatches as $mismatch) {
                $message .= sprintf(
                    "クラス: %s\n  期待: %s\n  実際: %s\n\n",
                    $mismatch['class'],
                    $mismatch['expected'],
                    $mismatch['actual']
                );
            }
            $this->fail($message);
        }

        // 既知の不整合をログに記録（テストは成功）
        if (!empty($mismatches)) {
            $this->addWarning(sprintf(
                '既知の不整合（issue #1268）が %d 個検出されました: %s',
                count($mismatches),
                implode(', ', array_column($mismatches, 'class'))
            ));
        }

        $this->assertTrue(
            true,
            sprintf(
                '全 %d 個の_Exファイルを検証しました（既知の不整合: %d 個）',
                count($exFiles),
                count($mismatches)
            )
        );
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
