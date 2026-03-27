<?php

require_once __DIR__.'/SC_Helper_Delivery_TestBase.php';

/**
 * SC_Helper_Delivery::checkExist()のテストクラス.
 */
class SC_Helper_Delivery_checkExistTest extends SC_Helper_Delivery_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の配送方法を作成
        $this->createDelivData(['deliv_id' => 1, 'service_name' => '既存配送A', 'del_flg' => 0]);
        $this->createDelivData(['deliv_id' => 2, 'service_name' => '既存配送B', 'del_flg' => 0]);
        $this->createDelivData(['deliv_id' => 3, 'service_name' => '削除済み配送', 'del_flg' => 1]);
    }

    public function testCheckExist新規登録で同名が存在する場合()
    {
        $arrDeliv = ['service_name' => '既存配送A'];

        $result = $this->objHelper->checkExist($arrDeliv);

        $this->assertTrue($result, '同名の配送方法が存在する場合はtrue');
    }

    public function testCheckExist新規登録で同名が存在しない場合()
    {
        $arrDeliv = ['service_name' => '新規配送C'];

        $result = $this->objHelper->checkExist($arrDeliv);

        $this->assertFalse($result, '同名の配送方法が存在しない場合はfalse');
    }

    public function testCheckExist更新で自分以外に同名が存在する場合()
    {
        $arrDeliv = ['deliv_id' => 2, 'service_name' => '既存配送A'];

        $result = $this->objHelper->checkExist($arrDeliv);

        $this->assertTrue($result, '自分以外に同名の配送方法が存在する場合はtrue');
    }

    public function testCheckExist更新で自分以外に同名が存在しない場合()
    {
        $arrDeliv = ['deliv_id' => 1, 'service_name' => '既存配送A'];

        $result = $this->objHelper->checkExist($arrDeliv);

        $this->assertFalse($result, '自分と同名の場合はfalse（更新可能）');
    }

    public function testCheckExist更新で新しい名前を使用する場合()
    {
        $arrDeliv = ['deliv_id' => 1, 'service_name' => '新規配送名'];

        $result = $this->objHelper->checkExist($arrDeliv);

        $this->assertFalse($result, '新しい名前の場合はfalse（更新可能）');
    }

    public function testCheckExist削除済み配送方法は無視される()
    {
        $arrDeliv = ['service_name' => '削除済み配送'];

        $result = $this->objHelper->checkExist($arrDeliv);

        $this->assertFalse($result, '削除済み配送方法は存在しないものとして扱われる');
    }

    public function testCheckExistサービス名がない場合()
    {
        $arrDeliv = [];

        $result = $this->objHelper->checkExist($arrDeliv);

        $this->assertFalse($result, 'service_nameがない場合はfalse');
    }
}
