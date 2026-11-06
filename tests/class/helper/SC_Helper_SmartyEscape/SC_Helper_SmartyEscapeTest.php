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
            ['$v|h', '$v|h nofilter'],
            ['$v|default:$f|h', '$v|default:$f|h nofilter'],
            ['$v|h|nl2br', '$v|h|nl2br nofilter'],
            ['$v|h|nl2br_html', '$v|h|nl2br_html nofilter'],
            ['$v|h|nl2br_html|default:$f', '$v|h|nl2br_html|default:($f|h) nofilter'],
            ['$v|wordwrap:20|h|nl2br', '$v|wordwrap:20|h|nl2br nofilter'],
            ['$v|h|default:$f', '$v|h|default:($f|h) nofilter'],
            ['$v|h|default:"a:b"', '$v|h|default:("a:b"|h) nofilter'],
            ['$v|h|nl2br|default:$x.y', '$v|h|nl2br|default:($x.y|h) nofilter'],
            ['$v|h nocache', '$v|h nocache nofilter'],
            ["\$v|escape:'javascript'|h", "\$v|escape:'javascript'|h nofilter"],
            // h の後に未知の modifier が続くものは対象外 (自動エスケープを維持)
            ['$v|h|cat:"#x"', '$v|h|cat:"#x"'],
            ['$v|h|sfDispDBDate', '$v|h|sfDispDBDate'],
            // C3
            ['$v|nl2br', '$v|nl2br|script_escape nofilter'],
            ['$v|nl2br_html', '$v|nl2br_html|script_escape nofilter'],
            // C4
            ['$arrErr.name', '$arrErr.name|h_allow_br nofilter'],
            ['$arrErr[$key]', '$arrErr[$key]|h_allow_br nofilter'],
            ['$arrErr[$key]|sfGetErrorColor', '$arrErr[$key]|sfGetErrorColor'],
            // C4 (エラーメッセージ系の変数名)
            ['$err', '$err|h_allow_br nofilter'],
            ['$errBirth', '$errBirth|h_allow_br nofilter'],
            ['$plg_pg_mulpay_msg', '$plg_pg_mulpay_msg|h_allow_br nofilter'],
            ['$tpl_exam_error', '$tpl_exam_error|h_allow_br nofilter'],
            ['$tpl_message', '$tpl_message|h_allow_br nofilter'],
            ['$item.err_msg', '$item.err_msg'],
            ['$err|h', '$err|h nofilter'],
            ['$err|nl2br', '$err|nl2br|script_escape nofilter'],
            ['$err ? $a : $b', '$err ? $a : $b'],
            // C6 は名前による C4 より優先
            ['$tpl_error', '$tpl_error|script_escape nofilter'],
            ['$GLOBAL_ERR', '$GLOBAL_ERR|script_escape nofilter'],
            // C5
            ['$v|sfGetChecked:1', '$v|sfGetChecked:1 nofilter'],
            ['$v|sfGetEnabled', '$v|sfGetEnabled nofilter'],
            ['$v|sfViewPriceTax2', '$v|sfViewPriceTax2 nofilter'],
            ['$v|default:0|sfViewPriceTax3:1', '$v|default:0|sfViewPriceTax3:1 nofilter'],
            ['$v|dispCurrency', '$v|dispCurrency nofilter'],
            ['$v|h|dispCurrency', '$v|h|dispCurrency'],
            // C6
            ['$tpl_onload', '$tpl_onload|script_escape nofilter'],
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
            ['$tpl_onload|escape', '$tpl_onload|escape'],
            // C6 の default は代替値がリテラルの場合だけ (代替値はエスケープされずに出力されるため)
            ["\$json_paidy|default:'a'", "\$json_paidy|default:'a'|script_escape nofilter"],
            ['$json_paidy|default:0', '$json_paidy|default:0|script_escape nofilter'],
            ['$json_paidy|default:$x', '$json_paidy|default:$x'],
            ['$json_paidy|default:"a$x"', '$json_paidy|default:"a$x"'],
            ['$json_paidy|default:"a`$x`"', '$json_paidy|default:"a`$x`"'],
            ['$json_paidy|default:$smarty.get.x', '$json_paidy|default:$smarty.get.x'],
            // C7
            ["\$m|escape:'hexentity'", "\$m|escape:'hexentity' nofilter"],
            ["\$m|escape:'javascript'", "\$m|escape:'javascript'|script_escape nofilter"],
            ['$m|escape:"hex"', '$m|escape:"hex"'],
            ['$m|escape', '$m|escape'],
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
            ['($a ? $b : $c)|h', '($a ? $b : $c)|h nofilter'],
            ['$arr[$i+1]|h', '$arr[$i+1]|h nofilter'],
            ['$o->p|h', '$o->p|h nofilter'],
            ['$v|default:($a ? 1 : 2)|h', '$v|default:($a ? 1 : 2)|h nofilter'],
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
        $expected = "<!--{* <!--{\$v|h}--> *}-->\n<!--{literal}--><!--{\$v|h}--><!--{/literal}-->\n<!--{\$v|h nofilter}-->";
        $this->assertSame($expected, $this->objHelper->transform($source));
    }

    public function testTransformIsIdempotent()
    {
        $source = '<!--{$v|h}--><!--{$arrErr.x}--><!--{$v|h|default:$f}--><!--{$tpl_onload}-->';
        $once = $this->objHelper->transform($source);
        $this->assertSame($once, $this->objHelper->transform($once));
    }

    public function testTransformKeepsDelimiterInString()
    {
        $source = '<!--{$v|default:"}-->"|h}-->';
        $this->assertSame('<!--{$v|default:"}-->"|h nofilter}-->', $this->objHelper->transform($source));
    }

    public function scriptProvider()
    {
        $js = '|escape_js|script_escape nofilter';

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
            'C6 の名前でも添字付きは C8' => ['<script><!--{$arrDeliv[cnt].deliv_id}--></script>', '<script><!--{$arrDeliv[cnt].deliv_id|escape_js|script_escape nofilter}--></script>'],
            'C1 を優先' => ["<script>a='<!--{\$v|h}-->';</script>", "<script>a='<!--{\$v|h nofilter}-->';</script>"],
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
            'nl2br は従来どおり' => ['<!--{$v|nl2br}-->', ['v' => "<a href=\"/\">x</a>\n2"], "<a href=\"/\">x</a><br />\n2"],
            'tpl_onload は従来どおり' => ['<!--{$tpl_onload}-->', ['tpl_onload' => "fnOnLoad('a');"], "fnOnLoad('a');"],
            'sfGetChecked' => ['<!--{1|sfGetChecked:$v}-->', ['v' => '1'], 'checked="checked"'],
            'sfGetEnabled' => ['<!--{$v|sfGetEnabled}-->', ['v' => ''], ' disabled="disabled"'],
            'hexentity' => ["<!--{\$v|escape:'hexentity'}-->", ['v' => 'a@'], '&#x61;&#x40;'],
            '条件式の h の後の出力' => ['<!--{if $v|h != ""}--><!--{/if}--><!--{$v}-->', ['v' => '<i>'], '&lt;i&gt;'],
            '代入の h の後の出力' => ['<!--{assign var=w value=$v|h}--><!--{$v}-->', ['v' => '<i>'], '&lt;i&gt;'],
            // h が掛かるのは $c だけなので, $b も自動エスケープで保護される (二重エスケープの $c は許容)
            'script 内の文字列' => ["<script>var a = '<!--{\$v}-->';</script>", ['v' => "a'b\"c</script>&<i>`\${x}\\\n"], "<script>var a = 'a\\x27b\\x22c\\x3c/script\\x3e\\x26\\x3ci\\x3e\\x60\\x24{x}\\\\\\n';</script>"],
            'script 内の URL' => ['<script>u="<!--{$u}-->";</script>', ['u' => '/shop/?a=1&b=2'], '<script>u="/shop/?a=1\\x26b=2";</script>'],
            'script 内の数値' => ['<script>n=<!--{$n}-->;</script>', ['n' => 12], '<script>n=12;</script>'],
            // HTML コメントの中の <script> で判定を誤っても, HTML として解釈される文字は出力しない
            'script の判定を誤った場合' => ['<!-- <script> --><p><!--{$v}--></p>', ['v' => '<img src=x onerror=alert(1)>'], '<!-- <script> --><p>\\x3cimg src=x onerror=alert(1)\\x3e</p>'],
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
}
