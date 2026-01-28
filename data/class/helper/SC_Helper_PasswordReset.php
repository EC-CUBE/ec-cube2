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
 * パスワード再発行トークン管理ヘルパークラス
 *
 * Issue #368: パスワードの再発行機能の改善
 * トークンベースのパスワードリセット機能を提供します。
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class SC_Helper_PasswordReset
{
    /**
     * 暗号学的に安全なトークンを生成する
     *
     * random_bytes()を使用して64文字のHEX文字列を生成します。
     *
     * @return string 64文字のトークン
     */
    public static function generateToken()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * トークンをSHA-256でハッシュ化する
     *
     * データベースにはハッシュ化されたトークンを保存し、
     * DB漏洩時にも元のトークンを復元できないようにします。
     *
     * @param string $token 平文トークン
     *
     * @return string ハッシュ化されたトークン
     */
    public static function hashToken($token)
    {
        return hash('sha256', $token);
    }

    /**
     * パスワードリセット用のトークンレコードを作成する
     *
     * @param string $email メールアドレス
     * @param int    $customer_id 顧客ID
     * @param string $ip_address IPアドレス
     * @param string $user_agent ユーザーエージェント
     *
     * @return string 平文トークン（メール送信用）
     */
    public static function createResetToken($email, $customer_id, $ip_address, $user_agent)
    {
        $token = self::generateToken();
        $token_hash = self::hashToken($token);
        $expire_date = date('Y-m-d H:i:s', strtotime('+'.PASSWORD_RESET_TOKEN_EXPIRE_HOURS.' hours'));

        $objQuery = SC_Query_Ex::getSingletonInstance();

        $sqlval = [
            'email' => $email,
            'token_hash' => $token_hash,
            'customer_id' => $customer_id,
            'status' => 0,
            'expire_date' => $expire_date,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
        ];

        $password_reset_id = $objQuery->nextVal('dtb_password_reset_password_reset_id');
        $sqlval['password_reset_id'] = $password_reset_id;

        $objQuery->insert('dtb_password_reset', $sqlval);

        return $token; // 平文トークンを返す（メール送信用）
    }

    /**
     * トークンを検証する
     *
     * トークンが有効（未使用かつ期限内）であることを確認します。
     *
     * @param string $token 平文トークン
     *
     * @return array|null トークンデータ、無効な場合はnull
     */
    public static function validateToken($token)
    {
        $token_hash = self::hashToken($token);
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $where = 'token_hash = ? AND status = 0 AND expire_date > CURRENT_TIMESTAMP';
        $result = $objQuery->select('*', 'dtb_password_reset', $where, [$token_hash]);

        if (empty($result)) {
            return null;
        }

        return $result[0];
    }

    /**
     * トークンを使用済みにマークする
     *
     * @param string $token_hash ハッシュ化されたトークン
     *
     * @return void
     */
    public static function markTokenAsUsed($token_hash)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $sqlval = [
            'status' => 1,
            'used_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
        ];

        $objQuery->update('dtb_password_reset', $sqlval, 'token_hash = ?', [$token_hash]);
    }

    /**
     * レート制限をチェックする
     *
     * 同一メールアドレスまたは同一IPアドレスからの
     * パスワードリセット要求が1時間に3回を超えていないか確認します。
     *
     * @param string $email      メールアドレス
     * @param string $ip_address IPアドレス
     *
     * @return array ['allowed' => bool, 'reason' => string|null]
     */
    public static function checkRateLimit($email, $ip_address)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $one_hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));

        // 同一メールアドレスのチェック
        $email_count = $objQuery->count(
            'dtb_password_reset',
            'email = ? AND create_date > ?',
            [$email, $one_hour_ago]
        );

        if ($email_count >= 3) {
            return ['allowed' => false, 'reason' => 'email'];
        }

        // 同一IPアドレスのチェック
        $ip_count = $objQuery->count(
            'dtb_password_reset',
            'ip_address = ? AND create_date > ?',
            [$ip_address, $one_hour_ago]
        );

        if ($ip_count >= 3) {
            return ['allowed' => false, 'reason' => 'ip'];
        }

        return ['allowed' => true];
    }

    /**
     * 期限切れトークンをクリーンアップする
     *
     * バッチ処理から定期的に呼び出されることを想定しています。
     *
     * @return int クリーンアップされたレコード数
     */
    public static function cleanupExpiredTokens()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        // 期限切れで未使用のトークンを期限切れステータスに更新
        $sqlval = [
            'status' => 2,
            'update_date' => 'CURRENT_TIMESTAMP',
        ];

        $count = $objQuery->update(
            'dtb_password_reset',
            $sqlval,
            'status = 0 AND expire_date < CURRENT_TIMESTAMP'
        );

        return $count;
    }

    /**
     * 顧客の全トークンを無効化する
     *
     * パスワード変更成功時に呼び出され、
     * 当該顧客の未使用トークンを全て使用済みにします。
     *
     * @param int $customer_id 顧客ID
     *
     * @return void
     */
    public static function invalidateAllTokensForCustomer($customer_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $sqlval = [
            'status' => 1,
            'update_date' => 'CURRENT_TIMESTAMP',
        ];

        $objQuery->update(
            'dtb_password_reset',
            $sqlval,
            'customer_id = ? AND status = 0',
            [$customer_id]
        );
    }
}
