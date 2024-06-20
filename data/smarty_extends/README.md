## このディレクトリのファイルは composer.json の autoload.classmap.files に登録することで利用可能です

1. autoload.classmap.files に登録します。

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

2. composer dump-autoload コマンドを実行することで autoload の対象となります。

``` shell
composer dump-autoload
```
