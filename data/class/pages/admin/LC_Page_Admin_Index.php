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
 * 管理者ログイン のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_Index extends LC_Page_Admin_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'login.tpl';
        $this->httpCacheControl('nocache');
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
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
        // パラメーター管理クラス
        $objFormParam = new SC_FormParam_Ex();

        switch ($this->getMode()) {
            case 'login':
                // ログイン処理
                $this->lfInitParam($objFormParam);
                $objFormParam->setParam($_POST);
                $objFormParam->trimParam();
                $this->arrErr = $this->lfCheckError($objFormParam);
                if (SC_Utils_Ex::isBlank($this->arrErr)) {
                    $this->lfDoLogin($objFormParam->getValue('login_id'));

                    SC_Response_Ex::sendRedirect(ADMIN_HOME_URLPATH);
                } else {
                    // ブルートフォースアタック対策
                    // ログイン失敗時に遅延させる
                    sleep(LOGIN_RETRY_INTERVAL);

                    SC_Utils_Ex::sfDispError(LOGIN_ERROR);
                }
                break;
            default:
                break;
        }

        // 管理者ログインテンプレートフレームの設定
        $this->setTemplate(LOGIN_FRAME);
    }

    /**
     * パラメーター情報の初期化
     *
     * @param  SC_FormParam_Ex $objFormParam フォームパラメータークラス
     *
     * @return void
     */
    public function lfInitParam(&$objFormParam)
    {
        $objFormParam->addParam('ID', 'login_id', ID_MAX_LEN, '', ['EXIST_CHECK', 'GRAPH_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('PASSWORD', 'password', PASSWORD_MAX_LEN, '', ['EXIST_CHECK', 'GRAPH_CHECK', 'MAX_LENGTH_CHECK']);
    }

    /**
     * パラメーターのエラーチェック
     *
     * TODO: ブルートフォースアタック対策チェックの実装
     *
     * @param  SC_FormParam_Ex $objFormParam フォームパラメータークラス
     *
     * @return array $arrErr エラー配列
     */
    public function lfCheckError(&$objFormParam)
    {
        // 書式チェック
        $arrErr = $objFormParam->checkError();
        if (SC_Utils_Ex::isBlank($arrErr)) {
            $arrForm = $objFormParam->getHashArray();
            // ログインチェック
            if (!$this->lfIsLoginMember($arrForm['login_id'], $arrForm['password'])) {
                $arrErr['password'] = 'ログイン出来ません。';
                $this->lfSetIncorrectData($arrForm['login_id']);
            }
        }

        return $arrErr;
    }

    /**
     * 有効な管理者ID/PASSかどうかチェックする
     *
     * @param  string  $login_id ログインID文字列
     * @param  string  $pass     ログインパスワード文字列
     *
     * @return bool ログイン情報が有効な場合 true
     */
    public function lfIsLoginMember($login_id, $pass)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        // パスワード、saltの取得
        $cols = 'password, salt';
        $table = 'dtb_member';
        $where = 'login_id = ? AND del_flg <> 1 AND work = 1';
        $arrData = $objQuery->getRow($cols, $table, $where, [$login_id]);
        if (SC_Utils_Ex::isBlank($arrData)) {
            return false;
        }
        // ユーザー入力パスワードの判定
        if (SC_Utils_Ex::sfIsMatchHashPassword($pass, $arrData['password'], $arrData['salt'])) {
            return true;
        }

        return false;
    }

    /**
     * 管理者ログイン設定処理
     *
     * @param  string $login_id ログインID文字列
     *
     * @return void
     */
    public function lfDoLogin($login_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        // メンバー情報取得
        $cols = 'member_id, authority, login_date, name';
        $table = 'dtb_member';
        $where = 'login_id = ? AND del_flg <> 1 AND work = 1';
        $arrData = $objQuery->getRow($cols, $table, $where, [$login_id]);
        // セッション登録
        $sid = $this->lfSetLoginSession($arrData['member_id'], $login_id, $arrData['authority'], $arrData['name'], $arrData['login_date']);
        // ログイン情報記録
        $this->lfSetLoginData($sid, $arrData['member_id'], $login_id, $arrData['authority'], $arrData['login_date']);
    }

    /**
     * ログイン情報セッション登録
     *
     * @param  int $member_id  メンバーID
     * @param  string  $login_id   ログインID文字列
     * @param  int $authority  権限ID
     * @param  string  $login_name ログイン表示名
     * @param  string  $last_login 最終ログイン日時(YYYY/MM/DD HH:ii:ss形式) またはNULL
     *
     * @return string  $sid 設定したセッションのセッションID
     */
    public function lfSetLoginSession($member_id, $login_id, $authority, $login_name, $last_login)
    {
        // Session Fixation対策
        SC_Session_Ex::regenerateSID();

        $objSess = new SC_Session_Ex();
        // 認証済みの設定
        $objSess->SetSession('cert', CERT_STRING);
        $objSess->SetSession('member_id', $member_id);
        $objSess->SetSession('login_id', $login_id);
        $objSess->SetSession('authority', $authority);
        $objSess->SetSession('login_name', $login_name);
        $objSess->SetSession('uniqid', $objSess->getUniqId());
        if (SC_Utils_Ex::isBlank($last_login)) {
            $objSess->SetSession('last_login', date('Y-m-d H:i:s'));
        } else {
            $objSess->SetSession('last_login', $last_login);
        }

        return $objSess->GetSID();
    }

    /**
     * ログイン情報の記録
     *
     * @param  string   $sid        セッションID
     * @param  int $member_id  メンバーID
     * @param  string  $login_id   ログインID文字列
     * @param  int $authority  権限ID
     * @param  string  $last_login 最終ログイン日時(YYYY/MM/DD HH:ii:ss形式) またはNULL
     *
     * @return void
     */
    public function lfSetLoginData($sid, $member_id, $login_id, $authority, $last_login)
    {
        // ログイン記録ログ出力
        $str_log = "login: user=$login_id($member_id) auth=$authority "
                    ."lastlogin=$last_login sid=$sid";
        GC_Utils_Ex::gfPrintLog($str_log);

        // 最終ログイン日時更新
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $sqlval = [];
        $sqlval['login_date'] = date('Y-m-d H:i:s');
        $table = 'dtb_member';
        $where = 'member_id = ?';
        $objQuery->update($table, $sqlval, $where, [$member_id]);
    }

    /**
     * ログイン失敗情報の記録
     *
     * TODO: ブルートフォースアタック対策の実装
     *
     * @param  string $error_login_id ログイン失敗時に投入されたlogin_id文字列
     *
     * @return void
     */
    public function lfSetIncorrectData($error_login_id)
    {
        GC_Utils_Ex::gfPrintLog($error_login_id.' password incorrect.');
    }
}
