# 拡張カスタマイズ用クラス用ディレクトリ

本ディレクトリ以下に `<クラス名>_Ex.php` を作成することにより、 [data/class](../class/) 以下のクラスを拡張カスタマイズできます。

## 使用方法

### SC_Product クラスをカスタマイズする場合

以下の内容で、 SC_Product_Ex.php を作成します。

```php
<?php
// path/to/ec-cube2/data/class_extends/SC_Product_Ex.php
class SC_Product_Ex extends SC_Product
{
    public function customizeMethod()
    {
        // ....
    }
}
```

## 作成済みの `*_Ex.php` ファイル

EC-CUBE2.17.2 までは、 [data/class](../class/) 以下すべてのクラスの拡張カスタマイズ用クラスが作成されていました。
[カスタマイズ時の利便性向上のため](https://github.com/EC-CUBE/ec-cube2/pull/526)、これらのファイルは削除されましたが、以下のファイルは下位互換性維持のため削除せずに残してあります。

### [app_initial.php](../app_initial.php) の spl_autoload_register を登録する前に必要なクラス

- [data/class_extends/SC_ClassAutoloader_Ex.php](SC_ClassAutoloader_Ex.php)
- [data/class_extends/helper_extends/SC_Helper_Plugin_Ex.php](helper_extends/SC_Helper_Plugin_Ex.php)
- [data/class_extends/SC_Query_Ex.php](SC_Query_Ex.php)

### 決済モジュールやプラグイン、 user_data 以下の PHP で `require` されているクラス

- [data/class_extends/page_extends/mypage/LC_Page_AbstractMypage_Ex.php](page_extends/mypage/LC_Page_AbstractMypage_Ex.php)
- [data/class_extends/page_extends/LC_Page_Ex.php](page_extends/LC_Page_Ex.php)
- [data/class_extends/page_extends/admin/LC_Page_Admin_Ex.php](page_extends/admin/LC_Page_Admin_Ex.php)
