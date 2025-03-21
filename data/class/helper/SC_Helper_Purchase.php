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
 * 商品購入関連のヘルパークラス.
 *
 * TODO 購入時強制会員登録機能(#521)の実装を検討
 * TODO dtb_customer.buy_times, dtb_customer.buy_total の更新
 *
 * @author Kentaro Ohkouchi
 *
 * @version $Id$
 */
class SC_Helper_Purchase
{
    public $arrShippingKey = [
        'name01',
        'name02',
        'kana01',
        'kana02',
        'company_name',
        'sex',
        'zip01',
        'zip02',
        'country_id',
        'zipcode',
        'pref',
        'addr01',
        'addr02',
        'tel01',
        'tel02',
        'tel03',
        'fax01',
        'fax02',
        'fax03',
    ];

    /**
     * 受注を完了する.
     *
     * 下記のフローで受注を完了する.
     *
     * 1. トランザクションを開始する
     * 2. カートの内容を検証する.
     * 3. 受注一時テーブルから受注データを読み込む
     * 4. ユーザーがログインしている場合はその他の発送先へ登録する
     * 5. 受注データを受注テーブルへ登録する
     * 6. トランザクションをコミットする
     *
     * 実行中に, 何らかのエラーが発生した場合, 処理を中止しエラーページへ遷移する
     *
     * 決済モジュールを使用する場合は対応状況を「決済処理中」に設定し,
     * 決済完了後「新規受付」に変更すること
     *
     * @param  int $orderStatus 受注処理を完了する際に設定する対応状況
     *
     * @return void
     */
    public function completeOrder($orderStatus = ORDER_NEW)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objSiteSession = new SC_SiteSession_Ex();
        $objCartSession = new SC_CartSession_Ex();
        $objCustomer = new SC_Customer_Ex();
        $customerId = $objCustomer->getValue('customer_id');

        $objQuery->begin();
        if (!$objSiteSession->isPrePage()) {
            // エラー時は、正当なページ遷移とは認めない
            $objSiteSession->setNowPage('');

            SC_Utils_Ex::sfDispSiteError(PAGE_ERROR, $objSiteSession);
        }

        $uniqId = $objSiteSession->getUniqId();
        $this->verifyChangeCart($uniqId, $objCartSession);

        $orderTemp = $this->getOrderTemp($uniqId);

        $orderTemp['status'] = $orderStatus;
        $cartkey = $objCartSession->getKey();
        $order_id = $this->registerOrderComplete($orderTemp, $objCartSession, $cartkey);
        $isMultiple = self::isMultiple();
        $shippingTemp = &$this->getShippingTemp($isMultiple);
        foreach ($shippingTemp as $shippingId => $val) {
            $this->registerShipmentItem($order_id, $shippingId, $val['shipment_item']);
        }

        $this->registerShipping($order_id, $shippingTemp);
        $objQuery->commit();

        // 会員情報の最終購入日、購入合計を更新
        if ($customerId > 0) {
            SC_Customer_Ex::updateOrderSummary($customerId);
        }

        $this->cleanupSession($order_id, $objCartSession, $objCustomer, $cartkey);

        GC_Utils_Ex::gfPrintLog('order complete. order_id='.$order_id);
    }

    /**
     * 受注をキャンセルする.
     *
     * 受注完了後の受注をキャンセルする.
     * この関数は, 主に決済モジュールにて, 受注をキャンセルする場合に使用する.
     *
     * 対応状況を引数 $orderStatus で指定した値に変更する.
     * (デフォルト ORDER_CANCEL)
     * 引数 $is_delete が true の場合は, 受注データを論理削除する.
     * 商品の在庫数は, 受注前の在庫数に戻される.
     *
     * @param  int $order_id    受注ID
     * @param  int $orderStatus 対応状況
     * @param  bool $is_delete   受注データを論理削除する場合 true
     *
     * @return void
     */
    public static function cancelOrder($order_id, $orderStatus = ORDER_CANCEL, $is_delete = false)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $in_transaction = $objQuery->inTransaction();
        if (!$in_transaction) {
            $objQuery->begin();
        }

        $arrParams = [];
        $arrParams['status'] = $orderStatus;
        if ($is_delete) {
            $arrParams['del_flg'] = 1;
        }

        static::registerOrder($order_id, $arrParams);

        $arrOrderDetail = static::getOrderDetail($order_id);
        foreach ($arrOrderDetail as $arrDetail) {
            $objQuery->update(
                'dtb_products_class',
                [],
                'product_class_id = ?',
                [$arrDetail['product_class_id']],
                ['stock' => 'stock + ?'],
                [$arrDetail['quantity']]
            );
        }
        if (!$in_transaction) {
            $objQuery->commit();
        }
    }

    /**
     * 受注をキャンセルし, カートをロールバックして, 受注一時IDを返す.
     *
     * 受注完了後の受注をキャンセルし, カートの状態を受注前の状態へ戻す.
     * この関数は, 主に, 決済モジュールに遷移した後, 購入確認画面へ戻る場合に使用する.
     *
     * 対応状況を引数 $orderStatus で指定した値に変更する.
     * (デフォルト ORDER_CANCEL)
     * 引数 $is_delete が true の場合は, 受注データを論理削除する.
     * 商品の在庫数, カートの内容は受注前の状態に戻される.
     *
     * @param  int $order_id    受注ID
     * @param  int $orderStatus 対応状況
     * @param  bool $is_delete   受注データを論理削除する場合 true
     *
     * @return string  受注一時ID
     */
    public static function rollbackOrder($order_id, $orderStatus = ORDER_CANCEL, $is_delete = false)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $in_transaction = $objQuery->inTransaction();
        if (!$in_transaction) {
            $objQuery->begin();
        }

        static::cancelOrder($order_id, $orderStatus, $is_delete);
        $arrOrderTemp = static::getOrderTempByOrderId($order_id);
        $objSiteSession = new SC_SiteSession_Ex();
        $uniqid = $objSiteSession->getUniqId();

        if (!empty($arrOrderTemp)) {
            $tempSession = unserialize($arrOrderTemp['session'] ?? '');
            $_SESSION = array_merge($_SESSION, $tempSession === false ? [] : $tempSession);

            $objCartSession = new SC_CartSession_Ex();
            $objCustomer = new SC_Customer_Ex();

            // 新たに受注一時情報を保存する
            $objSiteSession->unsetUniqId();
            $uniqid = $objSiteSession->getUniqId();
            $arrOrderTemp['del_flg'] = 0;
            static::saveOrderTemp($uniqid, $arrOrderTemp, $objCustomer);
            static::verifyChangeCart($uniqid, $objCartSession);
            $objSiteSession->setRegistFlag();
        }

        if (!$in_transaction) {
            $objQuery->commit();
        }

        return $uniqid;
    }

    /**
     * カートに変化が無いか検証する.
     *
     * ユニークIDとセッションのユニークIDを比較し, 異なる場合は
     * エラー画面を表示する.
     *
     * カートが空の場合, 購入ボタン押下後にカートが変更された場合は
     * カート画面へ遷移する.
     *
     * @param  string         $uniqId         ユニークID
     * @param  SC_CartSession $objCartSession
     *
     * @return void
     */
    public static function verifyChangeCart($uniqId, &$objCartSession)
    {
        $cartKey = $objCartSession->getKey();

        // カート内が空でないか
        if (SC_Utils_Ex::isBlank($cartKey)) {
            SC_Response_Ex::sendRedirect(CART_URL);
            exit;
        }

        // 初回のみカートの内容を保存
        $objCartSession->saveCurrentCart($uniqId, $cartKey);

        /*
         * POSTのユニークIDとセッションのユニークIDを比較
         *(ユニークIDがPOSTされていない場合はスルー)
         */
        if (!SC_SiteSession_Ex::checkUniqId()) {
            SC_Utils_Ex::sfDispSiteError(CANCEL_PURCHASE);
            exit;
        }

        // 購入ボタンを押してから変化がないか
        $quantity = $objCartSession->getTotalQuantity($cartKey);
        if ($objCartSession->checkChangeCart($cartKey) || !($quantity > 0)) {
            SC_Response_Ex::sendRedirect(CART_URL);
            exit;
        }
    }

    /**
     * 受注一時情報を取得する.
     *
     * @param  int $uniqId 受注一時情報ID
     *
     * @return array   受注一時情報の配列
     */
    public static function getOrderTemp($uniqId)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $result = $objQuery->getRow('*', 'dtb_order_temp', 'order_temp_id = ?', [$uniqId]);

        return is_array($result) ? $result : []; // 必ず配列を返す
    }

    /**
     * 受注IDをキーにして受注一時情報を取得する.
     *
     * @param  int $order_id 受注ID
     *
     * @return array   受注一時情報の配列
     */
    public static function getOrderTempByOrderId($order_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        return $objQuery->getRow('*', 'dtb_order_temp', 'order_id = ?', [$order_id]);
    }

    /**
     * 受注一時情報を保存する.
     *
     * 既存のデータが存在しない場合は新規保存. 存在する場合は更新する.
     *
     * @param  int     $uniqId      受注一時情報ID
     * @param  array       $params      登録する受注情報の配列
     * @param  SC_Customer $objCustomer SC_Customer インスタンス
     *
     * @return void
     */
    public static function saveOrderTemp($uniqId, $params, &$objCustomer = null)
    {
        if (SC_Utils_Ex::isBlank($uniqId)) {
            return;
        }
        $params['device_type_id'] = SC_Display_Ex::detectDevice();
        $objQuery = SC_Query_Ex::getSingletonInstance();
        // 存在するカラムのみを対象とする
        $cols = $objQuery->listTableFields('dtb_order_temp');
        $sqlval = [];
        foreach ($params as $key => $val) {
            if (in_array($key, $cols)) {
                $sqlval[$key] = $val;
            }
        }

        $sqlval['session'] = isset($_SESSION) ? serialize($_SESSION) : '';
        if (!empty($objCustomer)) {
            // 注文者の情報を常に最新に保つ
            static::copyFromCustomer($sqlval, $objCustomer);
        }
        $exists = SC_Helper_Purchase_Ex::getOrderTemp($uniqId);

        // 国ID追加
        $sqlval['order_country_id'] ??= DEFAULT_COUNTRY_ID;

        if (SC_Utils_Ex::isBlank($exists)) {
            $sqlval['order_temp_id'] = $uniqId;
            $sqlval['create_date'] = 'CURRENT_TIMESTAMP';
            $objQuery->insert('dtb_order_temp', $sqlval);
        } else {
            $objQuery->update('dtb_order_temp', $sqlval, 'order_temp_id = ?', [$uniqId]);
        }
    }

    /**
     * 配送情報をセッションから取得する.
     *
     * @param bool $has_shipment_item 配送商品を保有している配送先のみ返す。
     */
    public static function getShippingTemp($has_shipment_item = false)
    {
        // ダウンロード商品の場合setされていないので空の配列を返す.
        if (!isset($_SESSION['shipping'])) {
            return [];
        }
        if ($has_shipment_item) {
            $arrReturn = [];
            foreach ($_SESSION['shipping'] as $key => $arrVal) {
                if (is_array($arrVal['shipment_item']) && count($arrVal['shipment_item']) == 0) {
                    continue;
                }
                $arrReturn[$key] = $arrVal;
            }

            return $arrReturn;
        }

        return $_SESSION['shipping'];
    }

    /**
     * 配送商品をクリア(消去)する
     *
     * @param  int $shipping_id 配送先ID
     *
     * @return void
     */
    public function clearShipmentItemTemp($shipping_id = null)
    {
        if (is_null($shipping_id)) {
            foreach ($_SESSION['shipping'] as $key => $value) {
                $this->clearShipmentItemTemp($key);
            }
        } else {
            if (!isset($_SESSION['shipping'][$shipping_id])) {
                return;
            }
            if (!is_array($_SESSION['shipping'][$shipping_id])) {
                return;
            }
            unset($_SESSION['shipping'][$shipping_id]['shipment_item']);
        }
    }

    /**
     * 配送商品を設定する.
     *
     * @param  int $shipping_id      配送先ID
     * @param  int $product_class_id 商品規格ID
     * @param  int $quantity         数量
     *
     * @return void
     */
    public function setShipmentItemTemp($shipping_id, $product_class_id, $quantity)
    {
        // 配列が長くなるので, リファレンスを使用する
        $arrItems = &$_SESSION['shipping'][$shipping_id]['shipment_item'][$product_class_id];

        $arrItems['shipping_id'] = $shipping_id;
        $arrItems['product_class_id'] = $product_class_id;
        $arrItems['quantity'] = $quantity;

        $objProduct = new SC_Product_Ex();

        // カート情報から読みこめば済むと思うが、一旦保留。むしろ、カート情報も含め、セッション情報を縮小すべきかもしれない。
        /*
        $objCartSession = new SC_CartSession_Ex();
        $cartKey = $objCartSession->getKey();
        // 詳細情報を取得
        $cartItems = $objCartSession->getCartList($cartKey);
        */

        if (empty($arrItems['productsClass'])) {
            $product = &$objProduct->getDetailAndProductsClass($product_class_id);
            // セッション変数のデータ量を抑制するため、一部の商品情報を切り捨てる
            $objCartSession = new SC_CartSession_Ex();
            $objCartSession->adjustSessionProductsClass($product);
            $arrItems['productsClass'] = $product;
        }
        $arrItems['price'] = $arrItems['productsClass']['price02'];
        $inctax = SC_Helper_TaxRule_Ex::sfCalcIncTax(
            $arrItems['price'],
            $arrItems['productsClass']['product_id'] ?? 0,
            $arrItems['productsClass']['product_class_id'] ?? 0
        );
        $arrItems['total_inctax'] = $inctax * $arrItems['quantity'];
    }

    /**
     * 配送先都道府県の配列を返す.
     *
     * @param bool $is_multiple
     */
    public static function getShippingPref($is_multiple)
    {
        $results = [];
        foreach (SC_Helper_Purchase_Ex::getShippingTemp($is_multiple) as $val) {
            $results[] = $val['shipping_pref'];
        }

        return $results;
    }

    /**
     * 複数配送指定の購入かどうか.
     *
     * @return bool 複数配送指定の購入の場合 true
     */
    public function isMultiple()
    {
        return count(SC_Helper_Purchase_Ex::getShippingTemp(true)) >= 2;
    }

    /**
     * 配送情報をセッションに保存する.
     *
     * XXX マージする理由が不明(なんとなく便利な気はするけど)。分かる方コメントに残してください。
     *
     * @param  array   $arrSrc      配送情報の連想配列
     * @param  int $shipping_id 配送先ID
     *
     * @return void
     */
    public static function saveShippingTemp($arrSrc, $shipping_id = 0)
    {
        // 配送商品は引き継がない
        unset($arrSrc['shipment_item']);

        if (!isset($_SESSION['shipping'][$shipping_id])) {
            $_SESSION['shipping'][$shipping_id] = [];
        }
        $_SESSION['shipping'][$shipping_id] = array_merge($_SESSION['shipping'][$shipping_id], $arrSrc);
        $_SESSION['shipping'][$shipping_id]['shipping_id'] = $shipping_id;
    }

    /**
     * セッションの配送情報を破棄する.
     *
     * @deprecated 2.12.0 から EC-CUBE 本体では使用していない。
     *
     * @return void
     */
    public static function unsetShippingTemp()
    {
        SC_Helper_Purchase_Ex::unsetAllShippingTemp(true);
    }

    /**
     * セッションの配送情報を全て破棄する
     *
     * @param  bool $multiple_temp 複数お届け先の画面戻り処理用の情報も破棄するか
     *
     * @return void
     */
    public static function unsetAllShippingTemp($multiple_temp = false)
    {
        unset($_SESSION['shipping']);
        if ($multiple_temp) {
            unset($_SESSION['multiple_temp']);
        }
    }

    /**
     * セッションの配送情報を個別に破棄する
     *
     * @param  int $shipping_id 配送先ID
     *
     * @return void
     */
    public static function unsetOneShippingTemp($shipping_id)
    {
        unset($_SESSION['shipping'][$shipping_id]);
    }

    /**
     * 会員情報を受注情報にコピーする.
     *
     * ユーザーがログインしていない場合は何もしない.
     * 会員情報を $dest の order_* へコピーする.
     * customer_id は強制的にコピーされる.
     *
     * @param  array       $dest        コピー先の配列
     * @param  SC_Customer $objCustomer SC_Customer インスタンス
     * @param  string      $prefix      コピー先の接頭辞. デフォルト order
     * @param  array       $keys        コピー対象のキー
     *
     * @return void
     */
    public static function copyFromCustomer(
        &$dest,
        &$objCustomer,
        $prefix = 'order',
        $keys = [
            'name01',
            'name02',
            'kana01',
            'kana02',
            'company_name',
            'sex',
            'zip01',
            'zip02',
            'country_id',
            'zipcode',
            'pref',
            'addr01',
            'addr02',
            'tel01',
            'tel02',
            'tel03',
            'fax01',
            'fax02',
            'fax03',
            'job',
            'birth',
            'email',
        ]
    ) {
        if ($objCustomer->isLoginSuccess(true)) {
            foreach ($keys as $key) {
                if (in_array($key, $keys)) {
                    $dest[$prefix.'_'.$key] = $objCustomer->getValue($key);
                }
            }

            if ((SC_Display_Ex::detectDevice() == DEVICE_TYPE_MOBILE)
                && in_array('email', $keys)
            ) {
                $email_mobile = $objCustomer->getValue('email_mobile');
                if (empty($email_mobile)) {
                    $dest[$prefix.'_email'] = $objCustomer->getValue('email');
                } else {
                    $dest[$prefix.'_email'] = $email_mobile;
                }
            }

            $dest['customer_id'] = $objCustomer->getValue('customer_id');
            $dest['update_date'] = 'CURRENT_TIMESTAMP';
        }
    }

    /**
     * 受注情報を配送情報にコピーする.
     *
     * 受注情報($src)を $dest の order_* へコピーする.
     *
     * TODO 汎用的にして SC_Utils へ移動
     *
     * @param  array  $dest       コピー先の配列
     * @param  array  $src        コピー元の配列
     * @param  array  $arrKey     コピー対象のキー
     * @param  string $prefix     コピー先の接頭辞. デフォルト shipping
     * @param  string $src_prefix コピー元の接頭辞. デフォルト order
     *
     * @return void
     */
    public function copyFromOrder(&$dest, $src, $prefix = 'shipping', $src_prefix = 'order', $arrKey = null)
    {
        if (is_null($arrKey)) {
            $arrKey = $this->arrShippingKey;
        }
        if (!SC_Utils_Ex::isBlank($prefix)) {
            $prefix .= '_';
        }
        if (!SC_Utils_Ex::isBlank($src_prefix)) {
            $src_prefix .= '_';
        }
        foreach ($arrKey as $key) {
            if (isset($src[$src_prefix.$key])) {
                $dest[$prefix.$key] = $src[$src_prefix.$key];
            }
        }
    }

    /**
     * 配送情報のみ抜き出す。
     *
     * @param  string $arrSrc 元となる配列
     *
     * @return void
     */
    public function extractShipping($arrSrc)
    {
        $arrKey = [];
        foreach ($this->arrShippingKey as $key) {
            $arrKey[] = 'shipping_'.$key;
        }

        return SC_Utils_Ex::sfArrayIntersectKeys($arrSrc, $arrKey);
    }

    /**
     * お届け日一覧を取得する.
     *
     * @param SC_CartSession $objCartSess
     * @param int $product_type_id
     */
    public function getDelivDate(&$objCartSess, $product_type_id)
    {
        $cartList = $objCartSess->getCartList($product_type_id);
        $delivDateIds = [];
        foreach ($cartList as $item) {
            $delivDateIds[] = $item['productsClass']['deliv_date_id'];
        }
        $max_date = max($delivDateIds);
        // 発送目安
        switch ($max_date) {
            // 即日発送
            case '1':
                $start_day = 1;
                break;
                // 1-2日後
            case '2':
                $start_day = 3;
                break;
                // 3-4日後
            case '3':
                $start_day = 5;
                break;
                // 1週間以降
            case '4':
                $start_day = 8;
                break;
                // 2週間以降
            case '5':
                $start_day = 15;
                break;
                // 3週間以降
            case '6':
                $start_day = 22;
                break;
                // 1ヶ月以降
            case '7':
                $start_day = 32;
                break;
                // 2ヶ月以降
            case '8':
                $start_day = 62;
                break;
                // お取り寄せ(商品入荷後)
            case '9':
                $start_day = '';
                break;
            default:
                // お届け日が設定されていない場合
                $start_day = '';
                break;
        }
        // お届け可能日のスタート値から、お届け日の配列を取得する
        $arrDelivDate = $this->getDateArray($start_day, DELIV_DATE_END_MAX);

        return $arrDelivDate;
    }

    /**
     * お届け可能日のスタート値から, お届け日の配列を取得する.
     */
    public function getDateArray($start_day, $end_day)
    {
        $masterData = new SC_DB_MasterData_Ex();
        $arrWDAY = $masterData->getMasterData('mtb_wday');
        $arrDate = [];
        // お届け可能日のスタート値がセットされていれば
        if ($start_day >= 1) {
            $now_time = time();
            $max_day = $start_day + $end_day;
            // 集計
            for ($i = $start_day; $i < $max_day; $i++) {
                // 基本時間から日数を追加していく
                $tmp_time = $now_time + ($i * 24 * 3600);
                [$y, $m, $d, $w] = explode(' ', date('Y m d w', $tmp_time));
                $val = sprintf('%04d/%02d/%02d(%s)', $y, $m, $d, $arrWDAY[$w]);
                $arrDate[$val] = $val;
            }
        } else {
            $arrDate = false;
        }

        return $arrDate;
    }

    /**
     * 配送情報の登録を行う.
     *
     * $arrParam のうち, dtb_shipping テーブルに存在するカラムのみを登録する.
     *
     * TODO UPDATE/INSERT にする
     *
     * @param  int $order_id              受注ID
     * @param  array   $arrParams             配送情報の連想配列
     * @param  bool $convert_shipping_date yyyy/mm/dd(EEE) 形式の配送日付を変換する場合 true
     *
     * @return void
     */
    public static function registerShipping($order_id, $arrParams, $convert_shipping_date = true)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $table = 'dtb_shipping';
        $where = 'order_id = ?';

        $objQuery->delete($table, $where, [$order_id]);

        foreach ($arrParams as $key => $arrShipping) {
            $arrValues = $objQuery->extractOnlyColsOf($table, $arrShipping);

            // 配送日付を timestamp に変換
            if (
                isset($arrValues['shipping_date'])
                && $arrValues['shipping_date'] != ''
                && $convert_shipping_date
            ) {
                $d = mb_strcut($arrValues['shipping_date'], 0, 10);
                $arrDate = explode('/', $d);
                $ts = mktime(0, 0, 0, $arrDate[1], $arrDate[2], $arrDate[0]);
                $arrValues['shipping_date'] = date('Y-m-d', $ts);
            }

            // 非会員購入の場合は shipping_id が存在しない
            if (!isset($arrValues['shipping_id'])) {
                $arrValues['shipping_id'] = $key;
            }
            $arrValues['order_id'] = $order_id;
            $arrValues['create_date'] = 'CURRENT_TIMESTAMP';
            $arrValues['update_date'] = 'CURRENT_TIMESTAMP';
            // 国ID追加
            /*いらないかもしれないんでとりあえずコメントアウト
            $arrValues['shipping_country_id'] = DEFAULT_COUNTRY_ID;
            */

            $objQuery->insert($table, $arrValues);
        }

        $sql_sub = <<< __EOS__
            SELECT deliv_time
            FROM dtb_delivtime
            WHERE time_id = dtb_shipping.time_id
            AND deliv_id = (SELECT dtb_order.deliv_id FROM dtb_order WHERE order_id = dtb_shipping.order_id)
            __EOS__;
        $objQuery->update(
            'dtb_shipping',
            [],
            $where,
            [$order_id],
            ['shipping_time' => "($sql_sub)"]
        );
    }

    /**
     * 配送商品を登録する.
     *
     * @param  int $order_id    受注ID
     * @param  int $shipping_id 配送先ID
     * @param  array   $arrParams   配送商品の配列
     *
     * @return void
     */
    public static function registerShipmentItem($order_id, $shipping_id, $arrParams)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $table = 'dtb_shipment_item';
        $where = 'order_id = ? AND shipping_id = ?';
        $objQuery->delete($table, $where, [$order_id, $shipping_id]);

        $objProduct = new SC_Product_Ex();
        foreach ($arrParams as $arrValues) {
            if (!isset($arrValues['product_class_id']) || $arrValues['product_class_id'] == '') {
                continue;
            }
            $d = $objProduct->getDetailAndProductsClass($arrValues['product_class_id']);
            $name = !isset($arrValues['product_name']) || $arrValues['product_name'] == ''
                ? $d['name']
                : $arrValues['product_name'];

            $code = !isset($arrValues['product_code']) || $arrValues['product_code'] == ''
                ? $d['product_code']
                : $arrValues['product_code'];

            $cname1 = !isset($arrValues['classcategory_name1']) || $arrValues['classcategory_name1'] == ''
                ? $d['classcategory_name1']
                : $arrValues['classcategory_name1'];

            $cname2 = !isset($arrValues['classcategory_name2']) || $arrValues['classcategory_name2'] == ''
                ? $d['classcategory_name2']
                : $arrValues['classcategory_name2'];

            $price = !isset($arrValues['price']) || $arrValues['price'] == ''
                ? ($d['price'] ?? null)
                : $arrValues['price'];

            $arrValues['order_id'] = $order_id;
            $arrValues['shipping_id'] = $shipping_id;
            $arrValues['product_name'] = $name;
            $arrValues['product_code'] = $code;
            $arrValues['classcategory_name1'] = $cname1;
            $arrValues['classcategory_name2'] = $cname2;
            $arrValues['price'] = $price;

            $arrExtractValues = $objQuery->extractOnlyColsOf($table, $arrValues);
            $objQuery->insert($table, $arrExtractValues);
        }
    }

    /**
     * 受注登録を完了する.
     *
     * 引数の受注情報を受注テーブル及び受注詳細テーブルに登録する.
     * 登録後, 受注一時テーブルに削除フラグを立てる.
     *
     * @param array          $orderParams    登録する受注情報の配列
     * @param SC_CartSession $objCartSession カート情報のインスタンス
     * @param int        $cartKey        登録を行うカート情報のキー
     *
     * @return int 受注ID
     */
    public function registerOrderComplete($orderParams, &$objCartSession, $cartKey)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        // 不要な変数を unset
        $unsets = [
            'mailmaga_flg',
            'deliv_check',
            'point_check',
            'password',
            'reminder',
            'reminder_answer',
            'mail_flag',
            'session',
        ];
        foreach ($unsets as $unset) {
            unset($orderParams[$unset]);
        }

        // 対応状況の指定が無い場合は新規受付
        if (!isset($orderParams['status']) || $orderParams['status'] == '') {
            $orderParams['status'] = ORDER_NEW;
        }

        $orderParams['del_flg'] = '0';
        $orderParams['create_date'] = 'CURRENT_TIMESTAMP';
        $orderParams['update_date'] = 'CURRENT_TIMESTAMP';

        $this->registerOrder($orderParams['order_id'], $orderParams);

        // 詳細情報を取得
        $cartItems = $objCartSession->getCartList($cartKey, $orderParams['order_pref'] ?? 0, $orderParams['order_country_id'] ?? 0);

        // 詳細情報を生成
        $objProduct = new SC_Product_Ex();
        $i = 0;
        $arrDetail = [];
        foreach ($cartItems as $item) {
            $p = &$item['productsClass'];
            $arrDetail[$i]['order_id'] = $orderParams['order_id'];
            $arrDetail[$i]['product_id'] = $p['product_id'];
            $arrDetail[$i]['product_class_id'] = $p['product_class_id'];
            $arrDetail[$i]['product_name'] = $p['name'];
            $arrDetail[$i]['product_code'] = $p['product_code'];
            $arrDetail[$i]['classcategory_name1'] = $p['classcategory_name1'];
            $arrDetail[$i]['classcategory_name2'] = $p['classcategory_name2'];
            $arrDetail[$i]['point_rate'] = $item['point_rate'];
            $arrDetail[$i]['price'] = $item['price'];
            $arrDetail[$i]['quantity'] = $item['quantity'];
            $arrDetail[$i]['tax_rate'] = $item['tax_rate'] ?? null;
            $arrDetail[$i]['tax_rule'] = $item['tax_rule'] ?? null;
            $arrDetail[$i]['tax_adjust'] = $item['tax_adjust'] ?? null;

            // 在庫の減少処理
            if (!$objProduct->reduceStock($p['product_class_id'], $item['quantity'])) {
                $objQuery->rollback();
                SC_Utils_Ex::sfDispSiteError(SOLD_OUT, '', true);
            }
            $i++;
        }
        $this->registerOrderDetail($orderParams['order_id'], $arrDetail);

        $objQuery->update(
            'dtb_order_temp',
            ['del_flg' => 1],
            'order_temp_id = ?',
            [SC_SiteSession_Ex::getUniqId()]
        );

        return $orderParams['order_id'];
    }

    /**
     * 受注情報を登録する.
     *
     * 既に受注IDが存在する場合は, 受注情報を更新する.
     * 引数の受注IDが, 空白又は null の場合は, 新しく受注IDを発行して登録する.
     *
     * @param  int $order_id  受注ID
     * @param  array   $arrParams 受注情報の連想配列
     *
     * @return int 受注ID
     */
    public static function registerOrder($order_id, $arrParams)
    {
        $table = 'dtb_order';
        $where = 'order_id = ?';
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrValues = $objQuery->extractOnlyColsOf($table, $arrParams);

        $exists = $objQuery->exists($table, $where, [$order_id]);
        if ($exists) {
            static::sfUpdateOrderStatus(
                $order_id,
                $arrValues['status'],
                $arrValues['add_point'],
                $arrValues['use_point'],
                $arrValues
            );
            static::sfUpdateOrderNameCol($order_id);

            $arrValues['update_date'] = 'CURRENT_TIMESTAMP';
            $objQuery->update($table, $arrValues, $where, [$order_id]);
        } else {
            if (SC_Utils_Ex::isBlank($order_id)) {
                $order_id = static::getNextOrderID();
            }
            /*
             * 新規受付の場合は対応状況 null で insert し,
             * sfUpdateOrderStatus で引数で受け取った値に変更する.
             */
            $status = $arrValues['status'];
            $arrValues['status'] = null;
            $arrValues['order_id'] = $order_id;
            $arrValues['customer_id'] =
                SC_Utils_Ex::isBlank($arrValues['customer_id'])
                ? 0 : $arrValues['customer_id'];
            $arrValues['create_date'] = 'CURRENT_TIMESTAMP';
            $arrValues['update_date'] = 'CURRENT_TIMESTAMP';
            $objQuery->insert($table, $arrValues);

            static::sfUpdateOrderStatus(
                $order_id,
                $status,
                $arrValues['add_point'],
                $arrValues['use_point'],
                $arrValues
            );
            static::sfUpdateOrderNameCol($order_id);
        }

        return $order_id;
    }

    /**
     * 受注詳細情報を登録する.
     *
     * 既に, 該当の受注が存在する場合は, 受注情報を削除し, 登録する.
     *
     * @param  int $order_id  受注ID
     * @param  array   $arrParams 受注情報の連想配列
     *
     * @return void
     */
    public static function registerOrderDetail($order_id, $arrParams)
    {
        $table = 'dtb_order_detail';
        $where = 'order_id = ?';
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $objQuery->delete($table, $where, [$order_id]);
        foreach ($arrParams as $arrDetail) {
            $arrValues = $objQuery->extractOnlyColsOf($table, $arrDetail);
            $arrValues['order_detail_id'] = $objQuery->nextVal('dtb_order_detail_order_detail_id');
            $arrValues['order_id'] = $order_id;
            $objQuery->insert($table, $arrValues);
        }
    }

    /**
     * 受注情報を取得する.
     *
     * @param  int $order_id    受注ID
     * @param  int $customer_id 会員ID
     *
     * @return array   受注情報の配列
     */
    public static function getOrder($order_id, $customer_id = null)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $where = 'order_id = ?';
        $arrValues = [$order_id];
        if (!SC_Utils_Ex::isBlank($customer_id)) {
            $where .= ' AND customer_id = ?';
            $arrValues[] = $customer_id;
        }

        return $objQuery->getRow('*', 'dtb_order', $where, $arrValues);
    }

    /**
     * 受注詳細を取得する.
     *
     * @param  int $order_id         受注ID
     * @param  bool $has_order_status 対応状況, 入金日も含める場合 true
     *
     * @return array   受注詳細の配列
     */
    public static function getOrderDetail($order_id, $has_order_status = true)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $dbFactory = SC_DB_DBFactory_Ex::getInstance();
        $col = <<< __EOS__
            T3.product_id,
            T3.product_class_id as product_class_id,
            T3.product_type_id AS product_type_id,
            T2.product_code,
            T2.product_name,
            T2.classcategory_name1 AS classcategory_name1,
            T2.classcategory_name2 AS classcategory_name2,
            T2.price,
            T2.quantity,
            T2.point_rate,
            T2.tax_rate,
            T2.tax_rule,
            __EOS__;
        if ($has_order_status) {
            $col .= 'T1.status AS status, T1.payment_date AS payment_date,';
        }
        $col .= <<< __EOS__
            CASE WHEN
                EXISTS(
                    SELECT * FROM dtb_products
                    WHERE product_id = T3.product_id
                        AND del_flg = 0
                        AND status = 1
                )
                THEN '1'
                ELSE '0'
            END AS enable,
            __EOS__;
        $col .= $dbFactory->getDownloadableDaysWhereSql('T1').' AS effective';
        $from = <<< __EOS__
            dtb_order T1
            JOIN dtb_order_detail T2
                ON T1.order_id = T2.order_id
            LEFT JOIN dtb_products_class T3
                ON T2.product_class_id = T3.product_class_id
            __EOS__;
        $objQuery->setOrder('T2.order_detail_id');

        return $objQuery->select($col, $from, 'T1.order_id = ?', [$order_id]);
    }

    /**
     * ダウンロード可能フラグを, 受注詳細に設定する.
     *
     * ダウンロード可能と判断されるのは, 以下の通り.
     *
     * 1. ダウンロード可能期限が期限内かつ, 入金日が入力されている
     * 2. 販売価格が 0 円である
     *
     * 受注詳細行には, is_downloadable という真偽値が設定される.
     *
     * @param array 受注詳細の配列
     *
     * @return void
     */
    public static function setDownloadableFlgTo(&$arrOrderDetail)
    {
        foreach ($arrOrderDetail as $key => $value) {
            // 販売価格が 0 円
            if ($arrOrderDetail[$key]['price'] == '0') {
                $arrOrderDetail[$key]['is_downloadable'] = true;
            // ダウンロード期限内かつ, 入金日あり
            } elseif (
                $arrOrderDetail[$key]['effective'] == '1'
                && !SC_Utils_Ex::isBlank($arrOrderDetail[$key]['payment_date'])
            ) {
                $arrOrderDetail[$key]['is_downloadable'] = true;
            } else {
                $arrOrderDetail[$key]['is_downloadable'] = false;
            }
        }
    }

    /**
     * 配送情報を取得する.
     *
     * @param  int $order_id  受注ID
     * @param  bool $has_items 結果に配送商品も含める場合 true
     *
     * @return array   配送情報の配列
     */
    public function getShippings($order_id, $has_items = true)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrResults = [];
        $objQuery->setOrder('shipping_id');
        $arrShippings = $objQuery->select(
            '*',
            'dtb_shipping',
            'order_id = ?',
            [$order_id]
        );
        // shipping_id ごとの配列を生成する
        foreach ($arrShippings as $shipping) {
            foreach ($shipping as $key => $val) {
                $arrResults[$shipping['shipping_id']][$key] = $val;
            }
        }

        if ($has_items) {
            foreach ($arrResults as $shipping_id => $value) {
                $arrResults[$shipping_id]['shipment_item']
                    = &$this->getShipmentItems($order_id, $shipping_id);
            }
        }

        return $arrResults;
    }

    /**
     * 配送商品を取得する.
     *
     * @param  int $order_id    受注ID
     * @param  int $shipping_id 配送先ID
     * @param  bool $has_detail  商品詳細も取得する場合 true
     *
     * @return array   商品規格IDをキーにした配送商品の配列
     */
    public static function getShipmentItems($order_id, $shipping_id, $has_detail = true)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objProduct = new SC_Product_Ex();
        $arrResults = [];
        $objQuery->setOrder('order_detail_id');
        $arrItems = $objQuery->select(
            'dtb_shipment_item.*',
            'dtb_shipment_item JOIN dtb_order_detail
                                           ON dtb_shipment_item.product_class_id = dtb_order_detail.product_class_id
                                           AND dtb_shipment_item.order_id = dtb_order_detail.order_id',
            'dtb_order_detail.order_id = ? AND shipping_id = ?',
            [$order_id, $shipping_id]
        );

        foreach ($arrItems as $key => $arrItem) {
            $product_class_id = $arrItem['product_class_id'];

            foreach ($arrItem as $detailKey => $detailVal) {
                $arrResults[$key][$detailKey] = $detailVal;
            }
            // 商品詳細を関連づける
            if ($has_detail) {
                $arrResults[$key]['productsClass']
                    = &$objProduct->getDetailAndProductsClass($product_class_id);
            }
        }

        return $arrResults;
    }

    /**
     * 注文受付メールを送信する.
     *
     * 端末種別IDにより, 携帯電話の場合は携帯用の文面,
     * それ以外の場合は PC 用の文面でメールを送信する.
     *
     * @param int $order_id 受注ID
     * @param  object  $objPage LC_Page インスタンス
     *
     * @return bool 送信に成功したか。現状では、正確には取得できない。
     */
    public static function sendOrderMail($order_id, &$objPage = null)
    {
        $objMail = new SC_Helper_Mail_Ex();

        // setPageは、プラグインの処理に必要(see #1798)
        if (is_object($objPage)) {
            $objMail->setPage($objPage);
        }

        $arrOrder = SC_Helper_Purchase_Ex::getOrder($order_id);
        if (empty($arrOrder)) {
            return false; // 失敗
        }
        $template_id = $arrOrder['device_type_id'] == DEVICE_TYPE_MOBILE ? 2 : 1;
        $objMail->sfSendOrderMail($order_id, $template_id);

        return true; // 成功
    }

    /**
     * 受注.対応状況の更新
     *
     * 必ず呼び出し元でトランザクションブロックを開いておくこと。
     *
     * @param  int      $orderId     注文番号
     * @param  int|null $newStatus   対応状況 (null=変更無し)
     * @param  int|null $newAddPoint 加算ポイント (null=変更無し)
     * @param  int|null $newUsePoint 使用ポイント (null=変更無し)
     * @param  array        $sqlval      更新後の値をリファレンスさせるためのパラメーター
     *
     * @return void
     */
    public static function sfUpdateOrderStatus($orderId, $newStatus = null, $newAddPoint = null, $newUsePoint = null, &$sqlval = [])
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrOrderOld = $objQuery->getRow('status, add_point, use_point, customer_id', 'dtb_order', 'order_id = ?', [$orderId]);

        // 対応状況が変更無しの場合、DB値を引き継ぐ
        if (is_null($newStatus)) {
            $newStatus = $arrOrderOld['status'];
        }

        // 使用ポイント、DB値を引き継ぐ
        if (is_null($newUsePoint)) {
            $newUsePoint = $arrOrderOld['use_point'];
        }

        // 加算ポイント、DB値を引き継ぐ
        if (is_null($newAddPoint)) {
            $newAddPoint = $arrOrderOld['add_point'];
        }

        if (USE_POINT !== false) {
            // 会員.ポイントの加減値
            $addCustomerPoint = 0;

            // ▼使用ポイント
            // 変更前の対応状況が利用対象の場合、変更前の使用ポイント分を戻す
            if (static::isUsePoint($arrOrderOld['status'])) {
                $addCustomerPoint += $arrOrderOld['use_point'];
            }

            // 変更後の対応状況が利用対象の場合、変更後の使用ポイント分を引く
            if (static::isUsePoint($newStatus)) {
                $addCustomerPoint -= $newUsePoint;
            }
            // ▲使用ポイント

            // ▼加算ポイント
            // 変更前の対応状況が加算対象の場合、変更前の加算ポイント分を戻す
            if (static::isAddPoint($arrOrderOld['status'])) {
                $addCustomerPoint -= $arrOrderOld['add_point'];
            }

            // 変更後の対応状況が加算対象の場合、変更後の加算ポイント分を足す
            if (static::isAddPoint($newStatus)) {
                $addCustomerPoint += $newAddPoint;
            }
            // ▲加算ポイント

            if ($addCustomerPoint != 0) {
                // ▼会員テーブルの更新
                $objQuery->update(
                    'dtb_customer',
                    ['update_date' => 'CURRENT_TIMESTAMP'],
                    'customer_id = ?',
                    [$arrOrderOld['customer_id']],
                    ['point' => 'point + ?'],
                    [$addCustomerPoint]
                );
                // ▲会員テーブルの更新

                // 会員.ポイントをマイナスした場合、
                if ($addCustomerPoint < 0) {
                    $sql = 'SELECT point FROM dtb_customer WHERE customer_id = ?';
                    $point = $objQuery->getOne($sql, [$arrOrderOld['customer_id']]);
                    // 変更後の会員.ポイントがマイナスの場合、
                    if ($point < 0) {
                        // ロールバック
                        $objQuery->rollback();
                        // エラー
                        SC_Utils_Ex::sfDispSiteError(LACK_POINT);
                    }
                }
            }
        }

        // ▼受注テーブルの更新
        if (empty($sqlval)) {
            $sqlval = [];
        }

        if (USE_POINT !== false) {
            $sqlval['add_point'] = $newAddPoint;
            $sqlval['use_point'] = $newUsePoint;
        }
        // 対応状況が発送済みに変更の場合、発送日を更新
        if ($arrOrderOld['status'] != ORDER_DELIV && $newStatus == ORDER_DELIV) {
            $sqlval['commit_date'] = 'CURRENT_TIMESTAMP';
        // 対応状況が入金済みに変更の場合、入金日を更新
        } elseif ($arrOrderOld['status'] != ORDER_PRE_END && $newStatus == ORDER_PRE_END) {
            $sqlval['payment_date'] = 'CURRENT_TIMESTAMP';
        }

        $sqlval['status'] = $newStatus;
        $sqlval['update_date'] = 'CURRENT_TIMESTAMP';

        $dest = $objQuery->extractOnlyColsOf('dtb_order', $sqlval);
        $objQuery->update('dtb_order', $dest, 'order_id = ?', [$orderId]);
        // ▲受注テーブルの更新

        // 会員情報の最終購入日、購入合計を更新
        if ($arrOrderOld['customer_id'] > 0 && $arrOrderOld['status'] != $newStatus) {
            SC_Customer_Ex::updateOrderSummary($arrOrderOld['customer_id']);
        }
    }

    /**
     * 受注の名称列を更新する
     *
     * @param int $order_id   更新対象の注文番号
     * @param bool $temp_table 更新対象は「受注_Temp」か
     *
     * @static
     */
    public static function sfUpdateOrderNameCol($order_id, $temp_table = false)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        if ($temp_table) {
            $tgt_table = 'dtb_order_temp';
            $sql_where = 'order_temp_id = ?';
        } else {
            $tgt_table = 'dtb_order';
            $sql_where = 'order_id = ?';
        }

        $objQuery->update(
            $tgt_table,
            [],
            $sql_where,
            [$order_id],
            ['payment_method' => '(SELECT payment_method FROM dtb_payment WHERE payment_id = '.$tgt_table.'.payment_id)']
        );
    }

    /**
     * ポイント使用するかの判定
     *
     * $status が null の場合は false を返す.
     *
     * @param  int $status 対応状況
     *
     * @return bool 使用するか(会員テーブルから減算するか)
     */
    public static function isUsePoint($status)
    {
        if ($status == null) {
            return false;
        }
        switch ($status) {
            case ORDER_CANCEL:      // キャンセル
                return false;
            default:
                break;
        }

        return true;
    }

    /**
     * ポイント加算するかの判定
     *
     * @param  int $status 対応状況
     *
     * @return bool 加算するか
     */
    public static function isAddPoint($status)
    {
        switch ($status) {
            case ORDER_NEW:         // 新規注文
            case ORDER_PAY_WAIT:    // 入金待ち
            case ORDER_PRE_END:     // 入金済み
            case ORDER_CANCEL:      // キャンセル
            case ORDER_BACK_ORDER:  // 取り寄せ中
                return false;

            case ORDER_DELIV:       // 発送済み
                return true;

            default:
                break;
        }

        return false;
    }

    /**
     * セッションに保持している情報を破棄する.
     *
     * 通常、受注処理(completeOrder)完了後に呼び出され、
     * セッション情報を破棄する.
     *
     * 決済モジュール画面から確認画面に「戻る」場合を考慮し、
     * セッション情報を破棄しないカスタマイズを、モジュール側で
     * 加える機会を与える.
     *
     * $orderId が使われていない。
     *
     * @param int        $orderId        注文番号
     * @param SC_CartSession $objCartSession カート情報のインスタンス
     * @param SC_Customer    $objCustomer    SC_Customer インスタンス
     * @param int        $cartKey        登録を行うカート情報のキー
     */
    public static function cleanupSession($orderId, &$objCartSession, &$objCustomer, $cartKey)
    {
        // カートの内容を削除する.
        $objCartSession->delAllProducts($cartKey);
        SC_SiteSession_Ex::unsetUniqId();

        // セッションの配送情報を破棄する.
        static::unsetAllShippingTemp(true);
        $objCustomer->updateSession();
    }

    /**
     * 単一配送指定用に配送商品を設定する
     *
     * @param  SC_CartSession $objCartSession カート情報のインスタンス
     * @param  int        $shipping_id    配送先ID
     *
     * @return void
     */
    public function setShipmentItemTempForSole(&$objCartSession, $shipping_id = 0)
    {
        $objCartSess = new SC_CartSession_Ex();

        $this->clearShipmentItemTemp();

        $arrCartList = &$objCartSession->getCartList($objCartSess->getKey());
        foreach ($arrCartList as $arrCartRow) {
            if ($arrCartRow['quantity'] == 0) {
                continue;
            }
            $this->setShipmentItemTemp($shipping_id, $arrCartRow['id'], $arrCartRow['quantity']);
        }
    }

    /**
     * 新規受注の注文IDを返す
     *
     * @return int
     */
    public static function getNextOrderID()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        return $objQuery->nextVal('dtb_order_order_id');
    }

    /**
     * 決済処理中スタータスの受注データのキャンセル処理
     *
     * @param bool $cancel_flg 決済処理中ステータスのロールバックをするか(true:する false:しない)
     *
     * @return void
     */
    public function cancelPendingOrder($cancel_flg)
    {
        if ($cancel_flg == true) {
            $this->checkDbAllPendingOrder();
            $this->checkDbMyPendignOrder();
            $this->checkSessionPendingOrder();
        }
    }

    /**
     * 決済処理中スタータスの全受注検索
     */
    public function checkDbAllPendingOrder()
    {
        $term = PENDING_ORDER_CANCEL_TIME;
        if (!SC_Utils_Ex::isBlank($term) && preg_match('/^[0-9]+$/', $term)) {
            $target_time = strtotime('-'.$term.' sec');
            $objQuery = SC_Query_Ex::getSingletonInstance();
            $arrVal = [date('Y/m/d H:i:s', $target_time), ORDER_PENDING];
            $objQuery->begin();
            $arrOrders = $objQuery->select('order_id', 'dtb_order', 'create_date <= ? and status = ? and del_flg = 0', $arrVal);
            if (!SC_Utils_Ex::isBlank($arrOrders)) {
                foreach ($arrOrders as $arrOrder) {
                    $order_id = $arrOrder['order_id'];
                    SC_Helper_Purchase_Ex::cancelOrder($order_id, ORDER_CANCEL, true);
                    GC_Utils_Ex::gfPrintLog('order cancel.(time expire) order_id='.$order_id);
                }
            }
            $objQuery->commit();
        }
    }

    public function checkDbMyPendignOrder()
    {
        $objCustomer = new SC_Customer_Ex();
        if ($objCustomer->isLoginSuccess(true)) {
            $customer_id = $objCustomer->getValue('customer_id');
            $objQuery = SC_Query_Ex::getSingletonInstance();
            $arrVal = [$customer_id, ORDER_PENDING];
            $objQuery->setOrder('create_date desc');
            $objQuery->begin();
            $arrOrders = $objQuery->select('order_id,create_date', 'dtb_order', 'customer_id = ? and status = ? and del_flg = 0', $arrVal);
            if (!SC_Utils_Ex::isBlank($arrOrders)) {
                foreach ($arrOrders as $key => $arrOrder) {
                    $order_id = $arrOrder['order_id'];
                    if ($key == 0) {
                        $objCartSess = new SC_CartSession_Ex();
                        $cartKeys = $objCartSess->getKeys();
                        $term = PENDING_ORDER_CANCEL_TIME;
                        if (preg_match('/^[0-9]+$/', $term)) {
                            $target_time = strtotime('-'.$term.' sec');
                            $create_time = strtotime($arrOrder['create_date']);
                            if (SC_Utils_Ex::isBlank($cartKeys) && $target_time < $create_time) {
                                SC_Helper_Purchase_Ex::rollbackOrder($order_id, ORDER_CANCEL, true);
                                GC_Utils_Ex::gfPrintLog('order rollback.(my pending) order_id='.$order_id);
                            } else {
                                SC_Helper_Purchase_Ex::cancelOrder($order_id, ORDER_CANCEL, true);
                                if ($target_time > $create_time) {
                                    GC_Utils_Ex::gfPrintLog('order cancel.(my pending and time expire) order_id='.$order_id);
                                } else {
                                    GC_Utils_Ex::gfPrintLog('order cancel.(my pending and set cart) order_id='.$order_id);
                                }
                            }
                        }
                    } else {
                        SC_Helper_Purchase_Ex::cancelOrder($order_id, ORDER_CANCEL, true);
                        GC_Utils_Ex::gfPrintLog('order cancel.(my old pending) order_id='.$order_id);
                    }
                }
            }
            $objQuery->commit();
        }
    }

    public function checkSessionPendingOrder()
    {
        if (!isset($_SESSION['order_id'])) {
            return;
        }
        if (SC_Utils_Ex::isBlank($_SESSION['order_id'])) {
            return;
        }

        $order_id = $_SESSION['order_id'];
        unset($_SESSION['order_id']);
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();
        $arrOrder = SC_Helper_Purchase_Ex::getOrder($order_id);
        if ($arrOrder['status'] == ORDER_PENDING) {
            $objCartSess = new SC_CartSession_Ex();
            $cartKeys = $objCartSess->getKeys();
            if (SC_Utils_Ex::isBlank($cartKeys)) {
                SC_Helper_Purchase_Ex::rollbackOrder($order_id, ORDER_CANCEL, true);
                GC_Utils_Ex::gfPrintLog('order rollback.(session pending) order_id='.$order_id);
            } else {
                SC_Helper_Purchase_Ex::cancelOrder($order_id, ORDER_CANCEL, true);
                GC_Utils_Ex::gfPrintLog('order rollback.(session pending and set card) order_id='.$order_id);
            }
        }
        $objQuery->commit();
    }
}
