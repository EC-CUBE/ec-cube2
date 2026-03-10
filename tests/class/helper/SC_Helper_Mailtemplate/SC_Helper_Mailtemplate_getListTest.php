<?php

require_once __DIR__.'/SC_Helper_Mailtemplate_TestBase.php';

/**
 * SC_Helper_Mailtemplate::getList()のテストクラス.
 */
class SC_Helper_Mailtemplate_getListTest extends SC_Helper_Mailtemplate_TestBase
{
    public function testGetListメールテンプレート一覧を取得()
    {
        $this->createMailtemplateData(['template_id' => 1, 'subject' => '注文確認']);
        $this->createMailtemplateData(['template_id' => 2, 'subject' => '発送完了']);
        $this->createMailtemplateData(['template_id' => 3, 'subject' => '会員登録']);

        $result = $this->objHelper->getList();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    public function testGetList削除済みテンプレートは含まれない()
    {
        $this->createMailtemplateData(['template_id' => 1, 'del_flg' => 0]);
        $this->createMailtemplateData(['template_id' => 2, 'del_flg' => 1]);
        $this->createMailtemplateData(['template_id' => 3, 'del_flg' => 0]);

        $result = $this->objHelper->getList();

        $this->assertCount(2, $result, '削除済みテンプレートは除外される');
        $this->assertEquals(1, $result[0]['template_id']);
        $this->assertEquals(3, $result[1]['template_id']);
    }

    public function testGetList削除済みテンプレートをHasDeletedで取得()
    {
        $this->createMailtemplateData(['template_id' => 1, 'del_flg' => 0]);
        $this->createMailtemplateData(['template_id' => 2, 'del_flg' => 1]);
        $this->createMailtemplateData(['template_id' => 3, 'del_flg' => 0]);

        $result = $this->objHelper->getList(true);

        $this->assertCount(3, $result, 'has_deleted=trueで削除済みも含む');
    }

    public function testGetListテンプレートが存在しない場合()
    {
        $result = $this->objHelper->getList();

        $this->assertIsArray($result);
        $this->assertEmpty($result, 'テンプレートが存在しない場合は空配列');
    }

    public function testGetList全カラムが取得される()
    {
        $this->createMailtemplateData([
            'template_id' => 1,
            'subject' => 'テスト',
        ]);

        $result = $this->objHelper->getList();

        $this->assertArrayHasKey('template_id', $result[0]);
        $this->assertArrayHasKey('subject', $result[0]);
        $this->assertArrayHasKey('header', $result[0]);
        $this->assertArrayHasKey('footer', $result[0]);
    }
}
