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
 * メールマガジン関連のヘルパークラス.
 *
 * RFC 8058 対応のワンクリック登録解除機能を含む
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class SC_Helper_Mailmaga
{
    /** トークンの有効期限（日数） */
    public const TOKEN_EXPIRE_DAYS = 90;

    /** トークンの長さ */
    public const TOKEN_LENGTH = 64;

    /**
     * ワンクリック登録解除用トークンを生成
     *
     * @param int    $customer_id 会員ID
     * @param int    $send_id     配信ID
     * @param string $email       メールアドレス
     *
     * @return string トークン文字列
     */
    public static function generateUnsubscribeToken($customer_id, $send_id, $email)
    {
        // セキュアなトークン生成
        $token = SC_Utils_Ex::sfGetRandomString(self::TOKEN_LENGTH);

        // データベースに保存
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $sqlval = [];
        $sqlval['customer_id'] = $customer_id;
        $sqlval['send_id'] = $send_id;
        $sqlval['token'] = $token;
        $sqlval['email'] = $email;
        $sqlval['used_flag'] = 0;
        $sqlval['expire_date'] = date('Y-m-d H:i:s', strtotime('+'.self::TOKEN_EXPIRE_DAYS.' days'));
        $sqlval['create_date'] = 'CURRENT_TIMESTAMP';
        $sqlval['mailmaga_unsubscribe_token_id'] = $objQuery->nextVal('dtb_mailmaga_unsubscribe_token_mailmaga_unsubscribe_token_id');

        $objQuery->insert('dtb_mailmaga_unsubscribe_token', $sqlval);

        return $token;
    }

    /**
     * ワンクリック登録解除URLを生成
     *
     * @param string $token トークン文字列
     *
     * @return string 完全なURL
     */
    public static function getUnsubscribeUrl($token)
    {
        return HTTPS_URL.'mailmaga/unsubscribe/index.php?token='.urlencode($token);
    }

    /**
     * トークンの検証と取得
     *
     * @param string $token トークン文字列
     *
     * @return array|false トークン情報の配列 or false
     */
    public static function validateToken($token)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $where = 'token = ? AND used_flag = 0 AND expire_date > CURRENT_TIMESTAMP';
        $arrToken = $objQuery->getRow('*', 'dtb_mailmaga_unsubscribe_token', $where, [$token]);

        if (empty($arrToken)) {
            return false;
        }

        return $arrToken;
    }

    /**
     * トークンを使用済みにマーク
     *
     * @param string $token トークン文字列
     *
     * @return bool 成功した場合 true
     */
    public static function markTokenAsUsed($token)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $sqlval = [];
        $sqlval['used_flag'] = 1;
        $sqlval['used_date'] = 'CURRENT_TIMESTAMP';

        $ret = $objQuery->update(
            'dtb_mailmaga_unsubscribe_token',
            $sqlval,
            'token = ?',
            [$token]
        );

        return $ret > 0;
    }

    /**
     * メルマガ配信を解除（mailmaga_flg を 3 に設定）
     *
     * @param int $customer_id 会員ID
     *
     * @return bool 成功した場合 true
     */
    public static function unsubscribeMailmaga($customer_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $sqlval = [];
        $sqlval['mailmaga_flg'] = 3; // 配信拒否
        $sqlval['update_date'] = 'CURRENT_TIMESTAMP';

        $ret = $objQuery->update(
            'dtb_customer',
            $sqlval,
            'customer_id = ?',
            [$customer_id]
        );

        return $ret > 0;
    }

    /**
     * 期限切れトークンのクリーンアップ
     * （バッチ処理として定期実行を想定）
     *
     * @return int 削除件数
     */
    public static function cleanupExpiredTokens()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $where = 'expire_date < CURRENT_TIMESTAMP OR used_flag = 1';
        $ret = $objQuery->delete('dtb_mailmaga_unsubscribe_token', $where);

        return $ret;
    }
}
