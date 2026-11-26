<?php

class Modifier_HTest extends Common_TestCase
{
    public function testHエスケープされる()
    {
        $this->assertSame('&lt;a href=&quot;#&quot;&gt;&#039;&amp;&lt;/a&gt;', smarty_modifier_h('<a href="#">\'&</a>'));
    }

    public function testH数値は文字列として返る()
    {
        $this->assertSame('100', smarty_modifier_h(100));
    }

    public function testHNullの場合空文字が返る()
    {
        $ret = null;
        $deprecations = $this->collectDeprecations(function () use (&$ret) {
            $ret = smarty_modifier_h(null);
        });

        $this->assertSame('', $ret);
        $this->assertSame([], $deprecations);
    }

    public function testUエンコードされる()
    {
        $this->assertSame('a%20b%26c', smarty_modifier_u('a b&c'));
    }

    public function testUNullの場合空文字が返る()
    {
        $ret = null;
        $deprecations = $this->collectDeprecations(function () use (&$ret) {
            $ret = smarty_modifier_u(null);
        });

        $this->assertSame('', $ret);
        $this->assertSame([], $deprecations);
    }
}
