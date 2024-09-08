## このディレクトリのファイルは composer.json の autoload.classmap.files に登録することで利用可能です

1. [composer.json](../../composer.json) の autoload.classmap.files に登録します。

``` json
    "autoload": {
        "classmap": [
            "data/class",
            "data/class_extends"
        ],
        "files": [
            "data/smarty_extends/function.from_to.php",
            "data/smarty_extends/function.include_php_ex.php",
            "data/smarty_extends/modifier.h.php",
            "data/smarty_extends/modifier.n2s.php",
            "data/smarty_extends/modifier.nl2br_html.php",
            "data/smarty_extends/modifier.script_escape.php",
            "data/smarty_extends/modifier.u.php"
        ]
    }
```
2. [SC_View.php](../class/SC_View.php) のコンストラクタに modifier を設定します。
```diff
--- a/data/class/SC_View.php
+++ b/data/class/SC_View.php
@@ -54,7 +54,13 @@ public function init()
         $this->_smarty->registerPlugin('modifier', 'sfMultiply', array('SC_Utils_Ex', 'sfMultiply'));
         $this->_smarty->registerPlugin('modifier', 'sfRmDupSlash', array('SC_Utils_Ex', 'sfRmDupSlash'));
         $this->_smarty->registerPlugin('modifier', 'sfCutString', array('SC_Utils_Ex', 'sfCutString'));
-        $this->_smarty->addPluginsDir(array('plugins', realpath(dirname(__FILE__)) . '/../smarty_extends'));
+        $this->_smarty->registerPlugin('function', 'from_to', 'smarty_function_from_to');
+        $this->_smarty->registerPlugin('function', 'include_php_ex', 'smarty_function_include_php_ex');
+        $this->_smarty->registerPlugin('modifier', 'h', 'smarty_modifier_h');
+        $this->_smarty->registerPlugin('modifier', 'n2s', 'smarty_modifier_n2s');
+        $this->_smarty->registerPlugin('modifier', 'nl2br_html', 'smarty_modifier_nl2br_html');
+        $this->_smarty->registerPlugin('modifier', 'script_escape', 'smarty_modifier_script_escape');
+        $this->_smarty->registerPlugin('modifier', 'u', 'smarty_modifier_u');
         $this->_smarty->registerPlugin('modifier', 'sfMbConvertEncoding', array('SC_Utils_Ex', 'sfMbConvertEncoding'));
         $this->_smarty->registerPlugin('modifier', 'sfGetEnabled', array('SC_Utils_Ex', 'sfGetEnabled'));
         $this->_smarty->registerPlugin('modifier', 'sfNoImageMainList', array('SC_Utils_Ex', 'sfNoImageMainList'));
```
3. composer dump-autoload コマンドを実行することで autoload の対象となります。
``` shell
composer dump-autoload
```
