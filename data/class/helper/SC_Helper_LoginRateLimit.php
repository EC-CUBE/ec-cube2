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
 * ログイン試行レート制限ヘルパークラス
 *
 * Issue #1301: ログインエラー表示改善 + ブルートフォース攻撃対策
 * ログイン試行のレート制限機能を提供します。
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class SC_Helper_LoginRateLimit
{
    /**
     * レート制限をチェックする
     *
     * 同一メールアドレスまたは同一IPアドレスからの
     * ログイン試行失敗が制限を超えていないか確認します。
     *
     * レート制限ルール:
     * - 同一メールアドレス: 1時間に5回まで失敗を許可（6回目でブロック）
     * - 同一IPアドレス: 1時間に10回まで失敗を許可（11回目でブロック）
     *
     * セキュリティ考慮事項:
     * - アカウント列挙攻撃対策: 存在しないメールアドレスも同様にレート制限
     * - タイミング攻撃対策: エラーメッセージは常に同じ
     *
     * @param string $login_id メールアドレス
     * @param string $ip_address IPアドレス
     *
     * @return array ['allowed' => bool, 'reason' => string|null, 'email_count' => int, 'ip_count' => int]
     */
    public static function checkRateLimit($login_id, $ip_address)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        // データベースタイプに応じて1時間前の時刻を取得
        if (DB_TYPE == 'pgsql') {
            $interval_clause = "create_date > NOW() - INTERVAL '1 hour'";
        } else {
            // MySQL
            $interval_clause = 'create_date > NOW() - INTERVAL 1 HOUR';
        }

        // 同一メールアドレスの失敗回数をチェック
        $email_count = $objQuery->count(
            'dtb_login_attempt',
            "login_id = ? AND result = 0 AND {$interval_clause}",
            [$login_id]
        );

        if ($email_count >= 6) {
            return [
                'allowed' => false,
                'reason' => 'email',
                'email_count' => $email_count,
                'ip_count' => 0,
            ];
        }

        // 同一IPアドレスの失敗回数をチェック
        $ip_count = $objQuery->count(
            'dtb_login_attempt',
            "ip_address = ? AND result = 0 AND {$interval_clause}",
            [$ip_address]
        );

        if ($ip_count >= 11) {
            return [
                'allowed' => false,
                'reason' => 'ip',
                'email_count' => $email_count,
                'ip_count' => $ip_count,
            ];
        }

        return [
            'allowed' => true,
            'email_count' => $email_count,
            'ip_count' => $ip_count,
        ];
    }

    /**
     * ログイン試行を記録する
     *
     * すべてのログイン試行（成功・失敗）をデータベースに記録します。
     * この情報は監視用およびレート制限の判定に使用されます。
     *
     * @param string $login_id メールアドレス
     * @param string $ip_address IPアドレス
     * @param string $user_agent ユーザーエージェント
     * @param int    $result 結果（0:失敗, 1:成功）
     *
     * @return void
     */
    public static function recordLoginAttempt($login_id, $ip_address, $user_agent, $result)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $sqlval = [
            'login_id' => $login_id,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'result' => $result,
        ];

        $attempt_id = $objQuery->nextVal('dtb_login_attempt_attempt_id');
        $sqlval['attempt_id'] = $attempt_id;

        $objQuery->insert('dtb_login_attempt', $sqlval);

        // ログ出力（セキュリティ監視用）
        $result_text = $result === 1 ? 'SUCCESS' : 'FAILED';
        $log_message = sprintf(
            'Login attempt: %s | Email: %s | IP: %s | User-Agent: %s',
            $result_text,
            $login_id,
            $ip_address,
            $user_agent
        );

        GC_Utils_Ex::gfPrintLog($log_message, CUSTOMER_LOG_REALFILE, false);
    }

    /**
     * 古いログイン試行記録をクリーンアップする
     *
     * バッチ処理から定期的に呼び出されることを想定しています。
     * デフォルトでは30日以上前のレコードを削除します。
     *
     * @param int $days 保持日数（デフォルト: 30日）
     *
     * @return int 削除されたレコード数
     */
    public static function cleanupOldAttempts($days = 30)
    {
        $days = (int) $days;
        if ($days < 1) {
            $days = 30;
        }

        $objQuery = SC_Query_Ex::getSingletonInstance();

        // データベースタイプに応じて削除条件を設定
        if (DB_TYPE == 'pgsql') {
            $where = "create_date < NOW() - INTERVAL '{$days} days'";
        } else {
            // MySQL
            $where = "create_date < NOW() - INTERVAL {$days} DAY";
        }

        $count = $objQuery->delete(
            'dtb_login_attempt',
            $where
        );

        return $count;
    }

    /**
     * 特定メールアドレスのログイン試行統計を取得する
     *
     * 管理画面での表示や分析に使用します。
     *
     * @param string $login_id メールアドレス
     * @param int    $hours 集計期間（時間）
     *
     * @return array ['total' => int, 'failed' => int, 'success' => int]
     */
    public static function getAttemptStats($login_id, $hours = 24)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $threshold = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        $total = $objQuery->count(
            'dtb_login_attempt',
            'login_id = ? AND create_date > ?',
            [$login_id, $threshold]
        );

        $failed = $objQuery->count(
            'dtb_login_attempt',
            'login_id = ? AND result = 0 AND create_date > ?',
            [$login_id, $threshold]
        );

        $success = $objQuery->count(
            'dtb_login_attempt',
            'login_id = ? AND result = 1 AND create_date > ?',
            [$login_id, $threshold]
        );

        return [
            'total' => $total,
            'failed' => $failed,
            'success' => $success,
        ];
    }

    /**
     * 特定IPアドレスのログイン試行統計を取得する
     *
     * 管理画面での表示や分析に使用します。
     *
     * @param string $ip_address IPアドレス
     * @param int    $hours 集計期間（時間）
     *
     * @return array ['total' => int, 'failed' => int, 'success' => int]
     */
    public static function getIPAttemptStats($ip_address, $hours = 24)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $threshold = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        $total = $objQuery->count(
            'dtb_login_attempt',
            'ip_address = ? AND create_date > ?',
            [$ip_address, $threshold]
        );

        $failed = $objQuery->count(
            'dtb_login_attempt',
            'ip_address = ? AND result = 0 AND create_date > ?',
            [$ip_address, $threshold]
        );

        $success = $objQuery->count(
            'dtb_login_attempt',
            'ip_address = ? AND result = 1 AND create_date > ?',
            [$ip_address, $threshold]
        );

        return [
            'total' => $total,
            'failed' => $failed,
            'success' => $success,
        ];
    }
}
