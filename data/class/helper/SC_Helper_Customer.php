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
 * 会員情報の登録・編集・検索ヘルパークラス.
 *
 * @author Hirokazu Fukuda
 *
 * @version $Id$
 */
class SC_Helper_Customer
{
    /**
     * 会員情報の登録・編集処理を行う.
     *
     * @param array $arrData     登録するデータの配列（SC_FormParamのgetDbArrayの戻り値）
     * @param array $customer_id nullの場合はinsert, 存在する場合はupdate
     *
     * @return int 登録編集したユーザーのcustomer_id
     */
    public static function sfEditCustomerData($arrData, $customer_id = null)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();

        $old_version_flag = false;

        $arrData['update_date'] = 'CURRENT_TIMESTAMP';    // 更新日

        // salt値の生成(insert時)または取得(update時)。
        if (is_numeric($customer_id)) {
            $salt = $objQuery->get('salt', 'dtb_customer', 'customer_id = ? ', [$customer_id]);

            // 旧バージョン(2.11未満)からの移行を考慮
            if (strlen($salt) === 0) {
                $old_version_flag = true;
            }
        } else {
            $salt = SC_Utils_Ex::sfGetRandomString(10);
            $arrData['salt'] = $salt;
        }
        $is_password_updated = false;
        // -- パスワードの更新がある場合は暗号化
        if (!isset($arrData['password']) || $arrData['password'] == '' || $arrData['password'] == DEFAULT_PASSWORD) {
            // 更新しない
            unset($arrData['password']);
        } else {
            // 旧バージョン(2.11未満)からの移行を考慮
            if ($old_version_flag) {
                $is_password_updated = true;
                $salt = SC_Utils_Ex::sfGetRandomString(10);
                $arrData['salt'] = $salt;
            }

            $arrData['password'] = SC_Utils_Ex::sfGetHashString($arrData['password'], $salt);
        }
        // -- 秘密の質問の更新がある場合は暗号化
        if (!isset($arrData['reminder_answer']) || $arrData['reminder_answer'] == '' || $arrData['reminder_answer'] == DEFAULT_PASSWORD) {
            // 更新しない
            unset($arrData['reminder_answer']);

            // 旧バージョン(2.11未満)からの移行を考慮
            if ($old_version_flag && $is_password_updated) {
                // パスワードが更新される場合は、平文になっている秘密の質問を暗号化する
                $reminder_answer = $objQuery->get('reminder_answer', 'dtb_customer', 'customer_id = ? ', [$customer_id]);
                $arrData['reminder_answer'] = SC_Utils_Ex::sfGetHashString($reminder_answer, $salt);
            }
        } else {
            // 旧バージョン(2.11未満)からの移行を考慮
            if ($old_version_flag && !$is_password_updated) {
                // パスワードが更新されない場合は、平文のままにする
                unset($arrData['salt']);
            } else {
                $arrData['reminder_answer'] = SC_Utils_Ex::sfGetHashString($arrData['reminder_answer'], $salt);
            }
        }

        // デフォルト国IDを追加
        if (FORM_COUNTRY_ENABLE == false) {
            $arrData['country_id'] = DEFAULT_COUNTRY_ID;
        }

        // -- 編集登録実行
        if (is_numeric($customer_id)) {
            // 編集
            $objQuery->update('dtb_customer', $arrData, 'customer_id = ? ', [$customer_id]);
        } else {
            // 新規登録

            // 会員ID
            $customer_id = $objQuery->nextVal('dtb_customer_customer_id');
            $arrData['customer_id'] = $customer_id;
            // 作成日
            if (!isset($arrData['create_date'])) {
                $arrData['create_date'] = 'CURRENT_TIMESTAMP';
            }
            $objQuery->insert('dtb_customer', $arrData);
        }

        $objQuery->commit();

        return $customer_id;
    }

    /**
     * 注文番号、利用ポイント、加算ポイントから最終ポイントを取得する.
     *
     * @param  int $order_id  注文番号
     * @param  int $use_point 利用ポイント
     * @param  int $add_point 加算ポイント
     *
     * @return array   最終ポイントの配列
     */
    public static function sfGetCustomerPoint($order_id, $use_point, $add_point)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $arrRet = $objQuery->select('customer_id', 'dtb_order', 'order_id = ?', [$order_id]);
        $customer_id = $arrRet[0]['customer_id'];
        if ($customer_id != '' && $customer_id >= 1) {
            if (USE_POINT !== false) {
                $arrRet = $objQuery->select('point', 'dtb_customer', 'customer_id = ?', [$customer_id]);
                $point = $arrRet[0]['point'];
                $total_point = $arrRet[0]['point'] - $use_point + $add_point;
            } else {
                $total_point = 0;
                $point = 0;
            }
        } else {
            $total_point = '';
            $point = '';
        }

        return [$point, $total_point];
    }

    /**
     * emailアドレスから、登録済み会員や退会済み会員をチェックする
     *
     * XXX SC_CheckError からしか呼び出されず, 本クラスの中で SC_CheckError を呼び出している
     *
     * @param  string  $email メールアドレス
     *
     * @return int 0:登録可能     1:登録済み   2:再登録制限期間内削除ユーザー  3:自分のアドレス
     */
    public static function sfCheckRegisterUserFromEmail($email)
    {
        $objCustomer = new SC_Customer_Ex();
        $objQuery = SC_Query_Ex::getSingletonInstance();

        // ログインしている場合、すでに登録している自分のemailの場合
        if ($objCustomer->isLoginSuccess(true)
            && SC_Helper_Customer_Ex::sfCustomerEmailDuplicationCheck($objCustomer->getValue('customer_id'), $email)) {
            // 自分のアドレス
            return 3;
        }

        $arrRet = $objQuery->select(
            'email, update_date, del_flg',
            'dtb_customer',
            'email = ? OR email_mobile = ? ORDER BY del_flg',
            [$email, $email]
        );

        if (count($arrRet) > 0) {
            // 会員である場合
            if ($arrRet[0]['del_flg'] != '1') {
                // 登録済み
                return 1;
            } else {
                // 退会した会員である場合
                $leave_time = SC_Utils_Ex::sfDBDatetoTime($arrRet[0]['update_date']);
                $now_time = time();
                $pass_time = $now_time - $leave_time;
                // 退会から何時間-経過しているか判定する。
                $limit_time = ENTRY_LIMIT_HOUR * 3600;
                if ($pass_time < $limit_time) {
                    // 再登録制限期間内削除ユーザー
                    return 2;
                }
            }
        }

        // 登録可能
        return 0;
    }

    /**
     * ログイン時メールアドレス重複チェック.
     *
     * 会員の保持する email, mobile_email が, 引数 $email と一致するかチェックする
     *
     * @param  int $customer_id チェック対象会員の会員ID
     * @param  string  $email       チェック対象のメールアドレス
     *
     * @return bool メールアドレスが重複する場合 true
     */
    public static function sfCustomerEmailDuplicationCheck($customer_id, $email)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $arrResults = $objQuery->getRow(
            'email, email_mobile',
            'dtb_customer',
            'customer_id = ?',
            [$customer_id]
        );
        $return
            = strlen($arrResults['email']) >= 1 && $email === $arrResults['email']
            || strlen($arrResults['email_mobile']) >= 1 && $email === $arrResults['email_mobile']
        ;

        return $return;
    }

    /**
     * customer_idから会員情報を取得する
     *
     * @param mixed $customer_id
     * @param bool $mask_flg
     *
     * @return array 会員情報の配列を返す
     */
    public static function sfGetCustomerData($customer_id, $mask_flg = true)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        // 会員情報DB取得
        $ret = $objQuery->select('*', 'dtb_customer', 'customer_id=? AND del_flg = 0', [$customer_id]);

        if (empty($ret)) {
            trigger_error('存在しない会員IDです。', E_USER_ERROR);
        }

        $arrForm = $ret[0];

        // 確認項目に複製
        $arrForm['email02'] = $arrForm['email'];
        $arrForm['email_mobile02'] = $arrForm['email_mobile'];

        // 誕生日を年月日に分ける
        if (isset($arrForm['birth'])) {
            $birth = explode(' ', $arrForm['birth']);
            [$arrForm['year'], $arrForm['month'], $arrForm['day']] = array_map('intval', explode('-', $birth[0]));
        }

        if ($mask_flg) {
            $arrForm['password'] = DEFAULT_PASSWORD;
            $arrForm['password02'] = DEFAULT_PASSWORD;
            $arrForm['reminder_answer'] = DEFAULT_PASSWORD;
        }

        return $arrForm;
    }

    /**
     * 会員ID指定またはwhere条件指定での会員情報取得(単一行データ)
     *
     * TODO: sfGetCustomerDataと統合したい
     *
     * @param int $customer_id 会員ID (指定無しでも構わないが、Where条件を入れる事)
     * @param string  $add_where   追加WHERE条件
     * @param string[]   $arrAddVal   追加WHEREパラメーター
     *
     * @return array 対象会員データ
     */
    public static function sfGetCustomerDataFromId($customer_id, $add_where = '', $arrAddVal = [])
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        if ($add_where == '') {
            $where = 'customer_id = ? AND del_flg = 0';
            $arrData = $objQuery->getRow('*', 'dtb_customer', $where, [$customer_id]);
        } else {
            $where = $add_where;
            if (SC_Utils_Ex::sfIsInt($customer_id)) {
                $where .= ' AND customer_id = ?';
                $arrAddVal[] = $customer_id;
            }
            $arrData = $objQuery->getRow('*', 'dtb_customer', $where, $arrAddVal);
        }

        return $arrData;
    }

    /**
     * 重複しない会員登録キーを発行する。
     *
     * @return string 会員登録キーの文字列
     */
    public static function sfGetUniqSecretKey()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        do {
            $uniqid = SC_Utils_Ex::sfGetUniqRandomId('r');
            $exists = $objQuery->exists('dtb_customer', 'secret_key = ?', [$uniqid]);
        } while ($exists);

        return $uniqid;
    }

    /**
     * 会員登録キーから会員IDを取得する.
     *
     * @param string  $uniqid       会員登録キー
     * @param bool $check_status 本会員のみを対象とするか
     *
     * @return int 会員ID
     */
    public static function sfGetCustomerId($uniqid, $check_status = false)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $where = 'secret_key = ?';

        if ($check_status) {
            $where .= ' AND status = 1 AND del_flg = 0';
        }

        return $objQuery->get('customer_id', 'dtb_customer', $where, [$uniqid]);
    }

    /**
     * 会員登録時フォーム初期化
     *
     * @param SC_FormParam $objFormParam SC_FormParam インスタンス
     * @param bool      $isAdmin      true:管理者画面 false:会員向け
     *
     * @return void
     */
    public static function sfCustomerEntryParam(&$objFormParam, $isAdmin = false)
    {
        SC_Helper_Customer_Ex::sfCustomerCommonParam($objFormParam);
        SC_Helper_Customer_Ex::sfCustomerRegisterParam($objFormParam, $isAdmin);
        if ($isAdmin) {
            $objFormParam->addParam('会員ID', 'customer_id', INT_LEN, 'n', ['NUM_CHECK']);
            $objFormParam->addParam('携帯メールアドレス', 'email_mobile', null, 'a', ['NO_SPTAB', 'EMAIL_CHECK', 'SPTAB_CHECK', 'EMAIL_CHAR_CHECK', 'MOBILE_EMAIL_CHECK']);
            $objFormParam->addParam('会員状態', 'status', INT_LEN, 'n', ['EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);
            $objFormParam->addParam('SHOP用メモ', 'note', LTEXT_LEN, 'KVa', ['MAX_LENGTH_CHECK']);
            $objFormParam->addParam('所持ポイント', 'point', INT_LEN, 'n', ['EXIST_CHECK', 'NUM_CHECK'], 0);
        }

        if (SC_Display_Ex::detectDevice() == DEVICE_TYPE_MOBILE) {
            // 登録確認画面の「戻る」ボタンのためのパラメーター
            $objFormParam->addParam('戻る', 'return', '', '', [], '', false);
        }
    }

    /**
     * 会員情報変更フォーム初期化
     *
     * @param SC_FormParam $objFormParam SC_FormParam インスタンス
     *
     * @return void
     */
    public static function sfCustomerMypageParam(&$objFormParam)
    {
        SC_Helper_Customer_Ex::sfCustomerCommonParam($objFormParam);
        SC_Helper_Customer_Ex::sfCustomerRegisterParam($objFormParam, false, true);
        if (SC_Display_Ex::detectDevice() !== DEVICE_TYPE_MOBILE) {
            $objFormParam->addParam('携帯メールアドレス', 'email_mobile', null, 'a', ['NO_SPTAB', 'EMAIL_CHECK', 'SPTAB_CHECK', 'EMAIL_CHAR_CHECK', 'MOBILE_EMAIL_CHECK']);
            $objFormParam->addParam('携帯メールアドレス(確認)', 'email_mobile02', null, 'a', ['NO_SPTAB', 'EMAIL_CHECK', 'SPTAB_CHECK', 'EMAIL_CHAR_CHECK', 'MOBILE_EMAIL_CHECK'], '', false);
        } else {
            $objFormParam->addParam('携帯メールアドレス', 'email_mobile', null, 'a', ['EXIST_CHECK', 'NO_SPTAB', 'EMAIL_CHECK', 'SPTAB_CHECK', 'EMAIL_CHAR_CHECK', 'MOBILE_EMAIL_CHECK']);
            $objFormParam->addParam('メールアドレス', 'email', null, 'a', ['NO_SPTAB', 'EMAIL_CHECK', 'SPTAB_CHECK', 'EMAIL_CHAR_CHECK']);
        }
    }

    /**
     * 会員・顧客・お届け先共通
     *
     * @param SC_FormParam $objFormParam SC_FormParam インスタンス
     * @param string       $prefix       キー名にprefixを付ける場合に指定
     *
     * @return void
     */
    public static function sfCustomerCommonParam(&$objFormParam, $prefix = '')
    {
        $objFormParam->addParam('お名前(姓)', $prefix.'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('お名前(名)', $prefix.'name02', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('会社名', $prefix.'company_name', STEXT_LEN, 'aKV', ['MAX_LENGTH_CHECK', 'SPTAB_CHECK']);
        if (FORM_COUNTRY_ENABLE === false) {
            $objFormParam->addParam('お名前(フリガナ・姓)', $prefix.'kana01', STEXT_LEN, 'CKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK', 'KANA_CHECK']);
            $objFormParam->addParam('お名前(フリガナ・名)', $prefix.'kana02', STEXT_LEN, 'CKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK', 'KANA_CHECK']);
            $objFormParam->addParam('郵便番号1', $prefix.'zip01', ZIP01_LEN, 'n', ['EXIST_CHECK', 'SPTAB_CHECK', 'NUM_CHECK', 'NUM_COUNT_CHECK']);
            $objFormParam->addParam('郵便番号2', $prefix.'zip02', ZIP02_LEN, 'n', ['EXIST_CHECK', 'SPTAB_CHECK', 'NUM_CHECK', 'NUM_COUNT_CHECK']);
            $objFormParam->addParam('国', $prefix.'country_id', INT_LEN, 'n', ['NUM_CHECK']);
            $objFormParam->addParam('都道府県', $prefix.'pref', INT_LEN, 'n', ['EXIST_CHECK', 'NUM_CHECK', 'PREF_CHECK']);
        } else {
            $objFormParam->addParam('お名前(フリガナ・姓)', $prefix.'kana01', STEXT_LEN, 'CKV', ['NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK', 'KANA_CHECK']);
            $objFormParam->addParam('お名前(フリガナ・名)', $prefix.'kana02', STEXT_LEN, 'CKV', ['NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK', 'KANA_CHECK']);
            $objFormParam->addParam('郵便番号1', $prefix.'zip01', ZIP01_LEN, 'n', ['SPTAB_CHECK', 'NUM_CHECK', 'NUM_COUNT_CHECK']);
            $objFormParam->addParam('郵便番号2', $prefix.'zip02', ZIP02_LEN, 'n', ['SPTAB_CHECK', 'NUM_CHECK', 'NUM_COUNT_CHECK']);
            $objFormParam->addParam('国', $prefix.'country_id', INT_LEN, 'n', ['EXIST_CHECK', 'NUM_CHECK']);
            $objFormParam->addParam('ZIPCODE', $prefix.'zipcode', STEXT_LEN, 'n', ['NO_SPTAB', 'SPTAB_CHECK', 'GRAPH_CHECK', 'MAX_LENGTH_CHECK']);
            $objFormParam->addParam('都道府県', $prefix.'pref', INT_LEN, 'n', ['NUM_CHECK']);
        }
        $objFormParam->addParam('住所1', $prefix.'addr01', MTEXT_LEN, 'aKV', ['EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('住所2', $prefix.'addr02', MTEXT_LEN, 'aKV', ['EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('お電話番号1', $prefix.'tel01', TEL_ITEM_LEN, 'n', ['EXIST_CHECK', 'SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('お電話番号2', $prefix.'tel02', TEL_ITEM_LEN, 'n', ['EXIST_CHECK', 'SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('お電話番号3', $prefix.'tel03', TEL_ITEM_LEN, 'n', ['EXIST_CHECK', 'SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('FAX番号1', $prefix.'fax01', TEL_ITEM_LEN, 'n', ['SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('FAX番号2', $prefix.'fax02', TEL_ITEM_LEN, 'n', ['SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('FAX番号3', $prefix.'fax03', TEL_ITEM_LEN, 'n', ['SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);
    }

    /**
     * 会員登録共通
     *
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     * @param  bool      $isAdmin      true:管理者画面 false:会員向け
     * @param  bool      $is_mypage    マイページの場合 true
     * @param  string       $prefix       キー名にprefixを付ける場合に指定
     *
     * @return void
     */
    public static function sfCustomerRegisterParam(&$objFormParam, $isAdmin = false, $is_mypage = false, $prefix = '')
    {
        $objFormParam->addParam('パスワード', $prefix.'password', PASSWORD_MAX_LEN, '', ['EXIST_CHECK', 'SPTAB_CHECK', 'PASSWORD_CHAR_CHECK']);
        $objFormParam->addParam('パスワード確認用の質問の答え', $prefix.'reminder_answer', STEXT_LEN, '', ['EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('パスワード確認用の質問', $prefix.'reminder', STEXT_LEN, 'n', ['EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('性別', $prefix.'sex', INT_LEN, 'n', ['EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('職業', $prefix.'job', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('年', $prefix.'year', 4, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK'], '', false);
        $objFormParam->addParam('月', $prefix.'month', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK'], '', false);
        $objFormParam->addParam('日', $prefix.'day', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK'], '', false);

        $objFormParam->addParam('メールマガジン', $prefix.'mailmaga_flg', INT_LEN, 'n', ['EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);

        if (SC_Display_Ex::detectDevice() !== DEVICE_TYPE_MOBILE) {
            $objFormParam->addParam('メールアドレス', $prefix.'email', null, 'a', ['NO_SPTAB', 'EXIST_CHECK', 'EMAIL_CHECK', 'SPTAB_CHECK', 'EMAIL_CHAR_CHECK']);
            $objFormParam->addParam('パスワード(確認)', $prefix.'password02', PASSWORD_MAX_LEN, '', ['EXIST_CHECK', 'SPTAB_CHECK', 'PASSWORD_CHAR_CHECK'], '', false);
            if (!$isAdmin) {
                $objFormParam->addParam('メールアドレス(確認)', $prefix.'email02', null, 'a', ['NO_SPTAB', 'EXIST_CHECK', 'EMAIL_CHECK', 'SPTAB_CHECK', 'EMAIL_CHAR_CHECK'], '', false);
            }
        } else {
            if (!$is_mypage) {
                $objFormParam->addParam('メールアドレス', $prefix.'email', null, 'a', ['EXIST_CHECK', 'EMAIL_CHECK', 'NO_SPTAB', 'EMAIL_CHAR_CHECK', 'MOBILE_EMAIL_CHECK']);
            }
        }
    }

    /**
     * 会員登録エラーチェック
     *
     * @param SC_FormParam $objFormParam SC_FormParam インスタンス
     *
     * @return array エラーの配列
     */
    public static function sfCustomerEntryErrorCheck(&$objFormParam)
    {
        $objErr = SC_Helper_Customer_Ex::sfCustomerCommonErrorCheck($objFormParam);
        $objErr = SC_Helper_Customer_Ex::sfCustomerRegisterErrorCheck($objErr);

        /*
         * sfCustomerRegisterErrorCheck() では, ログイン中の場合は重複チェック
         * されないので, 再度チェックを行う
         */
        $objCustomer = new SC_Customer_Ex();
        if ($objCustomer->isLoginSuccess(true)
            && SC_Helper_Customer_Ex::sfCustomerEmailDuplicationCheck($objCustomer->getValue('customer_id'), $objFormParam->getValue('email'))) {
            $objErr->arrErr['email'] .= '※ すでに会員登録で使用されているメールアドレスです。<br />';
        }
        if ($objCustomer->isLoginSuccess(true)
            && SC_Helper_Customer_Ex::sfCustomerEmailDuplicationCheck($objCustomer->getValue('customer_id'), $objFormParam->getValue('email_mobile'))) {
            $objErr->arrErr['email_mobile'] .= '※ すでに会員登録で使用されているメールアドレスです。<br />';
        }

        return $objErr->arrErr;
    }

    /**
     * 会員情報変更エラーチェック
     *
     * @param SC_FormParam $objFormParam SC_FormParam インスタンス
     * @param bool      $isAdmin      管理画面チェック時:true
     *
     * @return array エラーの配列
     */
    public static function sfCustomerMypageErrorCheck(&$objFormParam, $isAdmin = false)
    {
        $objFormParam->toLower('email_mobile');
        $objFormParam->toLower('email_mobile02');

        $objErr = SC_Helper_Customer_Ex::sfCustomerCommonErrorCheck($objFormParam);
        $objErr = SC_Helper_Customer_Ex::sfCustomerRegisterErrorCheck($objErr, $isAdmin);

        if (isset($objErr->arrErr['password'])
            && $objFormParam->getValue('password') == DEFAULT_PASSWORD) {
            unset($objErr->arrErr['password']);
            unset($objErr->arrErr['password02']);
        }
        if (isset($objErr->arrErr['reminder_answer'])
                && $objFormParam->getValue('reminder_answer') == DEFAULT_PASSWORD) {
            unset($objErr->arrErr['reminder_answer']);
        }

        return $objErr->arrErr;
    }

    /**
     * 会員エラーチェック共通
     *
     * @param SC_FormParam $objFormParam SC_FormParam インスタンス
     * @param string       $prefix       キー名にprefixを付ける場合に指定
     *
     * @return SC_CheckError_Ex エラー情報の配列
     */
    public static function sfCustomerCommonErrorCheck(&$objFormParam, $prefix = '')
    {
        $objFormParam->convParam();
        $objFormParam->toLower($prefix.'email');
        $objFormParam->toLower($prefix.'email02');
        $arrParams = $objFormParam->getHashArray();

        // 入力データを渡す。
        $objErr = new SC_CheckError_Ex($arrParams);
        $objErr->arrErr = $objFormParam->checkError();

        $objErr->doFunc(['電話番号', $prefix.'tel01', $prefix.'tel02', $prefix.'tel03'], ['TEL_CHECK']);
        $objErr->doFunc(['FAX番号', $prefix.'fax01', $prefix.'fax02', $prefix.'fax03'], ['TEL_CHECK']);
        $objErr->doFunc(['郵便番号', $prefix.'zip01', $prefix.'zip02'], ['ALL_EXIST_CHECK']);

        return $objErr;
    }

    /**
     * 会員登録編集共通の相関チェック
     *
     * @param  SC_CheckError $objErr  SC_CheckError インスタンス
     * @param  bool       $isAdmin 管理画面チェック時:true
     *
     * @return SC_CheckError $objErr エラー情報
     */
    public static function sfCustomerRegisterErrorCheck(&$objErr, $isAdmin = false)
    {
        $objErr->doFunc(['生年月日', 'year', 'month', 'day'], ['CHECK_BIRTHDAY']);
        $objErr->doFunc(['パスワード', 'password', PASSWORD_MIN_LEN, PASSWORD_MAX_LEN], ['NUM_RANGE_CHECK']);

        if (SC_Display_Ex::detectDevice() !== DEVICE_TYPE_MOBILE) {
            if (!$isAdmin) {
                $objErr->doFunc(['メールアドレス', 'メールアドレス(確認)', 'email', 'email02'], ['EQUAL_CHECK']);
            }
            $objErr->doFunc(['パスワード', 'パスワード(確認)', 'password', 'password02'], ['EQUAL_CHECK']);
        }

        if (!$isAdmin) {
            // 現会員の判定 → 現会員もしくは仮登録中は、メアド一意が前提になってるので同じメアドで登録不可
            $objErr->doFunc(['メールアドレス', 'email'], ['CHECK_REGIST_CUSTOMER_EMAIL']);
            $objErr->doFunc(['携帯メールアドレス', 'email_mobile'], ['CHECK_REGIST_CUSTOMER_EMAIL', 'MOBILE_EMAIL_CHECK']);
        }

        return $objErr;
    }

    /**
     * 会員検索パラメーター（管理画面用）
     *
     * @param SC_FormParam $objFormParam SC_FormParam インスタンス
     *
     * @return void
     */
    public static function sfSetSearchParam(&$objFormParam)
    {
        $objFormParam->addParam('会員ID', 'search_customer_id', ID_MAX_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('お名前', 'search_name', STEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('お名前(フリガナ)', 'search_kana', STEXT_LEN, 'CKV', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK', 'KANABLANK_CHECK']);
        $objFormParam->addParam('都道府県', 'search_pref', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('誕生日(開始年)', 'search_b_start_year', 4, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('誕生日(開始月)', 'search_b_start_month', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('誕生日(開始日)', 'search_b_start_day', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);

        $objFormParam->addParam('誕生日(終了年)', 'search_b_end_year', 4, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('誕生日(終了月)', 'search_b_end_month', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('誕生日(終了日)', 'search_b_end_day', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('誕生月', 'search_birth_month', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('メールアドレス', 'search_email', MTEXT_LEN, 'a', ['SPTAB_CHECK', 'EMAIL_CHAR_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('携帯メールアドレス', 'search_email_mobile', MTEXT_LEN, 'a', ['SPTAB_CHECK', 'EMAIL_CHAR_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('電話番号', 'search_tel', TEL_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('購入金額(開始)', 'search_buy_total_from', PRICE_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('購入金額(終了)', 'search_buy_total_to', PRICE_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('購入回数(開始)', 'search_buy_times_from', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('購入回数(終了)', 'search_buy_times_to', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('登録・更新日(開始年)', 'search_start_year', 4, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('登録・更新日(開始月)', 'search_start_month', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('登録・更新日(開始日)', 'search_start_day', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('登録・更新日(終了年)', 'search_end_year', 4, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('登録・更新日(終了月)', 'search_end_month', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('登録・更新日(終了日)', 'search_end_day', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('表示件数', 'search_page_max', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK'], SEARCH_PMAX, false);
        $objFormParam->addParam('ページ番号', 'search_pageno', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK'], 1, false);
        $objFormParam->addParam('最終購入日(開始年)', 'search_buy_start_year', 4, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('最終購入日(開始月)', 'search_buy_start_month', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('最終購入日(開始日)', 'search_buy_start_day', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('最終購入日(終了年)', 'search_buy_end_year', 4, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('最終購入日(終了月)', 'search_buy_end_month', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('最終購入日(終了日)', 'search_buy_end_day', 2, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('購入商品コード', 'search_buy_product_code', STEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('購入商品名', 'search_buy_product_name', STEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('カテゴリ', 'search_category_id', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('性別', 'search_sex', INT_LEN, 'n', ['MAX_LENGTH_CHECK']);
        $objFormParam->addParam('会員状態', 'search_status', INT_LEN, 'n', ['MAX_LENGTH_CHECK']);
        $objFormParam->addParam('職業', 'search_job', INT_LEN, 'n', ['MAX_LENGTH_CHECK']);
    }

    /**
     * 会員検索パラメーター　エラーチェック（管理画面用）
     *
     * @param SC_FormParam $objFormParam SC_FormParam インスタンス
     *
     * @return array エラー配列
     */
    public static function sfCheckErrorSearchParam(&$objFormParam)
    {
        // パラメーターの基本チェック
        $arrErr = $objFormParam->checkError();
        // エラーチェック対象のパラメータ取得
        $array = $objFormParam->getHashArray();
        // 拡張エラーチェック初期化
        $objErr = new SC_CheckError_Ex($array);
        // 拡張エラーチェック
        $objErr->doFunc(['誕生日(開始日)', 'search_b_start_year', 'search_b_start_month', 'search_b_start_day'], ['CHECK_DATE']);
        $objErr->doFunc(['誕生日(終了日)', 'search_b_end_year', 'search_b_end_month', 'search_b_end_day'], ['CHECK_DATE']);

        $objErr->doFunc(['誕生日(開始日)', '誕生日(終了日)', 'search_b_start_year', 'search_b_start_month', 'search_b_start_day', 'search_b_end_year', 'search_b_end_month', 'search_b_end_day'], ['CHECK_SET_TERM']);
        $objErr->doFunc(['登録・更新日(開始日)', 'search_start_year', 'search_start_month', 'search_start_day'], ['CHECK_DATE']);
        $objErr->doFunc(['登録・更新日(終了日)', 'search_end_year', 'search_end_month', 'search_end_day'], ['CHECK_DATE']);
        $objErr->doFunc(['登録・更新日(開始日)', '登録・更新日(終了日)', 'search_start_year', 'search_start_month', 'search_start_day', 'search_end_year', 'search_end_month', 'search_end_day'], ['CHECK_SET_TERM']);
        $objErr->doFunc(['最終購入日(開始)', 'search_buy_start_year', 'search_buy_start_month', 'search_buy_start_day'], ['CHECK_DATE']);
        $objErr->doFunc(['最終購入日(終了)', 'search_buy_end_year', 'search_buy_end_month', 'search_buy_end_day'], ['CHECK_DATE']);
        // 開始 > 終了 の場合はエラーとする
        $objErr->doFunc(['最終購入日(開始)', '最終購入日(終了)', 'search_buy_start_year', 'search_buy_start_month', 'search_buy_start_day', 'search_buy_end_year', 'search_buy_end_month', 'search_buy_end_day'], ['CHECK_SET_TERM']);

        if (SC_Utils_Ex::sfIsInt($array['search_buy_total_from'])
            && SC_Utils_Ex::sfIsInt($array['search_buy_total_to'])
            && $array['search_buy_total_from'] > $array['search_buy_total_to']
        ) {
            $objErr->arrErr['search_buy_total_from'] .= '※ 購入金額の指定範囲が不正です。';
        }

        if (SC_Utils_Ex::sfIsInt($array['search_buy_times_from'])
            && SC_Utils_Ex::sfIsInt($array['search_buy_times_to'])
            && $array['search_buy_times_from'] > $array['search_buy_times_to']
        ) {
            $objErr->arrErr['search_buy_times_from'] .= '※ 購入回数の指定範囲が不正です。';
        }
        if (!SC_Utils_Ex::isBlank($objErr->arrErr)) {
            $arrErr = array_merge($arrErr, $objErr->arrErr);
        }

        return $arrErr;
    }

    /**
     * 会員一覧検索をする処理（ページング処理付き、管理画面用共通処理）
     *
     * @param  array  $arrParam  検索パラメーター連想配列
     * @param  string $limitMode ページングを利用するか判定用フラグ
     *
     * @return array( integer 全体件数, mixed 会員データ一覧配列, mixed SC_PageNaviオブジェクト)
     */
    public static function sfGetSearchData($arrParam, $limitMode = '')
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objSelect = new SC_CustomerList_Ex($arrParam, 'customer');

        $page_max = SC_Utils_Ex::sfGetSearchPageMax($arrParam['search_page_max']);
        $disp_pageno = $arrParam['search_pageno'];
        if ($disp_pageno == 0) {
            $disp_pageno = 1;
        }
        $offset = (int) $page_max * ((int) $disp_pageno - 1);
        if ($limitMode == '') {
            $objQuery->setLimitOffset($page_max, $offset);
        }
        $arrData = $objQuery->getAll($objSelect->getList(), $objSelect->arrVal);

        // 該当全体件数の取得
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $linemax = $objQuery->getOne($objSelect->getListCount(), $objSelect->arrVal);

        // ページ送りの取得
        $objNavi = new SC_PageNavi_Ex(
            $arrParam['search_pageno'],
            $linemax,
            $page_max,
            'eccube.moveSearchPage',
            NAVI_PMAX
        );

        return [$linemax, $arrData, $objNavi];
    }

    /**
     * 仮会員かどうかを判定する.
     *
     * @param  string  $login_email メールアドレス
     *
     * @return bool 仮会員の場合 true
     */
    public static function checkTempCustomer($login_email)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $where = 'email = ? AND status = 1 AND del_flg = 0';
        $exists = $objQuery->exists('dtb_customer', $where, [$login_email]);

        return $exists;
    }

    /**
     * 会員を削除する処理
     *
     * @param  int $customer_id 会員ID
     *
     * @return bool true:成功 false:失敗
     */
    public static function delete($customer_id)
    {
        $arrData = SC_Helper_Customer_Ex::sfGetCustomerDataFromId($customer_id, 'del_flg = 0');
        if (SC_Utils_Ex::isBlank($arrData)) {
            // 対象となるデータが見つからない。
            return false;
        }
        // XXXX: 仮会員は物理削除となっていたが論理削除に変更。
        $arrVal = [
            'del_flg' => '1',
        ];
        SC_Helper_Customer_Ex::sfEditCustomerData($arrVal, $customer_id);

        return true;
    }
}
