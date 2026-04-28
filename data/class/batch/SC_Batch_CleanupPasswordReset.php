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
 * パスワードリセットトークンクリーンアップバッチクラス
 *
 * Issue #368: パスワードの再発行機能の改善
 * 期限切れのパスワードリセットトークンを定期的にクリーンアップします。
 *
 * @author  EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class SC_Batch_CleanupPasswordReset extends SC_Batch
{
    /**
     * バッチ処理を実行する
     *
     * 期限切れで未使用のパスワードリセットトークンを
     * ステータス2（期限切れ）に更新します。
     *
     * 推奨実行頻度: 1日1回（深夜のメンテナンス時間帯）
     *
     * @param array $args コマンドライン引数（未使用）
     *
     * @return void
     */
    public function execute($args = [])
    {
        $this->start('パスワードリセットトークンクリーンアップバッチ');

        try {
            $count = SC_Helper_PasswordReset_Ex::cleanupExpiredTokens();

            if ($count > 0) {
                $msg = "期限切れトークン {$count} 件をクリーンアップしました。";
                $this->printLog($msg);
            } else {
                $msg = 'クリーンアップ対象のトークンはありませんでした。';
                $this->printLog($msg);
            }

            $this->end();
        } catch (Exception $e) {
            $msg = 'エラーが発生しました: '.$e->getMessage();
            $this->printLog($msg);
            GC_Utils_Ex::gfPrintLog($msg, ERROR_LOG_REALFILE);
            $this->end(true); // エラー終了
        }
    }

    /**
     * バッチ開始ログを出力する
     *
     * @param string $message バッチ名
     *
     * @return void
     */
    protected function start($message = '')
    {
        $msg = '========================================';
        $this->printLog($msg);
        $msg = $message.' 開始: '.date('Y-m-d H:i:s');
        $this->printLog($msg);
    }

    /**
     * バッチ終了ログを出力する
     *
     * @param bool $error エラー終了フラグ
     *
     * @return void
     */
    protected function end($error = false)
    {
        if ($error) {
            $msg = 'バッチ処理がエラーで終了しました: '.date('Y-m-d H:i:s');
        } else {
            $msg = 'バッチ処理が正常に終了しました: '.date('Y-m-d H:i:s');
        }
        $this->printLog($msg);
        $msg = '========================================';
        $this->printLog($msg);
    }

    /**
     * ログメッセージを出力する
     *
     * @param string $message ログメッセージ
     *
     * @return void
     */
    protected function printLog($message)
    {
        GC_Utils_Ex::gfPrintLog($message, LOG_REALFILE);
        echo $message.PHP_EOL;
    }
}
