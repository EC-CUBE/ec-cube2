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
 * 受注管理メール確認 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_Order_MailView extends LC_Page_Admin_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'order/mail_view.tpl';
        $this->tpl_subtitle = '受注管理メール確認';
        $this->httpCacheControl('nocache');
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
        $send_id = $_GET['send_id'] ?? null;
        if (SC_Utils_Ex::sfIsInt($send_id)) {
            $mailHistory = $this->getMailHistory($send_id);
            $this->tpl_subject = $mailHistory[0]['subject'];
            $this->tpl_body = $mailHistory[0]['mail_body'];
        }
        $this->setTemplate($this->tpl_mainpage);
    }

    /**
     * メールの履歴を取り出す。
     *
     * @param int $send_id
     */
    public function getMailHistory($send_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $col = 'subject, mail_body';
        $where = 'send_id = ?';
        $mailHistory = $objQuery->select($col, 'dtb_mail_history', $where, [$send_id]);

        return $mailHistory;
    }
}
