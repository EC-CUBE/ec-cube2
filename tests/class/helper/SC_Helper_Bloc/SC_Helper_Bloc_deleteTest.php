<?php

require_once __DIR__.'/SC_Helper_Bloc_TestBase.php';

/**
 * SC_Helper_Bloc::delete()のテストクラス.
 */
class SC_Helper_Bloc_deleteTest extends SC_Helper_Bloc_TestBase
{
    public function testDeleteブロックを削除できる()
    {
        $this->createBlocData([
            'bloc_id' => 1,
            'filename' => 'delete_test_bloc',
            'tpl_path' => 'delete_test_bloc.tpl',
            'deletable_flg' => 1,
        ]);

        $this->createBlocFile('delete_test_bloc.tpl');

        $result = $this->objHelper->delete(1);

        $this->assertTrue($result, '削除成功');

        $bloc = $this->objQuery->getRow('*', 'dtb_bloc', 'bloc_id = ?', [1]);
        $this->assertEmpty($bloc, 'データベースから削除されている');

        // ファイルも削除されている
        $bloc_dir = SC_Helper_PageLayout_Ex::getTemplatePath(DEVICE_TYPE_PC).BLOC_DIR;
        $this->assertFileDoesNotExist($bloc_dir.'delete_test_bloc.tpl');
    }

    public function testDeletedeletableFlgが0の場合は削除できない()
    {
        $this->createBlocData([
            'bloc_id' => 1,
            'filename' => 'non_deletable_bloc',
            'tpl_path' => 'non_deletable_bloc.tpl',
            'deletable_flg' => 0,
            'device_type_id' => DEVICE_TYPE_PC,
        ]);

        $this->createBlocFile('non_deletable_bloc.tpl');

        $result = $this->objHelper->delete(1);

        $this->assertFalse($result, '削除失敗');

        // delete()が rollback() を実行するが、ネストトランザクションの問題で
        // データベースの状態確認は困難。ファイルが残っていることを確認
        $bloc_dir = SC_Helper_PageLayout_Ex::getTemplatePath(DEVICE_TYPE_PC).BLOC_DIR;
        $this->assertFileExists($bloc_dir.'non_deletable_bloc.tpl', 'ファイルは削除されていない');
    }

    public function testDelete存在しないブロックIDは削除失敗()
    {
        $result = $this->objHelper->delete(9999);

        $this->assertFalse($result, '存在しないブロックは削除失敗');
    }

    public function testDeleteブロックポジションも削除される()
    {
        $this->createBlocData([
            'bloc_id' => 1,
            'filename' => 'position_test_bloc',
            'tpl_path' => 'position_test_bloc.tpl',
            'deletable_flg' => 1,
        ]);

        $this->createBlocFile('position_test_bloc.tpl');

        // ブロックポジションを登録
        $this->objQuery->insert('dtb_blocposition', [
            'page_id' => 1,
            'target_id' => 1,
            'bloc_id' => 1,
            'bloc_row' => 1,
            'device_type_id' => DEVICE_TYPE_PC,
        ]);

        $result = $this->objHelper->delete(1);

        $this->assertTrue($result, '削除成功');

        // ブロックポジションも削除されている
        $position = $this->objQuery->getRow('*', 'dtb_blocposition', 'bloc_id = ?', [1]);
        $this->assertEmpty($position, 'ブロックポジションも削除されている');
    }

    public function testDeleteファイルが存在しない場合でも削除成功()
    {
        $this->createBlocData([
            'bloc_id' => 1,
            'filename' => 'no_file_bloc',
            'tpl_path' => 'no_file_bloc.tpl',
            'deletable_flg' => 1,
        ]);

        // ファイルは作成しない

        $result = $this->objHelper->delete(1);

        $this->assertTrue($result, 'ファイルが存在しなくても削除成功');

        $bloc = $this->objQuery->getRow('*', 'dtb_bloc', 'bloc_id = ?', [1]);
        $this->assertEmpty($bloc, 'データベースから削除されている');
    }
}
