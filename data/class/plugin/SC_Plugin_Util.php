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

// プラグインのユーティリティクラス.
class SC_Plugin_Util
{
    /**
     * 稼働中のプラグインを取得する。
     */
    public static function getEnablePlugin()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $col = '*';
        $table = 'dtb_plugin';
        $where = 'enable = 1';
        // XXX 2.11.0 互換のため
        $arrCols = $objQuery->listTableFields($table);
        if (in_array('priority', $arrCols)) {
            $objQuery->setOrder('priority DESC, plugin_id ASC');
        }
        $arrRet = $objQuery->select($col, $table, $where);

        // プラグインフックポイントを取得.
        $max = count($arrRet);
        for ($i = 0; $i < $max; $i++) {
            $plugin_id = $arrRet[$i]['plugin_id'];
            $arrHookPoint = self::getPluginHookPoint($plugin_id);
            $arrRet[$i]['plugin_hook_point'] = $arrHookPoint;
        }

        return $arrRet;
    }

    /**
     * インストールされているプラグインを取得する。
     *
     * @return array $arrRet インストールされているプラグイン.
     */
    public static function getAllPlugin()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $col = '*';
        $table = 'dtb_plugin';
        // XXX 2.11.0 互換のため
        $arrCols = $objQuery->listTableFields($table);
        if (in_array('priority', $arrCols)) {
            $objQuery->setOrder('plugin_id ASC');
        }
        $arrRet = $objQuery->select($col, $table);

        return $arrRet;
    }

    /**
     * プラグインIDをキーにプラグインを取得する。
     *
     * @param  int   $plugin_id プラグインID.
     *
     * @return array プラグインの基本情報.
     */
    public static function getPluginByPluginId($plugin_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $col = '*';
        $table = 'dtb_plugin';
        $where = 'plugin_id = ?';
        $plugin = $objQuery->getRow($col, $table, $where, [$plugin_id]);

        return $plugin;
    }

    /**
     * プラグインコードをキーにプラグインを取得する。
     *
     * @param  string $plugin_code プラグインコード.
     *
     * @return array  プラグインの基本情報.
     */
    public static function getPluginByPluginCode($plugin_code)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $col = '*';
        $table = 'dtb_plugin';
        $where = 'plugin_code = ?';
        $plugin = $objQuery->getRow($col, $table, $where, [$plugin_code]);

        return $plugin;
    }

    /**
     * プラグインIDをキーにプラグインを削除する。
     *
     * @param  string $plugin_id プラグインID.
     *
     * @return void
     */
    public static function deletePluginByPluginId($plugin_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $where = 'plugin_id = ?';
        $objQuery->delete('dtb_plugin', $where, [$plugin_id]);
        $objQuery->delete('dtb_plugin_hookpoint', $where, [$plugin_id]);
    }

    /**
     * プラグインディレクトリの取得
     *
     * @return array $arrPluginDirectory
     */
    public static function getPluginDirectory($plugin_upload_realdir = PLUGIN_UPLOAD_REALDIR)
    {
        $arrPluginDirectory = [];
        if (is_dir($plugin_upload_realdir)) {
            if ($dh = opendir($plugin_upload_realdir)) {
                while (($pluginDirectory = readdir($dh)) !== false) {
                    $arrPluginDirectory[] = $pluginDirectory;
                }
                closedir($dh);
            }
        }

        return $arrPluginDirectory;
    }

    /**
     * プラグインIDをキーに, プラグインフックポイントを取得する.
     *
     * @param  int $plugin_id
     * @param  int $use_type  1=有効のみ 2=無効のみ 3=全て
     *
     * @return array   フックポイントの一覧
     */
    public static function getPluginHookPoint($plugin_id, $use_type = 1)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $cols = '*';
        $from = 'dtb_plugin_hookpoint';
        $where = 'plugin_id = ?';
        switch ($use_type) {
            case 1:
                $where .= ' AND use_flg = 1';
                break;

            case 2:
                $where .= ' AND use_flg = 0';
                break;

            case 3:
            default:
                break;
        }

        return $objQuery->select($cols, $from, $where, [$plugin_id]);
    }

    /**
     *  プラグインフックポイントを取得する.
     *
     * @param  int $use_type 1=有効のみ 2=無効のみ 3=全て
     *
     * @return array   フックポイントの一覧
     */
    public static function getPluginHookPointList($use_type = 3)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->setOrder('hook_point ASC, priority DESC');
        $cols = 'dtb_plugin_hookpoint.*, dtb_plugin.priority, dtb_plugin.plugin_name';
        $from = 'dtb_plugin_hookpoint LEFT JOIN dtb_plugin USING(plugin_id)';
        switch ($use_type) {
            case 1:
                $where = 'enable = 1 AND use_flg = 1';
                break;

            case 2:
                $where = 'enable = 1 AND use_flg = 0';
                break;

            case 3:
            default:
                $where = '';
                break;
        }

        return $objQuery->select($cols, $from, $where);
        // $arrList = array();
        // foreach ($arrRet AS $key=>$val) {
        //    $arrList[$val['hook_point']][$val['plugin_id']] = $val;
        // }
        // return $arrList;
    }

    /**
     * プラグイン利用に必須のモジュールチェック
     *
     * @param  string $key エラー情報を格納するキー
     *
     * @return array  $arrErr エラー情報を格納した連想配列.
     */
    public static function checkExtension($key)
    {
        // プラグイン利用に必須のモジュール
        // 'EC-CUBEバージョン' => array('モジュール名')
        $arrRequireExtension = [
            '2.12.0' => ['dom'],
            '2.12.1' => ['dom'],
            '2.12.2' => ['dom'],
        ];
        // 必須拡張モジュールのチェック
        $arrErr = [];
        if (isset($arrRequireExtension[ECCUBE_VERSION])
            && is_array($arrRequireExtension[ECCUBE_VERSION])) {
            foreach ($arrRequireExtension[ECCUBE_VERSION] as $val) {
                if (!extension_loaded($val)) {
                    $arrErr[$key] .= "※ プラグインを利用するには、拡張モジュール「{$val}」が必要です。<br />";
                }
            }
        }

        return $arrErr;
    }

    /**
     * フックポイントのON/OFF変更
     *
     * @param  intger $plugin_hookpoint_id フックポイントID
     *
     * @return void
     */
    public static function setPluginHookPointChangeUse($plugin_hookpoint_id, $use_flg = 0)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $sqlval['use_flg'] = $use_flg;
        $objQuery->update('dtb_plugin_hookpoint', $sqlval, 'plugin_hookpoint_id = ?', [$plugin_hookpoint_id]);
    }

    /**
     * フックポイントで衝突する可能性のあるプラグインを判定.メッセージを返します.
     *
     * @param  int    $plugin_id プラグインID
     *
     * @return string $conflict_alert_message メッセージ
     */
    public static function checkConflictPlugin($plugin_id = '')
    {
        // フックポイントを取得します.
        $where = 'T1.hook_point = ? AND NOT T1.plugin_id = ? AND T2.enable = ?';
        if ($plugin_id > 0) {
            $hookPoints = self::getPluginHookPoint($plugin_id, '');
        } else {
            $hookPoints = self::getPluginHookPointList(1);
            $where .= ' AND T1.use_flg = 1';
        }

        $conflict_alert_message = '';
        $arrConflictPluginName = [];
        $arrConflictHookPoint = [];
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->setGroupBy('T1.hook_point, T1.plugin_id, T2.plugin_name');
        $table = 'dtb_plugin_hookpoint AS T1 LEFT JOIN dtb_plugin AS T2 ON T1.plugin_id = T2.plugin_id';
        foreach ($hookPoints as $hookPoint) {
            // 競合するプラグインを取得する,
            $conflictPlugins = $objQuery->select('T1.hook_point, T1.plugin_id, T2.plugin_name', $table, $where, [$hookPoint['hook_point'], $hookPoint['plugin_id'], PLUGIN_ENABLE_TRUE]);

            // プラグイン名重複を削除する為、専用の配列に格納し直す.
            foreach ($conflictPlugins as $conflictPlugin) {
                // プラグイン名が見つからなければ配列に格納
                if (!in_array($conflictPlugin['plugin_name'], $arrConflictPluginName)) {
                    $arrConflictPluginName[] = $conflictPlugin['plugin_name'];
                }
                // プラグイン名が見つからなければ配列に格納
                if (!in_array($conflictPlugin['hook_point'], $arrConflictHookPoint)) {
                    $arrConflictHookPoint[] = $conflictPlugin['hook_point'];
                }
            }
        }

        if ($plugin_id > 0) {
            // メッセージをセットします.
            foreach ($arrConflictPluginName as $conflictPluginName) {
                $conflict_alert_message .= '* '.$conflictPluginName.'と競合する可能性があります。<br/>';
            }

            return $conflict_alert_message;
        } else {
            return $arrConflictHookPoint;
        }
    }
}
