<?php

require_once __DIR__.'/SC_Helper_Mailtemplate_TestBase.php';

/**
 * SC_Helper_Mailtemplate::save()のテストクラス.
 */
class SC_Helper_Mailtemplate_saveTest extends SC_Helper_Mailtemplate_TestBase
{
    public function testSave新規登録()
    {
        $sqlval = [
            'template_id' => 100,  // nextVal()の代わりに明示的にIDを指定
            'subject' => '新規テンプレート',
            'header' => 'ヘッダー',
            'footer' => 'フッター',
            'creator_id' => 1,
        ];

        $template_id = $this->objHelper->save($sqlval);

        $this->assertEquals(100, $template_id, '登録成功時はテンプレートIDが返る');

        $result = $this->objQuery->getRow('*', 'dtb_mailtemplate', 'template_id = ?', [$template_id]);
        $this->assertEquals('新規テンプレート', $result['subject']);
        $this->assertEquals('ヘッダー', $result['header']);
        $this->assertEquals('フッター', $result['footer']);
    }

    public function testSave新規登録でTemplateIdを指定()
    {
        $sqlval = [
            'template_id' => 101,
            'subject' => '特定IDのテンプレート',
            'creator_id' => 1,
        ];

        $template_id = $this->objHelper->save($sqlval);

        $this->assertEquals(101, $template_id, '指定したtemplate_idが返る');

        $result = $this->objQuery->getRow('*', 'dtb_mailtemplate', 'template_id = ?', [101]);
        $this->assertEquals('特定IDのテンプレート', $result['subject']);
    }

    public function testSave更新()
    {
        $this->createMailtemplateData([
            'template_id' => 1,
            'subject' => '既存テンプレート',
        ]);

        $sqlval = [
            'template_id' => 1,
            'subject' => '更新後テンプレート',
            'header' => '新しいヘッダー',
        ];

        $result = $this->objHelper->save($sqlval);

        $this->assertEquals(1, $result, '更新成功時はテンプレートIDが返る');

        $template = $this->objQuery->getRow('*', 'dtb_mailtemplate', 'template_id = ?', [1]);
        $this->assertEquals('更新後テンプレート', $template['subject']);
        $this->assertEquals('新しいヘッダー', $template['header']);
    }

    public function testSave更新時にcreatorIdとcreateDateは変更されない()
    {
        $this->createMailtemplateData([
            'template_id' => 1,
            'creator_id' => 1,
            'create_date' => '2020-01-01 00:00:00',
        ]);

        $sqlval = [
            'template_id' => 1,
            'subject' => '更新後',
            'creator_id' => 999,  // この値は無視される
            'create_date' => '2025-01-01 00:00:00',  // この値も無視される
        ];

        $this->objHelper->save($sqlval);

        $result = $this->objQuery->getRow('*', 'dtb_mailtemplate', 'template_id = ?', [1]);
        $this->assertEquals(1, $result['creator_id'], 'creator_idは変更されない');
        $this->assertEquals('2020-01-01 00:00:00', $result['create_date'], 'create_dateは変更されない');
    }

    public function testSave新規登録時にcreatorIdが設定される()
    {
        $sqlval = [
            'template_id' => 102,  // nextVal()の代わりに明示的にIDを指定
            'subject' => '新規',
            'creator_id' => 5,
        ];

        $template_id = $this->objHelper->save($sqlval);

        $result = $this->objQuery->getRow('*', 'dtb_mailtemplate', 'template_id = ?', [$template_id]);
        $this->assertEquals(5, $result['creator_id'], 'creator_idが設定される');
    }
}
