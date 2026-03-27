<?php

require_once __DIR__.'/SC_Helper_Bloc_TestBase.php';

/**
 * SC_Helper_Bloc::getList()のテストクラス.
 */
class SC_Helper_Bloc_getListTest extends SC_Helper_Bloc_TestBase
{
    public function testGetListブロック一覧を取得()
    {
        $this->createBlocData(['bloc_id' => 1, 'bloc_name' => 'ヘッダー']);
        $this->createBlocData(['bloc_id' => 2, 'bloc_name' => 'フッター']);
        $this->createBlocData(['bloc_id' => 3, 'bloc_name' => 'サイドバー']);

        $result = $this->objHelper->getList();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    public function testGetListデバイスタイプで絞り込まれる()
    {
        $this->createBlocData(['bloc_id' => 1, 'device_type_id' => DEVICE_TYPE_PC]);
        $this->createBlocData(['bloc_id' => 2, 'device_type_id' => DEVICE_TYPE_MOBILE]);
        $this->createBlocData(['bloc_id' => 3, 'device_type_id' => DEVICE_TYPE_PC]);

        $result = $this->objHelper->getList();

        $this->assertCount(2, $result, 'デバイスタイプPCのブロックのみ');
        $this->assertEquals(1, $result[0]['bloc_id']);
        $this->assertEquals(3, $result[1]['bloc_id']);
    }

    public function testGetListブロックが存在しない場合は空配列()
    {
        $result = $this->objHelper->getList();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetList全カラムが取得される()
    {
        $this->createBlocData(['bloc_id' => 1, 'bloc_name' => 'テスト']);

        $result = $this->objHelper->getList();

        $this->assertArrayHasKey('bloc_id', $result[0]);
        $this->assertArrayHasKey('bloc_name', $result[0]);
        $this->assertArrayHasKey('tpl_path', $result[0]);
        $this->assertArrayHasKey('filename', $result[0]);
        $this->assertArrayHasKey('device_type_id', $result[0]);
        $this->assertArrayHasKey('deletable_flg', $result[0]);
    }
}
