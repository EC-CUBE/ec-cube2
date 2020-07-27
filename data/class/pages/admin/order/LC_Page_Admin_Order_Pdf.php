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

require_once CLASS_EX_REALDIR . 'page_extends/admin/LC_Page_Admin_Ex.php';

/**
 * 帳票出力 のページクラス.
 *
 * @package Page
 * @author EC-CUBE CO.,LTD.
 * @version $Id$
 */
class LC_Page_Admin_Order_Pdf extends LC_Page_Admin_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'order/pdf_input.tpl';
        $this->tpl_mainno = 'order';
        $this->tpl_subno = 'pdf';
        $this->tpl_maintitle = '受注管理';
        $this->tpl_subtitle = '帳票出力';

        $this->SHORTTEXT_MAX = STEXT_LEN;
        $this->MIDDLETEXT_MAX = MTEXT_LEN;
        $this->LONGTEXT_MAX = LTEXT_LEN;

        $this->arrType[0]  = '納品書';

        $this->arrDownload[0] = 'ブラウザに開く';
        $this->arrDownload[1] = 'ファイルに保存';
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
        $objDb = new SC_Helper_DB_Ex();
        $objDate = new SC_Date_Ex(1901);
        $objDate->setStartYear(RELEASE_YEAR);
        $this->arrYear = $objDate->getYear();
        $this->arrMonth = $objDate->getMonth();
        $this->arrDay = $objDate->getDay();

        // パラメーター管理クラス
        $this->objFormParam = new SC_FormParam_Ex();
        // パラメーター情報の初期化
        $this->lfInitParam($this->objFormParam);
        $this->objFormParam->setParam($_POST);
        // 入力値の変換
        $this->objFormParam->convParam();

        // どんな状態の時に isset($arrRet) == trueになるんだ? これ以前に$arrRet無いが、、、、
        if (!isset($arrRet)) $arrRet = array();
        switch ($this->getMode()) {
            case 'confirm':

                $status = $this->createPdf($this->objFormParam);
                if ($status === true) {
                    SC_Response_Ex::actionExit();
                } else {
                    $this->arrErr = $status;
                }
                break;
            default:
                $this->arrForm = $this->createFromValues($_GET['order_id'], $_POST['pdf_order_id']);
                break;
        }
        $this->setTemplate($this->tpl_mainpage);
    }

    /**
     *
     * PDF作成フォームのデフォルト値の生成
     */
    public function createFromValues($order_id, $pdf_order_id)
    {
        // ここが$arrFormの初登場ということを明示するため宣言する。
        $arrForm = array();
        // タイトルをセット
        $arrForm['title'] = 'お買上げ明細書(納品書)';

        // 今日の日付をセット
        $arrForm['year']  = date('Y');
        $arrForm['month'] = date('n');
        $arrForm['day']   = date('j');

        // メッセージ
        $arrForm['msg1'] = 'このたびはお買上げいただきありがとうございます。';
        $arrForm['msg2'] = '下記の内容にて納品させていただきます。';
        $arrForm['msg3'] = 'ご確認くださいますよう、お願いいたします。';

        // 注文番号があったら、セットする
        if (SC_Utils_Ex::sfIsInt($order_id)) {
            $arrForm['order_id'][0] = $order_id;
        } elseif (is_array($pdf_order_id)) {
            sort($pdf_order_id);
            foreach ($pdf_order_id AS $key=>$val) {
                $arrForm['order_id'][] = $val;
            }
        }

        return $arrForm;
    }

    /**
     *
     * PDFの作成
     * @param SC_FormParam $objFormParam
     */
    public function createPdf(&$objFormParam)
    {
        $arrErr = $this->lfCheckError($objFormParam);
        $arrRet = $objFormParam->getHashArray();

    //タイトルが入力されていなければ、デフォルトのタイトルを表示
    if($arrRet['title'] == '') $arrRet['title'] = 'お買上げ明細書(納品書)';

        $this->arrForm = $arrRet;
        // エラー入力なし
        if (count($arrErr) == 0) {
            $objFpdf = new SC_Fpdf_Ex($arrRet['download'], $arrRet['title']);
            foreach ($arrRet['order_id'] AS $key => $val) {
                $arrPdfData = $arrRet;
                $arrPdfData['order_id'] = $val;
                $objFpdf->setData($arrPdfData);
            }
            $objFpdf->createPdf();

            return true;
        } else {
            return $arrErr;
        }
    }

    /**
     *  パラメーター情報の初期化
     *  @param SC_FormParam
     * @param SC_FormParam_Ex $objFormParam
     */
    public function lfInitParam(&$objFormParam)
    {
        $objFormParam->addParam('注文番号', 'order_id', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam('注文番号', 'pdf_order_id', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam('発行日', 'year', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam('発行日', 'month', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam('発行日', 'day', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam('帳票の種類', 'type', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam('ダウンロード方法', 'download', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam('帳票タイトル', 'title', STEXT_LEN, 'KVa', array ('MAX_LENGTH_CHECK'));
        $objFormParam->addParam('帳票メッセージ1行目', 'msg1', STEXT_LEN*3/5, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam('帳票メッセージ2行目', 'msg2', STEXT_LEN*3/5, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam('帳票メッセージ3行目', 'msg3', STEXT_LEN*3/5, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam('備考1行目', 'etc1', STEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam('備考2行目', 'etc2', STEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam('備考3行目', 'etc3', STEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam('ポイント表記', 'disp_point', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK'));
    }

    /**
     *  入力内容のチェック
     *  @var SC_FormParam
     * @param SC_FormParam $objFormParam
     */

    public function lfCheckError(&$objFormParam)
    {
        // 入力データを渡す。
        $arrParams = $objFormParam->getHashArray();
        $arrErr = $objFormParam->checkError();
        $objError = new SC_CheckError_Ex($arrParams);

        $year = $objFormParam->getValue('year');
        $month = $objFormParam->getValue('month');
        $day = $objFormParam->getValue('day');

        $objError->doFunc(array('発行日', 'year', 'month', 'day'), array('CHECK_DATE'));
        $arrErr = array_merge($arrErr, $objError->arrErr);

        $objQuery = SC_Query_Ex::getSingletonInstance();

        $order_temp = $_POST['order_id'];
        $order_max = max($order_temp);

        $date_check = $objQuery->select("create_date", "dtb_order", "order_id = ?", array($order_max));

        $date_check = substr($date_check[0]['create_date'], 0, 10);
        $date_check = explode("-", $date_check);
        $order_year  = $date_check[0];
        $order_month = $date_check[1];
        $order_day   = $date_check[2];

        $date1 = sprintf('%d%02d%02d000000', $order_year, $order_month, $order_day);
        $date2 = sprintf('%d%02d%02d235959', $year, $month, $day);

        if ($date1 > $date2) {
            $arrErr['year'] = '※ 発行日は受注日以降の日付を入力してください。<br />';
        }

        return $arrErr;
    }
}
