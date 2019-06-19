<?php
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

class SC_CheckError_HTML_TAG_CHECKTest extends SC_CheckError_AbstractTestCase
{

    /** @var array */
    protected $arrAllowedTag;

    protected function setUp()
    {
        parent::setUp();
        $masterData = new SC_DB_MasterData_Ex();
        $this->arrAllowedTag = $masterData->getMasterData('mtb_allowed_tag');
        $this->target_func = 'HTML_TAG_CHECK';
    }

    public function testHTML_TAG_CHECK_許可されていないhtmlタグが含まれる場合_エラー()
    {
        $not_allowed_tag = 'script';

        // 許可するタグリストに含まれていれば削除しておく
        if ($key = array_search($not_allowed_tag, $this->arrAllowedTag)) {
            unset($this->arrAllowedTag[$key]);
        }

        $this->arrForm = [
            self::FORM_NAME => "<{$not_allowed_tag}>not allowed</{$not_allowed_tag}>",
        ];
        $this->scenario();

        $this->expected = sprintf(
            '※ %sに許可されていないタグ [%s], [%s] が含まれています。<br />',
            $this->target_func, $not_allowed_tag, $not_allowed_tag);
        $this->verify();
    }

    public function testHTML_TAG_CHECK_許可されているhtmlタグが含まれる場合_エラーではない()
    {
        $allowed_tag = 'p';

        // 許可するタグリストに含まれていなければ追加しておく
        if (!in_array($allowed_tag, $this->arrAllowedTag)) {
            $this->arrAllowedTag[] = $allowed_tag;
        }

        $this->arrForm = [
            self::FORM_NAME => "<{$allowed_tag}>allowed</{$allowed_tag}>",
        ];
        $this->scenario();

        $this->expected = '';
        $this->verify();
    }

    public function testHTML_TAG_CHECK_htmlタグが含まれない場合_エラーではない()
    {

        $this->arrForm = [self::FORM_NAME => 'htmlタグを含まないテスト文章。'];

        $this->expected = '';
        $this->verify();
    }

    protected function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME, $this->arrAllowedTag],
                              [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME, $this->arrAllowedTag],
                              [$this->target_func]);

    }
}
