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
 * お届け先の指定 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Shopping_Deliv extends LC_Page_Ex
{
    /** @var array */
    public $arrAddr;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $masterData = new SC_DB_MasterData_Ex();
        $this->arrPref = $masterData->getMasterData('mtb_pref');
        $this->arrCountry = $masterData->getMasterData('mtb_country');
        $this->tpl_title = 'お届け先の指定';
        $this->httpCacheControl('nocache');
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
     * Page のプロセス.
     *
     * @return void
     */
    public function action()
    {
        // 決済処理中ステータスのロールバック
        $objPurchase = new SC_Helper_Purchase_Ex();
        $objPurchase->cancelPendingOrder(PENDING_ORDER_CANCEL_FLAG);

        $objSiteSess = new SC_SiteSession_Ex();
        $objCartSess = new SC_CartSession_Ex();
        $objCustomer = new SC_Customer_Ex();
        $objFormParam = new SC_FormParam_Ex();
        $objAddress = new SC_Helper_Address_Ex();

        $this->tpl_uniqid = $objSiteSess->getUniqId();
        $objPurchase->verifyChangeCart($this->tpl_uniqid, $objCartSess);

        $this->cartKey = $objCartSess->getKey();

        // ログインチェック
        if (!$objCustomer->isLoginSuccess(true)) {
            SC_Utils_Ex::sfDispSiteError(CUSTOMER_ERROR);
        }

        // ダウンロード商品の場合は、支払方法画面に転送
        if ($this->cartKey == PRODUCT_TYPE_DOWNLOAD) {
            $objPurchase->copyFromCustomer($sqlval, $objCustomer, 'shipping');
            $objPurchase->saveShippingTemp($sqlval);
            $objPurchase->saveOrderTemp($this->tpl_uniqid, $sqlval, $objCustomer);
            $objSiteSess->setRegistFlag();

            SC_Response_Ex::sendRedirect('payment.php');
            SC_Response_Ex::actionExit();
        }

        $this->lfInitParam($objFormParam);
        $objFormParam->setParam($_POST);
        $objFormParam->convParam();
        $arrErr = $objFormParam->checkError();
        if (!SC_Utils_Ex::isBlank($arrErr)) {
            SC_Utils_Ex::sfDispSiteError(PAGE_ERROR, '', true);
            SC_Response_Ex::actionExit();
        }

        $arrForm = $objFormParam->getHashArray();

        switch ($this->getMode()) {
            // 削除
            case 'delete':
                if (!$objAddress->deleteAddress($arrForm['other_deliv_id'], $objCustomer->getValue('customer_id'))) {
                    SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', false, '別のお届け先を削除できませんでした。');
                    SC_Response_Ex::actionExit();
                }
                break;

                // 会員登録住所に送る
            case 'customer_addr':
                $objPurchase->unsetShippingTemp();

                $shipping_id = $arrForm['deliv_check'] == -1 ? 0 : $arrForm['deliv_check'];
                $success = $this->registerDeliv($shipping_id, $this->tpl_uniqid, $objPurchase, $objCustomer, $objAddress);
                if (!$success) {
                    SC_Utils_Ex::sfDispSiteError(PAGE_ERROR, '', true);
                }

                $objPurchase->setShipmentItemTempForSole($objCartSess, $shipping_id);
                $objSiteSess->setRegistFlag();

                SC_Response_Ex::sendRedirect(SHOPPING_PAYMENT_URLPATH);
                SC_Response_Ex::actionExit();
                break;

                // 前のページに戻る
            case 'return':
                // 確認ページへ移動
                SC_Response_Ex::sendRedirect(CART_URL);
                SC_Response_Ex::actionExit();
                break;

                // お届け先複数指定
            case 'multiple':
                // 複数配送先指定が無効な場合はエラー
                if (USE_MULTIPLE_SHIPPING === false) {
                    SC_Utils_Ex::sfDispSiteError(PAGE_ERROR, '', true);
                    SC_Response_Ex::actionExit();
                }

                SC_Response_Ex::sendRedirect('multiple.php');
                SC_Response_Ex::actionExit();
                break;

            default:
                // 配送IDの取得
                $shippingData = $objPurchase->getShippingTemp();
                if (!SC_Utils_Ex::isBlank($shippingData)) {
                    $arrShippingId = array_keys($shippingData);
                }
                if (isset($arrShippingId[0])) {
                    $this->arrForm['deliv_check']['value'] = $arrShippingId[0] == 0 ? -1 : $arrShippingId[0];
                }
                break;
        }

        // 登録済み住所を取得
        $addr = [
            [
                'other_deliv_id' => null,
                'customer_id' => $objCustomer->getValue('customer_id'),
                'name01' => $objCustomer->getValue('name01'),
                'name02' => $objCustomer->getValue('name02'),
                'kana01' => $objCustomer->getValue('kana01'),
                'kana02' => $objCustomer->getValue('kana02'),
                'company_name' => $objCustomer->getValue('company_name'),
                'country_id' => $objCustomer->getValue('country_id'),
                'zipcode' => $objCustomer->getValue('zipcode'),
                'zip01' => $objCustomer->getValue('zip01'),
                'zip02' => $objCustomer->getValue('zip02'),
                'pref' => $objCustomer->getValue('pref'),
                'addr01' => $objCustomer->getValue('addr01'),
                'addr02' => $objCustomer->getValue('addr02'),
                'tel01' => $objCustomer->getValue('tel01'),
                'tel02' => $objCustomer->getValue('tel02'),
                'tel03' => $objCustomer->getValue('tel03'),
            ],
        ];
        $this->arrAddr = array_merge($addr, $objAddress->getList($objCustomer->getValue('customer_id')));
        $this->tpl_addrmax = count($this->arrAddr) - 1; // 会員の住所をカウントしない
    }

    /**
     * パラメーター情報の初期化を行う.
     *
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     *
     * @return void
     */
    public function lfInitParam(&$objFormParam)
    {
        $objFormParam->addParam('その他のお届け先ID', 'other_deliv_id', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('お届け先チェック', 'deliv_check', INT_LEN, 'n', ['MAX_LENGTH_CHECK']);
    }

    /**
     * お届け先チェックの値に応じて, お届け先情報を保存する.
     *
     * 会員住所がチェックされている場合は, 会員情報からお届け先を取得する.
     * その他のお届け先がチェックされている場合は, その他のお届け先からお届け先を取得する.
     * お届け先チェックの値が不正な場合は false を返す.
     *
     * @param  int            $other_deliv_id
     * @param  string             $uniqid         受注一時テーブルのユニークID
     * @param  SC_Helper_Purchase $objPurchase    SC_Helper_Purchase インスタンス
     * @param  SC_Customer        $objCustomer    SC_Customer インスタンス
     * @param SC_Helper_Address_Ex $objAddress
     *
     * @return bool            お届け先チェックの値が妥当な場合 true
     */
    public function registerDeliv($other_deliv_id, $uniqid, &$objPurchase, &$objCustomer, $objAddress)
    {
        $arrValues = [];
        // 会員登録住所がチェックされている場合
        if ($other_deliv_id == 0) {
            $objPurchase->copyFromCustomer($arrValues, $objCustomer, 'shipping');
        // 別のお届け先がチェックされている場合
        } else {
            $arrOtherDeliv = $objAddress->getAddress($other_deliv_id, $objCustomer->getValue('customer_id'));
            if (!$arrOtherDeliv) {
                return false;
            }

            $objPurchase->copyFromOrder($arrValues, $arrOtherDeliv, 'shipping', '');
        }
        $objPurchase->saveShippingTemp($arrValues, $other_deliv_id);
        $objPurchase->saveOrderTemp($uniqid, $arrValues, $objCustomer);

        return true;
    }
}
