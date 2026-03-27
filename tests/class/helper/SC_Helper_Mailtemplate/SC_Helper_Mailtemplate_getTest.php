<?php

require_once __DIR__.'/SC_Helper_Mailtemplate_TestBase.php';

/**
 * SC_Helper_Mailtemplate::get()のテストクラス.
 */
class SC_Helper_Mailtemplate_getTest extends SC_Helper_Mailtemplate_TestBase
{
    public function testGetメールテンプレート情報を取得()
    {
        $this->createMailtemplateData([
            'template_id' => 1,
            'subject' => '注文確認メール',
            'header' => 'ご注文ありがとうございます。',
            'footer' => 'よろしくお願いいたします。',
        ]);

        $result = $this->objHelper->get(1);

        $this->assertIsArray($result);
        $this->assertEquals('注文確認メール', $result['subject']);
        $this->assertEquals('ご注文ありがとうございます。', $result['header']);
        $this->assertEquals('よろしくお願いいたします。', $result['footer']);
    }

    public function testGet存在しないテンプレートID()
    {
        $result = $this->objHelper->get(9999);

        $this->assertNull($result, '存在しないテンプレートIDの場合はnull');
    }

    public function testGet削除済みテンプレートはデフォルトで取得されない()
    {
        $this->createMailtemplateData([
            'template_id' => 1,
            'subject' => '削除済み',
            'del_flg' => 1,
        ]);

        $result = $this->objHelper->get(1);

        $this->assertNull($result, '削除済みテンプレートは取得されない');
    }

    public function testGet削除済みテンプレートをHasDeletedで取得()
    {
        $this->createMailtemplateData([
            'template_id' => 1,
            'subject' => '削除済み',
            'del_flg' => 1,
        ]);

        $result = $this->objHelper->get(1, true);

        $this->assertIsArray($result);
        $this->assertEquals('削除済み', $result['subject']);
        $this->assertEquals(1, $result['del_flg']);
    }
}
