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
 * 会員管理クラス
 */
class SC_Customer
{
    /**
     * 会員情報
     *
     * @var array
     */
    public $customer_data;

    /**
     * メールアドレスとパスワードを使用して認証結果を返す.
     *
     * 認証に成功した場合は SC_Customer::customer_data に会員情報を格納する
     *
     * @param string $email メールアドレス
     * @param string $pass パスワード
     * @param bool $mobile email_mobile も検索対象とする場合 true
     *
     * @return bool
     */
    public function getCustomerDataFromEmailPass($pass, $email, $mobile = false)
    {
        // 小文字に変換
        $email = strtolower($email);
        $sql_mobile = $mobile ? ' OR email_mobile = ?' : '';
        $arrValues = [$email];
        if ($mobile) {
            $arrValues[] = $email;
        }
        // 本登録された会員のみ
        $sql = 'SELECT * FROM dtb_customer WHERE (email = ?'.$sql_mobile.') AND del_flg = 0 AND status = 2';
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $result = $objQuery->getAll($sql, $arrValues);
        if (empty($result)) {
            return false;
        } else {
            $data = $result[0];
        }

        // パスワードが合っていれば会員情報をcustomer_dataにセットしてtrueを返す
        if (SC_Utils_Ex::sfIsMatchHashPassword($pass, $data['password'], $data['salt'])) {
            $this->customer_data = $data;
            $this->startSession();

            return true;
        }

        return false;
    }

    /**
     * 携帯端末IDが一致する会員が存在するかどうかをチェックする。
     * FIXME
     *
     * @return bool 該当する会員が存在する場合は true、それ以外の場合
     *                 は false を返す。
     *
     * @deprecated
     */
    public function checkMobilePhoneId()
    {
        // docomo用にデータを取り出す。
        if (SC_MobileUserAgent_Ex::getCarrier() == 'docomo') {
            if ($_SESSION['mobile']['phone_id'] == '' && strlen($_SESSION['mobile']['phone_id']) == 0) {
                $_SESSION['mobile']['phone_id'] = SC_MobileUserAgent_Ex::getId();
            }
        }
        if (!isset($_SESSION['mobile']['phone_id']) || $_SESSION['mobile']['phone_id'] === false) {
            return false;
        }

        // 携帯端末IDが一致し、本登録された会員を検索する。
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $exists = $objQuery->exists('dtb_customer', 'mobile_phone_id = ? AND del_flg = 0 AND status = 2', [$_SESSION['mobile']['phone_id']]);

        return $exists;
    }

    /**
     * 携帯端末IDを使用して会員を検索し、パスワードの照合を行う。
     * パスワードが合っている場合は会員情報を取得する。
     *
     * @param  string  $pass パスワード
     *
     * @return bool 該当する会員が存在し、パスワードが合っている場合は true、
     *                 それ以外の場合は false を返す。
     *
     * @deprecated
     */
    public function getCustomerDataFromMobilePhoneIdPass($pass)
    {
        // docomo用にデータを取り出す。
        if (SC_MobileUserAgent_Ex::getCarrier() == 'docomo') {
            if ($_SESSION['mobile']['phone_id'] == '' && strlen($_SESSION['mobile']['phone_id']) == 0) {
                $_SESSION['mobile']['phone_id'] = SC_MobileUserAgent_Ex::getId();
            }
        }
        if (!isset($_SESSION['mobile']['phone_id']) || $_SESSION['mobile']['phone_id'] === false) {
            return false;
        }

        // 携帯端末IDが一致し、本登録された会員を検索する。
        $sql = 'SELECT * FROM dtb_customer WHERE mobile_phone_id = ? AND del_flg = 0 AND status = 2';
        $objQuery = SC_Query_Ex::getSingletonInstance();
        @[$data] = $objQuery->getAll($sql, [$_SESSION['mobile']['phone_id']]);

        // パスワードが合っている場合は、会員情報をcustomer_dataに格納してtrueを返す。
        if (SC_Utils_Ex::sfIsMatchHashPassword($pass, $data['password'], $data['salt'])) {
            $this->customer_data = $data;
            $this->startSession();

            return true;
        }

        return false;
    }

    /**
     * 携帯端末IDを登録する。
     *
     * @return void
     *
     * @deprecated
     */
    public function updateMobilePhoneId()
    {
        if (!isset($_SESSION['mobile']['phone_id']) || $_SESSION['mobile']['phone_id'] === false) {
            return;
        }

        if ($this->customer_data['mobile_phone_id'] == $_SESSION['mobile']['phone_id']) {
            return;
        }

        $objQuery = SC_Query_Ex::getSingletonInstance();
        $sqlval = ['mobile_phone_id' => $_SESSION['mobile']['phone_id']];
        $where = 'customer_id = ? AND del_flg = 0 AND status = 2';
        $objQuery->update('dtb_customer', $sqlval, $where, [$this->customer_data['customer_id']]);

        $this->customer_data['mobile_phone_id'] = $_SESSION['mobile']['phone_id'];
    }

    /**
     * パスワードを確認せずにログイン
     *
     * @param string $email メールアドレス
     */
    public function setLogin($email)
    {
        // 本登録された会員のみ
        $sql = 'SELECT * FROM dtb_customer WHERE (email = ? OR email_mobile = ?) AND del_flg = 0 AND status = 2';
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $result = $objQuery->getAll($sql, [$email, $email]);
        $data = $result[0] ?? [];
        $this->customer_data = $data;
        $this->startSession();
    }

    /**
     * セッション情報を最新の情報に更新する
     */
    public function updateSession()
    {
        $sql = 'SELECT * FROM dtb_customer WHERE customer_id = ? AND del_flg = 0';
        $customer_id = $this->getValue('customer_id');
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrRet = $objQuery->getAll($sql, [$customer_id]);
        $this->customer_data = $arrRet[0] ?? [];
        $_SESSION['customer'] = $this->customer_data;
    }

    /**
     * ログイン情報をセッションに登録し、ログに書き込む
     */
    public function startSession()
    {
        $_SESSION['customer'] = $this->customer_data;
        // セッション情報の保存
        GC_Utils_Ex::gfPrintLog('access : user='.($this->customer_data['customer_id'] ?? '')."\t".'ip='.$this->getRemoteHost(), CUSTOMER_LOG_REALFILE, false);
    }

    /**
     * ログアウト　$_SESSION['customer']を解放し、ログに書き込む
     */
    public function EndSession()
    {
        // セッション情報破棄の前にcustomer_idを保存
        $customer_id = $_SESSION['customer']['customer_id'];

        // $_SESSION['customer']の解放
        unset($_SESSION['customer']);
        // セッションの配送情報を全て破棄する
        SC_Helper_Purchase_Ex::unsetAllShippingTemp(true);
        // トランザクショントークンの破棄
        SC_Helper_Session_Ex::destroyToken();
        $objSiteSess = new SC_SiteSession_Ex();
        $objSiteSess->unsetUniqId();

        // ログに記録する
        $log = sprintf(
            "logout : user=%d\tip=%s",
            $customer_id,
            $this->getRemoteHost()
        );
        GC_Utils_Ex::gfPrintLog($log, CUSTOMER_LOG_REALFILE, false);
    }

    /**
     * ログインに成功しているか判定する。
     *
     * @param bool $dont_check_email_mobile
     *
     * @return bool ログインに成功している場合は true
     */
    public function isLoginSuccess($dont_check_email_mobile = false)
    {
        // ログイン時のメールアドレスとDBのメールアドレスが一致している場合
        if (isset($_SESSION['customer']['customer_id'])
            && SC_Utils_Ex::sfIsInt($_SESSION['customer']['customer_id'])
        ) {
            $objQuery = SC_Query_Ex::getSingletonInstance();
            $email = $objQuery->get('email', 'dtb_customer', 'customer_id = ?', [$_SESSION['customer']['customer_id']]);
            if ($email == $_SESSION['customer']['email']) {
                // モバイルサイトの場合は携帯のメールアドレスが登録されていることもチェックする。
                // ただし $dont_check_email_mobile が true の場合はチェックしない。
                if (SC_Display_Ex::detectDevice() == DEVICE_TYPE_MOBILE && !$dont_check_email_mobile) {
                    $email_mobile = $objQuery->get('email_mobile', 'dtb_customer', 'customer_id = ?', [$_SESSION['customer']['customer_id']]);

                    return isset($email_mobile);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * パラメーターの取得
     *
     * @param string $keyname パラメーターのキー名
     *
     * @return string|int|null パラメータの値
     */
    public function getValue($keyname)
    {
        // ポイントはリアルタイム表示
        if ($keyname == 'point') {
            $point = 0;
            if (isset($_SESSION['customer']['customer_id'])) {
                $objQuery = SC_Query_Ex::getSingletonInstance();
                $point = $objQuery->get('point', 'dtb_customer', 'customer_id = ?', [$_SESSION['customer']['customer_id']]);
                $_SESSION['customer']['point'] = $point;
            }

            return $point;
        } else {
            return $_SESSION['customer'][$keyname] ?? '';
        }
    }

    /**
     * パラメーターを配列で取得する
     *
     * @return array パラメータの値の配列
     */
    public function getValues()
    {
        if (!isset($_SESSION['customer']) || !is_array($_SESSION['customer'])) {
            throw new Exception();
        }

        return $_SESSION['customer'];
    }

    /**
     * パラメーターのセット
     *
     * @param string $keyname
     * @param string $val
     */
    public function setValue($keyname, $val)
    {
        $_SESSION['customer'][$keyname] = $val;
    }

    /**
     * パラメーターがNULLかどうかの判定
     *
     * @param string $keyname
     *
     * @return bool
     */
    public function hasValue($keyname)
    {
        if (isset($_SESSION['customer'][$keyname])) {
            return !SC_Utils_Ex::isBlank($_SESSION['customer'][$keyname]);
        }

        return false;
    }

    /**
     * 誕生日月であるかどうかの判定
     *
     * @return bool
     */
    public function isBirthMonth()
    {
        if (isset($_SESSION['customer']['birth'])) {
            $arrRet = preg_split('|[- :/]|', $_SESSION['customer']['birth']);
            $birth_month = (int) $arrRet[1];
            $now_month = (int) date('m');

            if ($birth_month == $now_month) {
                return true;
            }
        }

        return false;
    }

    /**
     * $_SERVER['REMOTE_HOST'] または $_SERVER['REMOTE_ADDR'] を返す.
     *
     * $_SERVER['REMOTE_HOST'] が取得できない場合は $_SERVER['REMOTE_ADDR']
     * を返す.
     *
     * @return string $_SERVER['REMOTE_HOST'] 又は $_SERVER['REMOTE_ADDR']の文字列
     */
    public function getRemoteHost()
    {
        if (!empty($_SERVER['REMOTE_HOST'])) {
            return $_SERVER['REMOTE_HOST'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        } else {
            return '';
        }
    }

    /**
     * 受注関連の会員情報を更新
     *
     * @param int $customer_id
     */
    public static function updateOrderSummary($customer_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $col = <<< __EOS__
            SUM( payment_total) AS buy_total,
            COUNT(order_id) AS buy_times,
            MAX( create_date) AS last_buy_date,
            MIN(create_date) AS first_buy_date
            __EOS__;
        $table = 'dtb_order';
        $where = 'customer_id = ? AND del_flg = 0 AND status <> ?';
        $arrWhereVal = [$customer_id, ORDER_CANCEL];
        $arrOrderSummary = $objQuery->getRow($col, $table, $where, $arrWhereVal);

        $objQuery->update('dtb_customer', $arrOrderSummary, 'customer_id = ?', [$customer_id]);
    }

    /**
     * ログインを実行する.
     *
     * ログインを実行し, 成功した場合はユーザー情報をセッションに格納し,
     * true を返す.
     * モバイル端末の場合は, 携帯端末IDを保存する.
     * ログインに失敗した場合は, false を返す.
     *
     * @param  string  $login_email ログインメールアドレス
     * @param  string  $login_pass  ログインパスワード
     *
     * @return bool ログインに成功した場合 true; 失敗した場合 false
     */
    public function doLogin($login_email, $login_pass)
    {
        switch (SC_Display_Ex::detectDevice()) {
            case DEVICE_TYPE_MOBILE:
                if (!$this->getCustomerDataFromMobilePhoneIdPass($login_pass)
                    && !$this->getCustomerDataFromEmailPass($login_pass, $login_email, true)
                ) {
                    return false;
                } else {
                    // Session Fixation対策
                    SC_Session_Ex::regenerateSID();

                    $this->updateMobilePhoneId();

                    return true;
                }
                break;

            case DEVICE_TYPE_SMARTPHONE:
            case DEVICE_TYPE_PC:
            default:
                if (!$this->getCustomerDataFromEmailPass($login_pass, $login_email)) {
                    return false;
                } else {
                    // Session Fixation対策
                    SC_Session_Ex::regenerateSID();

                    return true;
                }
                break;
        }
    }
}
