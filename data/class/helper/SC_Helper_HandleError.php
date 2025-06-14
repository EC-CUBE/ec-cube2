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
 * エラーハンドリングのクラス
 *
 * 依存するクラスに構文エラーがあると、捕捉できない。よって、依存は最小に留めること。
 * 現状 GC_Utils_Ex(GC_Utils) に依存しているため、その中で構文エラーは捕捉できない。
 *
 * @version $Id$
 */
class SC_Helper_HandleError
{
    /** エラー処理中か */
    public static $under_error_handling = false;

    /** display_errors の初期値 */
    public static $default_display_errors;

    /**
     * 処理の読み込みを行う
     *
     * @return void
     */
    public static function load()
    {
        // E_DEPRECATED 定数 (for PHP < 5.3)
        // TODO バージョン互換処理に統合したい。
        if (!defined('E_DEPRECATED')) {
            define('E_DEPRECATED', 8192);
        }

        // エラーレベル設定 (PHPのログに対する指定であり、以降のエラーハンドリングには影響しない模様)
        // 開発時は -1 (全て) を推奨
        error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE);

        static::$default_display_errors = ini_get('display_errors');

        if (!(defined('SAFE') && SAFE === true) && !(defined('INSTALL_FUNCTION') && INSTALL_FUNCTION === true)) {
            // E_USER_ERROR または警告を捕捉した場合のエラーハンドラ
            set_error_handler([__CLASS__, 'handle_warning'], E_USER_ERROR | E_WARNING | E_USER_WARNING | E_CORE_WARNING | E_COMPILE_WARNING);

            // E_USER_ERROR 以外のエラーを捕捉した場合の処理用
            register_shutdown_function([__CLASS__, 'handle_error']);
            // 以降の処理では画面へのエラー表示は行なわない
            if (!headers_sent()) {
                ini_set('display_errors', 0);
            }
        }
    }

    /**
     * 警告や E_USER_ERROR を捕捉した場合にエラー画面を表示させるエラーハンドラ関数.
     *
     * この関数は, set_error_handler() 関数に登録するための関数である.
     * trigger_error にて E_USER_ERROR が生成されると, エラーログを出力した後,
     * エラー画面を表示させる.
     * E_WARNING, E_USER_WARNING が発生した場合、ログを記録して、true を返す。
     * (エラー画面・エラー文言は表示させない。)
     *
     * @param  int      $errno   エラーコード
     * @param  string       $errstr  エラーメッセージ
     * @param  string       $errfile エラーが発生したファイル名
     * @param  int      $errline エラーが発生した行番号
     *
     * @return void|bool E_USER_ERROR が発生した場合は, エラーページへリダイレクト;
     *                      E_WARNING, E_USER_WARNING が発生した場合、true を返す
     */
    public static function handle_warning($errno, $errstr, $errfile, $errline)
    {
        // テスト中の E_USER_ERROR は例外をスローする。それにより、TestCase::expectException() を使える。
        if (defined('TEST_FUNCTION') && TEST_FUNCTION === true && $errno === E_USER_ERROR) {
            throw new Error(strlen($errstr) >= 1 ? $errstr : 'for phpunit');
        }

        // error_reporting 設定に含まれていないエラーコードは処理しない
        if (!(error_reporting() & $errno)) {
            return;
        }

        // パラメーターが読み込まれるまでは、PHP 標準のエラー処理とする。
        if (!defined('ERROR_LOG_REALFILE')) {
            return false;
        }

        $error_type_name = GC_Utils_Ex::getErrorTypeName($errno);

        switch ($errno) {
            case E_USER_ERROR:
                // パラメーターが読み込まれるまでは、エラー例外をスローする。(上の分岐があるため phpunit の実行中に限定される。)
                if (!defined('ERROR_LOG_REALFILE')) {
                    if (!headers_sent()) {
                        ini_set('display_errors', static::$default_display_errors);
                    }
                    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
                }

                $message = "Fatal error($error_type_name): $errstr on [$errfile($errline)]";
                GC_Utils_Ex::gfPrintLog($message, ERROR_LOG_REALFILE, true);

                SC_Helper_HandleError_Ex::displaySystemError($message);
                exit(1);
                break;

            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                if (defined('ERROR_LOG_REALFILE')) {
                    $message = "Warning($error_type_name): $errstr on [$errfile($errline)]";
                    GC_Utils_Ex::gfPrintLog($message, ERROR_LOG_REALFILE);
                }

                return true;

            default:
                break;
        }
    }

    /**
     * エラーを捕捉するための関数. (for PHP < 5.2.0)
     *
     * PHP4 では, try/catch が使用できず, かつ set_error_handler で Fatal Error は
     * 捕捉できないため, ob_start にこの関数を定義し, Fatal Error が発生した場合
     * に出力される HTML 出力を捕捉する.
     * この関数が実行され, エラーが捕捉されると, DEBUG_MODE が無効な場合,
     * エラーページへリダイレクトする.
     *
     * @param  string      $buffer 出力バッファリングの内容
     *
     * @return string|void エラーが捕捉された場合は, エラーページへリダイレクトする;
     *                     エラーが捕捉されない場合は, 出力バッファリングの内容を返す
     *
     * @deprecated 2.18 EC-CUBE 本体では使用していない。
     */
    public static function &_fatal_error_handler(&$buffer)
    {
        if (preg_match('/<b>(Fatal error)<\/b>: +(.+) in <b>(.+)<\/b> on line <b>(\d+)<\/b><br \/>/i', $buffer, $matches)) {
            $message = "$matches[1]: $matches[2] on [$matches[3]($matches[4])]";
            GC_Utils_Ex::gfPrintLog($message, ERROR_LOG_REALFILE, true);
            if (DEBUG_MODE !== true) {
                $url = HTTP_URL.'error.php';
                if (defined('ADMIN_FUNCTION') && ADMIN_FUNCTION === true) {
                    $url .= '?admin';
                }
                header("Location: $url");
                exit;
            }
        }

        return $buffer;
    }

    /**
     * エラー捕捉時のエラーハンドラ関数 (for PHP >= 5.2.0)
     *
     * この関数は, register_shutdown_function() 関数に登録するための関数である。
     * PHP 5.1 対応処理との互換運用ため E_USER_ERROR は handle_warning で捕捉する。
     *
     * @return void
     */
    public static function handle_error()
    {
        // 最後のエラーを確実に捉えるため、先頭で呼び出す。
        $arrError = error_get_last();

        $is_error = false;
        if (isset($arrError)) {
            switch ($arrError['type']) {
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                    $is_error = true;
                    break;
                default:
                    break;
            }
        }

        if (!$is_error) {
            return;
        }

        // パラメーターが読み込まれるまでは、エラー例外をスローする。
        if (!defined('ERROR_LOG_REALFILE')) {
            if (!headers_sent()) {
                ini_set('display_errors', static::$default_display_errors);
            }
            throw new ErrorException($arrError['message'], 0, $arrError['type'], $arrError['file'], $arrError['line']);
        }

        $error_type_name = GC_Utils_Ex::getErrorTypeName($arrError['type']);
        $errstr = "Fatal error($error_type_name): {$arrError['message']} on [{$arrError['file']}({$arrError['line']})]";

        GC_Utils_Ex::gfPrintLog($errstr, ERROR_LOG_REALFILE, true);

        // エラー画面を表示する
        SC_Helper_HandleError_Ex::displaySystemError($errstr);
    }

    /**
     * エラー画面を表示する
     *
     * @param  string|null $errstr エラーメッセージ
     *
     * @return void
     */
    public static function displaySystemError($errstr = null)
    {
        SC_Helper_HandleError_Ex::$under_error_handling = true;

        ob_clean();

        $objPage = new LC_Page_Error_SystemError_Ex();
        $objPage->init();
        if (isset($errstr)) {
            $objPage->addDebugMsg($errstr);
        }
        $objPage->process();
    }
}
