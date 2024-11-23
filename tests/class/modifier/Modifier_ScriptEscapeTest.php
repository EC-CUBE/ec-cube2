<?php

/**
 * (省略。アノテーションを認識されるのに必要なようなので記述している。)
 *
 * PHP 8.1 でグローバル変数が消失する不具合を回避するため、下で `backupGlobals` を指定している。本質的には PHPUnit が PHP8 に対応していないのが原因と考えられる。
 *
 */
class Modifier_ScriptEscapeTest extends PHPUnit_Framework_TestCase
{
    public function scriptEscapeProvider()
    {
        $default_pattern = '/#script escaped#/';
        return [
            ['<script type="text/javascript"></script>', $default_pattern],
            ['<svg onload="alert(1)">test</svg>', $default_pattern],
            ['<img onload="alert(1)">test</img>', $default_pattern],
            ['<body onload="alert(1)">test</body>', $default_pattern],
            ['<iframe></iframe>', $default_pattern],
            ['<object></object>', $default_pattern],
            ['<embed>', $default_pattern],
            ['\"onclick=\"alert(1)\"', $default_pattern],
            ['<p onclick="alert(1)">test</p>', $default_pattern],
            ['<p onsubmit="alert(1)">test</p>', $default_pattern],
            ['<p style="" onclick="alert(1)">test</p>', $default_pattern],
            ['<input type="button"onfocus="alert(1)">', '//'], // HTMLPurifier によって完全に削除される
            ['<input type="button" onblur="alert(1)">', $default_pattern],
            ['<input onfocus="alert(1)" type="button">', $default_pattern],
            ['<body onresize="alert(1)">', $default_pattern],
            ['<div onscroll="alert(1)">', $default_pattern],
            ['<div>javascript:test()</div>', $default_pattern],
            ['<input type="button" ondblclick="alert(1)">', $default_pattern],
            ['<input type="text" onchange="alert(1);">', $default_pattern],
            ['<input type="text" onselect="alert(1);">', $default_pattern],
            ['<form onsubmit="alert(1);">', $default_pattern],
            ['<input type="button" onkeydown="alert(1)">', $default_pattern],
            ['<input type="button" onkeypress="alert(1)">', $default_pattern],
            ['<input type="button" onkeyup="alert(1)">', $default_pattern],
            ['<input type=\"button\"\nonclick=\"alert(1)\">', '//'], // HTMLPurifier によって完全に削除される
            ['<div/onscroll="alert(1)">', $default_pattern],
        ];
    }

    public function scriptNoEscapeProvider()
    {
        return [
            ['<p id="test" class="test">test</p>', '<p id="test" class="test">test</p>'],
            ['<input type="button">', ''], // 許可タグではないのでHTMLPurifier によって完全に削除される
            ['<p>onclick</p>', '<p>onclick</p>'],
            ['<div>test</div>', '<div>test</div>'],
            ['<textarea>onclick="alert(1)";</textarea>', 'onclick="alert(1)";'], // 許可タグではないのでHTMLPurifierによって textarea タグが削除される
            ['<p>onclick="\ntest();"</p>', '<p>onclick="\ntest();"</p>'],
            ['<onclock', ''], // 許可タグではないのでHTMLPurifier によって完全に削除される
            ['<oncl\nick', ''], // 許可タグではないのでHTMLPurifier によって完全に削除される
        ];
    }

    /**
     * @dataProvider scriptEscapeProvider
     */
    public function testメールテンプレートエスケープされる($value, $pattern)
    {
        $ret = smarty_modifier_script_escape($value);
        $this->assertMatchesRegularExpression($pattern, $ret);
    }

    /**
     * @dataProvider scriptNoEscapeProvider
     */
    public function testメールテンプレートエスケープされない($value, $actual)
    {
        $ret = smarty_modifier_script_escape($value);
        $pattern = '/#script escaped#/';
        $this->assertDoesNotMatchRegularExpression($pattern, $ret);
        $this->assertSame($ret, $actual);
    }
}
