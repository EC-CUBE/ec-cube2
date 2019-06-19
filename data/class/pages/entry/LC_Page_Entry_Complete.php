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
 * 会員登録(完了) のページクラス.
 *
 * @package Page
 * @author EC-CUBE CO.,LTD.
 * @version $Id$
 */
class LC_Page_Entry_Complete extends LC_Page_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->httpCacheControl('nocache');
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        parent::process();
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
        $objCartSess = new SC_CartSession_Ex();

        // カートが空かどうかを確認する。
        $arrCartKeys = $objCartSess->getKeys();
        $this->tpl_cart_empty = true;
        foreach ($arrCartKeys as $cart_key) {
            if (count($objCartSess->getCartList($cart_key)) > 0) {
                $this->tpl_cart_empty = false;
                break;
            }
        }

        // 仮会員登録完了
        if (CUSTOMER_CONFIRM_MAIL == true) {
            // 登録された会員ID
            $this->tpl_customer_id = $_SESSION['registered_customer_id'];
            unset($_SESSION['registered_customer_id']);

            // メインテンプレートを設定
            $this->tpl_mainpage = 'entry/complete.tpl';
        }
        // 本会員登録完了
        else {
            SC_Response_Ex::sendRedirectFromUrlPath('regist/complete.php');
        }
    }
}
