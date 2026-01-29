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
 * パスワード発行 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Forgot extends LC_Page_Ex
{
    /** フォームパラメーターの配列 */
    public $objFormParam;

    /** エラーメッセージ */
    public $errmsg;

    /** リセットトークン */
    public $token;

    /** メールアドレス */
    public $email;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        $this->skip_load_page_layout = true;
        parent::init();
        $this->tpl_title = 'パスワードを忘れた方';
        $this->tpl_mainpage = 'forgot/index.tpl';
        $this->tpl_mainno = '';
        $this->device_type = SC_Display_Ex::detectDevice();
        // デフォルトログインアドレスロード
        $objCookie = new SC_Cookie_Ex();
        $this->tpl_login_email = $objCookie->getCookie('login_email');
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        parent::process();
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
        $objFormParam = new SC_FormParam_Ex();

        switch ($this->getMode()) {
            case 'request':
                // メールアドレス入力処理
                $this->lfInitRequestParam($objFormParam);
                $objFormParam->setParam($_POST);
                $objFormParam->convParam();
                $objFormParam->toLower('email');
                $this->arrForm = $objFormParam->getHashArray();
                $this->arrErr = $objFormParam->checkError();

                if (SC_Utils_Ex::isBlank($this->arrErr)) {
                    $this->errmsg = $this->lfProcessPasswordResetRequest($this->arrForm);
                    if (SC_Utils_Ex::isBlank($this->errmsg)) {
                        $this->tpl_mainpage = 'forgot/request_complete.tpl';
                    }
                }
                break;

            case 'reset':
                // トークン検証とパスワードリセット画面表示
                $token = $_GET['token'] ?? '';
                $token_data = SC_Helper_PasswordReset_Ex::validateToken($token);

                if ($token_data === null) {
                    $this->errmsg = 'このリンクは無効か期限切れです。';
                    $this->tpl_mainpage = 'forgot/error.tpl';
                } else {
                    $this->token = $token;
                    $this->email = $token_data['email'];
                    $this->tpl_mainpage = 'forgot/reset.tpl';
                }
                break;

            case 'complete':
                // パスワード変更実行
                $this->lfInitResetParam($objFormParam);
                $objFormParam->setParam($_POST);
                $objFormParam->convParam();
                $this->arrForm = $objFormParam->getHashArray();
                $this->arrErr = $objFormParam->checkError();

                if (SC_Utils_Ex::isBlank($this->arrErr)) {
                    $this->errmsg = $this->lfProcessPasswordReset($this->arrForm);
                    if (SC_Utils_Ex::isBlank($this->errmsg)) {
                        $this->tpl_mainpage = 'forgot/complete.tpl';
                        $this->tpl_onload .= 'opener.location.reload(true);';
                    } else {
                        $this->tpl_mainpage = 'forgot/reset.tpl';
                        $this->token = $this->arrForm['token'];
                    }
                } else {
                    $this->tpl_mainpage = 'forgot/reset.tpl';
                    $this->token = $this->arrForm['token'];
                }
                break;

            default:
                // デフォルトはリクエストフォーム表示
                $this->tpl_mainpage = 'forgot/index.tpl';
                break;
        }

        if ($this->device_type == DEVICE_TYPE_PC) {
            $this->setTemplate($this->tpl_mainpage);
        }
    }

    /**
     * リクエストフォームパラメータの初期化
     *
     * @param  SC_FormParam_Ex $objFormParam フォームパラメータークラス
     *
     * @return void
     */
    public function lfInitRequestParam(&$objFormParam)
    {
        if ($this->device_type === DEVICE_TYPE_MOBILE) {
            $objFormParam->addParam('メールアドレス', 'email', null, 'a', ['EXIST_CHECK', 'EMAIL_CHECK', 'NO_SPTAB', 'EMAIL_CHAR_CHECK', 'MOBILE_EMAIL_CHECK']);
        } else {
            $objFormParam->addParam('メールアドレス', 'email', null, 'a', ['NO_SPTAB', 'EXIST_CHECK', 'EMAIL_CHECK', 'SPTAB_CHECK', 'EMAIL_CHAR_CHECK']);
        }
    }

    /**
     * リセットフォームパラメータの初期化
     *
     * @param  SC_FormParam_Ex $objFormParam フォームパラメータークラス
     *
     * @return void
     */
    public function lfInitResetParam(&$objFormParam)
    {
        $objFormParam->addParam('トークン', 'token', null, 'a', ['EXIST_CHECK', 'ALNUM_CHECK']);
        $objFormParam->addParam('パスワード', 'password', PASSWORD_MAX_LEN, '', ['EXIST_CHECK', 'SPTAB_CHECK', 'ALNUM_CHECK'], PASSWORD_MIN_LEN);
        $objFormParam->addParam('パスワード(確認)', 'password02', PASSWORD_MAX_LEN, '', ['EXIST_CHECK', 'SPTAB_CHECK', 'ALNUM_CHECK'], PASSWORD_MIN_LEN);
    }

    /**
     * パスワードリセット要求処理
     *
     * @param  array $arrForm フォーム入力値
     *
     * @return string エラーメッセージ（成功時は空文字列）
     */
    public function lfProcessPasswordResetRequest($arrForm)
    {
        // レート制限チェック
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $rate_limit = SC_Helper_PasswordReset_Ex::checkRateLimit($arrForm['email'], $ip_address);

        if (!$rate_limit['allowed']) {
            GC_Utils_Ex::gfPrintLog('パスワードリセット要求: レート制限超過 email='.$arrForm['email'].' reason='.$rate_limit['reason']);

            return '短時間に複数のリクエストが送信されました。しばらく時間をおいてから再度お試しください。';
        }

        $objQuery = SC_Query_Ex::getSingletonInstance();

        // 顧客情報を取得（アカウント列挙攻撃対策: 存在チェックのみ）
        $where = '(email = ? OR email_mobile = ?) AND del_flg = 0';
        $arrVal = [$arrForm['email'], $arrForm['email']];
        $customer = $objQuery->getRow('customer_id, email, name01, name02, status', 'dtb_customer', $where, $arrVal);

        // 存在しない場合でも成功メッセージを表示（アカウント列挙攻撃対策）
        if (!$customer) {
            GC_Utils_Ex::gfPrintLog('パスワードリセット要求: 存在しないメールアドレス email='.$arrForm['email']);

            return ''; // エラーなし（ユーザーには成功として表示）
        }

        // 仮会員チェック
        if ($customer['status'] != '2') {
            GC_Utils_Ex::gfPrintLog('パスワードリセット要求: 仮会員 customer_id='.$customer['customer_id']);

            return 'ご入力のメールアドレスは現在仮登録中です。登録の際にお送りしたメールのURLにアクセスし、本会員登録をお願いします。';
        }

        // トークン生成と保存
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $token = SC_Helper_PasswordReset_Ex::createResetToken(
            $customer['email'],
            $customer['customer_id'],
            $ip_address,
            $user_agent
        );

        if ($token === null) {
            GC_Utils_Ex::gfPrintLog('パスワードリセット要求: トークン生成失敗 customer_id='.$customer['customer_id']);

            return 'システムエラーが発生しました。しばらく時間をおいてから再度お試しください。';
        }

        // リセットメール送信
        $mail_result = $this->lfSendResetMail($customer, $token);

        if (!$mail_result) {
            GC_Utils_Ex::gfPrintLog('パスワードリセット要求: メール送信失敗 customer_id='.$customer['customer_id']);
        // メール送信失敗でもユーザーには成功として表示（セキュリティ上の理由）
        } else {
            GC_Utils_Ex::gfPrintLog('パスワードリセット要求: 成功 customer_id='.$customer['customer_id']);
        }

        return ''; // エラーなし
    }

    /**
     * パスワードリセット実行
     *
     * @param  array $arrForm フォーム入力値
     *
     * @return string エラーメッセージ（成功時は空文字列）
     */
    public function lfProcessPasswordReset($arrForm)
    {
        // パスワード確認チェック
        if ($arrForm['password'] !== $arrForm['password02']) {
            return 'パスワードが一致しません。';
        }

        // トークン検証
        $token_data = SC_Helper_PasswordReset_Ex::validateToken($arrForm['token']);

        if ($token_data === null) {
            GC_Utils_Ex::gfPrintLog('パスワードリセット実行: 無効なトークン token='.$arrForm['token']);

            return 'このリンクは無効か期限切れです。最初からやり直してください。';
        }

        $objQuery = SC_Query_Ex::getSingletonInstance();

        // 顧客情報を再取得（削除済みチェック）
        $where = 'customer_id = ? AND del_flg = 0';
        $customer = $objQuery->getRow('customer_id, email, name01, name02, status', 'dtb_customer', $where, [$token_data['customer_id']]);

        if (!$customer) {
            GC_Utils_Ex::gfPrintLog('パスワードリセット実行: 削除済み顧客 customer_id='.$token_data['customer_id']);

            return 'このアカウントは削除されているため、パスワードをリセットできません。';
        }

        // 正会員チェック
        if ($customer['status'] != '2') {
            GC_Utils_Ex::gfPrintLog('パスワードリセット実行: 仮会員 customer_id='.$customer['customer_id']);

            return 'このアカウントは本会員登録が完了していないため、パスワードをリセットできません。';
        }

        $in_transaction = $objQuery->inTransaction();
        if (!$in_transaction) {
            $objQuery->begin();
        }
        try {
            // パスワード更新
            $sqlval = [];
            $sqlval['password'] = $arrForm['password'];
            SC_Helper_Customer_Ex::sfEditCustomerData($sqlval, $customer['customer_id']);

            // トークンを使用済みにマーク
            SC_Helper_PasswordReset_Ex::markTokenAsUsed($token_data['token_hash']);

            // 当該顧客の全トークンを無効化（セキュリティ強化）
            SC_Helper_PasswordReset_Ex::invalidateAllTokensForCustomer($customer['customer_id']);

            if (!$in_transaction) {
                $objQuery->commit();
            }

            GC_Utils_Ex::gfPrintLog('パスワードリセット実行: 成功 customer_id='.$customer['customer_id']);

            // 完了メール送信
            $this->lfSendCompleteMail($customer);

            return ''; // エラーなし
        } catch (Exception $e) {
            if (!$in_transaction) {
                $objQuery->rollback();
            }
            GC_Utils_Ex::gfPrintLog('パスワードリセット実行: 例外発生 customer_id='.$customer['customer_id'].' error='.$e->getMessage());

            return 'システムエラーが発生しました。しばらく時間をおいてから再度お試しください。';
        }
    }

    /**
     * トークンリンクメール送信
     *
     * @param  array  $customer 顧客情報
     * @param  string $token    リセットトークン
     *
     * @return bool 送信成功時true
     */
    public function lfSendResetMail($customer, $token)
    {
        try {
            $objDb = new SC_Helper_DB_Ex();
            $CONF = $objDb->sfGetBasisData();

            // リセットURL生成
            $reset_url = SC_Utils_Ex::sfIsHTTPS() ? HTTPS_URL : HTTP_URL;
            $reset_url .= 'forgot/index.php?mode=reset&token='.$token;

            // メール本文生成
            $objMailText = new SC_SiteView_Ex();
            $objMailText->setPage($this);
            $objMailText->assign('customer_name', $customer['name01'].' '.$customer['name02']);
            $objMailText->assign('reset_url', $reset_url);
            $objMailText->assign('expire_hours', PASSWORD_RESET_TOKEN_EXPIRE_HOURS);
            $objMailText->assign('shop_name', $CONF['shop_name']);
            $objMailText->assign('zip01', $CONF['zip01']);
            $objMailText->assign('zip02', $CONF['zip02']);
            $objMailText->assign('pref', $CONF['pref']);
            $objMailText->assign('addr01', $CONF['addr01']);
            $objMailText->assign('addr02', $CONF['addr02']);
            $objMailText->assign('tel01', $CONF['tel01']);
            $objMailText->assign('tel02', $CONF['tel02']);
            $objMailText->assign('tel03', $CONF['tel03']);
            $objMailText->assign('fax01', $CONF['fax01']);
            $objMailText->assign('fax02', $CONF['fax02']);
            $objMailText->assign('fax03', $CONF['fax03']);
            $toCustomerMail = $objMailText->fetch('mail_templates/password_reset_mail.tpl');

            $objHelperMail = new SC_Helper_Mail_Ex();
            $objHelperMail->setPage($this);

            // メール送信
            $objMail = new SC_SendMail_Ex();
            $objMail->setItem(
                '',
                $objHelperMail->sfMakeSubject('パスワード再設定のご案内'),
                $toCustomerMail,
                $CONF['email03'],
                $CONF['shop_name'],
                $CONF['email03'],
                $CONF['email04'],
                $CONF['email04']
            );
            $objMail->setTo($customer['email'], $customer['name01'].' '.$customer['name02'].' 様');
            $objMail->sendMail();

            return true;
        } catch (Exception $e) {
            GC_Utils_Ex::gfPrintLog('リセットメール送信失敗: '.$e->getMessage());

            return false;
        }
    }

    /**
     * 変更完了メール送信
     *
     * @param  array $customer 顧客情報
     *
     * @return bool 送信成功時true
     */
    public function lfSendCompleteMail($customer)
    {
        try {
            $objDb = new SC_Helper_DB_Ex();
            $CONF = $objDb->sfGetBasisData();

            // メール本文生成
            $objMailText = new SC_SiteView_Ex();
            $objMailText->setPage($this);
            $objMailText->assign('customer_name', $customer['name01'].' '.$customer['name02']);
            $objMailText->assign('change_date', date('Y/m/d H:i'));
            $objMailText->assign('shop_email', $CONF['email02']);
            $login_url = SC_Utils_Ex::sfIsHTTPS() ? HTTPS_URL : HTTP_URL;
            $login_url .= 'mypage/login.php';
            $objMailText->assign('login_url', $login_url);
            $objMailText->assign('shop_name', $CONF['shop_name']);
            $objMailText->assign('zip01', $CONF['zip01']);
            $objMailText->assign('zip02', $CONF['zip02']);
            $objMailText->assign('pref', $CONF['pref']);
            $objMailText->assign('addr01', $CONF['addr01']);
            $objMailText->assign('addr02', $CONF['addr02']);
            $objMailText->assign('tel01', $CONF['tel01']);
            $objMailText->assign('tel02', $CONF['tel02']);
            $objMailText->assign('tel03', $CONF['tel03']);
            $objMailText->assign('fax01', $CONF['fax01']);
            $objMailText->assign('fax02', $CONF['fax02']);
            $objMailText->assign('fax03', $CONF['fax03']);
            $toCustomerMail = $objMailText->fetch('mail_templates/password_reset_complete_mail.tpl');

            $objHelperMail = new SC_Helper_Mail_Ex();
            $objHelperMail->setPage($this);

            // メール送信
            $objMail = new SC_SendMail_Ex();
            $objMail->setItem(
                '',
                $objHelperMail->sfMakeSubject('パスワード変更完了のお知らせ'),
                $toCustomerMail,
                $CONF['email03'],
                $CONF['shop_name'],
                $CONF['email03'],
                $CONF['email04'],
                $CONF['email04']
            );
            $objMail->setTo($customer['email'], $customer['name01'].' '.$customer['name02'].' 様');
            $objMail->sendMail();

            return true;
        } catch (Exception $e) {
            GC_Utils_Ex::gfPrintLog('完了メール送信失敗: '.$e->getMessage());

            return false;
        }
    }
}
