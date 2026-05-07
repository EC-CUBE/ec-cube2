<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/Common_TestCase.php';

/**
 * SC_Helper_Blocのテストの基底クラス.
 */
class SC_Helper_Bloc_TestBase extends Common_TestCase
{
    /** @var SC_Helper_Bloc */
    protected $objHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objHelper = new SC_Helper_Bloc_Ex();

        // dtb_bloc, dtb_blocposition テーブルをクリア
        $this->objQuery->delete('dtb_bloc');
        $this->objQuery->delete('dtb_blocposition');
    }

    /**
     * テスト用のブロックデータを作成
     *
     * @param array $override 上書きする値の配列
     *
     * @return array 作成したブロックデータ
     */
    protected function createBlocData($override = [])
    {
        $bloc_id = $override['bloc_id'] ?? $this->objQuery->nextVal('dtb_bloc_bloc_id');

        $data = array_merge([
            'bloc_id' => $bloc_id,
            'device_type_id' => DEVICE_TYPE_PC,
            'bloc_name' => 'テストブロック_'.$bloc_id,
            'tpl_path' => 'test_bloc_'.$bloc_id.'.tpl',
            'filename' => 'test_bloc_'.$bloc_id,
            'deletable_flg' => 1,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
        ], $override);

        $this->objQuery->insert('dtb_bloc', $data);

        return $data;
    }

    /**
     * テンポラリのブロックファイルを作成
     *
     * @param string $filename
     * @param string $content
     */
    protected function createBlocFile($filename, $content = '<div>テストブロック</div>')
    {
        $bloc_dir = SC_Helper_PageLayout_Ex::getTemplatePath(DEVICE_TYPE_PC).BLOC_DIR;
        $file_path = $bloc_dir.$filename;
        $result = file_put_contents($file_path, $content);
        if ($result === false) {
            $this->fail("Failed to create bloc file: {$file_path}");
        }
    }

    /**
     * ブロックファイルを削除
     *
     * @param string $filename
     */
    protected function deleteBlocFile($filename)
    {
        $bloc_dir = SC_Helper_PageLayout_Ex::getTemplatePath(DEVICE_TYPE_PC).BLOC_DIR;
        $file_path = $bloc_dir.$filename;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    protected function tearDown(): void
    {
        // テスト用ファイルをクリーンアップ
        $bloc_dir = SC_Helper_PageLayout_Ex::getTemplatePath(DEVICE_TYPE_PC).BLOC_DIR;
        $pattern = $bloc_dir.'test_bloc_*.tpl';
        foreach (glob($pattern) as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        parent::tearDown();
    }
}
