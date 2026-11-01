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

## `*_Ex.php` の配置場所

`*_Ex.php` は、元クラスの [data/class](../class/) からの相対ディレクトリに合わせて配置します。
先頭のディレクトリ名には `_extends` を付けます (`pages` のみ `page_extends` になります)。

| 元クラス | `*_Ex.php` の配置場所 |
|---|---|
| `data/class/SC_Product.php` | `data/class_extends/SC_Product_Ex.php` |
| `data/class/helper/SC_Helper_Purchase.php` | `data/class_extends/helper_extends/SC_Helper_Purchase_Ex.php` |
| `data/class/pages/mypage/LC_Page_AbstractMypage.php` | `data/class_extends/page_extends/mypage/LC_Page_AbstractMypage_Ex.php` |
| `data/class/pages/admin/order/LC_Page_Admin_Order_Edit.php` | `data/class_extends/page_extends/admin/order/LC_Page_Admin_Order_Edit_Ex.php` |

`*_Ex.php` が存在しない場合、 `*_Ex` クラスは元クラスのエイリアスになります。

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
- [data/class_extends/page_extends/shopping/LC_Page_Shopping_Payment_Ex.php](page_extends/shopping/LC_Page_Shopping_Payment_Ex.php)
- [data/class_extends/page_extends/shopping/LC_Page_Shopping_LoadPaymentModule_Ex.php](page_extends/shopping/LC_Page_Shopping_LoadPaymentModule_Ex.php)
- [data/class_extends/SC_Display_Ex.php](SC_Display_Ex.php)
- [data/class_extends/SC_Response_Ex.php](SC_Response_Ex.php)
- [data/class_extends/page_extends/cart/LC_Page_Cart_Ex.php](LC_Page_Cart_Ex.php)
- [data/class_extends/SC_FormParam_Ex.php](SC_FormParam_Ex.php)
- [data/class_extends/page_extends/admin/ownersstore/LC_Page_Admin_OwnersStore_Ex.php](LC_Page_Admin_OwnersStore_Ex.php)
- [data/class_extends/plugin_extends/SC_Plugin_Util_Ex.php](SC_Plugin_Util_Ex.php)
- [data/class_extends/page_extends/admin/order/LC_Page_Admin_Order_Edit_Ex.php](LC_Page_Admin_Order_Edit_Ex.php)
