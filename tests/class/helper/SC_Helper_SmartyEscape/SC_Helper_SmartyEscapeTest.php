<?php

class SC_Helper_SmartyEscapeTest extends Common_TestCase
{
    /** @var SC_Helper_SmartyEscape */
    protected $objHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objHelper = new SC_Helper_SmartyEscape();
    }

    public function transformTagProvider()
    {
        return [
            // C1
            ['$v|h', '$v|h|script_escape nofilter'],
            ['$v|default:$f|h', '$v|default:$f|h|script_escape nofilter'],
            ['$v|h|nl2br', '$v|h|nl2br|script_escape nofilter'],
            ['$v|h|nl2br_html', '$v|h|nl2br_html|script_escape nofilter'],
            ['$v|h|nl2br_html|default:$f', '$v|h|nl2br_html|default:($f|h)|script_escape nofilter'],
            ['$v|wordwrap:20|h|nl2br', '$v|wordwrap:20|h|nl2br|script_escape nofilter'],
            ['$v|h|default:$f', '$v|h|default:($f|h)|script_escape nofilter'],
            ['$v|h|default:"a:b"', '$v|h|default:("a:b"|h)|script_escape nofilter'],
            ['$v|h|nl2br|default:$x.y', '$v|h|nl2br|default:($x.y|h)|script_escape nofilter'],
            ['$v|h nocache', '$v|h|script_escape nocache nofilter'],
            ["\$v|escape:'javascript'|h", "\$v|escape:'javascript'|h|script_escape nofilter"],
            // h の後に未知の modifier が続くものは対象外 (自動エスケープを維持)
            ['$v|h|cat:"#x"', '$v|h|cat:"#x"'],
            ['$v|h|sfDispDBDate', '$v|h|sfDispDBDate'],
            // C10
            ["\$v|wordwrap:75:'<br/>':true", '$v|wordwrap:75:"\\n":true|h|nl2br|script_escape nofilter'],
            ['$v|wordwrap:10:"<br />"', '$v|wordwrap:10:"\\n"|h|nl2br|script_escape nofilter'],
            ["\$v|wordwrap:10:'<BR>':false", '$v|wordwrap:10:"\\n":false|h|nl2br|script_escape nofilter'],
            // 区切りが <br> のリテラルでないもの, 幅・第 3 引数がリテラルでないものは対象外 (自動エスケープを維持)
            ["\$v|wordwrap:10:'<b>':true", "\$v|wordwrap:10:'<b>':true"],
            ["\$v|wordwrap:10:'<br onload=x>'", "\$v|wordwrap:10:'<br onload=x>'"],
            ['$v|wordwrap:10:$sep:true', '$v|wordwrap:10:$sep:true'],
            ["\$v|wordwrap:\$n:'<br/>'", "\$v|wordwrap:\$n:'<br/>'"],
            ["\$v|wordwrap:10:'<br/>':\$cut", "\$v|wordwrap:10:'<br/>':\$cut"],
            ['$v|wordwrap:10', '$v|wordwrap:10'],
            // C3
            ['$v|nl2br', '$v|nl2br|script_escape nofilter'],
            ['$v|nl2br_html', '$v|nl2br_html|script_escape nofilter'],
            // C4
            ['$arrErr.name', '$arrErr.name|h_allow_br|script_escape nofilter'],
            ['$arrErr[$key]', '$arrErr[$key]|h_allow_br|script_escape nofilter'],
            ['$arrErr[$key]|sfGetErrorColor', '$arrErr[$key]|sfGetErrorColor'],
            // C4 (エラーメッセージ系の変数名)
            ['$err', '$err|h_allow_br|script_escape nofilter'],
            ['$errBirth', '$errBirth|h_allow_br|script_escape nofilter'],
            ['$plg_pg_mulpay_msg', '$plg_pg_mulpay_msg|h_allow_br|script_escape nofilter'],
            ['$tpl_exam_error', '$tpl_exam_error|h_allow_br|script_escape nofilter'],
            ['$tpl_message', '$tpl_message|h_allow_br|script_escape nofilter'],
            ['$item.err_msg', '$item.err_msg'],
            ['$err|h', '$err|h|script_escape nofilter'],
            ['$err|nl2br', '$err|nl2br|script_escape nofilter'],
            ['$err ? $a : $b', '$err ? $a : $b'],
            // C6 は名前による C4 より優先
            ['$tpl_error', '$tpl_error|script_escape nofilter'],
            ['$GLOBAL_ERR', '$GLOBAL_ERR|script_escape nofilter'],
            // C5
            ['$v|sfGetChecked:1', '$v|sfGetChecked:1|script_escape nofilter'],
            ['$v|sfGetEnabled', '$v|sfGetEnabled|script_escape nofilter'],
            ['$v|sfViewPriceTax2', '$v|sfViewPriceTax2|script_escape nofilter'],
            ['$v|default:0|sfViewPriceTax3:1', '$v|default:0|sfViewPriceTax3:1|script_escape nofilter'],
            ['$v|dispCurrency', '$v|dispCurrency|script_escape nofilter'],
            ['$v|h|dispCurrency', '$v|h|dispCurrency'],
            // C6
            ['$tpl_onload', '$tpl_onload|script_escape nofilter'],
            ['$redirect_html', '$redirect_html|script_escape nofilter'],
            ['$redirect_html|h', '$redirect_html|h|script_escape nofilter'],
            ['$tpl_payment_onload', '$tpl_payment_onload|script_escape nofilter'],
            ['$paygent_token_js_url', '$paygent_token_js_url|script_escape nofilter'],
            ['$tpl_onload_x', '$tpl_onload_x'],
            ['$json_paidy', '$json_paidy|script_escape nofilter'],
            ['$json_paidy|default:"no_data"', '$json_paidy|default:"no_data"|script_escape nofilter'],
            ['$locale_error_message', '$locale_error_message|script_escape nofilter'],
            ['$arrDeliv', '$arrDeliv|script_escape nofilter'],
            // C6 は添字・プロパティ付きを対象にしない
            ['$arrDeliv[cnt].deliv_id', '$arrDeliv[cnt].deliv_id'],
            ['$arrDeliv.1', '$arrDeliv.1'],
            ['$tpl_onload|escape', '$tpl_onload|escape|script_escape nofilter'],
            // C6 の default は代替値がリテラルの場合だけ (代替値はエスケープされずに出力されるため)
            ["\$json_paidy|default:'a'", "\$json_paidy|default:'a'|script_escape nofilter"],
            ['$json_paidy|default:0', '$json_paidy|default:0|script_escape nofilter'],
            ['$json_paidy|default:$x', '$json_paidy|default:$x'],
            ['$json_paidy|default:"a$x"', '$json_paidy|default:"a$x"'],
            ['$json_paidy|default:"a`$x`"', '$json_paidy|default:"a`$x`"'],
            ['$json_paidy|default:$smarty.get.x', '$json_paidy|default:$smarty.get.x'],
            // C7
            ["\$m|escape:'hexentity'", "\$m|escape:'hexentity'|script_escape nofilter"],
            ["\$m|escape:'javascript'", "\$m|escape:'javascript'|script_escape nofilter"],
            ['$m|escape:"hex"', '$m|escape:"hex"'],
            ['$m|escape', '$m|escape|script_escape nofilter'],
            // C1 (html モードの escape は h と同じ扱い. Smarty の拡張で常にエスケープされるため, 二重エスケープを避ける)
            ['$v|escape|nl2br', '$v|escape|nl2br|script_escape nofilter'],
            ['$v|escape:"html"|nl2br', '$v|escape:"html"|nl2br|script_escape nofilter'],
            ['$v|escape:"html":"UTF-8"|nl2br_html', '$v|escape:"html":"UTF-8"|nl2br_html|script_escape nofilter'],
            ['$v|escape:"force"|nl2br', '$v|escape:"force"|nl2br|script_escape nofilter'],
            ['$v|@escape', '$v|@escape|script_escape nofilter'],
            ['$v|lower|escape', '$v|lower|escape|script_escape nofilter'],
            ['$v|escape|default:$f', '$v|escape|default:($f|h)|script_escape nofilter'],
            ['$v|escape:"javascript"|escape', '$v|escape:"javascript"|escape|script_escape nofilter'],
            ['$v|escape:"html":"UTF-8":false', '$v|escape:"html":"UTF-8":false|script_escape nofilter'],
            ["\$v|escape:'html':null:0", "\$v|escape:'html':null:0|script_escape nofilter"],
            ['$v|escape:"html":"UTF-8":true', '$v|escape:"html":"UTF-8":true|script_escape nofilter'],
            ['$v|escape:"html":"UTF-8":$flag', '$v|escape:"html":"UTF-8":$flag|script_escape nofilter'],
            // モード名は Smarty と同じく大文字・小文字を区別する (html モードでないものに nofilter を補わない)
            ['$v|escape:"HTML"', '$v|escape:"HTML"'],
            ['$v|escape:"Html"|default:$f', '$v|escape:"Html"|default:$f'],
            // C1 (escape の後に許可リスト外の modifier が続くものは変換しない. 二重エスケープになるが生出力にはならない)
            ['$v|escape|upper', '$v|escape|upper'],
            ['$v|escape|sfViewPriceTax', '$v|escape|sfViewPriceTax'],
            ['$v|escape|escape:"javascript"', '$v|escape|escape:"javascript"'],
            ['$v|escape|escape:"quotes"', '$v|escape|escape:"quotes"'],
            // html モード以外の escape は h と同じ扱いにしない (末尾の modifier の規則で判定する)
            ['$v|escape:"htmlall"|nl2br', '$v|escape:"htmlall"|nl2br|script_escape nofilter'],
            ['$v|escape:$mode|nl2br', '$v|escape:$mode|nl2br|script_escape nofilter'],
            ['$v|escape:$mode', '$v|escape:$mode'],
            // 括弧の中・modifier の引数の中・文字列の中の escape は見ない (最上位の modifier 列だけで判定する)
            ['($v|escape)|nl2br', '($v|escape)|nl2br|script_escape nofilter'],
            ['$v|cat:($w|escape)|nl2br', '$v|cat:($w|escape)|nl2br|script_escape nofilter'],
            ['$v|cat:"|escape"|nl2br', '$v|cat:"|escape"|nl2br|script_escape nofilter'],
            ['"`$v|escape`"|nl2br', '"`$v|escape`"|nl2br|script_escape nofilter'],
            ['"`$v|escape`"', '"`$v|escape`"'],
            ['count($v|escape)', 'count($v|escape)'],
            // nofilter / assign= が書かれた出力, 代入, 条件式, 関数の引数は変換しない (escape は Smarty の拡張で常にエスケープされる)
            ['$v|escape nofilter', '$v|escape nofilter'],
            ['$v|escape|nl2br nofilter', '$v|escape|nl2br nofilter'],
            ['$v|escape:"url" nofilter', '$v|escape:"url" nofilter'],
            ['$v|escape_js nofilter', '$v|escape_js nofilter'],
            ['$v|escape assign=w', '$v|escape assign=w'],
            ['assign var=w value=$v|escape', 'assign var=w value=$v|escape'],
            ['assign var=x value="`$v|escape`"', 'assign var=x value="`$v|escape`"'],
            ['append var=w value=$v|escape', 'append var=w value=$v|escape'],
            ['$w = $v|escape', '$w = $v|escape'],
            ['$rows[$i == 0] = $v|escape', '$rows[$i == 0] = $v|escape'],
            ['$w == $v|escape', '$w == $v|escape'],
            ['if $v|escape == "a"', 'if $v|escape == "a"'],
            ['include file="a.tpl" x=$v|escape', 'include file="a.tpl" x=$v|escape'],
            ['from_to|cat:"" from=$v|escape to=$v|escape', 'from_to|cat:"" from=$v|escape to=$v|escape'],
            ['foreach from=[$v|escape,$w|escape] item=x', 'foreach from=[$v|escape,$w|escape] item=x'],
            // 変換しないもの
            ['$v', '$v'],
            ['$v|h nofilter', '$v|h nofilter'],
            ['$v|h assign=foo', '$v|h assign=foo'],
            ['$x = $v|h', '$x = $v|h'],
            ['if $v|h == "a"', 'if $v|h == "a"'],
            ['"str|h"', '"str|h"'],
            ['include file="a.tpl"', 'include file="a.tpl"'],
            // modifier が式全体を覆わない (最上位に演算子がある) ものは変換しない
            ['$a ? $b : $c|h', '$a ? $b : $c|h'],
            ['$a?$b:$c|h', '$a?$b:$c|h'],
            ['$a + $b|h', '$a + $b|h'],
            ['$a-1|h', '$a-1|h'],
            ['$a|h . $b', '$a|h . $b'],
            ['$a||$b|h', '$a||$b|h'],
            ['$a == $b|h', '$a == $b|h'],
            ['!$v|h', '!$v|h'],
            ['$v|default:$a?1:2|h', '$v|default:$a?1:2|h'],
            ['$arrErr.x . $y', '$arrErr.x . $y'],
            ['$tpl_onload . $x', '$tpl_onload . $x'],
            // 括弧・添字・プロパティ参照の中は対象
            ['($a ? $b : $c)|h', '($a ? $b : $c)|h|script_escape nofilter'],
            ['$arr[$i+1]|h', '$arr[$i+1]|h|script_escape nofilter'],
            ['$o->p|h', '$o->p|h|script_escape nofilter'],
            ['$v|default:($a ? 1 : 2)|h', '$v|default:($a ? 1 : 2)|h|script_escape nofilter'],
        ];
    }

    /**
     * @dataProvider transformTagProvider
     */
    public function testTransformTag($input, $expected)
    {
        $this->assertSame($expected, $this->objHelper->transformTag($input));
    }

    public function testTransformSkipsCommentAndLiteral()
    {
        $source = "<!--{* <!--{\$v|h}--> *}-->\n<!--{literal}--><!--{\$v|h}--><!--{/literal}-->\n<!--{\$v|h}-->";
        $expected = "<!--{* <!--{\$v|h}--> *}-->\n<!--{literal}--><!--{\$v|h}--><!--{/literal}-->\n<!--{\$v|h|script_escape nofilter}-->";
        $this->assertSame($expected, $this->objHelper->transform($source));
    }

    public function testTransformIsIdempotent()
    {
        $source = '<!--{$v|h}--><!--{$arrErr.x}--><!--{$v|h|default:$f}--><!--{$tpl_onload}--><!--{$v|escape|nl2br}--><!--{$v|escape nofilter}-->'
            .'<!--{$v|escape assign=x}--><!--{assign var=y value=$v|escape}--><!--{($v|escape)|nl2br}-->';
        $once = $this->objHelper->transform($source);
        $this->assertSame($once, $this->objHelper->transform($once));
    }

    public function testTransformKeepsDelimiterInString()
    {
        $source = '<!--{$v|default:"}-->"|h}-->';
        $this->assertSame('<!--{$v|default:"}-->"|h|script_escape nofilter}-->', $this->objHelper->transform($source));
    }

    public function scriptProvider()
    {
        $js = '|script_escape|escape_js nofilter';

        return [
            '文字列の中' => ["<script>var a = '<!--{\$v}-->';</script>", "<script>var a = '<!--{\$v{$js}}-->';</script>"],
            'default' => ['<script>f(<!--{$v|default:1}-->);</script>', "<script>f(<!--{\$v|default:1{$js}}-->);</script>"],
            '属性付き・改行・大文字' => ["<SCRIPT type=\"text/javascript\">\nx = \"<!--{\$v}-->\";\n</SCRIPT>", "<SCRIPT type=\"text/javascript\">\nx = \"<!--{\$v{$js}}-->\";\n</SCRIPT>"],
            'src 属性の中は対象外' => ['<script src="<!--{$v}-->a.js"></script>', '<script src="<!--{$v}-->a.js"></script>'],
            '属性値の後の本文は対象' => ['<script src="<!--{$v}-->a.js">x="<!--{$w}-->";</script>', "<script src=\"<!--{\$v}-->a.js\">x=\"<!--{\$w{$js}}-->\";</script>"],
            'JS 以外の type は対象外' => ['<script type="text/template"><a title="<!--{$v}-->"></script>', '<script type="text/template"><a title="<!--{$v}-->"></script>'],
            'JSON-LD は対象外' => ['<script type="application/ld+json">{"a":"<!--{$v}-->"}</script>', '<script type="application/ld+json">{"a":"<!--{$v}-->"}</script>'],
            '閉じた後は対象外' => ['<script></script><!--{$v}-->', '<script></script><!--{$v}-->'],
            'scripts 等の別の要素は対象外' => ['<scripts><!--{$v}--></scripts>', '<scripts><!--{$v}--></scripts>'],
            'C6 を優先' => ['<script><!--{$tpl_onload}--></script>', '<script><!--{$tpl_onload|script_escape nofilter}--></script>'],
            'C6 の JSON 変数を優先' => ['<script>var a = <!--{$json_paidy|default:"no_data"}-->;</script>', '<script>var a = <!--{$json_paidy|default:"no_data"|script_escape nofilter}-->;</script>'],
            'C6 の default の代替値が変数なら C8' => ['<script>var a = <!--{$json_paidy|default:$x}-->;</script>', "<script>var a = <!--{\$json_paidy|default:\$x{$js}}-->;</script>"],
            'C6 の名前でも添字付きは C8' => ['<script><!--{$arrDeliv[cnt].deliv_id}--></script>', '<script><!--{$arrDeliv[cnt].deliv_id|script_escape|escape_js nofilter}--></script>'],
            'C1 を優先' => ["<script>a='<!--{\$v|h}-->';</script>", "<script>a='<!--{\$v|h|script_escape nofilter}-->';</script>"],
            'エラー系の名前より優先' => ["<script>a='<!--{\$err_msg}-->';</script>", "<script>a='<!--{\$err_msg{$js}}-->';</script>"],
            '他の modifier は対象外' => ["<script>a='<!--{\$v|upper}-->';</script>", "<script>a='<!--{\$v|upper}-->';</script>"],
            '演算子を含む式は対象外' => ['<script>a=<!--{$v+1}-->;</script>', '<script>a=<!--{$v+1}-->;</script>'],
            'literal の中の script' => ["<!--{literal}--><script><!--{/literal}-->a='<!--{\$v}-->';</script>", "<!--{literal}--><script><!--{/literal}-->a='<!--{\$v{$js}}-->';</script>"],
        ];
    }

    /**
     * @dataProvider scriptProvider
     */
    public function testTransformScript($source, $expected)
    {
        $this->assertSame($expected, $this->objHelper->transform($source));
    }

    public function testTransformScriptStateIsReset()
    {
        $this->objHelper->transform('<script>');
        $this->assertSame('<!--{$v}-->', $this->objHelper->transform('<!--{$v}-->'));
    }

    public function testEscapeAllowBr()
    {
        $this->assertSame('※ &lt;b&gt;A&amp;B&lt;/b&gt;<br />次<br />', SC_Helper_SmartyEscape::escapeAllowBr('※ <b>A&B</b><br>次<br />'));
    }

    public function renderProvider()
    {
        $v = "A&B <b>\"x\"</b> 'y'\n2";
        $esc = 'A&amp;B &lt;b&gt;&quot;x&quot;&lt;/b&gt; &#039;y&#039;';

        return [
            '変数' => ['<!--{$v}-->', ['v' => $v], $esc."\n2"],
            'h (二重にしない)' => ['<!--{$v|h}-->', ['v' => $v], $esc."\n2"],
            'h nofilter' => ['<!--{$v|h nofilter}-->', ['v' => $v], $esc."\n2"],
            'h|nl2br' => ['<!--{$v|h|nl2br}-->', ['v' => $v], $esc."<br />\n2"],
            'h|nl2br_html' => ['<!--{$v|h|nl2br_html}-->', ['v' => $v], $esc."<br />\n2"],
            'h|nl2br_html (行末のタグも文字列)' => ['<!--{$v|h|nl2br_html}-->', ['v' => "<p>\n2"], "&lt;p&gt;<br />\n2"],
            'default|h' => ['<!--{$v|default:$f|h}-->', ['v' => '', 'f' => '<i>'], '&lt;i&gt;'],
            'h|default の代替値を保護' => ['<!--{$v|h|default:$f}-->', ['v' => '', 'f' => '<i>'], '&lt;i&gt;'],
            'arrErr の br を保持' => ['<!--{$arrErr.x}-->', ['arrErr' => ['x' => '※ <b>A</b>が未入力です。<br />']], '※ &lt;b&gt;A&lt;/b&gt;が未入力です。<br />'],
            'エラー系の変数名の br を保持' => ['<!--{$plg_msg}-->', ['plg_msg' => '決済エラー<br>"x" <script>alert(1)</script>'], '決済エラー<br />&quot;x&quot; &lt;script&gt;alert(1)&lt;/script&gt;'],
            'wordwrap の <br> 区切り' => ['<!--{$v|wordwrap:4:"<br />":true}-->', ['v' => '<i>"&\'x'], "&lt;i&gt;&quot;<br />\n&amp;&#039;x"],
            'nl2br は従来どおり' => ['<!--{$v|nl2br}-->', ['v' => "<a href=\"/\">x</a>\n2"], "<a href=\"/\">x</a><br />\n2"],
            'tpl_onload は従来どおり' => ['<!--{$tpl_onload}-->', ['tpl_onload' => "fnOnLoad('a');"], "fnOnLoad('a');"],
            'sfGetChecked' => ['<!--{1|sfGetChecked:$v}-->', ['v' => '1'], 'checked="checked"'],
            'sfGetEnabled' => ['<!--{$v|sfGetEnabled}-->', ['v' => ''], ' disabled="disabled"'],
            'escape|nl2br' => ['<!--{$v|escape|nl2br}-->', ['v' => $v], $esc."<br />\n2"],
            'escape:html|nl2br_html' => ['<!--{$v|escape:"html"|nl2br_html}-->', ['v' => $v], $esc."<br />\n2"],
            'escape|nl2br nofilter' => ['<!--{$v|escape|nl2br nofilter}-->', ['v' => $v], $esc."<br />\n2"],
            'escape (二重にしない)' => ['<!--{$v|escape}-->', ['v' => $v], $esc."\n2"],
            'escape を代入して nofilter で出力' => ['<!--{$v|escape assign=x}--><!--{$x nofilter}-->', ['v' => $v], $esc."\n2"],
            'escape を assign で代入して nl2br で出力' => ['<!--{assign var=x value=$v|escape}--><!--{$x|nl2br}-->', ['v' => $v], $esc."<br />\n2"],
            // escape した値を代入して自動エスケープのまま出力すると二重エスケープになる (h と同じ. 生出力になるよりは安全側)
            'escape を代入して自動エスケープで出力' => ['<!--{$v|escape assign=x}--><!--{$x}-->', ['v' => '<i>'], '&amp;lt;i&amp;gt;'],
            '省略形の代入の escape' => ['<!--{$x = $v|escape}--><!--{$x|nl2br}-->', ['v' => $v], $esc."<br />\n2"],
            'append の escape' => ['<!--{append var=x value=$v|escape}--><!--{$x[0] nofilter}-->', ['v' => $v], $esc."\n2"],
            '埋め込み式の escape' => ['<!--{assign var=x value="`$v|escape`"}--><!--{$x|nl2br}-->', ['v' => $v], $esc."<br />\n2"],
            'include の引数の escape' => ['<!--{include file=$child x=$v|escape}-->', ['v' => $v, 'child' => 'string:<!--{$x|nl2br}-->'], $esc."<br />\n2"],
            '配列リテラルの中の escape' => ['<!--{foreach from=[$v|escape] item=x}--><!--{$x nofilter}--><!--{/foreach}-->', ['v' => $v], $esc."\n2"],
            '条件式の escape' => ['<!--{if $v|escape == "&lt;i&gt;"}-->EQ<!--{else}-->NE<!--{/if}-->', ['v' => '<i>'], 'EQ'],
            '添字に比較を含む代入の escape' => ['<!--{$rows[$i == 0] = $v|escape}--><!--{$rows[1] nofilter}-->', ['v' => $v, 'i' => 0, 'rows' => []], $esc."\n2"],
            '関数の引数の escape' => ['<!--{html_options options=$arr selected=$v|escape}-->', ['v' => 'a<b', 'arr' => ['a<b' => 'X', 'c' => 'Y']], "<option value=\"a&lt;b\" selected=\"selected\">X</option>\n<option value=\"c\">Y</option>\n"],
            // 同じ式の中の別の escape が Smarty の自動エスケープを止めても, html モードの escape は効く
            'escape:javascript|escape' => ['<!--{$v|escape:"javascript"|escape}-->', ['v' => '<details open ontoggle=alert(1)>'], '&lt;details open ontoggle=alert(1)&gt;'],
            'escape|escape:quotes' => ['<!--{$v|escape|escape:"quotes"}-->', ['v' => '<details open ontoggle=alert(1)>'], '&lt;details open ontoggle=alert(1)&gt;'],
            'escape|cat:(escape:url)' => ['<!--{$v|escape|cat:($s|escape:"url")}-->', ['v' => '<details open ontoggle=alert(1)>', 's' => 'a b'], '&lt;details open ontoggle=alert(1)&gt;a%20b'],
            // escape の後に許可リスト外の modifier が続くと二重エスケープになる (h と同じ)
            'escape|upper' => ['<!--{$v|escape|upper}-->', ['v' => '<i>'], '&amp;LT;I&amp;GT;'],
            'モードが変数の escape' => ['<!--{$v|escape:$mode}-->', ['v' => '<i>', 'mode' => 'html'], '&amp;lt;i&amp;gt;'],
            'escape|default の代替値を保護' => ['<!--{$v|escape|default:$f}-->', ['v' => '', 'f' => '<i>'], '&lt;i&gt;'],
            // script 要素の中: JS の文字列に戻したときに, 従来は置き換えていたタグが残らない
            'script 内の値を innerHTML に入れる場合' => ['<script>x.innerHTML="<!--{$v}-->";</script>', ['v' => '<img src=x onerror=alert(1)>'], '<script>x.innerHTML="#script escaped#";</script>'],
            // 文字コード・double_encode がリテラルでなくてもエスケープされる
            'escape の double_encode が変数' => ['<!--{$v|escape:"html":"UTF-8":$flag}-->', ['v' => '<i>&amp;', 'flag' => false], '&lt;i&gt;&amp;'],
            'escape の文字コードが変数' => ['<!--{$v|escape:"html":$cs}-->', ['v' => '<i>&amp;', 'cs' => 'UTF-8'], '&lt;i&gt;&amp;amp;'],
            'escape の文字コードが変数 (nofilter)' => ['<!--{$v|escape:"html":$cs nofilter}-->', ['v' => '<i>', 'cs' => null], '&lt;i&gt;'],
            'escape の引数が変数の値を代入' => ['<!--{assign var=x value=$v|escape:"html":"UTF-8":$flag}--><!--{$x nofilter}-->', ['v' => '<i>', 'flag' => true], '&lt;i&gt;'],
            'escape:force の引数が変数' => ['<!--{$v|escape:"force":"UTF-8":$flag}-->', ['v' => '<i>', 'flag' => true], '&lt;i&gt;'],
            'モード名の大文字は html モードではない' => ['<!--{$v|escape:"HTML"}-->', ['v' => '<i>'], '&lt;i&gt;'],
            'double_encode=false を維持' => ['<!--{$v|escape:"html":"UTF-8":false}-->', ['v' => 'A&amp;B <i>'], 'A&amp;B &lt;i&gt;'],
            '括弧の中の escape|nl2br' => ['<!--{($v|escape)|nl2br}-->', ['v' => $v], $esc."<br />\n2"],
            'modifier の引数の中の escape' => ['<!--{$v|cat:($w|escape)|nl2br}-->', ['v' => "<i>\n", 'w' => '<u>'], "<i><br />\n&lt;u&gt;"],
            // 従来の script_escape は自動エスケープと併用する (HTML エスケープでは防げない出力の保護を維持する)
            'javascript: の URL' => ['<a href="<!--{$v}-->">', ['v' => 'javascript:alert(1)'], '<a href="#script escaped#alert(1)">'],
            'h 付きの javascript: の URL' => ['<a href="<!--{$v|h}-->">', ['v' => 'JavaScript:alert(1)'], '<a href="#script escaped#alert(1)">'],
            '代入した javascript: の URL' => ['<!--{assign var=x value=$v}--><a href="<!--{$x}-->">', ['v' => 'javascript:alert(1)'], '<a href="#script escaped#alert(1)">'],
            'escape:quotes (Smarty が自動エスケープしないモード)' => ['<!--{$v|escape:"quotes"}-->', ['v' => "<script>alert('x')</script>"], "#script escaped#alert(\\'x\\')#script escaped#"],
            'エラーメッセージの script_escape' => ['<!--{$arrErr.x}-->', ['arrErr' => ['x' => 'A<br />javascript:<i>']], 'A<br />#script escaped#&lt;i&gt;'],
            'hexentity' => ["<!--{\$v|escape:'hexentity'}-->", ['v' => 'a@'], '&#x61;&#x40;'],
            '条件式の h の後の出力' => ['<!--{if $v|h != ""}--><!--{/if}--><!--{$v}-->', ['v' => '<i>'], '&lt;i&gt;'],
            '代入の h の後の出力' => ['<!--{assign var=w value=$v|h}--><!--{$v}-->', ['v' => '<i>'], '&lt;i&gt;'],
            // h が掛かるのは $c だけなので, $b も自動エスケープで保護される (二重エスケープの $c は許容)
            'script 内の文字列' => ["<script>var a = '<!--{\$v}-->';</script>", ['v' => "a'b\"c</script>&<i>`\${x}\\\n"], "<script>var a = 'a\\x27b\\x22c#script escaped#\\x26\\x3ci\\x3e\\x60\\x24{x}\\\\\\n';</script>"],
            'script 内の URL' => ['<script>u="<!--{$u}-->";</script>', ['u' => '/shop/?a=1&b=2'], '<script>u="/shop/?a=1\\x26b=2";</script>'],
            'script 内の数値' => ['<script>n=<!--{$n}-->;</script>', ['n' => 12], '<script>n=12;</script>'],
            // HTML コメントの中の <script> で判定を誤っても, HTML として解釈される文字は出力しない
            'script の判定を誤った場合' => ['<!-- <script> --><p><!--{$v}--></p>', ['v' => '<details open ontoggle=alert(1)>'], '<!-- <script> --><p>\\x3cdetails open ontoggle=alert(1)\\x3e</p>'],
            '三項演算子の片側の h' => ['<!--{$a ? $b : $c|h}-->', ['a' => true, 'b' => '<i>', 'c' => ''], '&lt;i&gt;'],
        ];
    }

    /**
     * @dataProvider renderProvider
     */
    public function testRender($template, array $vars, $expected)
    {
        $objView = new SC_SiteView_Ex();
        $objView->enableAutoEscape();
        $objView->_smarty->setCompileDir(sys_get_temp_dir().'/sc_helper_smarty_escape_test');
        foreach ($vars as $k => $val) {
            $objView->assign($k, $val);
        }
        $this->assertSame($expected, $objView->_smarty->fetch('string:'.$template));
    }

    public function testViewWithoutDisplayIsNotAutoEscaped()
    {
        // メール本文の生成など, SC_Display を通らない View は従来どおり
        $objView = new SC_SiteView_Ex();
        $this->assertFalse($objView->isAutoEscape());
        $objView->assign('v', 'A&B');
        $this->assertSame('A&B', $objView->_smarty->fetch('string:<!--{$v}-->'));
    }

    public function testDisplaySetViewEnablesAutoEscape()
    {
        $objDisplay = new SC_Display_Ex();
        $objView = new SC_SiteView_Ex();
        $objDisplay->setView($objView);
        $this->assertTrue($objView->isAutoEscape());
    }

    /**
     * class_extends やプラグイン (loadClassFileChange) が SC_Helper_SmartyEscape_Ex を差し替えると,
     * 規則・許可リスト・VERSION (compile_id) に反映される.
     *
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     */
    public function testHelperIsOverridableByExClass()
    {
        // 別プロセスで, 自動ロードされる前に差し替え用の _Ex を読み込む (class_extends に置いた場合と同じ状態)
        require_once __DIR__.'/fixtures/SC_Helper_SmartyEscape_Ex.php';
        $objView = new SC_SiteView_Ex();
        $objView->enableAutoEscape();
        $objView->_smarty->setCompileDir(sys_get_temp_dir().'/sc_helper_smarty_escape_test');
        $objView->assign('plg_raw_html', '<i>');
        $objView->assign('tpl_onload', '<i>');

        $this->assertStringEndsWith('aetest', $objView->_smarty->compile_id);
        // 差し替えた許可リストが使われる (tpl_onload は許可リストから外れたので自動エスケープされる)
        $this->assertSame('<i>&lt;i&gt;', $objView->_smarty->fetch('string:<!--{$plg_raw_html}--><!--{$tpl_onload}-->'));
    }
}
