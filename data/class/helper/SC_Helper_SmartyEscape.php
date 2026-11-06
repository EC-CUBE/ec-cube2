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
 * Smarty の自動エスケープ (escape_html) を有効にしたときの、既存テンプレートとの互換変換.
 *
 * prefilter としてコンパイル対象のソースだけを変換する. 保存済みのテンプレートは書き換えない.
 * 変換するのは出力タグ (変数・式を直接出力するタグ) だけで, 条件式・代入・関数引数は変更しない.
 *
 * - C1: `h` を含む既知の modifier 列に nofilter を補う. `h` の後の `default` の代替値は `h` で保護する.
 * - C3: `h` を伴わない `nl2br` / `nl2br_html` で終わる出力は従来どおり (`|script_escape nofilter`) とする.
 *       決済モジュールが決済情報 (arrOther) に入れるリンク等の HTML, 商品説明の HTML を保持するため.
 * - C4: modifier なしの `$arrErr…` と, エラーメッセージ系の名前 (err / msg / mess を含む) の変数は,
 *       エスケープ後に `<br>` だけを戻す modifier を通す. C6 の変数は C6 を, script 要素の中のエラーメッセージ系の名前は C8 を優先する.
 * - C5: 固定の属性文字列や HTML を返す modifier (sfGetChecked 等) で終わる出力に nofilter を補う.
 * - C6: HTML/JS 断片を入れる慣習の変数 (本体と既存の拡張で使われているもの) の modifier なし出力は
 *       従来どおり (`|script_escape nofilter`) とする.
 * - C7: `escape:'hexentity'` 等, 自動エスケープと重ねると壊れる `escape` のモードで終わる出力を従来どおりとする.
 * - C8: JavaScript の script 要素の中にある, modifier なし (`default` のみを含む) の出力は,
 *       HTML ではなく JavaScript 用にエスケープする (`|escape:'javascript'|script_escape nofilter`).
 *       script 要素の開始・終了は同じテンプレートのソースの中で判定する. 判定できない場合は通常の自動エスケープになる.
 *
 * C2 (画面出力の View だけを自動エスケープ対象にする) は SC_Display / SC_View 側で扱う.
 */
class SC_Helper_SmartyEscape
{
    /** 互換規則のバージョン. 規則を変えたら上げる (compile_id に反映される). */
    public const VERSION = '4';

    /** C4 で使う modifier 名 */
    public const MODIFIER_ERROR = 'h_allow_br';

    /** `h` の後に続いてよい modifier 列 (C1) */
    public static $arrAllowedAfterH = [
        [],
        ['nl2br'],
        ['default'],
        ['nl2br', 'default'],
    ];

    /**
     * nofilter を補う, 固定の属性文字列や HTML を返す modifier (C5).
     * sfGetChecked の本体テンプレートでの利用は if 文に置き換え済み. 既存のプラグイン・カスタムテンプレートのために残す.
     * それ以外は既存の拡張が登録する, 価格表示の HTML を返す modifier.
     */
    public static $arrFixedOutputModifiers = [
        'sfGetChecked',
        'sfViewPriceTax',
        'sfViewPriceTax2',
        'sfViewPriceTax3',
        'sfViewPriceTax4',
        'sfViewPriceTax5',
        'dispCurrency',
    ];

    /**
     * `escape` で終わる出力のモード別の扱い (C7).
     * html は Smarty が自動エスケープを省略するため対象外. hex は出力が `%xx` のみで自動エスケープの影響がない.
     * hexentity / decentity は出力が実体参照なので二重エスケープを避ける.
     * javascript は出力先 (script 要素か属性か) をコンパイル時に判別できないため従来どおりとする.
     */
    public static $arrEscapeModeSuffix = [
        'hexentity' => ' nofilter',
        'decentity' => ' nofilter',
        'javascript' => '|script_escape nofilter',
    ];

    /**
     * C4 の対象にするエラーメッセージ系の変数名 (最上位の変数名に対して判定する).
     * `<br>` 以外はエスケープするため, 名前の判定が外れても `<br>` 以外のタグが文字列で表示されるだけで済む.
     */
    public static $errorVariablePattern = '/err|msg|mess/i';

    /** HTML/JS 断片を入れる慣習の変数 (C6) */
    public static $arrRawVariables = [
        'tpl_onload',
        'tpl_javascript',
        'tpl_strnavi',
        'tpl_error',
        'GLOBAL_ERR',
        'tpl_bread_crumbs',
        'tpl_json_category_id',
        'tpl_now_dir',
        'json_payment',
        'json_customer_order_status',
        // 既存の拡張 (決済モジュール等) で, PHP で組み立てた JS を入れている変数
        'tpl_payment_onload',
        'tpl_onload_org',
        'sln_onload',
        'plg_pg_mulpay_onload',
        'paid_tpl_onload',
        'token_js',
        'paidy_js',
        'paygent_token_connect_url',
        'paygent_token_js_url',
    ];

    /** C8 の対象にする script 要素の type 属性 (小文字. 空文字は type 属性なし) */
    public static $arrScriptTypes = ['', 'text/javascript', 'application/javascript', 'module'];

    /** C8 で modifier なしと同じに扱う modifier 列 */
    public static $arrScriptAllowedModifiers = [[], ['default']];

    /** @var string */
    protected $left;

    /** @var int script 要素の判定状態 (SCRIPT_*) */
    protected $scriptState = self::SCRIPT_NONE;

    /** @var string 閉じていない開始タグの文字列 */
    protected $scriptOpenTag = '';

    private const SCRIPT_NONE = 0;
    private const SCRIPT_OPEN_TAG = 1;
    private const SCRIPT_IN = 2;
    private const SCRIPT_OTHER = 3;

    /** @var string */
    protected $right;

    public function __construct($left = '<!--{', $right = '}-->')
    {
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * prefilter.
     *
     * @param string $source
     *
     * @return string
     */
    public function prefilter($source, $template = null)
    {
        return $this->transform($source);
    }

    /**
     * テンプレートソースを変換する.
     *
     * @param string $source
     *
     * @return string
     */
    public function transform($source)
    {
        $L = $this->left;
        $R = $this->right;
        $out = '';
        $pos = 0;
        $len = strlen($source);
        $literal = false;
        $this->scriptState = self::SCRIPT_NONE;
        $this->scriptOpenTag = '';
        while (($start = strpos($source, $L, $pos)) !== false) {
            $text = substr($source, $pos, $start - $pos);
            $this->scanScript($text);
            $out .= $text;
            $innerStart = $start + strlen($L);
            // Smarty コメントは閉じ記号までそのまま
            if (substr($source, $innerStart, 1) === '*') {
                $end = strpos($source, '*'.$R, $innerStart);
                $end = $end === false ? $len : $end + 1 + strlen($R);
                $out .= substr($source, $start, $end - $start);
                $pos = $end;
                continue;
            }
            $end = $this->findTagEnd($source, $innerStart);
            if ($end === false) {
                $out .= substr($source, $start);
                $pos = $len;
                break;
            }
            $inner = substr($source, $innerStart, $end - $innerStart);
            $trimmed = trim($inner);
            if ($literal) {
                if ($trimmed === '/literal') {
                    $literal = false;
                }
                $out .= $L.$inner.$R;
            } elseif ($trimmed === 'literal') {
                $literal = true;
                $out .= $L.$inner.$R;
            } else {
                $out .= $L.$this->transformTag($inner, $this->scriptState === self::SCRIPT_IN).$R;
            }
            $pos = $end + strlen($R);
        }
        $out .= substr($source, $pos);

        return $out;
    }

    /**
     * テンプレートタグの外の文字列から, script 要素の中にいるかどうかを更新する (C8).
     * 開始タグの属性値の中 (`<script src="<!--{$x}-->">` 等) は script 要素の中として扱わない.
     *
     * @param string $text
     */
    protected function scanScript($text)
    {
        $offset = 0;
        $len = strlen($text);
        while ($offset < $len) {
            if ($this->scriptState === self::SCRIPT_OPEN_TAG) {
                $gt = strpos($text, '>', $offset);
                if ($gt === false) {
                    $this->scriptOpenTag .= substr($text, $offset);

                    return;
                }
                $this->scriptOpenTag .= substr($text, $offset, $gt - $offset);
                $type = '';
                if (preg_match('/\btype\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $this->scriptOpenTag, $m)) {
                    // 一致するのは 3 つのグループのどれか 1 つだけ
                    $type = strtolower(trim(implode('', array_slice($m, 1))));
                }
                $this->scriptState = in_array($type, static::$arrScriptTypes, true) ? self::SCRIPT_IN : self::SCRIPT_OTHER;
                $this->scriptOpenTag = '';
                $offset = $gt + 1;
            } elseif ($this->scriptState === self::SCRIPT_NONE) {
                if (!preg_match('/<script(?=[\s>\/]|$)/i', $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
                    return;
                }
                $this->scriptState = self::SCRIPT_OPEN_TAG;
                $this->scriptOpenTag = '';
                $offset = $m[0][1] + strlen($m[0][0]);
            } else {
                $close = stripos($text, '</script', $offset);
                if ($close === false) {
                    return;
                }
                $this->scriptState = self::SCRIPT_NONE;
                $offset = $close + strlen('</script');
            }
        }
    }

    /**
     * 引用符の外にある閉じ記号の位置を返す.
     */
    protected function findTagEnd($source, $offset)
    {
        $R = $this->right;
        $len = strlen($source);
        $quote = null;
        for ($i = $offset; $i < $len; $i++) {
            $c = $source[$i];
            if ($quote !== null) {
                if ($c === '\\') {
                    $i++;
                } elseif ($c === $quote) {
                    $quote = null;
                }
            } elseif ($c === '"' || $c === "'") {
                $quote = $c;
            } elseif ($c === $R[0] && substr_compare($source, $R, $i, strlen($R)) === 0) {
                return $i;
            }
        }

        return false;
    }

    /**
     * 1 つのタグの中身を変換する.
     *
     * @param string $inner 区切り記号を除いたタグの中身
     * @param bool $inScript JavaScript の script 要素の中のタグか (C8)
     *
     * @return string
     */
    public function transformTag($inner, $inScript = false)
    {
        $p = $this->parse($inner);
        if ($p === null) {
            return $inner;
        }
        $names = array_map(function ($m) { return $m['name']; }, $p['modifiers']);
        // |smarty:nodefaults は後方互換処理で除去済みの想定だが, 残っていても判定から外す
        $names = array_values(array_filter($names, function ($n) { return $n !== 'smarty'; }));
        $last = end($names);
        $expr = $p['expr'];
        $suffix = '';

        $hPos = array_search('h', $names, true);
        if ($hPos !== false) {
            // C1
            $after = array_slice($names, $hPos + 1);
            if (!in_array($after, static::$arrAllowedAfterH, true)) {
                return $inner;
            }
            $expr = $this->protectDefaultArguments($expr, $p['modifiers'], $hPos);
            $suffix = ' nofilter';
        } elseif ($last === 'nl2br' || $last === 'nl2br_html') {
            // C3
            $suffix = '|script_escape nofilter';
        } elseif ($last !== false && in_array($last, static::$arrFixedOutputModifiers, true)) {
            // C5
            $suffix = ' nofilter';
        } elseif ($last === 'escape' && ($mode = $this->getEscapeMode($expr, end($p['modifiers']))) !== null && isset(static::$arrEscapeModeSuffix[$mode])) {
            // C7
            $suffix = static::$arrEscapeModeSuffix[$mode];
        } elseif ($names === [] && preg_match('/^\$arrErr\b/', ltrim($expr))) {
            // C4
            $suffix = '|'.static::MODIFIER_ERROR.' nofilter';
        } elseif ($names === [] && preg_match('/^\$(\w+)/', ltrim($expr), $m) && in_array($m[1], static::$arrRawVariables, true)) {
            // C6
            $suffix = '|script_escape nofilter';
        } elseif ($inScript && in_array($names, static::$arrScriptAllowedModifiers, true)) {
            // C8
            $suffix = "|escape:'javascript'|script_escape nofilter";
        } elseif ($names === [] && preg_match('/^\$(\w+)/', ltrim($expr), $m) && preg_match(static::$errorVariablePattern, $m[1])) {
            // C4 (エラーメッセージ系の変数名)
            $suffix = '|'.static::MODIFIER_ERROR.' nofilter';
        } else {
            return $inner;
        }

        $exprBody = rtrim($expr);
        $trail = substr($expr, strlen($exprBody));
        if ($suffix[0] === '|') {
            [$mod, $attr] = explode(' ', $suffix, 2);

            return $exprBody.$mod.$trail.$p['attrs'].' '.$attr.$p['tail'];
        }

        return $exprBody.$trail.$p['attrs'].$suffix.$p['tail'];
    }

    /**
     * 出力タグを解析する. 出力タグでない, または変換対象外なら null.
     *
     * @return array{expr: string, attrs: string, tail: string, modifiers: array<int, array{name: string, start: int, end: int}>}|null
     */
    public function parse($inner)
    {
        $lead = strlen($inner) - strlen(ltrim($inner));
        $s = ltrim($inner);
        if ($s === '') {
            return null;
        }
        $first = $s[0];
        if (!($first === '$' || $first === '"' || $first === "'" || $first === '(' || ctype_digit($first))) {
            return null;
        }
        $len = strlen($s);
        $depth = 0;
        $quote = null;
        $modifiers = [];
        $exprEnd = $len;
        for ($i = 0; $i < $len; $i++) {
            $c = $s[$i];
            if ($quote !== null) {
                if ($c === '\\') {
                    $i++;
                } elseif ($c === $quote) {
                    $quote = null;
                }
                continue;
            }
            if ($c === '"' || $c === "'") {
                $quote = $c;
            } elseif ($c === '(' || $c === '[') {
                $depth++;
            } elseif ($c === ')' || $c === ']') {
                $depth = max(0, $depth - 1);
            } elseif ($depth === 0) {
                // modifier は直前の値にだけ掛かる. 最上位に演算子 (代入・比較・三項・算術・論理) や
                // 空白で区切られた式があると, modifier が式全体を覆わないため変換しない.
                if ($c === '-' && ($s[$i + 1] ?? '') === '>') {
                    // オブジェクトのプロパティ参照 (->)
                    $i++;
                    continue;
                }
                if (str_contains('=?+*/%<>!&^~-', $c) || ($c === ':' && $modifiers === [])) {
                    return null;
                }
                if ($c === '|') {
                    if (($s[$i + 1] ?? '') === '|') {
                        return null;
                    }
                    if (preg_match('/\G\s*(@?[A-Za-z_]\w*)/', $s, $m, 0, $i + 1)) {
                        $modifiers[] = ['name' => ltrim($m[1], '@'), 'start' => $i, 'end' => null];
                    }
                } elseif (ctype_space($c)) {
                    if (preg_match('/\G\s+(nofilter|nocache|assign\s*=)/', $s, $m, 0, $i) || trim(substr($s, $i)) === '') {
                        $exprEnd = $i;
                        break;
                    }

                    return null;
                }
            }
        }
        if ($quote !== null) {
            return null;
        }
        $attrs = substr($s, $exprEnd);
        if (preg_match('/\b(nofilter|assign\s*=)/', $attrs)) {
            return null;
        }
        // modifier の終端
        for ($k = 0, $n = count($modifiers); $k < $n; $k++) {
            $modifiers[$k]['end'] = $k + 1 < $n ? $modifiers[$k + 1]['start'] : $exprEnd;
        }
        $attrsBody = rtrim($attrs);
        $tail = substr($attrs, strlen($attrsBody));

        return [
            'expr' => substr($inner, 0, $lead).substr($s, 0, $exprEnd),
            'attrs' => $attrsBody,
            'tail' => $tail,
            'modifiers' => array_map(function ($m) use ($lead) {
                return ['name' => $m['name'], 'start' => $m['start'] + $lead, 'end' => $m['end'] + $lead];
            }, $modifiers),
        ];
    }

    /**
     * `h` より後ろの `default` の各引数を `(引数|h)` に置き換える.
     */
    protected function protectDefaultArguments($expr, array $modifiers, $hPos)
    {
        // 後ろから置換して位置をずらさない
        $hIndex = null;
        $count = 0;
        foreach ($modifiers as $i => $m) {
            if ($m['name'] === 'smarty') {
                continue;
            }
            if ($count === $hPos) {
                $hIndex = $i;
                break;
            }
            $count++;
        }
        for ($i = count($modifiers) - 1; $i > $hIndex; $i--) {
            $m = $modifiers[$i];
            if ($m['name'] !== 'default') {
                continue;
            }
            $segment = substr($expr, $m['start'], $m['end'] - $m['start']);
            $args = $this->splitArguments($segment);
            if (count($args) <= 1) {
                continue;
            }
            $rebuilt = array_shift($args);
            foreach ($args as $a) {
                $t = trim($a);
                $rebuilt .= ':('.$t.'|h)';
            }
            $expr = substr($expr, 0, $m['start']).$rebuilt.substr($expr, $m['end']);
        }

        return $expr;
    }

    /**
     * `escape` modifier の第 1 引数 (モード) を返す. 引数なしは html. リテラル以外は null.
     *
     * @param array{name: string, start: int, end: int} $modifier
     */
    protected function getEscapeMode($expr, array $modifier)
    {
        $args = $this->splitArguments(substr($expr, $modifier['start'], $modifier['end'] - $modifier['start']));
        if (count($args) < 2) {
            return 'html';
        }
        $mode = trim($args[1]);
        if (preg_match('/^([\'"])(\w+)\1$/', $mode, $m) || preg_match('/^(\w+)$/', $mode, $m)) {
            return strtolower(end($m));
        }

        return null;
    }

    /**
     * `|name:arg1:arg2` を ['|name', 'arg1', 'arg2'] に分ける (引用符・括弧の中の `:` は区切らない).
     */
    protected function splitArguments($segment)
    {
        $parts = [];
        $buf = '';
        $depth = 0;
        $quote = null;
        $len = strlen($segment);
        for ($i = 0; $i < $len; $i++) {
            $c = $segment[$i];
            if ($quote !== null) {
                $buf .= $c;
                if ($c === '\\' && $i + 1 < $len) {
                    $buf .= $segment[++$i];
                } elseif ($c === $quote) {
                    $quote = null;
                }
                continue;
            }
            if ($c === '"' || $c === "'") {
                $quote = $c;
            } elseif ($c === '(' || $c === '[') {
                $depth++;
            } elseif ($c === ')' || $c === ']') {
                $depth--;
            } elseif ($c === ':' && $depth === 0) {
                $parts[] = $buf;
                $buf = '';
                continue;
            }
            $buf .= $c;
        }
        $parts[] = $buf;

        return $parts;
    }

    /**
     * C4: HTML エスケープした後に `<br>` / `<br />` だけを戻す.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function escapeAllowBr($value)
    {
        $escaped = htmlspecialchars((string) $value, ENT_QUOTES, CHAR_CODE);

        return preg_replace('#&lt;br\s*/?&gt;#i', '<br />', $escaped);
    }
}
