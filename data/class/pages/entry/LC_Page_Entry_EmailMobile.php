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
 * 携帯メールアドレス登録のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Entry_EmailMobile extends LC_Page_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
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
        $objCustomer = new SC_Customer_Ex();
        $objFormParam = new SC_FormParam_Ex();

        $this->lfInitParam($objFormParam);
        $objFormParam->setParam($_POST);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->arrErr = $this->lfCheckError($objFormParam);

            if (empty($this->arrErr)) {
                $email_mobile = $this->lfRegistEmailMobile(
                    strtolower($objFormParam->getValue('email_mobile')),
                    $objCustomer->getValue('customer_id')
                );

                $objCustomer->setValue('email_mobile', $email_mobile);
                $this->tpl_mainpage = 'entry/email_mobile_complete.tpl';
                $this->tpl_title = '携帯メール登録完了';
            }
        }

        $this->tpl_name = $objCustomer->getValue('name01');
        $this->arrForm = $objFormParam->getFormParamList();
    }

    /**
     * lfInitParam
     *
     * @param SC_FormParam_Ex $objFormParam
     *
     * @return void
     */
    public function lfInitParam(&$objFormParam)
    {
        $objFormParam->addParam(
            'メールアドレス',
            'email_mobile',
            null,
            'a',
            ['NO_SPTAB', 'EXIST_CHECK', 'CHANGE_LOWER', 'EMAIL_CHAR_CHECK', 'EMAIL_CHECK', 'MOBILE_EMAIL_CHECK']
        );
    }

    /**
     * エラーチェックする
     *
     * @param SC_FormParam_Ex $objFormParam
     *
     * @return array エラー情報の配列
     */
    public function lfCheckError(&$objFormParam)
    {
        $objFormParam->convParam();
        $objErr = new SC_CheckError_Ex();
        $objErr->arrErr = $objFormParam->checkError();

        // FIXME: lfInitParam() で設定すれば良いように感じる
        $objErr->doFunc(['メールアドレス', 'email_mobile'], ['CHECK_REGIST_CUSTOMER_EMAIL']);

        return $objErr->arrErr;
    }

    /**
     * 携帯メールアドレスが登録されていないユーザーに携帯アドレスを登録する
     *
     * 登録完了後にsessionのemail_mobileを更新する
     *
     * @param string $email_mobile
     *
     * @return string
     */
    public function lfRegistEmailMobile($email_mobile, $customer_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->update(
            'dtb_customer',
            ['email_mobile' => $email_mobile],
            'customer_id = ?',
            [$customer_id]
        );

        return $email_mobile;
    }
}
