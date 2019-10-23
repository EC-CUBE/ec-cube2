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

require_once CLASS_REALDIR . 'pages/error/LC_Page_Error.php';

/**
 * システムエラー表示のページクラス
 * システムエラーや例外が発生した場合の表示ページ
 *
 * @package Page
 * @author EC-CUBE CO.,LTD.
 * @version $Id$
 */
class LC_Page_Error_SystemError extends LC_Page_Error
{
    /** PEAR_Error */
    public $pearResult;

    /** PEAR_Error がセットされていない場合用のバックトレーススタック */
    public $backtrace;

    /** デバッグ用のメッセージ配列 */
    public $arrDebugMsg = array();

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_title = 'システムエラー';
    }

    /**
     * Page のプロセス。
     *
     * @return void
     */
    public function process()
    {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のプロセス。
     *
     * @return void
     */
    public function action()
    {
        SC_Response_Ex::sendHttpStatus(500);

        $this->tpl_error = 'システムエラーが発生しました。<br />大変お手数ですが、サイト管理者までご連絡ください。';

        if (DEBUG_MODE) {
            echo '<div class="debug">';
            echo '<div>▼▼▼ デバッグ情報ここから ▼▼▼</div>';
            echo '<pre>';
            echo htmlspecialchars($this->sfGetErrMsg(), ENT_QUOTES, CHAR_CODE);
            echo '</pre>';
            echo '<div>▲▲▲ デバッグ情報ここまで ▲▲▲</div>';
            echo '</div>';
        }

    }

    /**
     * Page のレスポンス送信.
     *
     * @return void
     */
    public function sendResponse()
    {
        $this->adminPage = GC_Utils_Ex::isAdminFunction();

        if ($this->adminPage) {
            $this->tpl_mainpage = 'login_error.tpl';
            $this->template = LOGIN_FRAME;
            $this->objDisplay->prepare($this, true);
        } else {
            $this->objDisplay->prepare($this);
        }

        $this->objDisplay->response->write();
    }

    /**
     * トランザクショントークンに関して処理しないようにオーバーライド
     *
     * @param  boolean $is_admin 管理画面でエラー表示をする場合 true
     */
    public function doValidToken($is_admin = false)
    {
        // nothing.
    }

    /**
     * トランザクショントークンに関して処理しないようにオーバーライド
     */
    public function setTokenTo()
    {
    }

    /**
     * エラーメッセージを生成する
     *
     * @return string
     */
    public function sfGetErrMsg()
    {
        $errmsg = '';
        $errmsg .= $this->lfGetErrMsgHead();
        $errmsg .= "\n";

        // デバッグ用のメッセージが指定されている場合
        if (!empty($this->arrDebugMsg)) {
            $errmsg .= implode("\n\n", $this->arrDebugMsg) . "\n";
        }

        // PEAR エラーを伴う場合
        if (!is_null($this->pearResult)) {
            $errmsg .= $this->pearResult->message . "\n\n";
            $errmsg .= $this->pearResult->userinfo . "\n\n";
            $errmsg .= GC_Utils_Ex::toStringBacktrace($this->pearResult->backtrace);
        // (上に該当せず)バックトレーススタックが指定されている場合
        } else if (is_array($this->backtrace)) {
            $errmsg .= GC_Utils_Ex::toStringBacktrace($this->backtrace);
        } else {
            $arrBacktrace = GC_Utils_Ex::getDebugBacktrace();
            $errmsg .= GC_Utils_Ex::toStringBacktrace($arrBacktrace);
        }

        return $errmsg;
    }

    /**
     * エラーメッセージの冒頭部を生成する
     *
     * @return string
     */
    public function lfGetErrMsgHead()
    {
        $errmsg = '';
        $errmsg .= GC_Utils_Ex::getUrl() . "\n";
        $errmsg .= "\n";
        $errmsg .= 'SERVER_ADDR: ' . $_SERVER['SERVER_ADDR'] . "\n";
        $errmsg .= 'REMOTE_ADDR: ' . $_SERVER['REMOTE_ADDR'] . "\n";
        $errmsg .= 'USER_AGENT: ' . $_SERVER['HTTP_USER_AGENT'] . "\n";

        return $errmsg;
    }

    /**
     * デバッグ用のメッセージを追加
     *
     * @param string $debugMsg
     * @return void
     */
    public function addDebugMsg($debugMsg)
    {
        $this->arrDebugMsg[] = rtrim($debugMsg, "\n");
    }
}
