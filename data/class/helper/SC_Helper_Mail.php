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
 * メール関連 のヘルパークラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class SC_Helper_Mail
{
    /** メールテンプレートのパス */
    public $arrMAILTPLPATH;

    /**
     * LC_Pageオブジェクト.
     *
     * @var LC_Page
     */
    protected $objPage;

    /** @var array */
    protected $arrPref;
    /** @var array */
    protected $arrCountry;

    /**
     * コンストラクタ.
     */
    public function __construct()
    {
        $masterData = new SC_DB_MasterData_Ex();
        $this->arrMAILTPLPATH = $masterData->getMasterData('mtb_mail_tpl_path');
        $this->arrPref = $masterData->getMasterData('mtb_pref');
        $this->arrCountry = $masterData->getMasterData('mtb_country');
    }

    /**
     * LC_Pageオブジェクトをセットします.
     *
     * @param LC_Page $objPage
     */
    public function setPage(LC_Page $objPage)
    {
        $this->objPage = $objPage;
    }

    /**
     * LC_Pageオブジェクトを返します.
     *
     * @return LC_Page
     */
    public function getPage()
    {
        return $this->objPage;
    }

    /* DBに登録されたテンプレートメールの送信 */

    /**
     * @param string $to_name
     * @param int $template_id
     * @param LC_Page_Contact $objPage
     */
    public function sfSendTemplateMail($to, $to_name, $template_id, &$objPage, $from_address = '', $from_name = '', $reply_to = '', $bcc = '')
    {
        // メールテンプレート情報の取得
        $objMailtemplate = new SC_Helper_Mailtemplate_Ex();
        $mailtemplate = $objMailtemplate->get($template_id);
        $objPage->tpl_header = $mailtemplate['header'];
        $objPage->tpl_footer = $mailtemplate['footer'];
        $tmp_subject = $mailtemplate['subject'];

        $arrInfo = SC_Helper_DB_Ex::sfGetBasisData();

        $objMailView = new SC_SiteView_Ex();
        $objMailView->setPage($this->getPage());
        // メール本文の取得
        $objMailView->assignobj($objPage);
        $body = $objMailView->fetch($this->arrMAILTPLPATH[$template_id]);

        // メール送信処理
        $objSendMail = new SC_SendMail_Ex();
        if ($from_address == '') {
            $from_address = $arrInfo['email03'];
        }
        if ($from_name == '') {
            $from_name = $arrInfo['shop_name'];
        }
        if ($reply_to == '') {
            $reply_to = $arrInfo['email03'];
        }
        $error = $arrInfo['email04'];
        $tosubject = $this->sfMakeSubject($tmp_subject, $objMailView);

        $objSendMail->setItem('', $tosubject, $body, $from_address, $from_name, $reply_to, $error, $error, $bcc);
        $objSendMail->setTo($to, $to_name);
        $objSendMail->sendMail();    // メール送信
    }

    /* 注文受付メール送信 */
    public function sfSendOrderMail($order_id, $template_id, $subject = '', $header = '', $footer = '', $send = true)
    {
        $arrTplVar = new stdClass();
        $arrInfo = SC_Helper_DB_Ex::sfGetBasisData();
        $arrTplVar->arrInfo = $arrInfo;

        $objQuery = SC_Query_Ex::getSingletonInstance();

        if ($subject == '' && $header == '' && $footer == '') {
            // メールテンプレート情報の取得
            $objMailtemplate = new SC_Helper_Mailtemplate_Ex();
            $mailtemplate = $objMailtemplate->get($template_id);
            $arrTplVar->tpl_header = $mailtemplate['header'];
            $arrTplVar->tpl_footer = $mailtemplate['footer'];
            $tmp_subject = $mailtemplate['subject'];
        } else {
            $arrTplVar->tpl_header = $header;
            $arrTplVar->tpl_footer = $footer;
            $tmp_subject = $subject;
        }

        // 受注情報の取得
        $where = 'order_id = ? AND del_flg = 0';
        $arrOrder = $objQuery->getRow('*', 'dtb_order', $where, [$order_id]);

        if (empty($arrOrder)) {
            trigger_error("該当する受注が存在しない。(注文番号: $order_id)", E_USER_ERROR);
        }

        $where = 'order_id = ?';
        $objQuery->setOrder('order_detail_id');
        $arrTplVar->arrOrderDetail = $objQuery->select('*', 'dtb_order_detail', $where, [$order_id]);

        // 配送情報の取得
        $arrTplVar->arrShipping = $this->sfGetShippingData($order_id);

        $arrTplVar->Message_tmp = $arrOrder['message'];

        // 会員情報の取得
        $customer_id = $arrOrder['customer_id'];
        $objQuery->setOrder('customer_id');
        $arrRet = $objQuery->select('point', 'dtb_customer', 'customer_id = ?', [$customer_id]);
        $arrCustomer = $arrRet[0] ?? '';

        $arrTplVar->arrCustomer = $arrCustomer;
        $arrTplVar->arrOrder = $arrOrder;

        // その他決済情報
        if ($arrOrder['memo02'] != '') {
            $arrOther = unserialize($arrOrder['memo02']);

            foreach ($arrOther as $other_key => $other_val) {
                if (SC_Utils_Ex::sfTrim($other_val['value']) == '') {
                    $arrOther[$other_key]['value'] = '';
                }
            }

            $arrTplVar->arrOther = $arrOther;
        }

        // 都道府県変換
        $arrTplVar->arrPref = $this->arrPref;
        // 国変換
        $arrTplVar->arrCountry = $this->arrCountry;

        $objCustomer = new SC_Customer_Ex();
        $arrTplVar->tpl_user_point = $objCustomer->getValue('point');

        $objMailView = null;
        // 注文受付メール(携帯)
        if ($template_id == 2) {
            $objMailView = new SC_MobileView_Ex();
        } else {
            $objMailView = new SC_SiteView_Ex();
        }
        // メール本文の取得
        $objMailView->setPage($this->getPage());
        $objMailView->assignobj($arrTplVar);
        $body = $objMailView->fetch($this->arrMAILTPLPATH[$template_id]);

        // メール送信処理
        $objSendMail = new SC_SendMail_Ex();
        $bcc = $arrInfo['email01'];
        $from = $arrInfo['email03'];
        $error = $arrInfo['email04'];
        $tosubject = $this->sfMakeSubject($tmp_subject, $objMailView);

        $objSendMail->setItem('', $tosubject, $body, $from, $arrInfo['shop_name'], $from, $error, $error, $bcc);
        $objSendMail->setTo($arrOrder['order_email'], SC_Utils_Ex::formatName($arrOrder, 'order_name').' 様');

        // 送信フラグ:trueの場合は、送信する。
        if ($send) {
            if ($objSendMail->sendMail()) {
                $this->sfSaveMailHistory($order_id, $template_id, $tosubject, $body);
            }
        }

        return $objSendMail;
    }

    /**
     * 配送情報の取得
     *
     * @param int $order_id 受注ID
     *
     * @return array 配送情報を格納した配列
     */
    public function sfGetShippingData($order_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $objQuery->setOrder('shipping_id');
        $arrRet = $objQuery->select('*', 'dtb_shipping', 'order_id = ?', [$order_id]);
        foreach ($arrRet as $key => $value) {
            $col = 's_i.*, tax_rate, tax_rule';
            $from = 'dtb_shipment_item AS s_i JOIN dtb_order_detail AS o_d
                ON s_i.order_id = o_d.order_id AND s_i.product_class_id = o_d.product_class_id';
            $where = 'o_d.order_id = ? AND shipping_id = ?';
            $arrWhereVal = [$order_id, $arrRet[$key]['shipping_id']];
            $objQuery->setOrder('order_detail_id');
            $arrItems = $objQuery->select($col, $from, $where, $arrWhereVal);
            $arrRet[$key]['shipment_item'] = $arrItems;
        }

        return $arrRet;
    }

    // テンプレートを使用したメールの送信
    public function sfSendTplMail($to, $tmp_subject, $tplpath, &$objPage)
    {
        $objMailView = new SC_SiteView_Ex();
        $objMailView->setPage($this->getPage());
        $arrInfo = SC_Helper_DB_Ex::sfGetBasisData();
        // メール本文の取得
        $objPage->tpl_shopname = $arrInfo['shop_name'];
        $objPage->tpl_infoemail = $arrInfo['email02'];
        $objMailView->assignobj($objPage);
        $body = $objMailView->fetch($tplpath);
        // メール送信処理
        $objSendMail = new SC_SendMail_Ex();
        $bcc = $arrInfo['email01'];
        $from = $arrInfo['email03'];
        $error = $arrInfo['email04'];
        $tosubject = $this->sfMakeSubject($tmp_subject, $objMailView);

        $objSendMail->setItem($to, $tosubject, $body, $from, $arrInfo['shop_name'], $from, $error, $error, $bcc);
        $objSendMail->sendMail();
    }

    // 通常のメール送信
    public function sfSendMail($to, $tmp_subject, $body)
    {
        $arrInfo = SC_Helper_DB_Ex::sfGetBasisData();
        // メール送信処理
        $objSendMail = new SC_SendMail_Ex();
        $bcc = $arrInfo['email01'];
        $from = $arrInfo['email03'];
        $error = $arrInfo['email04'];
        $tosubject = $this->sfMakeSubject($tmp_subject);

        $objSendMail->setItem($to, $tosubject, $body, $from, $arrInfo['shop_name'], $from, $error, $error, $bcc);
        $objSendMail->sendMail();
    }

    // 件名にテンプレートを用いる

    /**
     * @param SC_SiteView_Ex $objMailView
     */
    public function sfMakeSubject($subject, &$objMailView = null)
    {
        if (empty($objMailView)) {
            $objMailView = new SC_SiteView_Ex();
            $objMailView->setPage($this->getPage());
        }
        $objTplAssign = new stdClass();

        $arrInfo = SC_Helper_DB_Ex::sfGetBasisData();
        $objTplAssign->tpl_shopname = $arrInfo['shop_name'];
        $objTplAssign->tpl_infoemail = $subject; // 従来互換
        $objTplAssign->tpl_mailtitle = $subject;
        $objMailView->assignobj($objTplAssign);
        $subject = $objMailView->fetch('mail_templates/mail_title.tpl');
        // #1940 (SC_Helper_Mail#sfMakeSubject 先頭に改行を含む値を返す) 対応
        $subject = trim($subject);

        return $subject;
    }

    // メール配信履歴への登録

    /**
     * @param string $subject
     */
    public function sfSaveMailHistory($order_id, $template_id, $subject, $body)
    {
        $sqlval = [];
        $sqlval['subject'] = $subject;
        $sqlval['order_id'] = $order_id;
        $sqlval['template_id'] = $template_id;
        $sqlval['send_date'] = 'CURRENT_TIMESTAMP';
        if (!isset($_SESSION['member_id'])) {
            $_SESSION['member_id'] = '';
        }
        if ($_SESSION['member_id'] != '') {
            $sqlval['creator_id'] = $_SESSION['member_id'];
        } else {
            $sqlval['creator_id'] = '0';
        }
        $sqlval['mail_body'] = $body;

        $objQuery = SC_Query_Ex::getSingletonInstance();
        $sqlval['send_id'] = $objQuery->nextVal('dtb_mail_history_send_id');
        $objQuery->insert('dtb_mail_history', $sqlval);
    }

    /**
     * 会員登録があるかどうかのチェック(仮会員を含まない)
     *
     * @deprecated 本体では使用されていないため非推奨
     */
    public function sfCheckCustomerMailMaga($email)
    {
        $col = 'email, mailmaga_flg, customer_id';
        $from = 'dtb_customer';
        $where = '(email = ? OR email_mobile = ?) AND status = 2 AND del_flg = 0';
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrRet = $objQuery->select($col, $from, $where, [$email]);
        // 会員のメールアドレスが登録されている
        if (!empty($arrRet[0]['customer_id'])) {
            return true;
        }

        return false;
    }

    /**
     * 登録メールを送信する。
     *
     * @param  string  $secret_key  会員固有キー。$customer_id に有効な数値が指定されると、無視される。
     * @param  int $customer_id 会員ID
     * @param  bool $is_mobile   false(default):PCアドレスにメールを送る true:携帯アドレスにメールを送る
     * @param $resend_flg true  仮登録メール再送
     *
     * @return bool true:成功 false:失敗
     */
    public function sfSendRegistMail($secret_key, $customer_id = '', $is_mobile = false, $resend_flg = false)
    {
        // 会員データの取得
        if (SC_Utils_Ex::sfIsInt($customer_id)) {
            $arrCustomerData = SC_Helper_Customer_Ex::sfGetCustomerDataFromId($customer_id);
        } else {
            $arrCustomerData = SC_Helper_Customer_Ex::sfGetCustomerDataFromId('', 'secret_key = ?', [$secret_key]);
        }
        if (SC_Utils_Ex::isBlank($arrCustomerData)) {
            return false;
        }

        $CONF = SC_Helper_DB_Ex::sfGetBasisData();

        $objMailText = new SC_SiteView_Ex();
        $objMailText->setPage($this->getPage());
        $objMailText->assign('CONF', $CONF);
        $objMailText->assign('arrCustomer', $arrCustomerData);

        // 旧テンプレート互換用 https://github.com/EC-CUBE/ec-cube2/issues/982
        $objMailText->assignarray($arrCustomerData);
        $objMailText->assign('uniqid', $arrCustomerData['secret_key']);

        $objHelperMail = new SC_Helper_Mail_Ex();
        // 仮会員が有効の場合 (FIXME: コメント不正確)
        if ($arrCustomerData['status'] == 1
            && (CUSTOMER_CONFIRM_MAIL == true || $resend_flg == true)
        ) {
            $subject = $objHelperMail->sfMakeSubject('会員登録のご確認', $objMailText);
            $toCustomerMail = $objMailText->fetch('mail_templates/customer_mail.tpl');
        } else {
            $subject = $objHelperMail->sfMakeSubject('会員登録のご完了', $objMailText);
            $toCustomerMail = $objMailText->fetch('mail_templates/customer_regist_mail.tpl');
        }

        $objMail = new SC_SendMail_Ex();
        $objMail->setItem(
            '',                     // 宛先
            $subject,               // サブジェクト
            $toCustomerMail,        // 本文
            $CONF['email03'],       // 配送元アドレス
            $CONF['shop_name'],     // 配送元 名前
            $CONF['email03'],       // reply_to
            $CONF['email04'],       // return_path
            $CONF['email04'],       // Errors_to
            $CONF['email01']        // Bcc
        );
        // 宛先の設定
        if ($is_mobile) {
            $to_addr = $arrCustomerData['email_mobile'];
        } else {
            $to_addr = $arrCustomerData['email'];
        }
        $objMail->setTo($to_addr, SC_Utils_Ex::formatName($arrCustomerData).' 様');

        $objMail->sendMail();

        return true;
    }

    /**
     * 保存されているメルマガテンプレートの取得
     *
     * @param int 特定IDのテンプレートを取り出したい時はtemplate_idを指定。未指定時は全件取得
     *
     * @return　array メールテンプレート情報を格納した配列
     *
     * @todo   表示順も引数で変更できるように
     */
    public static function sfGetMailmagaTemplate($template_id = null)
    {
        // 初期化
        $where = '';
        $objQuery = SC_Query_Ex::getSingletonInstance();

        // 条件文
        $where = 'del_flg = ?';
        $arrValues[] = 0;
        // template_id指定時
        if (SC_Utils_Ex::sfIsInt($template_id) === true) {
            $where .= ' AND template_id = ?';
            $arrValues[] = $template_id;
        }

        // 表示順
        $objQuery->setOrder('create_date DESC');

        $arrResults = $objQuery->select('*', 'dtb_mailmaga_template', $where, $arrValues);

        return $arrResults;
    }

    /**
     * 保存されているメルマガ送信履歴の取得
     *
     * @param int 特定の送信履歴を取り出したい時はsend_idを指定。未指定時は全件取得
     *
     * @return　array 送信履歴情報を格納した配列
     */
    public function sfGetSendHistory($send_id = null)
    {
        // 初期化
        $where = '';
        $objQuery = SC_Query_Ex::getSingletonInstance();

        // 条件文
        $where = 'del_flg = ?';
        $arrValues[] = 0;

        // send_id指定時
        if (SC_Utils_Ex::sfIsInt($send_id) === true) {
            $where .= ' AND send_id = ?';
            $arrValues[] = $send_id;
        }

        // 表示順
        $objQuery->setOrder('create_date DESC');

        $arrResults = $objQuery->select('*', 'dtb_send_history', $where, $arrValues);

        return $arrResults;
    }

    /**
     * 指定したIDのメルマガ配送を行う
     *
     * @param int $send_id dtb_send_history の情報
     *
     * @return　void
     */
    public static function sfSendMailmagazine($send_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objDb = new SC_Helper_DB_Ex();
        $objSite = $objDb->sfGetBasisData();
        $objMail = new SC_SendMail_Ex();

        $where = 'del_flg = 0 AND send_id = ?';
        $arrMail = $objQuery->getRow('*', 'dtb_send_history', $where, [$send_id]);

        // 対象となる$send_idが見つからない
        if (SC_Utils_Ex::isBlank($arrMail)) {
            return;
        }

        // 送信先リストの取得
        $arrDestinationList = $objQuery->select(
            '*',
            'dtb_send_customer',
            'send_id = ? AND (send_flag = 2 OR send_flag IS NULL)',
            [$send_id]
        );

        // 現在の配信数
        $complete_count = $arrMail['complete_count'];
        if (SC_Utils_Ex::isBlank($arrMail)) {
            $complete_count = 0;
        }

        foreach ($arrDestinationList as $arrDestination) {
            // お名前の変換
            $customerName = trim($arrDestination['name']);
            $subjectBody = preg_replace('/{name}/', $customerName, $arrMail['subject']);
            $mailBody = preg_replace('/{name}/', $customerName, $arrMail['body']);

            $objMail->setItem(
                $arrDestination['email'],
                $subjectBody,
                $mailBody,
                $objSite['email03'],      // 送信元メールアドレス
                $objSite['shop_name'],    // 送信元名
                $objSite['email03'],      // reply_to
                $objSite['email04'],      // return_path
                $objSite['email04']       // errors_to
            );

            // テキストメール配信の場合
            if ($arrMail['mail_method'] == 2) {
                $sendResut = $objMail->sendMail();
            // HTMLメール配信の場合
            } else {
                $sendResut = $objMail->sendHtmlMail();
            }

            // 送信完了なら1、失敗なら2をメール送信結果フラグとしてDBに挿入
            if (!$sendResut) {
                $sendFlag = '2';
            } else {
                // 完了を 1 増やす
                $sendFlag = '1';
                $complete_count++;
            }

            // 送信結果情報を更新
            $objQuery->update(
                'dtb_send_customer',
                ['send_flag' => $sendFlag],
                'send_id = ? AND customer_id = ?',
                [$send_id, $arrDestination['customer_id']]
            );
        }

        // メール全件送信完了後の処理
        $objQuery->update(
            'dtb_send_history',
            ['end_date' => 'CURRENT_TIMESTAMP', 'complete_count' => $complete_count],
            'send_id = ?',
            [$send_id]
        );

        // 送信完了　報告メール
        $compSubject = date('Y年m月d日H時i分').'  下記メールの配信が完了しました。';
        // 管理者宛に変更
        $objMail->setTo($objSite['email03']);
        $objMail->setSubject($compSubject);

        // テキストメール配信の場合
        if ($arrMail['mail_method'] == 2) {
            $sendResut = $objMail->sendMail();
        // HTMLメール配信の場合
        } else {
            $sendResut = $objMail->sendHtmlMail();
        }

        return;
    }
}
