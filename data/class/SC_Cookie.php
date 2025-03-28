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
 * クッキー用クラス
 */
class SC_Cookie
{
    public $expire;

    // コンストラクタ
    public function __construct($day = COOKIE_EXPIRE)
    {
        // 有効期限
        $this->expire = time() + ($day * 24 * 3600);
    }

    // クッキー書き込み

    /**
     * @param string $key
     * @param string $val
     * @param bool $secure
     * @param bool $httponly
     */
    public function setCookie($key, $val, $secure = false, $httponly = true)
    {
        setcookie($key, $val, $this->expire, ROOT_URLPATH, DOMAIN_NAME, $secure, $httponly);
    }

    /**
     * クッキー取得
     *
     * EC-CUBE をURLパスルート以外にインストールしている場合、上位ディレクトリの値も(劣後ではあるが)取得する点に留意。
     *
     * @param string $key
     */
    public function getCookie($key)
    {
        return $_COOKIE[$key] ?? null;
    }
}
