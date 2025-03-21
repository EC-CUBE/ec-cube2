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
 * デザイン管理 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_Design extends LC_Page_Admin_Ex
{
    /** @var array */
    public $arrTarget;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'design/index.tpl';
        $this->tpl_subno = 'layout';
        $this->tpl_mainno = 'design';
        $this->tpl_maintitle = 'デザイン管理';
        $this->tpl_subtitle = 'レイアウト設定';
        $masterData = new SC_DB_MasterData_Ex();
        $this->arrTarget = $masterData->getMasterData('mtb_target');
        $this->arrDeviceType = $masterData->getMasterData('mtb_device_type');
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
        $objLayout = new SC_Helper_PageLayout_Ex();
        $objFormParam = new SC_FormParam_Ex();
        $this->lfInitParam($objFormParam, (int) ($_REQUEST['bloc_cnt'] ?? 0));
        $objFormParam->setParam($_REQUEST);

        $this->device_type_id = $objFormParam->getValue('device_type_id', DEVICE_TYPE_PC);
        $this->page_id = $objFormParam->getValue('page_id', 1);

        switch ($this->getMode()) {
            // 新規ブロック作成
            case 'new_bloc':
                SC_Response_Ex::sendRedirect('bloc.php', ['device_type_id' => $this->device_type_id]);
                SC_Response_Ex::actionExit();
                break;

                // 新規ページ作成
            case 'new_page':
                SC_Response_Ex::sendRedirect('main_edit.php', ['device_type_id' => $this->device_type_id]);
                SC_Response_Ex::actionExit();
                break;

                // プレビュー
            case 'preview':
                $this->placingBlocs($objFormParam, true);
                $filename = $this->savePreviewData($this->page_id, $objLayout);
                $_SESSION['preview'] = 'ON';

                SC_Response_Ex::sendRedirectFromUrlPath('preview/'.DIR_INDEX_PATH, ['filename' => $filename]);
                SC_Response_Ex::actionExit();

                // 編集実行
                // no break
            case 'confirm':
                $this->placingBlocs($objFormParam);
                $arrQueryString = ['device_type_id' => $this->device_type_id, 'page_id' => $this->page_id, 'msg' => 'on'];

                SC_Response_Ex::reload($arrQueryString, true);
                SC_Response_Ex::actionExit();

                break;

                // データ削除処理
            case 'delete':
                // ベースデータでなければファイルを削除
                if ($objLayout->isEditablePage($this->device_type_id, $this->page_id)) {
                    $objLayout->lfDelPageData($this->page_id, $this->device_type_id);

                    SC_Response_Ex::reload(['device_type_id' => $this->device_type_id], true);
                    SC_Response_Ex::actionExit();
                }
                break;

            default:
                // 完了メッセージ表示
                if (isset($_GET['msg']) && $_GET['msg'] == 'on') {
                    $this->tpl_onload = "alert('登録が完了しました。');";
                }
                break;
        }

        $this->arrBlocs = $this->getLayout($this->device_type_id, $this->page_id, $objLayout);
        $this->bloc_cnt = count($this->arrBlocs);
        // 編集中のページ
        $this->arrPageData = $objLayout->getPageProperties($this->device_type_id, $this->page_id);
        // 編集可能ページ一覧
        $this->arrEditPage = $objLayout->getPageProperties($this->device_type_id, null);
        // サブタイトルを取得
        $this->tpl_subtitle = $this->arrDeviceType[$this->device_type_id].'＞'.$this->tpl_subtitle;
    }

    /**
     * フォームパラメーターの初期化を行う.
     *
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス.
     * @param  int      $bloc_cnt     ブロック数
     *
     * @return void
     */
    public function lfInitParam(&$objFormParam, $bloc_cnt = 0)
    {
        $objFormParam->addParam('ページID', 'page_id', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('端末種別ID', 'device_type_id', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        $objFormParam->addParam('ブロック数', 'bloc_cnt', INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);

        for ($i = 1; $i <= $bloc_cnt; $i++) {
            $objFormParam->addParam('ブロック名', 'name_'.$i, STEXT_LEN, 'a', ['MAX_LENGTH_CHECK', 'GRAPH_CHECK']);
            $objFormParam->addParam('ブロックID', 'id_'.$i, INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
            $objFormParam->addParam('ターゲットID', 'target_id_'.$i, STEXT_LEN, 'a', ['MAX_LENGTH_CHECK', 'ALNUM_CHECK']);
            $objFormParam->addParam('TOP座標', 'top_'.$i, INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
            $objFormParam->addParam('全ページ', 'anywhere_'.$i, INT_LEN, 'n', ['MAX_LENGTH_CHECK', 'NUM_CHECK']);
        }
    }

    /**
     * ブロックのレイアウト情報を取得する.
     *
     * @param  int              $device_type_id 端末種別ID
     * @param  int              $page_id        ページID
     * @param  SC_Helper_PageLayout $objLayout      SC_Helper_PageLayout インスタンス
     *
     * @return array                レイアウト情報の配列
     */
    public function getLayout($device_type_id, $page_id, &$objLayout)
    {
        $arrResults = [];
        $i = 0;

        // レイアウト済みのブロックデータを追加
        $arrBlocPos = $objLayout->getBlocPositions($device_type_id, $page_id);
        foreach ($arrBlocPos as $arrBloc) {
            $this->copyBloc($arrResults, $arrBloc, $i);
            $i++;
        }

        // 未使用のブロックデータを追加
        $arrBloc = $objLayout->getBlocs($device_type_id);
        foreach ($arrBloc as $arrBloc) {
            if (!$this->existsBloc($arrBloc, $arrResults)) {
                $arrBloc['target_id'] = TARGET_ID_UNUSED;
                $this->copyBloc($arrResults, $arrBloc, $i);
                $i++;
            }
        }

        return $arrResults;
    }

    /**
     * ブロック情報の配列をコピーする.
     *
     * @param  array   $arrDest コピー先ブロック情報
     * @param  array   $arrFrom コピー元ブロック情報
     * @param  int $cnt     配列番号
     *
     * @return void
     */
    public function copyBloc(&$arrDest, $arrFrom, $cnt)
    {
        $arrDest[$cnt]['target_id'] = $this->arrTarget[$arrFrom['target_id']];
        $arrDest[$cnt]['bloc_id'] = $arrFrom['bloc_id'];
        $arrDest[$cnt]['bloc_row'] = $arrFrom['bloc_row'];
        $arrDest[$cnt]['anywhere'] = $arrFrom['anywhere'];
        $arrDest[$cnt]['name'] = $arrFrom['bloc_name'];
    }

    /**
     * ブロックIDがコピー先の配列に追加されているかのチェックを行う.
     *
     * @param  array $arrBloc    ブロックの配列
     * @param  array $arrToBlocs チェックを行うデータ配列
     *
     * @return bool  存在する場合 true
     */
    public function existsBloc($arrBloc, $arrToBlocs)
    {
        foreach ($arrToBlocs as $arrToBloc) {
            if ($arrBloc['bloc_id'] === $arrToBloc['bloc_id']) {
                return true;
            }
        }

        return false;
    }

    /**
     * プレビューするデータを DB に保存する.
     *
     * @param  int              $page_id   プレビューを行うページID
     * @param  SC_Helper_PageLayout $objLayout SC_Helper_PageLayout インスタンス
     *
     * @return string               プレビューを行う tpl_mainpage ファイル名
     */
    public function savePreviewData($page_id, &$objLayout)
    {
        $arrPageData = $objLayout->getPageProperties(DEVICE_TYPE_PC, $page_id);
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrPageData[0]['page_id'] = 0;
        $objQuery->update('dtb_pagelayout', $arrPageData[0], 'page_id = 0 AND device_type_id = ?', [DEVICE_TYPE_PC]);

        return $arrPageData[0]['filename'];
    }

    /**
     * ブロックを配置する.
     *
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     * @param  bool      $is_preview   プレビュー時の場合 true
     *
     * @return void
     */
    public function placingBlocs(&$objFormParam, $is_preview = false)
    {
        $page_id = $is_preview ? 0 : $objFormParam->getValue('page_id');
        $device_type_id = $objFormParam->getValue('device_type_id');
        $bloc_cnt = $objFormParam->getValue('bloc_cnt');
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();
        $objQuery->delete(
            'dtb_blocposition',
            'page_id = ? AND device_type_id = ?',
            [$page_id, $device_type_id]
        );
        $arrTargetFlip = array_flip($this->arrTarget);
        for ($i = 1; $i <= $bloc_cnt; $i++) {
            // bloc_id が取得できない場合は INSERT しない
            $id = $objFormParam->getValue('id_'.$i);
            if (SC_Utils_Ex::isBlank($id)) {
                continue;
            }
            // 未使用は INSERT しない
            $arrParams['target_id'] = $arrTargetFlip[$objFormParam->getValue('target_id_'.$i)];
            if ($arrParams['target_id'] == TARGET_ID_UNUSED) {
                continue;
            }

            // 他のページに anywhere が存在する場合は INSERT しない
            $arrParams['anywhere'] = (int) $objFormParam->getValue('anywhere_'.$i);
            if ($arrParams['anywhere'] == 1) {
                $exists = $objQuery->exists(
                    'dtb_blocposition',
                    'anywhere = 1 AND bloc_id = ? AND device_type_id = ?',
                    [$id, $device_type_id]
                );
                if ($exists) {
                    continue;
                }
            }

            $arrParams['device_type_id'] = $device_type_id;
            $arrParams['page_id'] = $page_id;
            $arrParams['bloc_id'] = $id;
            $arrParams['bloc_row'] = $objFormParam->getValue('top_'.$i);

            if ($arrParams['page_id'] == 0) {
                $arrParams['anywhere'] = 0;
            }

            $objQuery->insert('dtb_blocposition', $arrParams);
        }
        $objQuery->commit();
    }
}
