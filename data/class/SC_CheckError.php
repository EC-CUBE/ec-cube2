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

/*----------------------------------------------------------------------
 * [名称] SC_CheckError
 * [概要] エラーチェッククラス
 *----------------------------------------------------------------------
 */
class SC_CheckError
{
    public $arrErr = [];
    public $arrParam;

    // チェック対象の値が含まれる配列をセットする。
    public function __construct($array = '')
    {
        if ($array != '') {
            $this->arrParam = $array;
        } else {
            $this->arrParam = $_POST;
        }
    }

    /**
     * @param string[] $arrFunc
     */
    public function doFunc($value, $arrFunc)
    {
        foreach ($arrFunc as $key) {
            $this->$key($value);
        }
    }

    /**
     * HTMLのタグをチェックする
     *
     * @param  array $value value[0] = 表示名
     *                      value[1] = 判定対象配列キー
     *                      value[2] = 許可するタグが格納された配列
     *
     * @return void
     */
    public function HTML_TAG_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];
        $arrAllowedTag = $value[2];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        // HTMLに含まれているタグを抽出する
        $match = [];
        preg_match_all('/<\/?([a-z\d]+)/i', $this->arrParam[$keyname], $match);
        $arrTagIncludedHtml = $match[1];
        // 抽出結果を小文字に変換
        foreach ($arrTagIncludedHtml as $key => $matchedTag) {
            $arrTagIncludedHtml[$key] = strtolower($matchedTag);
        }
        $arrDiffTag = array_diff($arrTagIncludedHtml, $arrAllowedTag);

        if (empty($arrDiffTag)) {
            return;
        }

        // 少々荒っぽいが、表示用 HTML に変換する
        foreach ($arrDiffTag as &$tag) {
            $tag = '['.htmlspecialchars($tag).']';
        }
        $html_diff_tag_list = implode(', ', $arrDiffTag);

        $this->arrErr[$keyname] = sprintf(
            '※ %sに許可されていないタグ %s が含まれています。<br />',
            $disp_name,
            $html_diff_tag_list
        );
    }

    /**
     * 必須入力の判定
     *
     * 受け取りがない場合エラーを返す
     *
     * @param  array $value value[0] = 表示名 value[1] = 判定対象配列キー
     *
     * @return void
     */
    public function EXIST_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $input_var = $this->arrParam[$keyname] ?? '';
        if (is_array($input_var)) {
            if (count($input_var) == 0) {
                $this->arrErr[$keyname] = "※ {$disp_name}が選択されていません。<br />";
            }
        } else {
            if (strlen($input_var) == 0) {
                $this->arrErr[$keyname] = "※ {$disp_name}が入力されていません。<br />";
            }
        }
    }

    /**
     * 必須入力の判定(逆順)
     *
     * 受け取りがない場合エラーを返す
     *
     * @param  array $value value[0] = 判定対象配列キー value[1] = 表示名
     *
     * @return void
     */
    public function EXIST_CHECK_REVERSE($value)
    {
        $keyname = $value[0];
        $disp_name = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value, [0]);

        if (strlen($this->arrParam[$keyname]) == 0) {
            $this->arrErr[$keyname] = "※ {$disp_name}が入力されていません。<br />";
        }
    }

    /**
     * スペース、タブの判定
     *
     * 受け取りがない場合エラーを返す
     *
     * @param  array $value value[0] = 表示名 value[1] = 判定対象配列キー
     *
     * @return void
     */
    public function SPTAB_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $input_var = $this->arrParam[$keyname];
        if (strlen($input_var) != 0
            && preg_match("/^[ 　\t\r\n]+$/", $input_var)
        ) {
            $this->arrErr[$keyname] = sprintf(
                '※ %sにスペース、タブ、改行のみの入力はできません。<br />',
                $disp_name
            );
        }
    }

    /**
     * スペース、タブの判定
     *
     * 受け取りがない場合エラーを返す
     *
     * @param  array $value value[0] = 表示名 value[1] = 判定対象配列キー
     *
     * @return void
     */
    public function NO_SPTAB($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $input_var = $this->arrParam[$keyname];
        if (strlen($input_var) != 0
            && preg_match("/[　 \t\r\n]+/u", $input_var)
        ) {
            $this->arrErr[$keyname] = sprintf(
                '※ %sにスペース、タブ、改行は含めないで下さい。<br />',
                $disp_name
            );
        }
    }

    /**
     * ゼロで開始されている数値の判定
     *
     * ゼロで始まる数値の場合エラーを返す
     *
     * @param  array $value value[0] = 表示名 value[1] = 判定対象配列キー
     *
     * @return void
     */
    public function ZERO_START($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $input_var = $this->arrParam[$keyname];
        if (strlen($input_var) != 0
            && preg_match('/^[0]+[0-9]+$/', $input_var)
        ) {
            $this->arrErr[$keyname] = "※ {$disp_name}に0で始まる数値が入力されています。<br />";
        }
    }

    /**
     * 必須選択の判定
     *
     * プルダウンなどで選択されていない場合エラーを返す
     *
     * @param  array $value value[0] = 表示名 value[1] = 判定対象配列キー
     *
     * @return void
     */
    public function SELECT_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        if (strlen($this->arrParam[$keyname]) == 0) {
            $this->arrErr[$keyname] = "※ {$disp_name}が選択されていません。<br />";
        }
    }

    /**
     * 同一性の判定
     *
     * 入力が指定文字数以上ならエラーを返す
     *
     * @param  array $value value[0] = 表示名1
     *                      value[1] = 表示名2
     *                      value[2] = 判定対象配列キー1
     *                      value[3] = 判定対象配列キー2
     *
     * @return void
     */
    public function EQUAL_CHECK($value)
    {
        $disp_name1 = $value[0];
        $disp_name2 = $value[1];
        $keyname1 = $value[2];
        $keyname2 = $value[3];

        if (isset($this->arrErr[$keyname1])
            || isset($this->arrErr[$keyname2])
        ) {
            return;
        }

        $this->createParam($value, [2, 3]);

        // 文字数の取得
        if (($this->arrParam[$keyname1] ?? '') !== ($this->arrParam[$keyname2] ?? '')) {
            $this->arrErr[$keyname1] =
                "※{$disp_name1}と{$disp_name2}が一致しません。<br />";
        }
    }

    /**
     * 値が異なることの判定
     *
     * 入力が指定文字数以上ならエラーを返す
     *
     * @param  array $value value[0] = 表示名1
     *                      value[1] = 表示名2
     *                      value[2] = 判定対象配列キー1
     *                      value[3] = 判定対象配列キー2
     *
     * @return void
     */
    public function DIFFERENT_CHECK($value)
    {
        $disp_name1 = $value[0];
        $disp_name2 = $value[1];
        $keyname1 = $value[2];
        $keyname2 = $value[3];

        if (isset($this->arrErr[$keyname1])
            || isset($this->arrErr[$keyname2])
        ) {
            return;
        }

        $this->createParam($value, [2, 3]);

        // 文字数の取得
        if (($this->arrParam[$keyname1] ?? '') == ($this->arrParam[$keyname2] ?? '')) {
            $this->arrErr[$keyname1] = sprintf(
                '※ %sと%sは、同じ値を使用できません。<br />',
                $disp_name1,
                $disp_name2
            );
        }
    }

    /**
     * 値の大きさを比較する value[2] < value[3]でなければエラー
     *
     * 入力が指定文字数以上ならエラーを返す
     *
     * @param  array $value value[0] = 表示名1
     *                      value[1] = 表示名2
     *                      value[2] = 判定対象配列キー1
     *                      value[3] = 判定対象配列キー2
     *
     * @return void
     */
    public function GREATER_CHECK($value)
    {
        $disp_name1 = $value[0];
        $disp_name2 = $value[1];
        $keyname1 = $value[2];
        $keyname2 = $value[3];

        if (isset($this->arrErr[$keyname1])
            || isset($this->arrErr[$keyname2])
        ) {
            return;
        }

        $this->createParam($value, [2, 3]);

        // 文字数の取得
        $input_var1 = $this->arrParam[$keyname1] ?? '';
        $input_var2 = $this->arrParam[$keyname2] ?? '';
        if ($input_var1 != ''
            && $input_var2 != ''
            && ($input_var1 > $input_var2)
        ) {
            $this->arrErr[$keyname1] = sprintf(
                '※ %sは%sより大きい値を入力できません。<br />',
                $disp_name1,
                $disp_name2
            );
        }
    }

    /**
     * 最大文字数制限の判定
     *
     * 入力が指定文字数以上ならエラーを返す
     *
     * @param  int[] $value value[0] = 表示名
     *                      value[1] = 判定対象配列キー
     *                      value[2] = 最大文字数(半角も全角も1文字として数える)
     *
     * @return void
     */
    public function MAX_LENGTH_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];
        $max_str_len = $value[2];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        // 文字数の取得
        if (mb_strlen($this->arrParam[$keyname]) > $max_str_len) {
            $this->arrErr[$keyname] = sprintf(
                '※ %sは%d字以下で入力してください。<br />',
                $disp_name,
                $max_str_len
            );
        }
    }

    /**
     * 最小文字数制限の判定
     *
     * 入力が指定文字数未満ならエラーを返す
     *
     * @param  array $value value[0] = 表示名
     *                      value[1] = 判定対象配列キー
     *                      value[2] = 最小文字数(半角も全角も1文字として数える)
     *
     * @return void
     */
    public function MIN_LENGTH_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];
        $min_str_len = $value[2];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        // 文字数の取得
        $len = mb_strlen($this->arrParam[$keyname]);

        // 未入力の場合はエラーにしない(EXIST_CHECKを使用すること)
        if ($len === 0) {
            return;
        }
        if ($len < $min_str_len) {
            $this->arrErr[$keyname] = sprintf(
                '※ %sは%d字以上で入力してください。<br />',
                $disp_name,
                $min_str_len
            );
        }
    }

    /**
     * 最大数制限の判定
     *
     * 入力が最大数より大きければエラーを返す
     *
     * @param  array $value value[0] = 表示名
     *                      value[1] = 判定対象配列キー
     *                      value[2] = 最大数
     *
     * @return void
     */
    public function MAX_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];
        $max_threshold = $value[2];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        // 文字数の取得
        if ($this->arrParam[$keyname] > $max_threshold) {
            $this->arrErr[$keyname] = sprintf(
                '※ %sは%d以下で入力してください。<br />',
                $disp_name,
                $max_threshold
            );
        }
    }

    /**
     * 最小数値制限の判定
     *
     * 入力が最小数未満ならエラーを返す
     *
     * @param  array $value value[0] = 表示名
     *                      value[1] = 判定対象配列キー
     *                      value[2] = 最小数
     *
     * @return void
     */
    public function MIN_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];
        $min_threshold = $value[2];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        if ($this->arrParam[$keyname] < $min_threshold) {
            $this->arrErr[$keyname] = sprintf(
                '※ %sは%d以上で入力してください。<br />',
                $disp_name,
                $min_threshold
            );
        }
    }

    /**
     * 数字の判定
     *
     * 入力文字が数字以外ならエラーを返す
     *
     * @param  array $value value[0] = 表示名 value[1] = 判定対象配列キー
     *
     * @return void
     */
    public function NUM_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        if ($this->numelicCheck($this->arrParam[$keyname])) {
            $this->arrErr[$keyname] = "※ {$disp_name}は数字で入力してください。<br />";
        }
    }

    /**
     * 小数点を含む数字の判定
     *
     * 入力文字が数字以外ならエラーを返す
     *
     * @param  array $value value[0] = 表示名 value[1] = 判定対象配列キー
     *
     * @return void
     */
    public function NUM_POINT_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        if (strlen($this->arrParam[$keyname]) > 0
            && !is_numeric($this->arrParam[$keyname])
        ) {
            $this->arrErr[$keyname] = "※ {$disp_name}は数字で入力してください。<br />";
        }
    }

    public function ALPHA_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        if (strlen($this->arrParam[$keyname]) > 0
            && !ctype_alpha($this->arrParam[$keyname])
        ) {
            $this->arrErr[$keyname] = "※ {$disp_name}は半角英字で入力してください。<br />";
        }
    }

    /**
     * 電話番号の判定 (3項目入力)
     *
     * 数字チェックと文字数チェックを実施する。
     *
     * @param array $value 各要素は以下の通り。
     *     [0]: 表示名
     *     [1]: 判定対象配列キー1
     *     [2]: 判定対象配列キー2
     *     [3]: 判定対象配列キー3
     *     [4]: 電話番号各項目制限 (指定なしの場合、TEL_ITEM_LEN)
     *     [5]: 電話番号総数 (指定なしの場合、TEL_LEN)
     *
     * @return void
     */
    public function TEL_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname1 = $value[1];
        $keyname2 = $value[2];
        $keyname3 = $value[3];

        $telItemLen = $value[4] ?? TEL_ITEM_LEN;
        $telLen = $value[5] ?? TEL_LEN;

        if (isset($this->arrErr[$keyname1])
            || isset($this->arrErr[$keyname2])
            || isset($this->arrErr[$keyname3])
        ) {
            return;
        }

        $this->createParam($value, [1, 2, 3]);

        $cnt = 0;
        for ($i = 1; $i <= 3; $i++) {
            $keyname = ${"keyname{$i}"};
            if (strlen($this->arrParam[$keyname]) > 0) {
                $cnt++;
            }
        }

        // 全ての項目が満たされていない場合を判定(一部だけ入力されている状態)
        if ($cnt > 0 && $cnt < 3) {
            $this->arrErr[$keyname1] =
                "※ {$disp_name}は全ての項目を入力してください。<br />";
        }

        $total_count = 0;
        for ($i = 1; $i <= 3; $i++) {
            $keyname = ${"keyname{$i}"};
            $input_var = $this->arrParam[$keyname];

            if (strlen($input_var) > 0 && strlen($input_var) > $telItemLen) {
                $this->arrErr[$keyname] = sprintf(
                    '※ %sは%d字以内で入力してください。<br />',
                    $disp_name.$i,
                    $telItemLen
                );
            } elseif ($this->numelicCheck($input_var)) {
                $this->arrErr[$keyname] = "※ {$disp_name}{$i}は数字で入力してください。<br />";
            }

            $total_count += strlen($input_var);
        }

        // 合計値チェック
        if ($total_count > $telLen) {
            $this->arrErr[$keyname3] =
                "※ {$disp_name}は{$telLen}文字以内で入力してください。<br />";
        }
    }

    /* 関連項目が完全に満たされているか判定
        value[0]    : 表示名
        value[1..]  : 判定対象配列キー
    */
    public function FULL_EXIST_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        // 既に該当項目にエラーがある場合は、判定しない。
        $max = count($value);
        for ($i = 1; $i < $max; $i++) {
            if (isset($this->arrErr[$value[$i]])) {
                return;
            }
        }

        $this->createParam($value, range(1, $max));

        // 全ての項目が入力されていない場合はエラーとする。
        $blank = false;

        for ($i = 1; $i < $max; $i++) {
            if (strlen($this->arrParam[$value[$i]]) <= 0) {
                $blank = true;
            }
        }

        if ($blank) {
            $this->arrErr[$keyname] = "※ {$disp_name}が入力されていません。<br />";
        }
    }

    /* 関連項目が全て満たされているか判定
        value[0]    : 表示名
        value[1..]  : 判定対象配列キー
    */
    public function ALL_EXIST_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        // 既に該当項目にエラーがある場合は、判定しない。
        $max = count($value);
        for ($i = 1; $i < $max; $i++) {
            if (isset($this->arrErr[$value[$i]])) {
                return;
            }
        }

        $this->createParam($value, range(1, $max));

        $blank = false;
        $input = false;

        // 全ての項目がブランクでないか、全ての項目が入力されていない場合はエラーとする。
        for ($i = 1; $i < $max; $i++) {
            if (strlen($this->arrParam[$value[$i]]) <= 0) {
                $blank = true;
            } else {
                $input = true;
            }
        }

        if ($blank && $input) {
            $this->arrErr[$keyname] = "※ {$disp_name}は全ての項目を入力して下さい。<br />";
        }
    }

    /* 関連項目がどれか一つ満たされているか判定
        value[0]    : 表示名
        value[1..]  : 判定対象配列キー
    */
    public function ONE_EXIST_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        // 既に該当項目にエラーがある場合は、判定しない。
        $max = count($value);
        for ($i = 1; $i < $max; $i++) {
            if (isset($this->arrErr[$value[$i]])) {
                return;
            }
        }

        $this->createParam($value, range(1, $max));

        $input = false;

        // 全ての項目がブランクでないか、全ての項目が入力されていない場合はエラーとする。
        for ($i = 1; $i < $max; $i++) {
            if (strlen($this->arrParam[$value[$i]]) > 0) {
                $input = true;
            }
        }

        if (!$input) {
            $this->arrErr[$keyname] = "※ {$disp_name}が入力されていません。<br />";
        }
    }

    /* 上位の項目が満たされているか判定
        value[0]    : 表示名
        value[1..]  : 判定対象配列キー
    */
    public function TOP_EXIST_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        // 既に該当項目にエラーがある場合は、判定しない。
        $max = count($value);
        for ($i = 1; $i < $max; $i++) {
            if (isset($this->arrErr[$value[$i]])) {
                return;
            }
        }

        $this->createParam($value, range(1, $max));

        $blank = false;
        $error = false;

        // 全ての項目がブランクでないか、全ての項目が入力されていない場合はエラーとする。
        for ($i = 1; $i < $max; $i++) {
            if (strlen($this->arrParam[$value[$i]]) <= 0) {
                $blank = true;
            } else {
                if ($blank) {
                    $error = true;
                }
            }
        }

        if ($error) {
            $this->arrErr[$keyname] = "※ {$disp_name}は先頭の項目から順番に入力して下さい。<br />";
        }
    }

    /*　カタカナの判定　 */
    // 入力文字がカナ以外ならエラーを返す
    // value[0] = 表示名 value[1] = 判定対象配列キー
    public function KANA_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $input_var = $this->arrParam[$keyname];
        $pattern = '/^[ァ-ヶｦ-ﾟー]+$/u';
        if (strlen($input_var) > 0 && !preg_match($pattern, $input_var)) {
            $this->arrErr[$keyname] = "※ {$disp_name}はカタカナで入力してください。<br />";
        }
    }

    /*　カタカナの判定2 (タブ、スペースは許可する) */
    // 入力文字がカナ以外ならエラーを返す
    // value[0] = 表示名 value[1] = 判定対象配列キー
    public function KANABLANK_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $input_var = $this->arrParam[$keyname];
        $pattern = "/^([　 \t\r\n]|[ァ-ヶ]|[ー])+$/u";
        if (strlen($input_var) > 0 && !preg_match($pattern, $input_var)) {
            $this->arrErr[$keyname] = "※ {$disp_name}はカタカナで入力してください。<br />";
        }
    }

    /*　英数字の判定　 */
    // 入力文字が英数字以外ならエラーを返す
    // value[0] = 表示名 value[1] = 判定対象配列キー
    public function ALNUM_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $input_var = $this->arrParam[$keyname];
        if (strlen($input_var) > 0 && !ctype_alnum($input_var)) {
            $this->arrErr[$keyname] = "※ {$disp_name}は英数字で入力してください。<br />";
        }
    }

    /*　英数記号の判定　 */
    // 入力文字が英数記号以外ならエラーを返す
    // value[0] = 表示名 value[1] = 判定対象配列キー
    public function GRAPH_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $input_var = $this->arrParam[$keyname];
        $pattern = '/^[[:graph:][:space:]]+$/i';
        if (strlen($input_var) > 0 && !preg_match($pattern, $input_var)) {
            $this->arrErr[$keyname] = "※ {$disp_name}は英数記号で入力してください。<br />";
        }
    }

    /**
     * パスワードに使用可能な文字列のチェック
     *
     * 半角英数字をそれぞれ1種類以上含む PASSWORD_MIN_LEN 文字以上 PASSWORD_MAX_LEN 文字以下の文字列ではない場合エラーとする
     * PASSWORD_MIN_LEN が8未満の場合は E_USER_WARNING を出力する
     *
     * @param array $value $value[0] = 表示名 $value[1] = 判定対象配列キー
     */
    public function PASSWORD_CHAR_CHECK($value)
    {
        if (PASSWORD_MIN_LEN < 8) {
            trigger_error('PASSWORD_MIN_LEN が8未満に設定されています。', E_USER_WARNING);
        }
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $input_var = $this->arrParam[$keyname];
        // see https://qiita.com/mpyw/items/886218e7b418dfed254b
        $pattern = '/\A(?=.*?[a-z])(?=.*?\d)[!-~]{'.PASSWORD_MIN_LEN.','.PASSWORD_MAX_LEN.'}+\z/i';
        if (strlen($input_var) > 0 && !preg_match($pattern, $input_var)) {
            $this->arrErr[$keyname] =
                "※ {$disp_name}は英数字をそれぞれ1種類使用し、".PASSWORD_MIN_LEN.'文字以上で入力してください。<br />';
        }
    }

    /*　必須選択の判定　 */
    // 入力値で0が許されない場合エラーを返す
    // value[0] = 表示名 value[1] = 判定対象配列キー
    public function ZERO_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        $this->createParam($value);

        if ($this->arrParam[$keyname] == '0') {
            $this->arrErr[$keyname] = "※ {$disp_name}は1以上を入力してください。<br />";
        }
    }

    /*　桁数の判定 (最小最大) */
    // 入力文字の桁数判定　→　最小桁数＜入力文字列＜最大桁数
    // value[0] = 表示名 value[1] = 判定対象配列キー value[2] = 最小桁数 value[3] = 最大桁数
    public function NUM_RANGE_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];
        $min_digit = $value[2];
        $max_digit = $value[3];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        // $this->arrParam[$keyname] = mb_convert_kana($this->arrParam[$keyname], 'n');
        $count = strlen($this->arrParam[$keyname]);
        if (($count > 0) && $min_digit > $count || $max_digit < $count) {
            $this->arrErr[$keyname] = sprintf(
                '※ %sは%d桁～%d桁で入力して下さい。<br />',
                $disp_name,
                $min_digit,
                $max_digit
            );
        }
    }

    /*　桁数の判定　 */
    // 入力文字の桁数判定　→　入力文字列 = 桁数　以外はNGの場合
    // value[0] = 表示名 value[1] = 判定対象配列キー value[2] = 桁数
    public function NUM_COUNT_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];
        $digit = $value[2];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $count = strlen($this->arrParam[$keyname]);
        if (($count > 0) && $count != $digit) {
            $this->arrErr[$keyname] = "※ {$disp_name}は{$digit}桁で入力して下さい。<br />";
        }
    }

    /**
     * メールアドレス形式の判定
     *
     * @param array $value 各要素は以下の通り。
     *     [0]: 表示名
     *     [1]: 判定対象配列キー
     *
     * @return void
     */
    public function EMAIL_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        // 入力がない場合処理しない
        if (strlen($this->arrParam[$keyname]) === 0) {
            return;
        }

        $wsp = '[\x20\x09]';
        $vchar = '[\x21-\x7e]';
        $quoted_pair = "\\\\(?:$vchar|$wsp)";
        $qtext = '[\x21\x23-\x5b\x5d-\x7e]';
        $qcontent = "(?:$qtext|$quoted_pair)";
        $quoted_string = "\"$qcontent*\"";
        $atext = '[a-zA-Z0-9!#$%&\'*+\-\/\=?^_`{|}~]';
        $dot_atom = "$atext+(?:[.]$atext+)*";
        $local_part = "(?:$dot_atom|$quoted_string)";
        $domain = $dot_atom;
        $addr_spec = "{$local_part}[@]$domain";

        $dot_atom_loose = "$atext+(?:[.]|$atext)*";
        $local_part_loose = "(?:$dot_atom_loose|$quoted_string)";
        $addr_spec_loose = "{$local_part_loose}[@]$domain";

        if (RFC_COMPLIANT_EMAIL_CHECK) {
            $regexp = "/\A{$addr_spec}\z/";
        } else {
            // 携帯メールアドレス用に、..や.@を許容する。
            $regexp = "/\A{$addr_spec_loose}\z/";
        }

        if (!preg_match($regexp, $this->arrParam[$keyname])) {
            $this->arrErr[$keyname] = "※ {$disp_name}の形式が不正です。<br />";

            return;
        }

        // 最大文字数制限の判定 (#871)
        $arrValueTemp = $value;
        $arrValueTemp[2] = 256;
        $this->MAX_LENGTH_CHECK($arrValueTemp);
    }

    /*　メールアドレスに使用できる文字の判定　 */
    //　メールアドレスに使用する文字を正規表現で判定する
    //  value[0] = 表示名 value[1] = 判定対象配列キー
    public function EMAIL_CHAR_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $input_var = $this->arrParam[$keyname];
        $pattern = "/^[a-zA-Z0-9_\.@\+\?-]+$/i";
        if (strlen($input_var) > 0 && !preg_match($pattern, $input_var)) {
            $this->arrErr[$keyname] = "※ {$disp_name}に使用する文字を正しく入力してください。<br />";
        }
    }

    /*　URL形式の判定　 */
    //　URLを正規表現で判定する。デフォルトでhttp://があってもOK
    //  value[0] = 表示名 value[1] = 判定対象配列キー
    public function URL_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $input_var = $this->arrParam[$keyname];
        $pattern = "@^https?://+($|[a-zA-Z0-9_~=:&\?\.\/-])+$@i";
        if (strlen($input_var) > 0 && !preg_match($pattern, $input_var)) {
            $this->arrErr[$keyname] = "※ {$disp_name}を正しく入力してください。<br />";
        }
    }

    /*　IPアドレスの判定　 */
    //  value[0] = 表示名 value[1] = 判定対象配列キー
    public function IP_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        // 改行コードが含まれている場合には配列に変換
        $params = str_replace("\r", '', $this->arrParam[$keyname]);
        if (!empty($params)) {
            if (!str_contains($params, "\n")) {
                $params .= "\n";
            }
            $params = explode("\n", $params);
            foreach ($params as $param) {
                $param = trim($param);
                if (long2ip(ip2long($param)) != trim($param) && !empty($param)) {
                    $this->arrErr[$keyname] = "※ {$disp_name}に正しい形式のIPアドレスを入力してください。<br />";
                }
            }
        }
    }

    /*　拡張子の判定　 */
    // 受け取りがない場合エラーを返す
    // value[0] = 表示名 value[1] = 判定対象配列キー value[2]=array(拡張子)
    public function FILE_EXT_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];
        $arrExtension = $value[2];

        if (isset($this->arrErr[$keyname]) || count($arrExtension) == 0) {
            return;
        }

        $this->createParam($value);

        $match = false;
        $filename = $_FILES[$keyname]['name'];
        if (strlen($filename) >= 1) {
            foreach ($arrExtension as $check_ext) {
                $pattern = '/'.preg_quote('.'.$check_ext).'$/i';
                $match = preg_match($pattern, $filename) >= 1;
                if ($match === true) {
                    break;
                }
            }
        }
        if ($match === false) {
            $str_ext = implode('・', $arrExtension);
            $this->arrErr[$keyname] = "※ {$disp_name}で許可されている形式は、{$str_ext}です。<br />";
        }
    }

    /* ファイルが存在するかチェックする */
    // 受け取りがない場合エラーを返す
    // value[0] = 表示名 value[1] = 判定対象配列キー  value[2] = 指定ディレクトリ
    public function FIND_FILE($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];
        $target_dir = $value[2];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        if ($target_dir != '') {
            $dir = $target_dir;
        } else {
            $dir = IMAGE_SAVE_REALDIR;
        }

        $path = $dir.'/'.$this->arrParam[$keyname];
        $path = str_replace('//', '/', $path);

        if ($this->arrParam[$keyname] != '' && !file_exists($path)) {
            $this->arrErr[$keyname] = "※ {$disp_name}が見つかりません。<br />";
        }
    }

    /*　ファイルが上げられたか確認　 */
    // 受け取りがない場合エラーを返す
    // value[0] = 表示名 value[1] = 判定対象配列キー  value[2] = 指定サイズ(KB)
    public function FILE_EXIST_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $file_size = $_FILES[$keyname]['size'];
        if ($file_size == '' || !($file_size > 0)) {
            $this->arrErr[$keyname] = "※ {$disp_name}をアップロードして下さい。<br />";
        }
    }

    /*　ファイルサイズの判定　 */
    // 受け取りがない場合エラーを返す
    // value[0] = 表示名 value[1] = 判定対象配列キー  value[2] = 指定サイズ(KB)
    public function FILE_SIZE_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];
        $max_file_size = $value[2];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        if (isset($_FILES[$keyname]['size']) && $_FILES[$keyname]['size'] > $max_file_size * 1024) {
            $byte = 'KB';
            if ($max_file_size >= 1000) {
                $max_file_size /= 1000;
                $byte = 'MB';
            }
            $this->arrErr[$keyname] = sprintf(
                '※ %sのファイルサイズは%d%s以下のものを使用してください。<br />',
                $disp_name,
                $max_file_size,
                $byte
            );
        }
    }

    /*　ファイル名の判定　 */
    // 入力文字が英数字,'_','-','.'以外ならエラーを返す
    // value[0] = 表示名 value[1] = 判定対象配列キー
    public function FILE_NAME_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $filename = $_FILES[$keyname]['name'];
        $pattern = "/^[[:alnum:]_\.-]+$/i";
        if (strlen($filename) > 0 && !preg_match($pattern, $filename)) {
            $this->arrErr[$keyname] = "※ {$disp_name}のファイル名には、英数字、記号（_ - .）のみを入力して下さい。<br />";
        }
    }

    /*　ファイル名の判定(アップロード以外の時)　 */
    // 入力文字が英数字,'_','-','.'以外ならエラーを返す
    // value[0] = 表示名 value[1] = 判定対象配列キー
    public function FILE_NAME_CHECK_BY_NOUPLOAD($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $filename = $this->arrParam[$keyname];
        $pattern = '/[^[:alnum:]_.\\-]/';
        if (strlen($filename) > 0 && preg_match($pattern, $filename)) {
            $this->arrErr[$keyname] = "※ {$disp_name}のファイル名には、英数字、記号（_ - .）のみを入力して下さい。<br />";
        }
    }

    // 日付チェック
    // value[0] = 表示名
    // value[1] = 判定対象配列キー (年)
    // value[2] = 判定対象配列キー (月)
    // value[3] = 判定対象配列キー (日)
    public function CHECK_DATE($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value, [1, 2, 3]);

        $input_year = $this->arrParam[$value[1]];
        $input_month = $this->arrParam[$value[2]];
        $input_day = $this->arrParam[$value[3]];
        // 少なくともどれか一つが入力されている。
        if ($input_year > 0 || $input_month > 0 || $input_day > 0) {
            // 年月日のどれかが入力されていない。
            if (!(strlen($input_year) > 0 && strlen($input_month) > 0 && strlen($input_day) > 0)) {
                $this->arrErr[$keyname] = "※ {$disp_name}は全ての項目を入力して下さい。<br />";
            } elseif (!checkdate((int) $input_month, (int) $input_day, (int) $input_year)) {
                $this->arrErr[$keyname] = "※ {$disp_name}が正しくありません。<br />";
            }
        }
    }

    /**
     * 日時チェック (年月日時分)
     *
     * @param array $value
     *      [0] = 表示名,
     *      [1] = 判定対象配列キー (年),
     *      [2] = 判定対象配列キー (月),
     *      [3] = 判定対象配列キー (日),
     *      [4] = 判定対象配列キー (時),
     *      [5] = 判定対象配列キー (分),
     *
     * @return void
     */
    public function CHECK_DATE2($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value, [1, 2, 3, 4, 5]);

        $input_year = $this->arrParam[$value[1]];
        $input_month = $this->arrParam[$value[2]];
        $input_day = $this->arrParam[$value[3]];
        $input_hour = $this->arrParam[$value[4]];
        $input_minute = $this->arrParam[$value[5]];
        // 少なくともどれか一つが入力されている。
        if ($input_year > 0 || $input_month > 0 || $input_day > 0
            || $input_hour >= 0 || $input_minute >= 0
        ) {
            // 年月日時のどれかが入力されていない。
            if (!(strlen($input_year) > 0 && strlen($input_month) > 0 && strlen($input_day) > 0 && strlen($input_hour) > 0 && strlen($input_minute) > 0)) {
                $this->arrErr[$keyname] = "※ {$disp_name}は全ての項目を入力して下さい。<br />";
            } elseif (!checkdate((int) $input_month, (int) $input_day, (int) $input_year)) {
                $this->arrErr[$keyname] = "※ {$disp_name}が正しくありません。<br />";
            }
        }
    }

    /**
     * 年月チェック
     *
     * @param array $value
     *      [0] = 表示名
     *      [1] = 判定対象配列キー (年)
     *      [2] = 判定対象配列キー (月)
     *
     * @return void
     */
    public function CHECK_DATE3($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value, [1, 2]);

        $input_year = $this->arrParam[$value[1]];
        $input_month = $this->arrParam[$value[2]];
        // 少なくともどれか一つが入力されている。
        if ($input_year > 0 || $input_month > 0) {
            // 年月日時のどれかが入力されていない。
            if (!(strlen($input_year) > 0 && strlen($input_month) > 0)) {
                $this->arrErr[$keyname] = "※ {$disp_name}は全ての項目を入力して下さい。<br />";
            } elseif (!checkdate((int) $input_month, 1, (int) $input_year)) {
                $this->arrErr[$keyname] = "※ {$disp_name}が正しくありません。<br />";
            }
        }
    }

    /**
     * 誕生日チェック
     *
     * @param array $value
     *      [0] = 表示名
     *      [1] = 判定対象配列キー (年)
     *      [2] = 判定対象配列キー (月)
     *      [3] = 判定対象配列キー (日)
     *
     * @return void
     */
    public function CHECK_BIRTHDAY($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value, [1, 2, 3]);

        // 年が入力されている。
        if (strlen($this->arrParam[$keyname]) >= 1) {
            // 年の数字チェック、最小数値制限チェック
            $this->doFunc(
                ["{$disp_name}(年)", $keyname, BIRTH_YEAR],
                ['NUM_CHECK', 'MIN_CHECK']
            );
            // 上のチェックでエラーある場合、中断する。
            if (isset($this->arrErr[$keyname])) {
                return;
            }

            // 年の最大数値制限チェック
            $current_year = date('Y');
            $this->doFunc(
                ["{$disp_name}(年)", $keyname, $current_year],
                ['MAX_CHECK']
            );
            // 上のチェックでエラーある場合、中断する。
            if (isset($this->arrErr[$keyname])) {
                return;
            }
        }

        // XXX createParam() が二重に呼ばれる問題を抱える
        $this->CHECK_DATE($value);
    }

    /**
     * 年月日に別れた2つの期間の妥当性をチェックする。
     *
     * @param array $value
     *      [0] = 表示名1
     *      [1] = 表示名2
     *      [2] = 判定対象配列キー (開始年)
     *      [3] = 判定対象配列キー (開始月)
     *      [4] = 判定対象配列キー (開始日)
     *      [5] = 判定対象配列キー (終了年)
     *      [6] = 判定対象配列キー (終了月)
     *      [7] = 判定対象配列キー (終了日)
     *
     * @return void
     */
    public function CHECK_SET_TERM($value)
    {
        $disp_name1 = $value[0];
        $disp_name2 = $value[1];
        $keyname1 = $value[2];
        $keyname2 = $value[5];

        // 期間指定
        if (isset($this->arrErr[$keyname1]) || isset($this->arrErr[$keyname2])) {
            return;
        }

        $this->createParam($value, range(2, 7));

        $start_year = $this->arrParam[$value[2]];
        $start_month = $this->arrParam[$value[3]];
        $start_day = $this->arrParam[$value[4]];
        $end_year = $this->arrParam[$value[5]];
        $end_month = $this->arrParam[$value[6]];
        $end_day = $this->arrParam[$value[7]];
        if ((strlen($start_year) > 0 || strlen($start_month) > 0 || strlen($start_day) > 0)
            && !checkdate((int) $start_month, (int) $start_day, (int) $start_year)
        ) {
            $this->arrErr[$keyname1] =
                "※ {$disp_name1}を正しく指定してください。<br />";
        }
        if ((strlen($end_year) > 0 || strlen($end_month) > 0 || strlen($end_day) > 0)
            && !checkdate((int) $end_month, (int) $end_day, (int) $end_year)
        ) {
            $this->arrErr[$keyname2] =
                "※ {$disp_name2}を正しく指定してください。<br />";
        }
        if ((strlen($start_year) > 0 && strlen($start_month) > 0 && strlen($start_day) > 0)
            && (strlen($end_year) > 0 || strlen($end_month) > 0 || strlen($end_day) > 0)
        ) {
            $date1 = sprintf('%d%02d%02d000000', $start_year, $start_month, $start_day);
            $date2 = sprintf('%d%02d%02d235959', $end_year, $end_month, $end_day);

            if ((!isset($this->arrErr[$keyname1]) && !isset($this->arrErr[$keyname2])) && $date1 > $date2) {
                $this->arrErr[$keyname1] =
                    "※ {$disp_name1}と{$disp_name2}の期間指定が不正です。<br />";
            }
        }
    }

    /**
     * 年月日時分秒に別れた2つの期間の妥当性をチェックする。
     *
     * @param array $value
     *      [0]  = 表示名1
     *      [1]  = 表示名2
     *      [2]  = 判定対象配列キー (開始年)
     *      [3]  = 判定対象配列キー (開始月)
     *      [4]  = 判定対象配列キー (開始日)
     *      [5]  = 判定対象配列キー (開始時)
     *      [6]  = 判定対象配列キー (開始分)
     *      [7]  = 判定対象配列キー (開始秒)
     *      [8]  = 判定対象配列キー (終了年)
     *      [9]  = 判定対象配列キー (終了月)
     *      [10] = 判定対象配列キー (終了日)
     *      [11] = 判定対象配列キー (終了時)
     *      [12] = 判定対象配列キー (終了分)
     *      [13] = 判定対象配列キー (終了秒)
     *
     * @return void
     */
    public function CHECK_SET_TERM2($value)
    {
        $disp_name1 = $value[0];
        $disp_name2 = $value[1];
        $keyname1 = $value[2];
        $keyname2 = $value[8];

        // 期間指定
        if (isset($this->arrErr[$keyname1]) || isset($this->arrErr[$keyname2])) {
            return;
        }

        $this->createParam($value, range(2, 13));

        $start_year = $this->arrParam[$value[2]];
        $start_month = $this->arrParam[$value[3]];
        $start_day = $this->arrParam[$value[4]];
        $start_hour = $this->arrParam[$value[5]];
        $start_minute = $this->arrParam[$value[6]];
        $start_second = $this->arrParam[$value[7]];
        $end_year = $this->arrParam[$value[8]];
        $end_month = $this->arrParam[$value[9]];
        $end_day = $this->arrParam[$value[10]];
        $end_hour = $this->arrParam[$value[11]];
        $end_minute = $this->arrParam[$value[12]];
        $end_second = $this->arrParam[$value[13]];
        if ((strlen($start_year) > 0 || strlen($start_month) > 0 || strlen($start_day) > 0 || strlen($start_hour) > 0)
            && !checkdate((int) $start_month, (int) $start_day, (int) $start_year)
        ) {
            $this->arrErr[$keyname1] =
                "※ {$disp_name1}を正しく指定してください。<br />";
        }
        if ((strlen($end_year) > 0 || strlen($end_month) > 0 || strlen($end_day) > 0 || strlen($end_hour) > 0)
            && !checkdate((int) $end_month, (int) $end_day, (int) $end_year)
        ) {
            $this->arrErr[$keyname2] =
                "※ {$disp_name2}を正しく指定してください。<br />";
        }
        if ((strlen($start_year) > 0 && strlen($start_month) > 0 && strlen($start_day) > 0 && strlen($start_hour) > 0)
            && (strlen($end_year) > 0 || strlen($end_month) > 0 || strlen($end_day) > 0 || strlen($end_hour) > 0)
        ) {
            $date1 = sprintf(
                '%d%02d%02d%02d%02d%02d',
                $start_year,
                $start_month,
                $start_day,
                $start_hour,
                $start_minute,
                $start_second
            );
            $date2 = sprintf(
                '%d%02d%02d%02d%02d%02d',
                $end_year,
                $end_month,
                $end_day,
                $end_hour,
                $end_minute,
                $end_second
            );

            if ((!isset($this->arrErr[$keyname1]) && !isset($this->arrErr[$keyname2])) && $date1 > $date2) {
                $this->arrErr[$keyname1] =
                    "※ {$disp_name1}と{$disp_name2}の期間指定が不正です。<br />";
            }
            if ($date1 == $date2) {
                $this->arrErr[$keyname1] =
                    "※ {$disp_name1}と{$disp_name2}の期間指定が不正です。<br />";
            }
        }
    }

    /**
     * 年月に別れた2つの期間の妥当性をチェックする。
     *
     * @param array $value
     *      [0] = 判定対象配列キー (表示名1)
     *      [1] = 判定対象配列キー (表示名2)
     *      [2] = 判定対象配列キー (開始年)
     *      [3] = 判定対象配列キー (開始月)
     *      [4] = 判定対象配列キー (終了年)
     *      [5] = 判定対象配列キー (終了月)
     *
     * @return void
     */
    public function CHECK_SET_TERM3($value)
    {
        $disp_name1 = $value[0];
        $disp_name2 = $value[1];
        $keyname1 = $value[2];
        $keyname2 = $value[4];

        // 期間指定
        if (isset($this->arrErr[$keyname1]) || isset($this->arrErr[$keyname2])) {
            return;
        }

        $this->createParam($value, range(2, 5));

        $start_year = $this->arrParam[$value[2]];
        $start_month = $this->arrParam[$value[3]];
        $end_year = $this->arrParam[$value[4]];
        $end_month = $this->arrParam[$value[5]];
        if ((strlen($start_year) > 0 || strlen($start_month) > 0)
            && !checkdate((int) $start_month, 1, (int) $start_year)
        ) {
            $this->arrErr[$keyname1] =
                "※ {$disp_name1}を正しく指定してください。<br />";
        }
        if ((strlen($end_year) > 0 || strlen($end_month) > 0)
            && !checkdate((int) $end_month, 1, (int) $end_year)
        ) {
            $this->arrErr[$keyname2] =
                "※ {$disp_name2}を正しく指定してください。<br />";
        }
        if (strlen($start_year) > 0 && strlen($start_month) > 0 && (strlen($end_year) > 0 || strlen($end_month) > 0)) {
            $date1 = sprintf('%d%02d', $start_year, $start_month);
            $date2 = sprintf('%d%02d', $end_year, $end_month);

            if ((!isset($this->arrErr[$keyname1]) && !isset($this->arrErr[$keyname2])) && $date1 > $date2) {
                $this->arrErr[$keyname1] =
                    "※ {$disp_name1}と{$disp_name2}の期間指定が不正です。<br />";
            }
        }
    }

    // ディレクトリ存在チェック
    public function DIR_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        if (!is_dir($this->arrParam[$keyname])) {
            $this->arrErr[$keyname] = "※ 指定した{$disp_name}は存在しません。<br />";
        }
    }

    // ドメインチェック
    public function DOMAIN_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $input_var = $this->arrParam[$keyname];
        $pattern = "/^\.[^.]+\..+/i";
        if (strlen($input_var) > 0 && !preg_match($pattern, $input_var)) {
            $this->arrErr[$keyname] = "※ {$disp_name}の形式が不正です。<br />";
        }
    }

    /*　携帯メールアドレスの判定　 */
    //　メールアドレスを正規表現で判定する
    // value[0] = 表示名 value[1] = 判定対象配列キー
    public function MOBILE_EMAIL_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $objMobile = new SC_Helper_Mobile_Ex();
        $input_var = $this->arrParam[$keyname];
        if (strlen($input_var) > 0
            && !$objMobile->gfIsMobileMailAddress($input_var)
        ) {
            $this->arrErr[$keyname] = "※ {$disp_name}は携帯電話のものではありません。<br />";
        }
    }

    /**
     * メールアドレスが会員登録されているか調べる
     *
     * @param array $value value[0] = 表示名 value[1] = 判定対象配列キー
     *
     * @return void
     */
    public function CHECK_REGIST_CUSTOMER_EMAIL($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        $register_user_flg = SC_Helper_Customer_Ex::sfCheckRegisterUserFromEmail($this->arrParam[$keyname]);
        switch ($register_user_flg) {
            case 1:
                $this->arrErr[$keyname] = "※ すでに会員登録で使用されている{$disp_name}です。<br />";
                break;
            case 2:
                $this->arrErr[$keyname] = "※ 退会から一定期間の間は、同じ{$disp_name}を使用することはできません。<br />";
                break;
            default:
                break;
        }
    }

    /**
     * 禁止文字列のチェック
     *
     * @param array $value
     *      [0] = 表示名
     *      [1] = 判定対象配列キー
     *      [2] = 入力を禁止する文字列(配列)
     *
     * @example $objErr->doFunc(array('URL', 'contents', $arrReviewDenyURL), array('PROHIBITED_STR_CHECK'));
     */
    public function PROHIBITED_STR_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];
        $arrProhibitedStr = $value[2];

        if (isset($this->arrErr[$keyname]) || empty($this->arrParam[$keyname])) {
            return;
        }

        $this->createParam($value);

        $targetStr = $this->arrParam[$keyname];
        $prohibitedStr = str_replace(['|', '/'], ['\|', '\/'], $arrProhibitedStr);

        $pattern = '/'.implode('|', $prohibitedStr).'/i';
        if (preg_match_all($pattern, $targetStr, $matches)) {
            $this->arrErr[$keyname] = "※ {$disp_name}は入力できません。<br />";
        }
    }

    /**
     * パラメーターとして適切な文字列かチェックする.
     *
     * @param  array $value [0] => 表示名, [1] => 判定対象配列キー
     *
     * @return void
     */
    public function EVAL_CHECK($value)
    {
        $disp_name = $value[0];
        $keyname = $value[1];

        if (isset($this->arrErr[$keyname])) {
            return;
        }

        $this->createParam($value);

        if ($this->evalCheck($this->arrParam[$keyname]) === false) {
            $this->arrErr[$keyname] = "※ {$disp_name} の形式が不正です。<br />";
        }
    }

    /**
     * パラメーターとして適切な文字列かチェックする.(サブルーチン)
     *
     * 下記を満たす場合を真とする。
     * ・PHPコードとして評価可能であること。
     * ・評価した結果がスカラデータ(定数に指定できる値)であること。
     * 本メソッドの利用や改訂にあたっては、eval 関数の危険性を意識する必要がある。
     *
     * @param string $value 評価する文字列
     *
     * @return bool パラメーターとして適切な文字列か
     */
    public function evalCheck($value)
    {
        if ($value === '' || $value === null) {
            return true;
        }

        return @eval('return is_scalar('.$value.');');
    }

    /**
     * 未定義の $this->arrParam に空要素を代入する.
     *
     * @param  array $value 配列
     * @param int[] $arrKeyNameIndexes 配列のうち判定対象配列キーの位置
     *
     * @return void
     */
    public function createParam($value, $arrKeyNameIndexes = [1])
    {
        foreach ($value as $index => $key) {
            // 判定対象配列キー以外は処理しない。
            if (!in_array($index, $arrKeyNameIndexes)) {
                continue;
            }
            if (get_debug_type($key) !== 'string') {
                trigger_error('$key='.var_export($key, true).var_export($index, true), E_USER_ERROR);
            }
            if (preg_match('/[^a-z0-9_]/i', $key)) {
                trigger_error("判定対象配列キーに使用不可文字を含む: {$key}", E_USER_ERROR);
            }
            if (!isset($this->arrParam[$key])) {
                $this->arrParam[$key] = '';
            } elseif (is_int($this->arrParam[$key])) {
                $this->arrParam[$key] = (string) $this->arrParam[$key];
            }
            if (!is_array($this->arrParam[$key])
                && strlen($this->arrParam[$key]) > 0
                && (
                    preg_match('/^[[:alnum:]\-\_]*[\.\/\\\\]*\.\.(\/|\\\\)/', $this->arrParam[$key])
                    || !preg_match('/\A[^\x00-\x08\x0b\x0c\x0e-\x1f\x7f]+\z/u', $this->arrParam[$key])
                )
            ) {
                $this->arrErr[$value[1]] = '※ '.$value[0].'に禁止された記号の並びまたは制御文字が入っています。<br />';
            }
        }
    }

    /**
     * 値が数字だけかどうかチェックする
     *
     * @param  string  $string チェックする文字列
     *
     * @return bool 値が10進数の数値表現以外の場合 true
     */
    public function numelicCheck($string)
    {
        /*
         * XXX 10進数の数値表現か否かを調べたいだけだが,
         * ctype_digit() は文字列以外 false を返す.
         * string ではなく int 型の数値が入る場合がある.
         */
        $string = (string) $string;

        return strlen($string) > 0 && !ctype_digit($string);
    }

    // 都道府県マスタに存在する値かチェック
    public function PREF_CHECK($value)
    {
        $disp = $value[0];
        $key = $value[1];

        $pref_id = $this->arrParam[$key];

        // 未入力の場合はエラーにしない(EXIST_CHECKを使用すること)
        if (strlen($pref_id ?? '') === 0) {
            return;
        }

        $this->createParam($value);

        $arrPref = (new SC_DB_MasterData_Ex())->getMasterData('mtb_pref');
        if (!isset($arrPref[$pref_id])) {
            $this->arrErr[$key] = '※ '.$disp.'が不正な値です。<br />';
        }
    }
}
