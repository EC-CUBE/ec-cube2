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
 * ファイル管理 のヘルパークラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class SC_Helper_FileManager
{
    /**
     * 指定パス配下のディレクトリ取得する.
     *
     * @param  string $dir 取得するディレクトリパス
     *
     * @return array
     */
    public static function sfGetFileList($dir)
    {
        $arrFileList = [];
        $arrDirList = [];

        if (is_dir($dir)) {
            $dh = opendir($dir);
            if ($dh) {
                $cnt = 0;
                $arrDir = [];
                // 行末の/を取り除く
                while (($file = readdir($dh)) !== false) {
                    $arrDir[] = $file;
                }
                $dir = rtrim($dir, '/');
                // アルファベットと数字でソート
                natcasesort($arrDir);
                foreach ($arrDir as $file) {
                    // ./ と ../を除くファイルのみを取得
                    if ($file != '.' && $file != '..') {
                        $path = $dir.'/'.$file;
                        // SELECT内の見た目を整えるため指定文字数で切る
                        $file_size = SC_Utils_Ex::sfCutString(self::sfGetDirSize($path), FILE_NAME_LEN);
                        $file_time = date('Y/m/d', filemtime($path));

                        // ディレクトリとファイルで格納配列を変える
                        if (is_dir($path)) {
                            $path = SC_Helper_FileManager_Ex::convertToRelativePath($path);
                            $arrDirList[$cnt]['file_name'] = $file;
                            $arrDirList[$cnt]['file_path'] = $path;
                            $arrDirList[$cnt]['file_size'] = $file_size;
                            $arrDirList[$cnt]['file_time'] = $file_time;
                            $arrDirList[$cnt]['is_dir'] = true;
                        } else {
                            $path = SC_Helper_FileManager_Ex::convertToRelativePath($path);
                            $arrFileList[$cnt]['file_name'] = $file;
                            $arrFileList[$cnt]['file_path'] = $path;
                            $arrFileList[$cnt]['file_size'] = $file_size;
                            $arrFileList[$cnt]['file_time'] = $file_time;
                            $arrFileList[$cnt]['is_dir'] = false;
                        }
                        $cnt++;
                    }
                }
                closedir($dh);
            }
        }

        // フォルダを先頭にしてマージ
        return array_merge($arrDirList, $arrFileList);
    }

    /**
     * 指定したディレクトリのバイト数を取得する.
     *
     * @param  string $dir ディレクトリ
     *
     * @return int
     */
    public static function sfGetDirSize($dir)
    {
        $bytes = 0;
        if (file_exists($dir)) {
            // ディレクトリの場合下層ファイルの総量を取得
            if (is_dir($dir)) {
                $handle = opendir($dir);
                while ($file = readdir($handle)) {
                    // 行末の/を取り除く
                    $dir = rtrim($dir, '/');
                    $path = $dir.'/'.$file;
                    if ($file != '..' && $file != '.' && !is_dir($path)) {
                        $bytes += filesize($path);
                    } elseif (is_dir($path) && $file != '..' && $file != '.') {
                        // 下層ファイルのバイト数を取得する為、再帰的に呼び出す。
                        $bytes += SC_Helper_FileManager_Ex::sfGetDirSize($path);
                    }
                }
            } else {
                // ファイルの場合
                $bytes = filesize($dir);
            }
        }
        // ディレクトリ(ファイル)が存在しない場合は0byteを返す
        if ($bytes == '') {
            $bytes = 0;
        }

        return $bytes;
    }

    /**
     * ツリー生成用配列取得(javascriptに渡す用).
     *
     * @param string $dir         ディレクトリ
     * @param string $tree_status 現在のツリーの状態開いているフォルダのパスを
     *                            | 区切りで格納
     *
     * @return array ツリー生成用の配列
     */
    public function sfGetFileTree($dir, $tree_status)
    {
        $cnt = 0;
        $arrTree = [];
        $default_rank = count(explode('/', $dir));

        // 文末の/を取り除く
        $dir = rtrim($dir, '/');
        // 最上位層を格納(user_data/)
        if ($this->sfDirChildExists($dir)) {
            $arrTree[$cnt]['type'] = '_parent';
        } else {
            $arrTree[$cnt]['type'] = '_child';
        }
        $arrTree[$cnt]['path'] = $dir;
        $arrTree[$cnt]['rank'] = 0;
        $arrTree[$cnt]['count'] = $cnt;
        // 初期表示はオープン
        if (isset($_POST['mode'])) {
            $arrTree[$cnt]['open'] = $this->lfIsFileOpen($dir, $tree_status);
        } else {
            $arrTree[$cnt]['open'] = true;
        }
        $cnt++;

        $this->sfGetFileTreeSub($dir, $default_rank, $cnt, $arrTree, $tree_status);

        return $arrTree;
    }

    /**
     * ツリー生成用配列取得(javascriptに渡す用).
     *
     * @param string $dir          ディレクトリ
     * @param string $default_rank デフォルトの階層
     *                             (/区切りで　0,1,2・・・とカウント)
     * @param int $cnt         連番
     * @param string  $tree_status 現在のツリーの状態開いているフォルダのパスが
     *                            | 区切りで格納
     *
     * @return void
     */
    public function sfGetFileTreeSub($dir, $default_rank, &$cnt, &$arrTree, $tree_status)
    {
        if (file_exists($dir)) {
            $handle = opendir($dir);
            if ($handle) {
                $arrDir = [];
                while (false !== ($item = readdir($handle))) {
                    $arrDir[] = $item;
                }
                // アルファベットと数字でソート
                natcasesort($arrDir);
                foreach ($arrDir as $item) {
                    if ($item != '.' && $item != '..') {
                        // 文末の/を取り除く
                        $dir = rtrim($dir, '/');
                        $path = $dir.'/'.$item;
                        // ディレクトリのみ取得
                        if (is_dir($path)) {
                            $arrTree[$cnt]['path'] = $path;
                            if ($this->sfDirChildExists($path)) {
                                $arrTree[$cnt]['type'] = '_parent';
                            } else {
                                $arrTree[$cnt]['type'] = '_child';
                            }

                            // 階層を割り出す
                            $arrCnt = explode('/', $path);
                            $rank = count($arrCnt);
                            $arrTree[$cnt]['rank'] = $rank - $default_rank + 1;
                            $arrTree[$cnt]['count'] = $cnt;
                            // フォルダが開いているか
                            $arrTree[$cnt]['open'] = $this->lfIsFileOpen($path, $tree_status);
                            $cnt++;
                            // 下層ディレクトリ取得の為、再帰的に呼び出す
                            $this->sfGetFileTreeSub($path, $default_rank, $cnt, $arrTree, $tree_status);
                        }
                    }
                }
            }
            closedir($handle);
        }
    }

    /**
     * 指定したディレクトリ配下にファイルがあるかチェックする.
     *
     * @param string ディレクトリ
     * @param string $dir
     *
     * @return bool ファイルが存在する場合 true
     */
    public function sfDirChildExists($dir)
    {
        if (file_exists($dir)) {
            if (is_dir($dir)) {
                $handle = opendir($dir);
                while ($file = readdir($handle)) {
                    // 行末の/を取り除く
                    $dir = rtrim($dir, '/');
                    $path = $dir.'/'.$file;
                    if ($file != '..' && $file != '.' && is_dir($path)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * 指定したファイルが前回開かれた状態にあったかチェックする.
     *
     * @param string $dir         ディレクトリ
     * @param string $tree_status 現在のツリーの状態開いているフォルダのパスが
     *                            | 区切りで格納
     *
     * @return bool 前回開かれた状態の場合 true
     */
    public function lfIsFileOpen($dir, $tree_status)
    {
        $arrTreeStatus = explode('|', $tree_status);
        $dir = SC_Helper_FileManager_Ex::convertToRelativePath($dir);
        if (in_array($dir, $arrTreeStatus)) {
            return true;
        }

        return false;
    }

    /**
     * ファイルのダウンロードを行う.
     *
     * @param  string $file ファイルパス
     *
     * @return void
     */
    public function sfDownloadFile($file)
    {
        // ファイルの場合はダウンロードさせる
        $file_name = basename($file);
        header('Content-disposition: attachment; filename='.$file_name);
        header('Content-type: application/octet-stream; name='.$file_name);
        header('Cache-Control: ');
        header('Pragma: ');
        echo $this->sfReadFile($file);
    }

    /**
     * ファイル作成を行う.
     *
     * @param  string  $file ファイルパス
     * @param  int $mode パーミッション
     *
     * @return bool    ファイル作成に成功した場合 true
     */
    public function sfCreateFile($file, $mode = '')
    {
        // 行末の/を取り除く
        if ($mode != '') {
            $ret = @mkdir($file, $mode);
        } else {
            $ret = @mkdir($file);
        }

        return $ret;
    }

    /**
     * ファイル読込を行う.
     *
     * @param string ファイルパス
     * @param string $filename
     *
     * @return string ファイルの内容
     */
    public function sfReadFile($filename)
    {
        $str = '';
        // バイナリモードでオープン
        $fp = @fopen($filename, 'r');
        // ファイル内容を全て変数に読み込む
        if ($fp) {
            $str = @fread($fp, filesize($filename) + 1);
        }
        @fclose($fp);

        return $str;
    }

    /**
     * ファイル書込を行う.
     *
     * @param  string  $filename ファイルパス
     * @param  string  $value    書き込み内容
     *
     * @return bool ファイルの書き込みに成功した場合 true
     */
    public static function sfWriteFile($filename, $value)
    {
        if (!is_dir(dirname($filename))) {
            SC_Utils_Ex::recursiveMkdir(dirname($filename), 0777);
        }
        $fp = fopen($filename, 'w');
        if ($fp === false) {
            return false;
        }
        if (fwrite($fp, $value) === false) {
            return false;
        }

        return fclose($fp);
    }

    /**
     * ユーザが作成したファイルをアーカイブしダウンロードさせる
     * TODO 要リファクタリング
     *
     * @param  string  $dir           アーカイブを行なうディレクトリ
     * @param  string  $template_code テンプレートコード
     *
     * @return bool 成功した場合 true; 失敗した場合 false
     */
    public static function downloadArchiveFiles($dir, $template_code)
    {
        // ダウンロードされるファイル名
        $dlFileName = 'tpl_package_'.$template_code.'_'.date('YmdHis').'.tar.gz';

        $debug_message = $dir.' から '.$dlFileName." を作成します...\n";
        // ファイル一覧取得
        $arrFileHash = SC_Helper_FileManager_Ex::sfGetFileList($dir);
        $arrFileList = [];
        foreach ($arrFileHash as $val) {
            $arrFileList[] = $val['file_name'];
            $debug_message .= '圧縮：'.$val['file_name']."\n";
        }
        GC_Utils_Ex::gfPrintLog($debug_message);

        // ディレクトリを移動
        chdir($dir);
        // 圧縮をおこなう
        $tar = new Archive_Tar($dlFileName, true);
        if ($tar->create($arrFileList)) {
            // ダウンロード用HTTPヘッダ出力
            header("Content-disposition: attachment; filename={$dlFileName}");
            header("Content-type: application/octet-stream; name={$dlFileName}");
            header('Cache-Control: ');
            header('Pragma: ');
            readfile($dlFileName);
            unlink($dir.'/'.$dlFileName);

            return true;
        } else {
            return false;
        }
    }

    /**
     * tarアーカイブを解凍する.
     *
     * @param  string  $path アーカイブパス
     *
     * @return bool Archive_Tar::extractModify()のエラー
     */
    public static function unpackFile($path)
    {
        // 圧縮フラグTRUEはgzip解凍をおこなう
        $tar = new Archive_Tar($path, true);

        $dir = dirname($path);
        $file_name = basename($path);

        // 拡張子を切り取る
        $unpacking_name = preg_replace("/(\.tar|\.tar\.gz)$/", '', $file_name);

        // 指定されたフォルダ内に解凍する
        $result = $tar->extractModify($dir.'/', $unpacking_name);
        GC_Utils_Ex::gfPrintLog('解凍：'.$dir.'/'.$file_name.'->'.$dir.'/'.$unpacking_name);

        // フォルダ削除
        SC_Helper_FileManager_Ex::deleteFile($dir.'/'.$unpacking_name);
        // 圧縮ファイル削除
        unlink($path);

        return $result;
    }

    /**
     * 指定されたパスの配下を再帰的に削除.
     *
     * @param  string  $path       削除対象のディレクトリまたはファイルのパス
     * @param  bool $del_myself $pathそのものを削除するか. true なら削除する.
     *
     * @return bool
     */
    public static function deleteFile($path, $del_myself = true)
    {
        $flg = false;
        // 対象が存在するかを検証.
        if (file_exists($path) === false) {
            GC_Utils_Ex::gfPrintLog($path.' が存在しません.');
        } elseif (is_dir($path)) {
            // ディレクトリが指定された場合
            $handle = opendir($path);
            if (!$handle) {
                GC_Utils_Ex::gfPrintLog($path.' が開けませんでした.');
            }
            while (($item = readdir($handle)) !== false) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $cur_path = $path.'/'.$item;
                if (is_dir($cur_path)) {
                    // ディレクトリの場合、再帰処理
                    $flg = SC_Helper_FileManager_Ex::deleteFile($cur_path);
                } else {
                    // ファイルの場合、unlink
                    $flg = @unlink($cur_path);
                }
            }
            closedir($handle);
            // ディレクトリを削除
            GC_Utils_Ex::gfPrintLog($path.' を削除します.');
            if ($del_myself) {
                $flg = @rmdir($path);
            }
        } else {
            // ファイルが指定された場合.
            GC_Utils_Ex::gfPrintLog($path.' を削除します.');
            $flg = @unlink($path);
        }

        return $flg;
    }

    /**
     * パスを USER_REALDIR 以下の相対パスへ変換する.
     *
     * パスが USER_REALDIR の範囲外の場合は USER_DIR を返す.
     *
     * @param string $path 変換するパス
     *
     * @return string USER_REALDIR 以下の相対パス
     */
    public static function convertToRelativePath($path)
    {
        $endsWithSlash = substr($path, -1) === '/';
        $path = SC_Helper_FileManager_Ex::convertToAbsolutePath($path);

        // パスを正規化して返す
        $path = str_replace(realpath(HTML_REALDIR).DIRECTORY_SEPARATOR, '', realpath($path));
        $path = str_replace('\\', '/', $path);
        $path = $endsWithSlash ? $path.'/' : $path;

        return $path;
    }

    /**
     * パスを USER_REALDIR 以下の絶対パスへ変換する.
     *
     * パスが USER_REALDIR の範囲外の場合は USER_REALDIR を返す.
     *
     * @param string $path 変換するパス
     *
     * @return string USER_REALDIR 以下の絶対パス
     */
    public static function convertToAbsolutePath($path)
    {
        $endsWithSlash = substr($path, -1) === '/';
        // 絶対パスかどうか
        if (str_starts_with($path, '/') || preg_match('/^[a-z]:/i', $path)) {
            $path = realpath($path);
        } else {
            $path = realpath(HTML_REALDIR.$path);
        }

        // USER_REALDIR 以下のパスかどうか
        if ($path === false || !str_contains($path, realpath(USER_REALDIR))) {
            $path = realpath(USER_REALDIR);
        }

        // パスを正規化して返す
        $path = str_replace('\\', '/', $path);
        $path = $endsWithSlash ? $path.'/' : $path;

        return $path;
    }
}
