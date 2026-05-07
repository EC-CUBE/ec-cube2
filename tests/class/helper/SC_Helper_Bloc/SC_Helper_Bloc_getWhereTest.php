<?php

require_once __DIR__.'/SC_Helper_Bloc_TestBase.php';

/**
 * SC_Helper_Bloc::getWhere()のテストクラス.
 */
class SC_Helper_Bloc_getWhereTest extends SC_Helper_Bloc_TestBase
{
    public function testGetWhere条件を指定してブロック一覧を取得()
    {
        $this->createBlocData(['bloc_id' => 1, 'deletable_flg' => 1]);
        $this->createBlocData(['bloc_id' => 2, 'deletable_flg' => 0]);
        $this->createBlocData(['bloc_id' => 3, 'deletable_flg' => 1]);

        $result = $this->objHelper->getWhere('deletable_flg = ?', [1]);

        $this->assertCount(2, $result, '削除可能なブロックのみ');
        $this->assertEquals(1, $result[0]['bloc_id']);
        $this->assertEquals(3, $result[1]['bloc_id']);
    }

    public function testGetWhere条件なしで全件取得()
    {
        $this->createBlocData(['bloc_id' => 1]);
        $this->createBlocData(['bloc_id' => 2]);
        $this->createBlocData(['bloc_id' => 3]);

        $result = $this->objHelper->getWhere('', []);

        $this->assertCount(3, $result);
    }

    public function testGetWhereデバイスタイプで自動的に絞り込まれる()
    {
        $this->createBlocData(['bloc_id' => 1, 'device_type_id' => DEVICE_TYPE_PC, 'deletable_flg' => 1]);
        $this->createBlocData(['bloc_id' => 2, 'device_type_id' => DEVICE_TYPE_MOBILE, 'deletable_flg' => 1]);

        $result = $this->objHelper->getWhere('deletable_flg = ?', [1]);

        $this->assertCount(1, $result, 'デバイスタイプPCのみ');
        $this->assertEquals(1, $result[0]['bloc_id']);
    }

    public function testGetWhere複数条件で絞り込み()
    {
        $this->createBlocData(['bloc_id' => 1, 'bloc_name' => 'ヘッダー', 'deletable_flg' => 1]);
        $this->createBlocData(['bloc_id' => 2, 'bloc_name' => 'フッター', 'deletable_flg' => 1]);
        $this->createBlocData(['bloc_id' => 3, 'bloc_name' => 'ヘッダー', 'deletable_flg' => 0]);

        $result = $this->objHelper->getWhere('bloc_name = ? AND deletable_flg = ?', ['ヘッダー', 1]);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['bloc_id']);
    }

    public function testGetWhere該当データがない場合は空配列()
    {
        $this->createBlocData(['bloc_id' => 1, 'deletable_flg' => 0]);

        $result = $this->objHelper->getWhere('deletable_flg = ?', [1]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
