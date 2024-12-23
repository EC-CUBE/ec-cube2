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
 * セッション維持の方法にCookieを使用するクラス.
 *
 * このクラスを直接インスタンス化しないこと.
 * 必ず SC_SessionFactory クラスを経由してインスタンス化する.
 * また, SC_SessionFactory クラスの関数を必ずオーバーライドしている必要がある.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class SC_SessionFactory_UseCookie extends SC_SessionFactory_Ex
{
    /**
     * セッションパラメーターの指定
     * ・ブラウザを閉じるまで有効
     * ・EC-CUBE ルート配下で有効
     * ・同じドメイン間で共有
     * FIXME セッションキーのキーが PHP デフォルトのため、上位ディレクトリーで定義があると、その値で動作すると考えられる。
     **/
    public function initSession()
    {
        // header が送信されている場合は何もしない
        if (headers_sent()) {
            return;
        }

        // (session.auto_start などで)セッションが開始されていた場合に備えて閉じる。(FIXME: 保存する必要はない。破棄で良い。)
        session_write_close();
        $params = [
            'lifetime' => 0,
            'path' => ROOT_URLPATH,
            'domain' => DOMAIN_NAME,
            'secure' => $this->getSecureOption(),
            'httponly' => true,
            'samesite' => '',
        ];
        if ($this->getSecureOption()) {
            $params['samesite'] = 'None'; // require secure option
        }
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params($params);
        } else {
            $samesite = '';
            if (!empty($params['samesite'])) {
                $samesite = '; SameSite='.$params['samesite'];
            }
            session_set_cookie_params($params['lifetime'], $params['path'].$samesite, $params['domain'], $params['secure'], $params['httponly']);
        }
        // セッション開始
        // FIXME EC-CUBE をネストしてインストールした場合を考慮して、一意とすべき
        session_name('ECSESSID');
        session_start();
        if (session_id() !== '') {
            // SameSite=None を未サポートの UA 向けに 互換用 cookie を発行する. secure option 必須
            setcookie('legacy-'.session_name(), session_id(), $params['lifetime'], $params['path'], $params['domain'], true, true);
        }
    }

    /**
     * Cookieを使用するかどうか
     *
     * @return bool 常に true を返す
     */
    public function useCookie()
    {
        return true;
    }

    /**
     * secure オプションの値を返す.
     *
     * この値をもとに secure オプションを設定する.
     *
     * @return bool HTTP_URL 及び HTTPS_URL が https の場合は true
     */
    protected function getSecureOption()
    {
        return str_contains(HTTP_URL, 'https') && str_contains(HTTPS_URL, 'https');
    }
}
/*
 * Local variables:
 * coding: utf-8
 * End:
 */
