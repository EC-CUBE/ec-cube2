<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/Common_TestCase.php';
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * SC_Helper_Mailtemplateのテストの基底クラス.
 */
class SC_Helper_Mailtemplate_TestBase extends Common_TestCase
{
    /** @var SC_Helper_Mailtemplate */
    protected $objHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objHelper = new SC_Helper_Mailtemplate_Ex();

        // テーブルをクリア（トランザクション内なので自動的にロールバックされる）
        $this->objQuery->delete('dtb_mailtemplate');
    }

    /**
     * テスト用のメールテンプレートデータを作成
     *
     * @param array $override 上書きするフィールド
     *
     * @return array
     */
    protected function createMailtemplateData($override = [])
    {
        // template_idが指定されていない場合のみnextValを使用
        if (!isset($override['template_id'])) {
            $template_id = $this->objQuery->nextVal('dtb_mailtemplate_template_id');
            $override['template_id'] = $template_id;
        } else {
            $template_id = $override['template_id'];
        }

        $data = array_merge([
            'subject' => 'テスト件名_'.$template_id,
            'header' => 'ヘッダー',
            'footer' => 'フッター',
            'del_flg' => 0,
            'creator_id' => 1,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
        ], $override);

        $this->objQuery->insert('dtb_mailtemplate', $data);

        return $data;
    }
}
