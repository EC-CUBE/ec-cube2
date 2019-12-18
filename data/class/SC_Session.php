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

/* セッション管理クラス */
class SC_Session
{
    /** ログインユーザ名 */
    public $login_id;

    /** ユーザ権限 */
    public $authority;

    /** 認証文字列(認証成功の判定に使用) */
    public $cert;

    /** セッションID */
    public $sid;

    /** ログインユーザの主キー */
    public $member_id;

    /** ページ遷移の正当性チェックに使用 */
    public $uniqid;

    /* コンストラクタ */
    public function __construct()
    {
        // セッション情報の保存
        if (isset($_SESSION['cert'])) {
            $this->sid = substr(sha1(session_id()), 0, 8);
            $this->cert = $_SESSION['cert'];
            $this->login_id  = $_SESSION['login_id'];
            // 管理者:0, 店舗オーナー:1, 閲覧:2, 販売担当:3 (XXX 現状 0, 1 を暫定実装。2, 3 は未実装。)
            $this->authority = $_SESSION['authority'];
            $this->member_id = $_SESSION['member_id'];
            if (isset($_SESSION['uniq_id'])) {
                $this->uniqid    = $_SESSION['uniq_id'];
            }

            // ログに記録する
            GC_Utils_Ex::gfPrintLog('access : user='.$this->login_id.' auth='.$this->authority.' sid='.$this->sid);
        } else {
            // ログに記録する
            GC_Utils_Ex::gfPrintLog('access error.');
        }
    }
    /* 認証成功の判定 */
    public function IsSuccess()
    {
        if ($this->cert == CERT_STRING) {
            $script_path = realpath($_SERVER['SCRIPT_FILENAME']);
            $arrScriptPath = explode('/', str_replace('\\', '/', $script_path));

            $masterData = new SC_DB_MasterData_Ex();
            $arrPERMISSION = $masterData->getMasterData('mtb_permission');

            foreach ($arrPERMISSION as $path => $auth) {
                $permission_path = realpath(HTML_REALDIR . $path);
                $arrPermissionPath = explode('/', str_replace('\\', '/', $permission_path));
                $arrDiff = array_diff_assoc($arrScriptPath, $arrPermissionPath);
                // 一致した場合は、権限チェックを行う
                if (count($arrDiff) === 0) {
                    // 数値が自分の権限以上のものでないとアクセスできない。
                    if ($auth < $this->authority) {
                        return ACCESS_ERROR;
                    }
                }
            }
            return SUCCESS;
        }

        return ACCESS_ERROR;
    }

    /* セッションの書き込み */

    /**
     * @param string $key
     */
    public function SetSession($key, $val)
    {
        $_SESSION[$key] = $val;
    }

    /* セッションの読み込み */

    /**
     * @param string $key
     */
    public function GetSession($key)
    {
        return $_SESSION[$key];
    }

    /* セッションIDの取得 */
    public function GetSID()
    {
        return $this->sid;
    }

    /** ユニークIDの取得 **/
    public function getUniqId()
    {
        // ユニークIDがセットされていない場合はセットする。
        if (empty($_SESSION['uniqid'])) {
            $this->setUniqId();
        }

        return $this->GetSession('uniqid');
    }

    /** ユニークIDのセット **/
    public function setUniqId()
    {
        // 予測されないようにランダム文字列を付与する。
        $this->SetSession('uniqid', SC_Utils_Ex::sfGetUniqRandomId());
    }

    // 関連セッションのみ破棄する。
    public function logout()
    {
        unset($_SESSION['cert']);
        unset($_SESSION['login_id']);
        unset($_SESSION['authority']);
        unset($_SESSION['member_id']);
        unset($_SESSION['uniqid']);
        // トランザクショントークンを破棄
        SC_Helper_Session_Ex::destroyToken();
        // ログに記録する
        GC_Utils_Ex::gfPrintLog('logout : user='.$this->login_id.' auth='.$this->authority.' sid='.$this->sid);
    }

    /**
     * セッションIDを新しいIDに書き換える
     *
     * @return bool
     */
    public function regenerateSID()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return session_regenerate_id(true);
        }
        return false;
    }
}
