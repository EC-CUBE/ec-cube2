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

/**
 * メルマガプレビュー のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_Mail_Preview extends LC_Page_Admin_Ex
{
    /** @var string */
    public $mail;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_subtitle = 'プレビュー';
        $this->tpl_mainpage = 'mail/preview.tpl';
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
        $objMailHelper = new SC_Helper_Mail_Ex();

        switch ($this->getMode()) {
            case 'template':
                if (SC_Utils_Ex::sfIsInt($_GET['template_id'])) {
                    $arrMail = $objMailHelper->sfGetMailmagaTemplate($_GET['template_id']);
                    $this->mail = $arrMail[0];
                }
                break;
            case 'history':
                if (SC_Utils_Ex::sfIsInt($_GET['send_id'])) {
                    $arrMail = $objMailHelper->sfGetSendHistory($_GET['send_id']);
                    $this->mail = $arrMail[0];
                }
                break;
            case 'presend':
                $this->mail['body'] = $_POST['body'];
                // no break
            default:
                break;
        }

        $this->setTemplate($this->tpl_mainpage);
    }
}
