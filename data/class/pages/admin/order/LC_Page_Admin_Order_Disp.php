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
 * 受注情報表示 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_Order_Disp extends LC_Page_Admin_Order_Ex
{
    /** @var string */
    public $tpl_subnavi;

    public $arrShippingKeys = [
        'shipping_id',
        'shipping_name01',
        'shipping_name02',
        'shipping_kana01',
        'shipping_kana02',
        'shipping_company_name',
        'shipping_tel01',
        'shipping_tel02',
        'shipping_tel03',
        'shipping_fax01',
        'shipping_fax02',
        'shipping_fax03',
        'shipping_country_id',
        'shipping_zipcode',
        'shipping_pref',
        'shipping_zip01',
        'shipping_zip02',
        'shipping_addr01',
        'shipping_addr02',
        'shipping_date_year',
        'shipping_date_month',
        'shipping_date_day',
        'time_id',
    ];

    public $arrShipmentItemKeys = [
        'shipment_product_class_id',
        'shipment_product_code',
        'shipment_product_name',
        'shipment_classcategory_name1',
        'shipment_classcategory_name2',
        'shipment_price',
        'shipment_quantity',
    ];

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'order/disp.tpl';
        $this->tpl_mainno = 'order';
        $this->tpl_subnavi = '';
        $this->tpl_subno = '';
        $this->tpl_subtitle = '受注情報表示';

        $masterData = new SC_DB_MasterData_Ex();
        $this->arrPref = $masterData->getMasterData('mtb_pref');
        $this->arrORDERSTATUS = $masterData->getMasterData('mtb_order_status');
        $this->arrDeviceType = $masterData->getMasterData('mtb_device_type');
        $this->arrCountry = $masterData->getMasterData('mtb_country');
        $this->arrSex = $masterData->getMasterData('mtb_sex');
        $this->arrJob = $masterData->getMasterData('mtb_job');

        // 支払い方法の取得
        $this->arrPayment = SC_Helper_Payment_Ex::getIDValueList();

        // 配送業者の取得
        $this->arrDeliv = SC_Helper_Delivery_Ex::getIDValueList();
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
        $objPurchase = new SC_Helper_Purchase_Ex();
        $objFormParam = new SC_FormParam_Ex();

        // パラメータ情報の初期化
        $this->lfInitParam($objFormParam);
        $objFormParam->setParam($_REQUEST);
        $objFormParam->convParam();
        $order_id = $objFormParam->getValue('order_id');

        // DBから受注情報を読み込む
        $this->setOrderToFormParam($objFormParam, $order_id);

        $this->arrForm = $objFormParam->getFormParamList();
        $this->arrAllShipping = $objFormParam->getSwapArray(array_merge($this->arrShippingKeys, $this->arrShipmentItemKeys));
        $this->tpl_shipping_quantity = count($this->arrAllShipping);
        $this->arrDelivTime = SC_Helper_Delivery_Ex::getDelivTime($objFormParam->getValue('deliv_id'));
        $this->arrInfo = SC_Helper_DB_Ex::sfGetBasisData();

        $this->setTemplate($this->tpl_mainpage);
    }

    /**
     * パラメータ情報の初期化を行う.
     *
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     *
     * @return void
     */
    public function lfInitParam(&$objFormParam)
    {
        // 検索条件のパラメータを初期化
        parent::lfInitParam($objFormParam);

        // お客様情報
        $objFormParam->addParam('注文者 お名前(姓)', 'order_name01', STEXT_LEN, 'KVa', ['EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('注文者 お名前(名)', 'order_name02', STEXT_LEN, 'KVa', ['EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('注文者 お名前(フリガナ・姓)', 'order_kana01', STEXT_LEN, 'KVCa', ['EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('注文者 お名前(フリガナ・名)', 'order_kana02', STEXT_LEN, 'KVCa', ['EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('注文者 会社名', 'order_company_name', STEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('メールアドレス', 'order_email', null, 'KVCa', ['NO_SPTAB', 'EMAIL_CHECK', 'EMAIL_CHAR_CHECK']);
        if (FORM_COUNTRY_ENABLE) {
            $objFormParam->addParam('国', 'order_country_id', INT_LEN, 'n', ['EXIST_CHECK', 'NUM_CHECK']);
            $objFormParam->addParam('ZIPCODE', 'order_zipcode', STEXT_LEN, 'n', ['NO_SPTAB', 'SPTAB_CHECK', 'GRAPH_CHECK', 'MAX_LENGTH_CHECK']);
        }
        $objFormParam->addParam('郵便番号1', 'order_zip01', ZIP01_LEN, 'n', ['NUM_CHECK', 'NUM_COUNT_CHECK']);
        $objFormParam->addParam('郵便番号2', 'order_zip02', ZIP02_LEN, 'n', ['NUM_CHECK', 'NUM_COUNT_CHECK']);
        $objFormParam->addParam('都道府県', 'order_pref', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('住所1', 'order_addr01', MTEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('住所2', 'order_addr02', MTEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('電話番号1', 'order_tel01', TEL_ITEM_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('電話番号2', 'order_tel02', TEL_ITEM_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('電話番号3', 'order_tel03', TEL_ITEM_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('性別', 'order_sex', TEL_ITEM_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('職業', 'order_job', TEL_ITEM_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('生年月日(年)', 'order_birth_year', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('生年月日(月)', 'order_birth_month', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('生年月日(日)', 'order_birth_day', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);

        // 受注商品情報
        $objFormParam->addParam('値引き', 'discount', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'], '0');
        $objFormParam->addParam('送料', 'deliv_fee', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'], '0');
        $objFormParam->addParam('手数料', 'charge', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'], '0');

        // ポイント機能ON時のみ
        if (USE_POINT !== false) {
            $objFormParam->addParam('利用ポイント', 'use_point', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK']);
        }

        $objFormParam->addParam('配送業者', 'deliv_id', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('お支払い方法', 'payment_id', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('対応状況', 'status', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('お支払方法名称', 'payment_method');

        // 受注詳細情報
        $objFormParam->addParam('商品種別ID', 'product_type_id', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'], '0');
        $objFormParam->addParam('単価', 'price', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'], '0');
        $objFormParam->addParam('数量', 'quantity', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'], '0');
        $objFormParam->addParam('商品ID', 'product_id', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'], '0');
        $objFormParam->addParam('商品規格ID', 'product_class_id', INT_LEN, 'n', ['EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'], '0');
        $objFormParam->addParam('ポイント付与率', 'point_rate');
        $objFormParam->addParam('商品コード', 'product_code');
        $objFormParam->addParam('商品名', 'product_name');
        $objFormParam->addParam('規格名1', 'classcategory_name1');
        $objFormParam->addParam('規格名2', 'classcategory_name2');
        $objFormParam->addParam('メモ', 'note', MTEXT_LEN, 'KVa', ['MAX_LENGTH_CHECK']);
        $objFormParam->addParam('削除用項番', 'delete_no', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('消費税率', 'tax_rate');
        $objFormParam->addParam('課税規則', 'tax_rule');

        // DB読込用
        $objFormParam->addParam('小計', 'subtotal');
        $objFormParam->addParam('合計', 'total');
        $objFormParam->addParam('支払い合計', 'payment_total');
        $objFormParam->addParam('加算ポイント', 'add_point');
        $objFormParam->addParam('お誕生日ポイント', 'birth_point');
        $objFormParam->addParam('消費税合計', 'tax');
        $objFormParam->addParam('最終保持ポイント', 'total_point');
        $objFormParam->addParam('会員ID', 'customer_id', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK'], '0');
        $objFormParam->addParam('会員ID', 'edit_customer_id', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK'], '0');
        $objFormParam->addParam('現在のポイント', 'customer_point');
        $objFormParam->addParam('受注前ポイント', 'point');
        $objFormParam->addParam('注文番号', 'order_id');
        $objFormParam->addParam('受注日', 'create_date');
        $objFormParam->addParam('発送日', 'commit_date');
        $objFormParam->addParam('備考', 'message');
        $objFormParam->addParam('入金日', 'payment_date');
        $objFormParam->addParam('端末種別', 'device_type_id');

        // 複数情報
        $objFormParam->addParam('配送ID', 'shipping_id', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK'], 0);
        $objFormParam->addParam('お名前1', 'shipping_name01', STEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('お名前2', 'shipping_name02', STEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('お名前(フリガナ・姓)', 'shipping_kana01', STEXT_LEN, 'KVCa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('お名前(フリガナ・名)', 'shipping_kana02', STEXT_LEN, 'KVCa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('会社名', 'shipping_company_name', STEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        if (FORM_COUNTRY_ENABLE) {
            $objFormParam->addParam('国', 'shipping_country_id', INT_LEN, 'n', ['EXIST_CHECK', 'NUM_CHECK']);
            $objFormParam->addParam('ZIPCODE', 'shipping_zipcode', STEXT_LEN, 'n', ['NO_SPTAB', 'SPTAB_CHECK', 'GRAPH_CHECK', 'MAX_LENGTH_CHECK']);
        }
        $objFormParam->addParam('郵便番号1', 'shipping_zip01', ZIP01_LEN, 'n', ['NUM_CHECK', 'NUM_COUNT_CHECK']);
        $objFormParam->addParam('郵便番号2', 'shipping_zip02', ZIP02_LEN, 'n', ['NUM_CHECK', 'NUM_COUNT_CHECK']);
        $objFormParam->addParam('都道府県', 'shipping_pref', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('住所1', 'shipping_addr01', MTEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('住所2', 'shipping_addr02', MTEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('電話番号1', 'shipping_tel01', TEL_ITEM_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('電話番号2', 'shipping_tel02', TEL_ITEM_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('電話番号3', 'shipping_tel03', TEL_ITEM_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('お届け時間ID', 'time_id', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('お届け日(年)', 'shipping_date_year', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('お届け日(月)', 'shipping_date_month', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('お届け日(日)', 'shipping_date_day', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('お届け日', 'shipping_date', STEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('配送商品数量', 'shipping_product_quantity', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);

        $objFormParam->addParam('商品規格ID', 'shipment_product_class_id', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('商品コード', 'shipment_product_code');
        $objFormParam->addParam('商品名', 'shipment_product_name');
        $objFormParam->addParam('規格名1', 'shipment_classcategory_name1');
        $objFormParam->addParam('規格名2', 'shipment_classcategory_name2');
        $objFormParam->addParam('単価', 'shipment_price', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK'], '0');
        $objFormParam->addParam('数量', 'shipment_quantity', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK'], '0');

        $objFormParam->addParam('商品項番', 'no', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('追加商品規格ID', 'add_product_class_id', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('修正商品規格ID', 'edit_product_class_id', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('アンカーキー', 'anchor_key', STEXT_LEN, 'KVa', ['SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
    }

    /**
     * 受注データを取得して, SC_FormParam へ設定する.
     *
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     * @param  int      $order_id     取得元の受注ID
     *
     * @return void
     */
    public function setOrderToFormParam(&$objFormParam, $order_id)
    {
        $objPurchase = new SC_Helper_Purchase_Ex();

        // 受注詳細を設定
        $arrOrderDetail = $objPurchase->getOrderDetail($order_id, false);
        $objFormParam->setParam(SC_Utils_Ex::sfSwapArray($arrOrderDetail));

        $arrShippingsTmp = $objPurchase->getShippings($order_id);
        $arrShippings = [];
        foreach ($arrShippingsTmp as $row) {
            // お届け日の処理
            if (!SC_Utils_Ex::isBlank($row['shipping_date'])) {
                $ts = strtotime($row['shipping_date']);
                $row['shipping_date_year'] = date('Y', $ts);
                $row['shipping_date_month'] = date('n', $ts);
                $row['shipping_date_day'] = date('j', $ts);
            }
            $arrShippings[$row['shipping_id']] = $row;
        }
        $objFormParam->setParam(SC_Utils_Ex::sfSwapArray($arrShippings));

        /*
         * 配送商品を設定
         *
         * $arrShipmentItem['shipment_(key)'][$shipping_id][$item_index] = 値
         * $arrProductQuantity[$shipping_id] = 配送先ごとの配送商品数量
         */
        $arrProductQuantity = [];
        $arrShipmentItem = [];
        foreach ($arrShippings as $shipping_id => $arrShipping) {
            $arrProductQuantity[$shipping_id] = count($arrShipping['shipment_item']);
            foreach ($arrShipping['shipment_item'] as $item_index => $arrItem) {
                foreach ($arrItem as $item_key => $item_val) {
                    $arrShipmentItem['shipment_'.$item_key][$shipping_id][$item_index] = $item_val;
                }
            }
        }
        $objFormParam->setValue('shipping_product_quantity', $arrProductQuantity);
        $objFormParam->setParam($arrShipmentItem);

        /*
         * 受注情報を設定
         * $arrOrderDetail と項目が重複しており, $arrOrderDetail は連想配列の値
         * が渡ってくるため, $arrOrder で上書きする.
         */
        $arrOrder = $objPurchase->getOrder($order_id);

        // 生年月日の処理
        if (isset($arrOrder['order_birth'])) {
            $orderBirth = new DateTimeImmutable($arrOrder['order_birth']);
            $arrOrder['order_birth_year'] = (int) $orderBirth->format('Y');
            $arrOrder['order_birth_month'] = (int) $orderBirth->format('n');
            $arrOrder['order_birth_day'] = (int) $orderBirth->format('j');
        }

        $objFormParam->setParam($arrOrder);

        // ポイントを設定
        [$db_point, $rollback_point] = SC_Helper_DB_Ex::sfGetRollbackPoint(
            $order_id,
            $arrOrder['use_point'] ?? 0,
            $arrOrder['add_point'] ?? 0,
            $arrOrder['status'] ?? null
        );
        $objFormParam->setValue('total_point', $db_point);
        $objFormParam->setValue('point', $rollback_point);

        if (!SC_Utils_Ex::isBlank($objFormParam->getValue('customer_id'))) {
            $arrCustomer = SC_Helper_Customer_Ex::sfGetCustomerDataFromId($objFormParam->getValue('customer_id'));
            $objFormParam->setValue('customer_point', $arrCustomer['point'] ?? 0);
        }
    }
}
