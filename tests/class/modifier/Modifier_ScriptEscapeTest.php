<?php

/**
 * (省略。アノテーションを認識されるのに必要なようなので記述している。)
 *
 * PHP 8.1 でグローバル変数が消失する不具合を回避するため、下で `backupGlobals` を指定している。本質的には PHPUnit が PHP8 に対応していないのが原因と考えられる。
 *
 * @backupGlobals disabled
 */
class Modifier_ScriptEscapeTest extends PHPUnit_Framework_TestCase
{
    public function scriptEscapeProvider()
    {
        return [
            ['<script type="text/javascript"></script>'],
            ['<svg onload="alert(1)">test</svg>'],
            ['<img onload="alert(1)">test</img>'],
            ['<body onload="alert(1)">test</body>'],
            ['<iframe></iframe>'],
            ['<object></object>'],
            ['<embed>'],
            ['\"onclick=\"alert(1)\"'],
            ['<p onclick="alert(1)">test</p>'],
            ['<p onsubmit="alert(1)">test</p>'],
            ['<p style="" onclick="alert(1)">test</p>'],
            ['<input type="button"onfocus="alert(1)">'],
            ['<input type="button" onblur="alert(1)">'],
            ['<input onfocus="alert(1)" type="button">'],
            ['<body onresize="alert(1)">'],
            ['<div onscroll="alert(1)">'],
            ['<div>javascript:test()</div>'],
            ['<input type="button" ondblclick="alert(1)">'],
            ['<input type="text" onchange="alert(1);">'],
            ['<input type="text" onselect="alert(1);">'],
            ['<form onsubmit="alert(1);">'],
            ['<input type="button" onkeydown="alert(1)">'],
            ['<input type="button" onkeypress="alert(1)">'],
            ['<input type="button" onkeyup="alert(1)">'],
            ['<input type=\"button\"\nonclick=\"alert(1)\">'],
            ['<div/onscroll="alert(1)">'],
        ];
    }

    public function scriptNoEscapeProvider()
    {
        return [
            ['<p>test</p>'],
            ['<input type="button">'],
            ['<p>onclick</p>'],
            ['<div>test</div>'],
            ['<textarea>onclick="alert(1)";</textarea>'],
            ['<p>onclick="\ntest();"</p>'],
            ['<onclock'],
            ['<oncl\nick'],
        ];
    }

    /**
     * @dataProvider scriptEscapeProvider
     */
    public function testメールテンプレートエスケープされる($value)
    {
        $ret = smarty_modifier_script_escape($value);
        $pattern = '/#script escaped#/';
        $this->assertMatchesRegularExpression($pattern, $ret);
    }

    /**
     * @dataProvider scriptNoEscapeProvider
     */
    public function testメールテンプレートエスケープされない($value)
    {
        $ret = smarty_modifier_script_escape($value);
        $pattern = '/#script escaped#/';
        $this->assertDoesNotMatchRegularExpression($pattern, $ret);
    }
}
