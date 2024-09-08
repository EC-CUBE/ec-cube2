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
        return array(
            array('<script type="text/javascript"></script>'),
            array('<svg onload="alert(1)">test</svg>'),
            array('<img onload="alert(1)">test</img>'),
            array('<body onload="alert(1)">test</body>'),
            array('<iframe></iframe>'),
            array('<object></object>'),
            array('<embed>'),
            array('\"onclick=\"alert(1)\"'),
            array('<p onclick="alert(1)">test</p>'),
            array('<p onsubmit="alert(1)">test</p>'),
            array('<p style="" onclick="alert(1)">test</p>'),
            array('<input type="button"onfocus="alert(1)">'),
            array('<input type="button" onblur="alert(1)">'),
            array('<input onfocus="alert(1)" type="button">'),
            array('<body onresize="alert(1)">'),
            array('<div onscroll="alert(1)">'),
            array('<div>javascript:test()</div>'),
            array('<input type="button" ondblclick="alert(1)">'),
            array('<input type="text" onchange="alert(1);">'),
            array('<input type="text" onselect="alert(1);">'),
            array('<form onsubmit="alert(1);">'),
            array('<input type="button" onkeydown="alert(1)">'),
            array('<input type="button" onkeypress="alert(1)">'),
            array('<input type="button" onkeyup="alert(1)">'),
            array('<input type=\"button\"\nonclick=\"alert(1)\">'),
            array('<div/onscroll="alert(1)">'),
        );
    }

    public function scriptNoEscapeProvider()
    {
        return array(
            array('<p>test</p>'),
            array('<input type="button">'),
            array('<p>onclick</p>'),
            array('<div>test</div>'),
            array('<textarea>onclick="alert(1)";</textarea>'),
            array('<p>onclick="\ntest();"</p>'),
            array('<onclock'),
            array('<oncl\nick'),
        );
    }

    /**
     * @dataProvider scriptEscapeProvider
     */
    public function testメールテンプレート_エスケープされる($value)
    {
        $ret = smarty_modifier_script_escape($value);
        $pattern = "/#script escaped#/";
        $this->assertRegExp($pattern, $ret);
    }

    /**
     * @dataProvider scriptNoEscapeProvider
     */
    public function testメールテンプレート_エスケープされない($value)
    {
        $ret = smarty_modifier_script_escape($value);
        $pattern = "/#script escaped#/";
        $this->assertNotRegExp($pattern, $ret);
    }
}
