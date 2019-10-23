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

require_once CLASS_EX_REALDIR . 'page_extends/LC_Page_Ex.php';

/**
 * ブロック の基底クラス.
 *
 * @package Page
 * @author EC-CUBE CO.,LTD.
 * @version $Id$
 */
class LC_Page_FrontParts_Bloc extends LC_Page_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        // 開始時刻を設定する。
        $this->timeStart = microtime(true);

        $this->tpl_authority = $_SESSION['authority'];

        // ディスプレイクラス生成
        $this->objDisplay = new SC_Display_Ex();

        $this->setTplMainpage($this->blocItems['tpl_path']);

        // トランザクショントークンの検証と生成
        $this->setTokenTo();

        // ローカルフックポイントを実行.
        $objPlugin = SC_Helper_Plugin_Ex::getSingletonInstance();
        $this->doLocalHookpointBefore($objPlugin);
    }

    /**
     * ブロックファイルに応じて tpl_mainpage を設定する
     *
     * @param  string $bloc_file ブロックファイル名
     * @return void
     */
    public function setTplMainpage($bloc_file)
    {
        if (SC_Utils_Ex::isAbsoluteRealPath($bloc_file)) {
            $this->tpl_mainpage = $bloc_file;
        } else {
            $this->tpl_mainpage = SC_Helper_PageLayout_Ex::getTemplatePath($this->objDisplay->detectDevice()) . BLOC_DIR . $bloc_file;
        }

        $this->setTemplate($this->tpl_mainpage);
    }

    /**
     * デストラクタ
     *
     * @return void
     */
    public function __destruct()
    {
        // 親がリクエスト単位を意図した処理なので、断絶する。
    }
}
