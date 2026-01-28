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
 * ログインチェック のページクラス.
 *
 * TODO mypage/LC_Page_Mypage_LoginCheck と統合
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_FrontParts_LoginCheck extends LC_Page_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        $this->skip_load_page_layout = true;
        parent::init();
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
        // 決済処理中ステータスのロールバック
        $objPurchase = new SC_Helper_Purchase_Ex();
        $objPurchase->cancelPendingOrder(PENDING_ORDER_CANCEL_FLAG);

        // 会員管理クラス
        $objCustomer = new SC_Customer_Ex();
        // クッキー管理クラス
        $objCookie = new SC_Cookie_Ex();
        // パラメーター管理クラス
        $objFormParam = new SC_FormParam_Ex();

        // パラメーター情報の初期化
        $this->lfInitParam($objFormParam);

        // リクエスト値をフォームにセット
        $objFormParam->setParam($_POST);

        $url = htmlspecialchars($_POST['url'], ENT_QUOTES);

        // モードによって分岐
        switch ($this->getMode()) {
            case 'login':
                // --- ログイン

                // 入力値のエラーチェック
                $objFormParam->trimParam();
                $objFormParam->toLower('login_email');

                $login_email = $objFormParam->getValue('login_email');
                $login_pass = $objFormParam->getValue('login_pass');
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

                // レート制限チェック
                $rate_limit = SC_Helper_LoginRateLimit_Ex::checkRateLimit($login_email, $ip_address);

                if (!$rate_limit['allowed']) {
                    // レート制限超過時のエラーメッセージ
                    $this->arrErr['login'] = '短時間に複数のログイン試行が検出されました。しばらく時間をおいてから再度お試しください。';
                    // 失敗として記録
                    SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($login_email, $ip_address, $user_agent, 0);

                    // AJAX対応: JSON返却（401でパスワードマネージャーの誤認を防止）
                    SC_Response_Ex::sendHttpStatus(401);
                    echo SC_Utils_Ex::jsonEncode(['error' => $this->arrErr['login']]);
                    SC_Response_Ex::actionExit();
                } else {
                    // バリデーション
                    $arrErr = $objFormParam->checkError();

                    if (count($arrErr) > 0) {
                        // バリデーションエラーの場合
                        $this->arrErr['login'] = 'メールアドレスもしくはパスワードが正しくありません。';

                        // バリデーションエラーも失敗として記録
                        SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($login_email, $ip_address, $user_agent, 0);

                        // AJAX対応: JSON返却（401でパスワードマネージャーの誤認を防止）
                        SC_Response_Ex::sendHttpStatus(401);
                        echo SC_Utils_Ex::jsonEncode(['error' => $this->arrErr['login']]);
                        SC_Response_Ex::actionExit();
                    } else {
                        // 入力チェック後の値を取得
                        $arrForm = $objFormParam->getHashArray();

                        // クッキー保存判定
                        if ($arrForm['login_memory'] == '1' && $arrForm['login_email'] != '') {
                            $objCookie->setCookie('login_email', $arrForm['login_email']);
                        } else {
                            $objCookie->setCookie('login_email', '');
                        }

                        // ログイン処理
                        if ($objCustomer->doLogin($login_email, $login_pass)) {
                            // ログイン成功を記録
                            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($login_email, $ip_address, $user_agent, 1);

                            if (SC_Display_Ex::detectDevice() === DEVICE_TYPE_MOBILE) {
                                // ログインが成功した場合は携帯端末IDを保存する。
                                $objCustomer->updateMobilePhoneId();

                                /*
                                 * email がモバイルドメインでは無く,
                                 * 携帯メールアドレスが登録されていない場合
                                 */
                                $objMobile = new SC_Helper_Mobile_Ex();
                                if (!$objMobile->gfIsMobileMailAddress($objCustomer->getValue('email'))) {
                                    if (!$objCustomer->hasValue('email_mobile')) {
                                        SC_Response_Ex::sendRedirectFromUrlPath('entry/email_mobile.php');
                                        SC_Response_Ex::actionExit();
                                    }
                                }
                            }

                            // --- ログインに成功した場合
                            // AJAX対応: JSON返却
                            echo SC_Utils_Ex::jsonEncode(['success' => $url]);
                            SC_Response_Ex::actionExit();
                        } else {
                            // --- ログインに失敗した場合

                            // ログイン失敗を記録
                            SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($login_email, $ip_address, $user_agent, 0);

                            // 仮登録の場合
                            if (SC_Helper_Customer_Ex::checkTempCustomer($login_email)) {
                                $this->arrErr['login'] = "メールアドレスもしくはパスワードが正しくありません。\n本登録がお済みでない場合は、仮登録メールに記載されているURLより本登録を行ってください。";
                            } else {
                                $this->arrErr['login'] = 'メールアドレスもしくはパスワードが正しくありません。';
                            }

                            // AJAX対応: JSON返却（401でパスワードマネージャーの誤認を防止）
                            SC_Response_Ex::sendHttpStatus(401);
                            echo SC_Utils_Ex::jsonEncode(['error' => $this->arrErr['login']]);
                            SC_Response_Ex::actionExit();
                        }
                    }
                }

                break;
            case 'logout':
                // --- ログアウト

                // ログイン情報の解放
                $objCustomer->EndSession();
                // 画面遷移の制御
                $mypage_url_search = strpos('.'.$url, 'mypage');
                if ($mypage_url_search == 2) {
                    // マイページログイン中はログイン画面へ移行
                    SC_Response_Ex::sendRedirectFromUrlPath('mypage/login.php');
                } else {
                    // 上記以外の場合、トップへ遷移
                    SC_Response_Ex::sendRedirect(TOP_URL);
                }
                SC_Response_Ex::actionExit();

                break;
            default:
                break;
        }

        SC_Utils_Ex::sfDispSiteError(CUSTOMER_ERROR);
    }

    /**
     * パラメーター情報の初期化.
     *
     * @param  SC_FormParam $objFormParam パラメーター管理クラス
     *
     * @return void
     */
    public function lfInitParam(&$objFormParam)
    {
        $objFormParam->addParam('記憶する', 'login_memory', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('メールアドレス', 'login_email', MTEXT_LEN, 'a', ['EXIST_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('パスワード', 'login_pass', PASSWORD_MAX_LEN, '', ['EXIST_CHECK', 'MAX_LENGTH_CHECK']);
    }

    /**
     * エラーメッセージを JSON 形式で返す.
     *
     * TODO リファクタリング
     * この関数は主にスマートフォンで使用します.
     *
     * @param int エラーコード
     *
     * @return string JSON 形式のエラーメッセージ
     *
     * @see LC_PageError
     */
    public function lfGetErrorMessage($error)
    {
        switch ($error) {
            case TEMP_LOGIN_ERROR:
                $msg = "メールアドレスもしくはパスワードが正しくありません。\n本登録がお済みでない場合は、仮登録メールに記載されているURLより本登録を行ってください。";
                break;
            case SITE_LOGIN_ERROR:
            default:
                $msg = 'メールアドレスもしくはパスワードが正しくありません。';
        }

        return SC_Utils_Ex::jsonEncode(['login_error' => $msg]);
    }
}
