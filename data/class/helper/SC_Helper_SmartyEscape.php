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
 * Smarty の自動エスケープ (escape_html) を有効にしたときの, 既存テンプレートとの互換変換.
 *
 * prefilter としてコンパイル対象のソースだけを変換する. 保存済みのテンプレートは書き換えない.
 * 変換するのは出力タグ (変数・式を直接出力するタグ) だけで, 条件式・代入・関数の引数は変更しない.
 *
 * 処理の流れ:
 *   transform()    テンプレートのソースを走査し, タグごとに transformTag() を呼ぶ.
 *                  Smarty コメントと {literal} の中は変更しない. script 要素の中にいるかを追跡する (C8 のため).
 *   transformTag() タグを parse() で分解し, 規則を上から順に試す. 最初に当たった規則で nofilter を補う.
 *                  どの規則にも当たらない出力タグは変更せず, Smarty の自動エスケープに任せる.
 *
 * 規則 (番号は PR の説明と共通. 詳細は各 rule メソッドのコメント):
 *   C1  `h` / html モードの `escape` を含む既知の modifier 列  → nofilter (二重エスケープを避ける)
 *   C2  画面出力の View だけを自動エスケープの対象にする      → SC_Display / SC_View 側で扱う
 *   C3  `nl2br` / `nl2br_html` で終わる出力                   → nofilter (従来どおり)
 *   C4  `$arrErr…` と, エラーメッセージ系の名前の変数         → |h_allow_br nofilter (エスケープして <br> だけ戻す)
 *   C5  固定の属性文字列や HTML を返す modifier で終わる出力   → nofilter
 *   C6  HTML/JS 断片や JSON を入れる慣習の変数                → nofilter (従来どおり)
 *   C7  自動エスケープと重ねると壊れる `escape` のモード       → nofilter
 *   C8  script 要素の中の modifier なしの出力                 → |script_escape|escape_js nofilter (JS の文字列用にエスケープ)
 *   C9  html モードの `escape` を常に効かせる                 → createExtension() (Smarty のコンパイラ側で扱う)
 *   C10 `wordwrap` の区切りに `<br>` を指定した出力            → |wordwrap:幅:"\n"|h|nl2br nofilter
 *
 * 従来の保護 (既定の modifier の script_escape) は自動エスケープと併用する. nofilter を付けると既定の modifier も外れるため,
 * 規則で nofilter を補う出力には `|script_escape` を明示する (上の表の nofilter はすべて `|script_escape nofilter`).
 *
 * 設計の前提: 見落とした書き方があっても, 従来より弱い出力にならないようにする.
 *   - `escape` は Smarty 5.4 以降, 自動エスケープを有効にすると html モードが何もしない式になる. これを createExtension() で
 *     従来どおり常にエスケープするようにしている. タグの種類や式の中の位置に関係なく, `escape` の値は従来と同じになる.
 *   - そのため, 規則が当たった出力 (nofilter を補う) は従来と同じ値, 当たらなかった出力は従来の値を HTML エスケープした値になる.
 *     見落としは二重エスケープ (表示の崩れ) になり, 生出力にはならない.
 *
 * `h` / html モードの `escape` を含む出力は C1 だけで判定する. それ以外は $arrRules の順に試し, 最初に当たった規則だけを使う.
 */
class SC_Helper_SmartyEscape
{
    // ------------------------------------------------------------------
    // 設定
    // ------------------------------------------------------------------

    /** 互換規則のバージョン. 規則を変えたら上げる (compile_id に反映される). */
    public const VERSION = '18';

    /** C4 で使う modifier 名 */
    public const MODIFIER_ERROR = 'h_allow_br';

    /** C8 で使う modifier 名 */
    public const MODIFIER_SCRIPT = 'escape_js';

    /**
     * 規則を試す順. 先に当たった規則だけを使う (C1 は `h` を含む出力だけに使うため含めない).
     * 各メソッドは [書き換えた式, 補う modifier] を返す. 対象外なら null.
     */
    protected static $arrRules = [
        'ruleC10WordwrapBr',
        'ruleC3Nl2br',
        'ruleC5FixedOutput',
        'ruleC7EscapeMode',
        'ruleC4ArrErr',
        'ruleC6RawVariable',
        'ruleC8InScript',
        'ruleC4ErrorName',
    ];

    /**
     * `h` の後に続いてよい modifier 列 (C1).
     * nl2br_html は `h` の後では `<br />` を足すだけになる (エスケープ後の文字列にタグが無いため).
     */
    public static $arrAllowedAfterH = [
        [],
        ['nl2br'],
        ['nl2br_html'],
        ['default'],
        ['nl2br', 'default'],
        ['nl2br_html', 'default'],
    ];

    /**
     * nofilter を補う, 固定の属性文字列や HTML を返す modifier (C5).
     * sfGetChecked / sfGetEnabled は固定の属性文字列 (`checked="checked"` / ` disabled="disabled"`) か空文字だけを返す.
     * それ以外は既存の拡張が登録する, 価格表示の HTML を返す modifier.
     */
    public static $arrFixedOutputModifiers = [
        'sfGetChecked',
        'sfGetEnabled',
        'sfViewPriceTax',
        'sfViewPriceTax2',
        'sfViewPriceTax3',
        'sfViewPriceTax4',
        'sfViewPriceTax5',
        'dispCurrency',
    ];

    /**
     * `escape` で終わる出力を従来どおりにするモードと, そのときに補う modifier (C7).
     * html は C1 で扱う. hex は出力が `%xx` のみで自動エスケープの影響がない.
     * hexentity / decentity は出力が実体参照なので二重エスケープを避ける.
     * javascript は出力先 (script 要素か属性か) をコンパイル時に判別できないため従来どおりとする.
     */
    public static $arrRawEscapeModes = [
        'hexentity' => '',
        'decentity' => '',
        'javascript' => '',
    ];

    /**
     * C4 の対象にするエラーメッセージ系の変数名 (式の先頭の変数名に対して判定する).
     * `<br>` 以外はエスケープするため, 名前の判定が外れても `<br>` 以外のタグが文字列で表示されるだけで済む.
     */
    public static $errorVariablePattern = '/err|msg|mess/i';

    /** HTML/JS 断片や JSON を入れる慣習の変数 (C6). 添字・プロパティなしの出力だけが対象 */
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
        // 既存の拡張 (決済モジュール等) で, 決済代行会社が返した支払画面へのフォームの HTML を入れている変数
        'redirect_html',
        // 既存の拡張 (決済モジュール等) で, PHP で組み立てた JSON を入れている変数
        'arrDeliv',
        'json_jaccs_payment_charge_data',
        'json_paidy',
        'json_periodic_purchase_status',
        'locale_error_message',
        'strForeignCardOrdersJSON',
        'tpl_jsonJaccsPayment',
    ];

    /** C8 の対象にする script 要素の type 属性 (小文字. 空文字は type 属性なし) */
    public static $arrScriptTypes = ['', 'text/javascript', 'application/javascript', 'module'];

    /** modifier なしと同じに扱う modifier 列 (C6 / C8). `default` は値を変えず, 代替値を出すだけのため */
    public static $arrPlainModifiers = [[], ['default']];

    private const SCRIPT_NONE = 0;
    private const SCRIPT_OPEN_TAG = 1;
    private const SCRIPT_IN = 2;
    private const SCRIPT_OTHER = 3;

    /** @var string 左の区切り記号 */
    protected $left;

    /** @var string 右の区切り記号 */
    protected $right;

    /** @var int script 要素の判定状態 (SCRIPT_*). C8 で使う */
    protected $scriptState = self::SCRIPT_NONE;

    /** @var string 閉じていない script の開始タグの文字列. C8 で使う */
    protected $scriptOpenTag = '';

    public function __construct($left = '<!--{', $right = '}-->')
    {
        $this->left = $left;
        $this->right = $right;
    }

    // ------------------------------------------------------------------
    // 入口: テンプレートのソースの走査
    // ------------------------------------------------------------------

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

    // ------------------------------------------------------------------
    // タグ単位の変換
    // ------------------------------------------------------------------

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
        $tag = $this->parse($inner);
        if ($tag === null) {
            // 出力タグでない (関数タグ・条件式など), または最上位に演算子がある式は変更しない
            return $inner;
        }
        if (preg_match('/\b(nofilter|assign\s*=)/', $tag['attrs'])) {
            // nofilter が書かれた出力と変数への代入は変更しない
            return $inner;
        }
        $tag['inScript'] = $inScript;
        $tag['escapedAt'] = $this->escapedAt($tag);
        if ($tag['escapedAt'] !== null) {
            // `h` / html モードの `escape` を含む出力は C1 だけで判定する
            $result = $this->ruleC1Escaped($tag);
        } else {
            $result = null;
            foreach (static::$arrRules as $rule) {
                $result = $this->$rule($tag);
                if ($result !== null) {
                    break;
                }
            }
        }
        if ($result === null) {
            // どの規則にも当たらない出力は Smarty の自動エスケープに任せる
            return $inner;
        }

        return $this->build($tag, $result[0], $result[1]);
    }

    /**
     * 規則の結果からタグの中身を組み立てる. 式の後ろに modifier と script_escape を足し, 属性の後ろに nofilter を足す.
     *
     * @param array $tag parse() の結果
     * @param string $expr 書き換えた式
     * @param string $modifiers 補う modifier (`|script_escape|escape_js` 等. 無ければ空文字). script_escape が無ければ最後に補う
     *
     * @return string
     */
    protected function build(array $tag, $expr, $modifiers)
    {
        $body = rtrim($expr);
        $space = substr($expr, strlen($body)); // 式と属性の間の空白はそのまま残す
        // nofilter を付けると既定の modifier (script_escape) も外れるので, 従来どおり最後に掛ける (規則が先に掛けていれば足さない)
        if (!str_contains($modifiers, '|script_escape')) {
            $modifiers .= '|script_escape';
        }

        return $body.$modifiers.$space.$tag['attrs'].' nofilter'.$tag['tail'];
    }

    // ------------------------------------------------------------------
    // 規則. 引数は parse() の結果. 戻り値は [書き換えた式, 補う modifier], 対象外なら null
    // ------------------------------------------------------------------

    /**
     * C1: `h` または html モードの `escape` を含む既知の modifier 列 (`h`, `escape`, `default|h`, `h|nl2br`, `h|default` 等)
     * に nofilter を補う. 自動エスケープと重ねると二重エスケープになるため.
     * その後に許可リスト外の modifier が続く出力は対象外 (変換せず自動エスケープのまま. 二重エスケープで表示が崩れる
     * ことはあっても生出力にはならない).
     * 後ろの `default` の代替値は `h` / `escape` を通らずに出力されるため, `(代替値|h)` に書き換えて保護する.
     *
     * @return array{0: string, 1: string}|null
     */
    protected function ruleC1Escaped(array $tag)
    {
        $after = array_slice($tag['names'], $tag['escapedAt'] + 1);
        if (!in_array($after, static::$arrAllowedAfterH, true)) {
            return null;
        }

        return [$this->protectDefaultArguments($tag, $tag['escapedAt']), ''];
    }

    /**
     * 値を HTML エスケープする modifier (`h`, html モードの `escape`) が最初に現れる位置を返す (C1).
     * `escape` は第 1 引数が無いか, html / force のリテラルのものだけを数える (`escape:'url'` や `escape:$mode` は数えない).
     *
     * @param array $tag parse() の結果
     *
     * @return int|null modifier の番号. 無ければ null
     */
    protected function escapedAt(array $tag)
    {
        foreach ($tag['names'] as $i => $name) {
            if ($name === 'h') {
                return $i;
            }
            if ($name === 'escape' && in_array($this->escapeMode($this->segment($tag, $i)), ['html', 'force'], true)) {
                return $i;
            }
        }

        return null;
    }

    /**
     * C10: `wordwrap` の区切りに `<br>` のリテラルを指定した出力 (`|wordwrap:幅:'<br />'[:真偽]` だけのもの) は,
     * 改行で折り返してから `h` でエスケープし, `nl2br` で改行を `<br />` にする (`|wordwrap:幅:"\n"[:真偽]|h|nl2br nofilter`).
     * 区切りの `<br>` を保ったまま, 値はエスケープされる. 値に元からある改行も `<br />` になる.
     *
     * @return array{0: string, 1: string}|null
     */
    protected function ruleC10WordwrapBr(array $tag)
    {
        if ($tag['names'] !== ['wordwrap']) {
            return null;
        }
        $args = $this->wordwrapBrArguments($this->segment($tag, 0));
        if ($args === null) {
            return null;
        }
        $expr = substr($tag['expr'], 0, $tag['modifiers'][0]['start']).'|wordwrap:'.implode(':', $args).'|h|nl2br';

        return [$expr, ''];
    }

    /**
     * C3: `h` を伴わない `nl2br` / `nl2br_html` で終わる出力は従来どおり (script_escape のみ) とする.
     * 決済モジュールが決済情報 (arrOther) に入れるリンク等の HTML, 商品説明の HTML を保持するため.
     * 決済情報の項目名 (`$item.name`) も決済モジュールが HTML を入れるため, 本体の完了画面のテンプレートで
     * `|script_escape nofilter` を明示している.
     *
     * @return array{0: string, 1: string}|null
     */
    protected function ruleC3Nl2br(array $tag)
    {
        if (!in_array($tag['last'], ['nl2br', 'nl2br_html'], true)) {
            return null;
        }

        return [$tag['expr'], ''];
    }

    /**
     * C5: 固定の属性文字列や HTML を返す modifier (sfGetChecked 等) で終わる出力に nofilter を補う.
     *
     * @return array{0: string, 1: string}|null
     */
    protected function ruleC5FixedOutput(array $tag)
    {
        if ($tag['last'] === null || !in_array($tag['last'], static::$arrFixedOutputModifiers, true)) {
            return null;
        }

        return [$tag['expr'], ''];
    }

    /**
     * C7: `escape:'hexentity'` 等, 自動エスケープと重ねると壊れる `escape` のモードで終わる出力を従来どおりとする.
     * モードごとの扱いは $arrRawEscapeModes を参照.
     *
     * html モードの `escape` は C1 で扱う.
     *
     * @return array{0: string, 1: string}|null
     */
    protected function ruleC7EscapeMode(array $tag)
    {
        if ($tag['last'] !== 'escape') {
            return null;
        }
        $segment = $this->segment($tag, count($tag['modifiers']) - 1);
        $mode = $this->escapeMode($segment);
        if ($mode === null || !isset(static::$arrRawEscapeModes[$mode])) {
            return null;
        }

        return [$tag['expr'], static::$arrRawEscapeModes[$mode]];
    }

    /**
     * C4: modifier なしの `$arrErr…` は, エスケープした後に `<br>` だけを戻す modifier (h_allow_br) を通す.
     * エラーメッセージは `<br>` で改行する慣習のため.
     *
     * @return array{0: string, 1: string}|null
     */
    protected function ruleC4ArrErr(array $tag)
    {
        if ($tag['names'] !== [] || $tag['variable'] !== 'arrErr') {
            return null;
        }

        return [$tag['expr'], '|'.static::MODIFIER_ERROR];
    }

    /**
     * C6: HTML/JS 断片や JSON を入れる慣習の変数 ($arrRawVariables) を, 添字・プロパティなしで
     * modifier なし (または `default` だけ) で出力する場合は従来どおり (script_escape のみ) とする.
     * 添字・プロパティ付きは対象外 (本体で配列として使う名前 (arrDeliv 等) の要素を素の出力にしない).
     * `default` の代替値はエスケープされずに出力されるため, リテラルの場合だけ対象にする.
     *
     * @return array{0: string, 1: string}|null
     */
    protected function ruleC6RawVariable(array $tag)
    {
        if (!$tag['isBareVariable'] || !in_array($tag['variable'], static::$arrRawVariables, true)) {
            return null;
        }
        if (!in_array($tag['names'], static::$arrPlainModifiers, true)) {
            return null;
        }
        if ($tag['names'] !== [] && !$this->hasLiteralArguments($this->segment($tag, 0))) {
            return null;
        }

        return [$tag['expr'], ''];
    }

    /**
     * C8: JavaScript の script 要素の中にある, modifier なし (または `default` だけ) の出力は,
     * HTML ではなく JavaScript の文字列用にエスケープする (|script_escape|escape_js).
     * 従来の script_escape を escape_js の前に掛ける. 後ろに掛けると `<` が `\x3c` になった後で, JavaScript が文字列に戻した
     * ときに従来は置き換えていたタグが残るため (値を innerHTML 等に入れる既存のテンプレートのため).
     * HTML 用のエスケープでは JS の文字列が `&amp;` 等に化けるため.
     * script 要素の開始・終了は同じテンプレートのソースの中で判定する (scanScript). 判定できない場合は通常の自動エスケープになる.
     * escape_js は `<` `>` `&` 引用符等も `\x3c` 形式にするため, 判定を誤って HTML の中に出力しても HTML として解釈されない.
     * JavaScript で値を HTML として DOM に入れる箇所 (`.html()` / `innerHTML` 等) は HTML としてのエスケープにならないため,
     * テンプレートで `|h` を付ける (C1 により `|h nofilter` になる).
     *
     * @return array{0: string, 1: string}|null
     */
    protected function ruleC8InScript(array $tag)
    {
        if (!$tag['inScript'] || !in_array($tag['names'], static::$arrPlainModifiers, true)) {
            return null;
        }

        return [$tag['expr'], '|script_escape|'.static::MODIFIER_SCRIPT];
    }

    /**
     * C4: modifier なしの, エラーメッセージ系の名前 ($errorVariablePattern) の変数も `$arrErr…` と同じ扱いにする.
     * C6 の変数は C6 を, script 要素の中は C8 を優先するため, この規則は最後に試す.
     *
     * @return array{0: string, 1: string}|null
     */
    protected function ruleC4ErrorName(array $tag)
    {
        if ($tag['names'] !== [] || $tag['variable'] === null || !preg_match(static::$errorVariablePattern, $tag['variable'])) {
            return null;
        }

        return [$tag['expr'], '|'.static::MODIFIER_ERROR];
    }

    // ------------------------------------------------------------------
    // 式の解析
    // ------------------------------------------------------------------

    /**
     * 出力タグを分解する.
     *
     * 出力タグでない (関数タグ・条件式など) 場合と, 最上位に演算子や空白区切りの式があって modifier が
     * 式全体に掛からない場合は null を返す (変換しない).
     *
     * 返す配列:
     *   expr           式 (先頭の空白と modifier を含む)
     *   modifiers      最上位の modifier の名前 (name) と $inner 内の位置 (start / end). `|smarty:nodefaults` は除く
     *   names          modifiers の名前の一覧
     *   last           最後の modifier の名前. 無ければ null
     *   variable       式の先頭の変数名 (`$arrErr.x` なら arrErr). 変数で始まらなければ null
     *   isBareVariable 式が添字・プロパティ・modifier の無い変数 1 つだけか
     *   attrs          式の後ろの属性 (nocache / nofilter / assign=…). 末尾の空白は含まない
     *   tail           タグ末尾の空白
     *
     * @param string $inner 区切り記号を除いたタグの中身
     *
     * @return array|null
     */
    public function parse($inner)
    {
        $len = strlen($inner);
        $lead = $len - strlen(ltrim($inner));
        if ($lead === $len) {
            return null;
        }
        $first = $inner[$lead];
        if (!($first === '$' || $first === '"' || $first === "'" || $first === '(' || ctype_digit($first))) {
            return null;
        }
        $depth = 0; // 括弧 () [] の深さ. 最上位 (0) だけを見る
        $quote = null; // 引用符の中は読み飛ばす
        $modifiers = [];
        $exprEnd = $len;
        for ($i = $lead; $i < $len; $i++) {
            $c = $inner[$i];
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
                continue;
            }
            if ($c === '(' || $c === '[') {
                $depth++;
                continue;
            }
            if ($c === ')' || $c === ']') {
                $depth = max(0, $depth - 1);
                continue;
            }
            if ($depth > 0) {
                continue;
            }
            if ($c === '-' && ($inner[$i + 1] ?? '') === '>') {
                // オブジェクトのプロパティ参照 (->)
                $i++;
                continue;
            }
            if (str_contains('=?+*/%<>!&^~-', $c) || ($c === ':' && $modifiers === [])) {
                // 最上位に演算子 (代入・比較・三項・算術・論理) がある. modifier が式全体を覆わないため変換しない
                return null;
            }
            if ($c === '|') {
                if (($inner[$i + 1] ?? '') === '|') {
                    // 論理和
                    return null;
                }
                if (preg_match('/\G\s*(@?[A-Za-z_]\w*)/', $inner, $m, 0, $i + 1)) {
                    $modifiers[] = ['name' => ltrim($m[1], '@'), 'start' => $i, 'end' => null];
                }
                continue;
            }
            if (ctype_space($c)) {
                // 空白の後ろに置けるのは属性 (nofilter / nocache / assign=) だけ. それ以外は空白区切りの式なので変換しない
                if (preg_match('/\G\s+(nofilter|nocache|assign\s*=)/', $inner, $m, 0, $i) || trim(substr($inner, $i)) === '') {
                    $exprEnd = $i;
                    break;
                }

                return null;
            }
        }
        if ($quote !== null) {
            // 引用符が閉じていない
            return null;
        }
        // modifier の終端は次の modifier の開始 (最後は式の終わり)
        for ($k = 0, $n = count($modifiers); $k < $n; $k++) {
            $modifiers[$k]['end'] = $k + 1 < $n ? $modifiers[$k + 1]['start'] : $exprEnd;
        }
        // |smarty:nodefaults は後方互換処理 (lower_compatibility_smarty) で除去済みの想定だが, 残っていても判定から外す
        $modifiers = array_values(array_filter($modifiers, function ($m) { return $m['name'] !== 'smarty'; }));
        $names = array_column($modifiers, 'name');
        $expr = substr($inner, 0, $exprEnd);
        $base = $modifiers === [] ? $expr : substr($expr, 0, $modifiers[0]['start']);
        $attrs = substr($inner, $exprEnd);
        $attrsBody = rtrim($attrs);

        return [
            'expr' => $expr,
            'modifiers' => $modifiers,
            'names' => $names,
            'last' => $names === [] ? null : $names[count($names) - 1],
            'variable' => preg_match('/^\s*\$(\w+)/', $expr, $m) ? $m[1] : null,
            'isBareVariable' => (bool) preg_match('/^\s*\$\w+\s*$/', $base),
            'attrs' => $attrsBody,
            'tail' => substr($attrs, strlen($attrsBody)),
        ];
    }

    /**
     * modifier の文字列 (`|name:arg1:arg2`) を式から取り出す.
     *
     * @param array $tag parse() の結果
     * @param int $index modifiers の添字
     *
     * @return string
     */
    protected function segment(array $tag, $index)
    {
        $m = $tag['modifiers'][$index];

        return substr($tag['expr'], $m['start'], $m['end'] - $m['start']);
    }

    /**
     * C1: `h` より後ろの `default` の各引数を `(引数|h)` に置き換えた式を返す.
     *
     * @param array $tag parse() の結果
     * @param int $hIndex `h` の modifiers での添字
     *
     * @return string
     */
    protected function protectDefaultArguments(array $tag, $hIndex)
    {
        $expr = $tag['expr'];
        // 後ろから置換して, 前にある modifier の位置をずらさない
        for ($i = count($tag['modifiers']) - 1; $i > $hIndex; $i--) {
            $m = $tag['modifiers'][$i];
            if ($m['name'] !== 'default') {
                continue;
            }
            $args = $this->splitArguments($this->segment($tag, $i));
            if (count($args) <= 1) {
                continue;
            }
            $rebuilt = array_shift($args);
            foreach ($args as $arg) {
                $rebuilt .= ':('.trim($arg).'|h)';
            }
            $expr = substr($expr, 0, $m['start']).$rebuilt.substr($expr, $m['end']);
        }

        return $expr;
    }

    /**
     * C10: `|wordwrap:幅:'<br />'[:真偽]` の形なら, 区切りを改行に替えた引数 [幅, "\n", 真偽] を返す. 対象外なら null.
     * 幅は数値, 区切りは `<br>` / `<br />` のリテラル, 第 3 引数は真偽値・数値のリテラルに限る.
     *
     * @param string $segment modifier の文字列
     *
     * @return array<int, string>|null
     */
    protected function wordwrapBrArguments($segment)
    {
        $args = array_map('trim', $this->splitArguments($segment));
        if (count($args) < 3 || count($args) > 4) {
            return null;
        }
        if (!preg_match('/^\d+$/', $args[1]) || !preg_match('/^([\'"])<br\s*\/?>\1$/i', $args[2])) {
            return null;
        }
        if (isset($args[3]) && !preg_match('/^(?:true|false|0|1)$/i', $args[3])) {
            return null;
        }
        $args[2] = '"\n"';

        return array_slice($args, 1);
    }

    /**
     * modifier の引数がすべてリテラルか (C6).
     * 数値, シングルクォートの文字列, 変数の埋め込み (`$` / `` ` ``) を含まないダブルクォートの文字列をリテラルとする.
     *
     * @param string $segment modifier の文字列
     *
     * @return bool
     */
    protected function hasLiteralArguments($segment)
    {
        $args = $this->splitArguments($segment);
        foreach (array_slice($args, 1) as $arg) {
            if (!preg_match('/^\s*(?:-?\d+(?:\.\d+)?|\'(?:[^\'\\\\]|\\\\.)*\'|"[^"$`\\\\]*")\s*$/', $arg)) {
                return false;
            }
        }

        return true;
    }

    /**
     * `escape` modifier の第 1 引数 (モード) を小文字で返す. 引数なしは html. リテラル以外は null.
     *
     * @param string $segment modifier の文字列
     *
     * @return string|null
     */
    protected function escapeMode($segment)
    {
        $args = $this->splitArguments($segment);
        if (count($args) < 2) {
            return 'html';
        }
        $mode = trim($args[1]);
        if (preg_match('/^([\'"])(\w+)\1$/', $mode, $m) || preg_match('/^(\w+)$/', $mode, $m)) {
            // Smarty はモード名の大文字・小文字を区別する (`escape:'HTML'` は html モードにならない) ので, そのまま返す
            return end($m);
        }

        return null;
    }

    /**
     * `|name:arg1:arg2` を ['|name', 'arg1', 'arg2'] に分ける (引用符・括弧の中の `:` は区切らない).
     *
     * @param string $segment modifier の文字列
     *
     * @return array<int, string>
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

    // ------------------------------------------------------------------
    // C9: html モードの escape を常に効かせる
    // ------------------------------------------------------------------

    /**
     * C9: html モードの `escape` を, 自動エスケープを有効にしても従来どおりエスケープさせる Smarty の拡張を返す.
     *
     * Smarty 5.4 以降は, 自動エスケープを有効にすると html モードの `escape` (引数なし / 'html') を何もしない式に
     * コンパイルする (smarty-php/smarty#1188). 出力時に自動エスケープが掛かる場合はそれで良いが, nofilter を付けた出力や,
     * 変数に代入したり {include} や関数の引数に渡したりしてから nofilter 等で出力する場合, 同じ式の中の別の modifier が
     * 自動エスケープを止める場合 (`escape:'url'` 等) は, 値がエスケープされずに出力されてしまう.
     * テンプレートの書き方ごとに対処すると漏れが生出力になるため, コンパイラの側で html モードを常にエスケープさせる.
     * これで `escape` の値は, タグの種類や式の中の位置に関係なく従来と同じになる.
     * 自動エスケープされる出力では二重エスケープになるので, 出力タグの `escape` は C1 で `h` と同じように nofilter を補う.
     *
     * SC_View::enableAutoEscape() が, Smarty の標準の拡張より先に引かれるように登録する.
     * Smarty 3 / 4 の `escape` は常にエスケープするので, この拡張は要らない.
     *
     * @return Smarty\Extension\ExtensionInterface
     */
    public static function createExtension()
    {
        return new class extends Smarty\Extension\Base {
            public function getModifierCompiler(string $modifier): ?Smarty\Compile\Modifier\ModifierCompilerInterface
            {
                if ($modifier !== 'escape') {
                    return null;
                }

                return new class extends Smarty\Compile\Modifier\EscapeModifierCompiler {
                    public function compile($params, Smarty\Compiler\Template $compiler)
                    {
                        try {
                            $mode = $this->literal_compiler_param($params, 1, 'html');
                        } catch (Smarty\Exception $e) {
                            // 第 1 引数がリテラルでない (`escape:$mode`). Smarty が実行時の escape 関数を呼ぶのでそのままで良い
                            return parent::compile($params, $compiler);
                        }
                        if ($mode !== 'html' && $mode !== 'force') {
                            return parent::compile($params, $compiler);
                        }
                        // 文字コード・double_encode がリテラルでなくても (`escape:'html':$charset`) エスケープされるように,
                        // 親クラスには渡さず, 引数の式をそのまま使って htmlspecialchars() を組み立てる.
                        // (親クラスはリテラルでない引数があると実行時の escape 関数に任せるが, そちらは 'force' を知らない)
                        $default = var_export(Smarty\Smarty::$_CHARSET, true);
                        $charset = isset($params[2]) ? '(('.$params[2].') ?: '.$default.')' : $default;
                        $doubleEncode = isset($params[3]) ? '(bool) ('.$params[3].')' : 'true';

                        return 'htmlspecialchars((string) '.$params[0].', ENT_QUOTES, '.$charset.', '.$doubleEncode.')';
                    }
                };
            }
        };
    }

    // ------------------------------------------------------------------
    // modifier の実装
    // ------------------------------------------------------------------

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

    /**
     * C8: JavaScript の文字列リテラル用にエスケープする.
     *
     * 文字列から抜け出す文字に加えて `<` `>` `&` 引用符 `` ` `` `$` を `\xHH` にする.
     * JavaScript の文字列の中では元の値に戻り, HTML の中に出力されても HTML として解釈されない.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function escapeJs($value)
    {
        if (is_scalar($value) && !is_string($value) || $value === null) {
            return $value;
        }
        $value = (string) $value;
        static $map = [
            '\\' => '\\\\',
            "'" => '\\x27',
            '"' => '\\x22',
            '`' => '\\x60',
            '$' => '\\x24',
            '<' => '\\x3c',
            '>' => '\\x3e',
            '&' => '\\x26',
            "\r" => '\\r',
            "\n" => '\\n',
            "\u{2028}" => '\\u2028',
            "\u{2029}" => '\\u2029',
        ];

        return strtr($value, $map);
    }
}
