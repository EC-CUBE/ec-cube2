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

require_once CLASS_EX_REALDIR . 'page_extends/admin/LC_Page_Admin_Ex.php';

/**
 * ファイル表示 のページクラス.
 *
 * @package Page
 * @author EC-CUBE CO.,LTD.
 * @version $Id$
 */
class LC_Page_Admin_Contents_FileView extends LC_Page_Admin_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    public function action()
    {
        switch ($this->getMode()) {
            default:
                // フォーム操作クラス
                $objFormParam = new SC_FormParam_Ex();
                // パラメーター情報の初期化
                $this->lfInitParam($objFormParam);
                $objFormParam->setParam($_GET);
                $objFormParam->convParam();

                // 表示するファイルにエラーチェックを行う
                if ($this->checkErrorDispFile($objFormParam)) {
                    $this->execFileView($objFormParam);
                } else {
                    SC_Utils_Ex::sfDispError('');
                }

                SC_Response_Ex::actionExit();
                break;
        }
    }

    /**
     * 初期化を行う.
     *
     * @param  SC_FormParam $objFormParam SC_FormParamインスタンス
     * @return void
     */
    public function lfInitParam(&$objFormParam)
    {
        $objFormParam->addParam('ファイル名', 'file', MTEXT_LEN, 'a', array('EXIST_CHECK'));
    }

    /**
     * 表示するファイルにエラーチェックを行う
     *
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     * @return boolean       $file_check_flg エラーチェックの結果
     */
    public function checkErrorDispFile($objFormParam)
    {
        $file_check_flg = false;
        // FIXME パスのチェック関数が必要
        $file = $objFormParam->getValue('file');
        $path_exists = SC_Utils::checkFileExistsWithInBasePath($file, USER_REALDIR);
        if ($path_exists) {
            $file_check_flg = true;
        }
        return $file_check_flg;
    }

    /**
     * ファイル内容を表示する
     *
     * @return void
     */
    public function execFileView($objFormParam)
    {
        $file = $objFormParam->getValue('file');

        // ソースとして表示するファイルを定義(直接実行しないファイル)
        $arrViewFile = array(
            'html',
            'htm',
            'tpl',
            'php',
            'css',
            'js',
        );

        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if (in_array($extension, $arrViewFile)) {
            $objFileManager = new SC_Helper_FileManager_Ex();
            // ファイルを読み込んで表示
            header("Content-type: text/plain\n\n");
            echo $objFileManager->sfReadFile(USER_REALDIR . $file);
        } else {
            SC_Response_Ex::sendRedirect(USER_URL . $file);
        }
    }
}
