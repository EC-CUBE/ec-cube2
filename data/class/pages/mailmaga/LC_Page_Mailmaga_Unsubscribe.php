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
 * メールマガジンワンクリック登録解除のページクラス.
 *
 * RFC 8058 準拠
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Mailmaga_Unsubscribe extends LC_Page_Ex
{
    /** @var string */
    public $tpl_title;
    /** @var string */
    public $tpl_message;
    /** @var bool */
    public $tpl_success;
    /** @var string */
    public $tpl_email;
    /** @var string */
    public $tpl_mainpage;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_title = 'メルマガ登録解除';
        $this->tpl_success = false;
        $this->tpl_mainpage = 'mailmaga/unsubscribe.tpl';
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
        // トークンの取得
        $token = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';

        // トークンの検証
        if (empty($token)) {
            $this->tpl_message = '無効なURLです。';

            return;
        }

        $arrToken = SC_Helper_Mailmaga_Ex::validateToken($token);

        if ($arrToken === false) {
            $this->tpl_message = 'このURLは無効か、既に使用済みか、有効期限が切れています。';

            return;
        }

        // RFC 8058 準拠: POST リクエストで List-Unsubscribe=One-Click の場合
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postBody = file_get_contents('php://input');

            // List-Unsubscribe=One-Click の確認
            if ($postBody === 'List-Unsubscribe=One-Click') {
                // ワンクリック登録解除処理
                $this->processUnsubscribe($arrToken, $token);

                // RFC 8058: 成功時は 200 OK を返す（コンテンツなし）
                http_response_code(200);
                exit;
            } else {
                // 通常のフォーム送信（確認ページからの送信）
                $mode = isset($_POST['mode']) ? $_POST['mode'] : '';

                if ($mode === 'confirm') {
                    $this->processUnsubscribe($arrToken, $token);
                }
            }
        }

        // GET リクエストの場合: 確認ページを表示
        $this->tpl_email = $arrToken['email'];
    }

    /**
     * 登録解除処理
     *
     * @param array  $arrToken トークン情報
     * @param string $token    トークン文字列
     *
     * @return void
     */
    protected function processUnsubscribe($arrToken, $token)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $objQuery->begin();

        try {
            // メルマガ配信フラグを「配信拒否」に更新
            $success = SC_Helper_Mailmaga_Ex::unsubscribeMailmaga($arrToken['customer_id']);

            if (!$success) {
                throw new Exception('メルマガ登録解除に失敗しました。');
            }

            // トークンを使用済みにマーク
            SC_Helper_Mailmaga_Ex::markTokenAsUsed($token);

            $objQuery->commit();

            $this->tpl_success = true;
            $this->tpl_message = 'メルマガの登録を解除しました。';
        } catch (Exception $e) {
            $objQuery->rollback();

            $this->tpl_success = false;
            $this->tpl_message = 'エラーが発生しました: '.$e->getMessage();

            GC_Utils_Ex::gfPrintLog($e->getMessage());
        }
    }
}
