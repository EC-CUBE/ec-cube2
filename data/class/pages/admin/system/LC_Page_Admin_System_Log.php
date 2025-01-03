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
 * ログ のページクラス.
 *
 * @author Seasoft 塚田将久
 *
 * @version $Id$
 */
class LC_Page_Admin_System_Log extends LC_Page_Admin_Ex
{
    public $arrLogList = [];
    /** @var string */
    public $tpl_ec_log;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'system/log.tpl';
        $this->tpl_subno = 'log';
        $this->tpl_mainno = 'system';
        $this->tpl_maintitle = 'システム設定';
        $this->tpl_subtitle = 'EC-CUBE ログ表示';
        $this->line_max = 50;
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
        $objFormParam = new SC_FormParam_Ex();

        // パラメーター情報初期化
        $this->lfInitParam($objFormParam);

        // POST値をセット
        $objFormParam->setParam($_REQUEST);
        $this->arrErr = $objFormParam->checkError();
        $this->arrForm = $objFormParam->getFormParamList();

        $this->loadLogList();

        if (empty($this->arrErr)) {
            $this->line_max = $objFormParam->getValue('line_max');

            $log_path = $this->getLogPath($objFormParam->getValue('log'));
            $this->tpl_ec_log = $this->getEccubeLog($log_path);
        }
    }

    /**
     * パラメーターの初期化.
     *
     * @param SC_FormParam_Ex $objFormParam
     *
     * @return void
     */
    public function lfInitParam(&$objFormParam)
    {
        $objFormParam->addParam('ファイル', 'log', null, '', []);
        $objFormParam->addParam('行数', 'line_max', INT_LEN, '', ['NUM_CHECK', 'MAX_LENGTH_CHECK'], 50);
    }

    /**
     * EC-CUBE ログを取得する.
     *
     * @return array $arrLogs 取得したログ
     */
    public function getEccubeLog($log_path_base)
    {
        $index = 0;
        $arrLogs = [];
        for ($gen = 0; $gen <= MAX_LOG_QUANTITY; $gen++) {
            $path = $log_path_base;
            if ($gen != 0) {
                $path .= ".$gen";
            }

            // ファイルが存在しない場合、前世代のログへ
            if (!file_exists($path)) {
                continue;
            }

            $arrLogTmp = array_reverse(file($path));

            $arrBodyReverse = [];
            foreach ($arrLogTmp as $line) {
                // 上限に達した場合、処理を抜ける
                if (count($arrLogs) >= $this->line_max) {
                    break 2;
                }

                $line = rtrim($line);
                if (preg_match('/^(\d+\/\d+\/\d+ \d+:\d+:\d+) \[([^\]]+)\] (.*)$/', $line, $arrMatch)) {
                    $arrLogLine = [];
                    // 日時
                    $arrLogLine['date'] = $arrMatch[1];
                    // パス
                    $arrLogLine['path'] = $arrMatch[2];
                    // 内容
                    $arrBodyReverse[] = $arrMatch[3];
                    $arrLogLine['body'] = implode("\n", array_reverse($arrBodyReverse));
                    $arrBodyReverse = [];

                    $arrLogs[] = $arrLogLine;
                } else {
                    // 内容
                    $arrBodyReverse[] = $line;
                }
            }
        }

        return $arrLogs;
    }

    /**
     * ログファイルのパスを取得する
     *
     * セキュリティ面をカバーする役割もある。
     */
    public function getLogPath($log_name)
    {
        if (strlen($log_name) === 0) {
            return LOG_REALFILE;
        }
        if (defined($const_name = $log_name.'_LOG_REALFILE')) {
            return constant($const_name);
        }
        trigger_error('不正なログが指定されました。', E_USER_ERROR);
    }

    /**
     * ログファイルの一覧を読み込む
     *
     * TODO mtb_constants から動的生成したい。
     *
     * @return void
     */
    public function loadLogList()
    {
        $this->arrLogList[''] = '標準ログファイル';
        $this->arrLogList['CUSTOMER'] = '会員ログイン ログファイル';
        $this->arrLogList['ADMIN'] = '管理機能ログファイル';

        if (defined('DEBUG_LOG_REALFILE') && strlen(DEBUG_LOG_REALFILE) >= 1) {
            $this->arrLogList['DEBUG'] = 'デバッグログファイル';
        }

        if (defined('ERROR_LOG_REALFILE') && strlen(ERROR_LOG_REALFILE) >= 1) {
            $this->arrLogList['ERROR'] = 'エラーログファイル';
        }

        if (defined('DB_LOG_REALFILE') && strlen(DB_LOG_REALFILE) >= 1) {
            $this->arrLogList['DB'] = 'DBログファイル';
        }
        if (defined('PLUGIN_LOG_REALFILE') && strlen(PLUGIN_LOG_REALFILE) >= 1) {
            $this->arrLogList['PLUGIN'] = 'プラグインログファイル';
        }
    }
}
