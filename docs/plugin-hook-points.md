# EC-CUBE2 プラグインフックポイント一覧

このドキュメントは、EC-CUBE2のプラグイン開発で利用可能なすべてのフックポイントの完全な一覧です。

## 目次

- [概要](#概要)
- [スーパーフックポイント（システム全体）](#スーパーフックポイントシステム全体)
- [ローカルフックポイント（ページクラス別）](#ローカルフックポイントページクラス別)
- [フロント画面ページクラス](#フロント画面ページクラス)
- [管理画面ページクラス](#管理画面ページクラス)
- [API関連フックポイント](#api関連フックポイント)
- [特殊なフックポイント](#特殊なフックポイント)
- [使用例](#使用例)
- [統計情報](#統計情報)

## 概要

EC-CUBE2では、プラグインシステムを通じてシステムの動作を拡張・カスタマイズできる強力なフックポイント機能を提供しています。フックポイントは、コード内の特定の場所でプラグインが独自の機能を挿入できるポイントです。

### フックポイントの種類

1. **スーパーフックポイント**: システム全体のページで実行
2. **ローカルフックポイント**: 特定のページクラスで実行
3. **特殊フックポイント**: 特定の機能のためのカスタム実装

## スーパーフックポイント（システム全体）

これらのフックポイントは、システム内のすべてのページで実行されます。

### 基本スーパーフックポイント

| フックポイント | 説明 | 実行タイミング |
|-------------|------|-------------|
| `LC_Page_preProcess` | 全ページの前処理で実行 | ページクラス初期化時 |
| `LC_Page_process` | 全ページの後処理で実行 | レスポンス送信時 |

### テンプレート関連フックポイント

| フックポイント | 説明 | 実行タイミング |
|-------------|------|-------------|
| `prefilterTransform` | Smartyテンプレートのプリフィルタ処理 | テンプレートコンパイル時 |
| `outputfilterTransform` | Smartyテンプレートのアウトプットフィルタ処理 | テンプレート出力時 |

### システム関連フックポイント

| フックポイント | 説明 | 実行タイミング |
|-------------|------|-------------|
| `loadClassFileChange` | クラスファイルのオートロード処理 | クラス読み込み時 |
| `SC_FormParam_construct` | SC_FormParamクラスのコンストラクタ | フォームパラメータ初期化時 |

## ローカルフックポイント（ページクラス別）

ローカルフックポイントは、各ページクラスに対して特定の命名パターンに従って自動的に生成されます。

### 命名規則

各ページクラスに対して、以下のフックポイントが自動的に利用可能です：

#### Before/Afterパターン
- `{親クラス名}_action_before`
- `{クラス名}_action_before`
- `{親クラス名}_action_after`
- `{クラス名}_action_after`

#### モード固有パターン
- `{親クラス名}_action_{mode}`
- `{クラス名}_action_{mode}`

#### 特殊パターン
- `{親クラス名}_action_exit`
- `{クラス名}_action_exit`
- `{親クラス名}_action_redirect`
- `{クラス名}_action_redirect`

## フロント画面ページクラス

### 基本ページ

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Index` | `LC_Page_Index_action_before`<br>`LC_Page_Index_action_after` |
| `LC_Page_InputZip` | `LC_Page_InputZip_action_before`<br>`LC_Page_InputZip_action_after` |
| `LC_Page_ResizeImage` | `LC_Page_ResizeImage_action_before`<br>`LC_Page_ResizeImage_action_after` |
| `LC_Page_Sitemap` | `LC_Page_Sitemap_action_before`<br>`LC_Page_Sitemap_action_after` |

### 商品関連ページ

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Products_Detail` | `LC_Page_Products_Detail_action_before`<br>`LC_Page_Products_Detail_action_after`<br>`LC_Page_Products_Detail_action_cart`<br>`LC_Page_Products_Detail_action_add_favorite`<br>`LC_Page_Products_Detail_action_add_favorite_sphone`<br>`LC_Page_Products_Detail_action_select`<br>`LC_Page_Products_Detail_action_select2`<br>`LC_Page_Products_Detail_action_selectItem` |
| `LC_Page_Products_List` | `LC_Page_Products_List_action_before`<br>`LC_Page_Products_List_action_after` |
| `LC_Page_Products_CategoryList` | `LC_Page_Products_CategoryList_action_before`<br>`LC_Page_Products_CategoryList_action_after` |
| `LC_Page_Products_Search` | `LC_Page_Products_Search_action_before`<br>`LC_Page_Products_Search_action_after` |
| `LC_Page_Products_Review` | `LC_Page_Products_Review_action_before`<br>`LC_Page_Products_Review_action_after` |
| `LC_Page_Products_ReviewComplete` | `LC_Page_Products_ReviewComplete_action_before`<br>`LC_Page_Products_ReviewComplete_action_after` |

### カート・ショッピングページ

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Cart` | `LC_Page_Cart_action_before`<br>`LC_Page_Cart_action_after` |
| `LC_Page_Shopping` | `LC_Page_Shopping_action_before`<br>`LC_Page_Shopping_action_after` |
| `LC_Page_Shopping_Confirm` | `LC_Page_Shopping_Confirm_action_before`<br>`LC_Page_Shopping_Confirm_action_after`<br>`LC_Page_Shopping_Confirm_action_confirm` |
| `LC_Page_Shopping_Complete` | `LC_Page_Shopping_Complete_action_before`<br>`LC_Page_Shopping_Complete_action_after` |
| `LC_Page_Shopping_Deliv` | `LC_Page_Shopping_Deliv_action_before`<br>`LC_Page_Shopping_Deliv_action_after` |
| `LC_Page_Shopping_Multiple` | `LC_Page_Shopping_Multiple_action_before`<br>`LC_Page_Shopping_Multiple_action_after` |
| `LC_Page_Shopping_Payment` | `LC_Page_Shopping_Payment_action_before`<br>`LC_Page_Shopping_Payment_action_after` |
| `LC_Page_Shopping_LoadPaymentModule` | `LC_Page_Shopping_LoadPaymentModule_action_before`<br>`LC_Page_Shopping_LoadPaymentModule_action_after` |

### マイページ関連ページ

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_AbstractMypage` | `LC_Page_AbstractMypage_action_before`<br>`LC_Page_AbstractMypage_action_after` |
| `LC_Page_Mypage` | `LC_Page_Mypage_action_before`<br>`LC_Page_Mypage_action_after` |
| `LC_Page_Mypage_Change` | `LC_Page_Mypage_Change_action_before`<br>`LC_Page_Mypage_Change_action_after` |
| `LC_Page_Mypage_ChangeComplete` | `LC_Page_Mypage_ChangeComplete_action_before`<br>`LC_Page_Mypage_ChangeComplete_action_after` |
| `LC_Page_Mypage_Delivery` | `LC_Page_Mypage_Delivery_action_before`<br>`LC_Page_Mypage_Delivery_action_after` |
| `LC_Page_Mypage_DeliveryAddr` | `LC_Page_Mypage_DeliveryAddr_action_before`<br>`LC_Page_Mypage_DeliveryAddr_action_after` |
| `LC_Page_Mypage_DownLoad` | `LC_Page_Mypage_DownLoad_action_before`<br>`LC_Page_Mypage_DownLoad_action_after` |
| `LC_Page_Mypage_Favorite` | `LC_Page_Mypage_Favorite_action_before`<br>`LC_Page_Mypage_Favorite_action_after` |
| `LC_Page_Mypage_History` | `LC_Page_Mypage_History_action_before`<br>`LC_Page_Mypage_History_action_after` |
| `LC_Page_Mypage_Login` | `LC_Page_Mypage_Login_action_before`<br>`LC_Page_Mypage_Login_action_after` |
| `LC_Page_Mypage_MailView` | `LC_Page_Mypage_MailView_action_before`<br>`LC_Page_Mypage_MailView_action_after` |
| `LC_Page_Mypage_Order` | `LC_Page_Mypage_Order_action_before`<br>`LC_Page_Mypage_Order_action_after` |
| `LC_Page_Mypage_Refusal` | `LC_Page_Mypage_Refusal_action_before`<br>`LC_Page_Mypage_Refusal_action_after` |
| `LC_Page_Mypage_RefusalComplete` | `LC_Page_Mypage_RefusalComplete_action_before`<br>`LC_Page_Mypage_RefusalComplete_action_after` |

### その他フロントページ

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Contact` | `LC_Page_Contact_action_before`<br>`LC_Page_Contact_action_after` |
| `LC_Page_Contact_Complete` | `LC_Page_Contact_Complete_action_before`<br>`LC_Page_Contact_Complete_action_after` |
| `LC_Page_Entry` | `LC_Page_Entry_action_before`<br>`LC_Page_Entry_action_after` |
| `LC_Page_Entry_Complete` | `LC_Page_Entry_Complete_action_before`<br>`LC_Page_Entry_Complete_action_after` |
| `LC_Page_Entry_EmailMobile` | `LC_Page_Entry_EmailMobile_action_before`<br>`LC_Page_Entry_EmailMobile_action_after` |
| `LC_Page_Entry_Kiyaku` | `LC_Page_Entry_Kiyaku_action_before`<br>`LC_Page_Entry_Kiyaku_action_after` |
| `LC_Page_Error` | `LC_Page_Error_action_before`<br>`LC_Page_Error_action_after` |
| `LC_Page_Error_DispError` | `LC_Page_Error_DispError_action_before`<br>`LC_Page_Error_DispError_action_after` |
| `LC_Page_Error_SystemError` | `LC_Page_Error_SystemError_action_before`<br>`LC_Page_Error_SystemError_action_after` |
| `LC_Page_Forgot` | `LC_Page_Forgot_action_before`<br>`LC_Page_Forgot_action_after` |

### ガイドページ

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Guide` | `LC_Page_Guide_action_before`<br>`LC_Page_Guide_action_after` |
| `LC_Page_Guide_About` | `LC_Page_Guide_About_action_before`<br>`LC_Page_Guide_About_action_after` |
| `LC_Page_Guide_Charge` | `LC_Page_Guide_Charge_action_before`<br>`LC_Page_Guide_Charge_action_after` |
| `LC_Page_Guide_Kiyaku` | `LC_Page_Guide_Kiyaku_action_before`<br>`LC_Page_Guide_Kiyaku_action_after` |
| `LC_Page_Guide_Privacy` | `LC_Page_Guide_Privacy_action_before`<br>`LC_Page_Guide_Privacy_action_after` |
| `LC_Page_Guide_Usage` | `LC_Page_Guide_Usage_action_before`<br>`LC_Page_Guide_Usage_action_after` |

### フロントパーツページ

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_FrontParts_LoginCheck` | `LC_Page_FrontParts_LoginCheck_action_before`<br>`LC_Page_FrontParts_LoginCheck_action_after` |
| `LC_Page_FrontParts_Bloc` | `LC_Page_FrontParts_Bloc_action_before`<br>`LC_Page_FrontParts_Bloc_action_after` |
| `LC_Page_FrontParts_Bloc_Calendar` | `LC_Page_FrontParts_Bloc_Calendar_action_before`<br>`LC_Page_FrontParts_Bloc_Calendar_action_after` |
| `LC_Page_FrontParts_Bloc_Cart` | `LC_Page_FrontParts_Bloc_Cart_action_before`<br>`LC_Page_FrontParts_Bloc_Cart_action_after` |
| `LC_Page_FrontParts_Bloc_Category` | `LC_Page_FrontParts_Bloc_Category_action_before`<br>`LC_Page_FrontParts_Bloc_Category_action_after` |
| `LC_Page_FrontParts_Bloc_Login` | `LC_Page_FrontParts_Bloc_Login_action_before`<br>`LC_Page_FrontParts_Bloc_Login_action_after` |
| `LC_Page_FrontParts_Bloc_LoginFooter` | `LC_Page_FrontParts_Bloc_LoginFooter_action_before`<br>`LC_Page_FrontParts_Bloc_LoginFooter_action_after` |
| `LC_Page_FrontParts_Bloc_LoginHeader` | `LC_Page_FrontParts_Bloc_LoginHeader_action_before`<br>`LC_Page_FrontParts_Bloc_LoginHeader_action_after` |
| `LC_Page_FrontParts_Bloc_NaviFooter` | `LC_Page_FrontParts_Bloc_NaviFooter_action_before`<br>`LC_Page_FrontParts_Bloc_NaviFooter_action_after` |
| `LC_Page_FrontParts_Bloc_NaviHeader` | `LC_Page_FrontParts_Bloc_NaviHeader_action_before`<br>`LC_Page_FrontParts_Bloc_NaviHeader_action_after` |
| `LC_Page_FrontParts_Bloc_News` | `LC_Page_FrontParts_Bloc_News_action_before`<br>`LC_Page_FrontParts_Bloc_News_action_after` |
| `LC_Page_FrontParts_Bloc_Recommend` | `LC_Page_FrontParts_Bloc_Recommend_action_before`<br>`LC_Page_FrontParts_Bloc_Recommend_action_after` |
| `LC_Page_FrontParts_Bloc_SearchProducts` | `LC_Page_FrontParts_Bloc_SearchProducts_action_before`<br>`LC_Page_FrontParts_Bloc_SearchProducts_action_after` |

## 管理画面ページクラス

### 基本管理画面

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Admin` | `LC_Page_Admin_action_before`<br>`LC_Page_Admin_action_after` |
| `LC_Page_Admin_Home` | `LC_Page_Admin_Home_action_before`<br>`LC_Page_Admin_Home_action_after` |
| `LC_Page_Admin_Index` | `LC_Page_Admin_Index_action_before`<br>`LC_Page_Admin_Index_action_after` |
| `LC_Page_Admin_Logout` | `LC_Page_Admin_Logout_action_before`<br>`LC_Page_Admin_Logout_action_after` |

### 基本設定

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Admin_Basis` | `LC_Page_Admin_Basis_action_before`<br>`LC_Page_Admin_Basis_action_after` |
| `LC_Page_Admin_Basis_Delivery` | `LC_Page_Admin_Basis_Delivery_action_before`<br>`LC_Page_Admin_Basis_Delivery_action_after` |
| `LC_Page_Admin_Basis_DeliveryInput` | `LC_Page_Admin_Basis_DeliveryInput_action_before`<br>`LC_Page_Admin_Basis_DeliveryInput_action_after` |
| `LC_Page_Admin_Basis_Holiday` | `LC_Page_Admin_Basis_Holiday_action_before`<br>`LC_Page_Admin_Basis_Holiday_action_after` |
| `LC_Page_Admin_Basis_Kiyaku` | `LC_Page_Admin_Basis_Kiyaku_action_before`<br>`LC_Page_Admin_Basis_Kiyaku_action_after` |
| `LC_Page_Admin_Basis_Mail` | `LC_Page_Admin_Basis_Mail_action_before`<br>`LC_Page_Admin_Basis_Mail_action_after` |
| `LC_Page_Admin_Basis_Payment` | `LC_Page_Admin_Basis_Payment_action_before`<br>`LC_Page_Admin_Basis_Payment_action_after` |
| `LC_Page_Admin_Basis_PaymentInput` | `LC_Page_Admin_Basis_PaymentInput_action_before`<br>`LC_Page_Admin_Basis_PaymentInput_action_after` |
| `LC_Page_Admin_Basis_Point` | `LC_Page_Admin_Basis_Point_action_before`<br>`LC_Page_Admin_Basis_Point_action_after` |
| `LC_Page_Admin_Basis_Tax` | `LC_Page_Admin_Basis_Tax_action_before`<br>`LC_Page_Admin_Basis_Tax_action_after` |
| `LC_Page_Admin_Basis_Tradelaw` | `LC_Page_Admin_Basis_Tradelaw_action_before`<br>`LC_Page_Admin_Basis_Tradelaw_action_after` |
| `LC_Page_Admin_Basis_ZipInstall` | `LC_Page_Admin_Basis_ZipInstall_action_before`<br>`LC_Page_Admin_Basis_ZipInstall_action_after` |

### 商品管理

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Admin_Products` | `LC_Page_Admin_Products_action_before`<br>`LC_Page_Admin_Products_action_after` |
| `LC_Page_Admin_Products_Category` | `LC_Page_Admin_Products_Category_action_before`<br>`LC_Page_Admin_Products_Category_action_after` |
| `LC_Page_Admin_Products_Class` | `LC_Page_Admin_Products_Class_action_before`<br>`LC_Page_Admin_Products_Class_action_after` |
| `LC_Page_Admin_Products_ClassCategory` | `LC_Page_Admin_Products_ClassCategory_action_before`<br>`LC_Page_Admin_Products_ClassCategory_action_after` |
| `LC_Page_Admin_Products_Maker` | `LC_Page_Admin_Products_Maker_action_before`<br>`LC_Page_Admin_Products_Maker_action_after` |
| `LC_Page_Admin_Products_Product` | `LC_Page_Admin_Products_Product_action_before`<br>`LC_Page_Admin_Products_Product_action_after` |
| `LC_Page_Admin_Products_ProductClass` | `LC_Page_Admin_Products_ProductClass_action_before`<br>`LC_Page_Admin_Products_ProductClass_action_after` |
| `LC_Page_Admin_Products_ProductRank` | `LC_Page_Admin_Products_ProductRank_action_before`<br>`LC_Page_Admin_Products_ProductRank_action_after` |
| `LC_Page_Admin_Products_ProductSelect` | `LC_Page_Admin_Products_ProductSelect_action_before`<br>`LC_Page_Admin_Products_ProductSelect_action_after` |
| `LC_Page_Admin_Products_Review` | `LC_Page_Admin_Products_Review_action_before`<br>`LC_Page_Admin_Products_Review_action_after` |
| `LC_Page_Admin_Products_ReviewEdit` | `LC_Page_Admin_Products_ReviewEdit_action_before`<br>`LC_Page_Admin_Products_ReviewEdit_action_after` |
| `LC_Page_Admin_Products_UploadCSV` | `LC_Page_Admin_Products_UploadCSV_action_before`<br>`LC_Page_Admin_Products_UploadCSV_action_after` |
| `LC_Page_Admin_Products_UploadCSVCategory` | `LC_Page_Admin_Products_UploadCSVCategory_action_before`<br>`LC_Page_Admin_Products_UploadCSVCategory_action_after` |

### 受注管理

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Admin_Order` | `LC_Page_Admin_Order_action_before`<br>`LC_Page_Admin_Order_action_after` |
| `LC_Page_Admin_Order_Disp` | `LC_Page_Admin_Order_Disp_action_before`<br>`LC_Page_Admin_Order_Disp_action_after` |
| `LC_Page_Admin_Order_Edit` | `LC_Page_Admin_Order_Edit_action_before`<br>`LC_Page_Admin_Order_Edit_action_after` |
| `LC_Page_Admin_Order_Mail` | `LC_Page_Admin_Order_Mail_action_before`<br>`LC_Page_Admin_Order_Mail_action_after` |
| `LC_Page_Admin_Order_MailView` | `LC_Page_Admin_Order_MailView_action_before`<br>`LC_Page_Admin_Order_MailView_action_after` |
| `LC_Page_Admin_Order_Multiple` | `LC_Page_Admin_Order_Multiple_action_before`<br>`LC_Page_Admin_Order_Multiple_action_after` |
| `LC_Page_Admin_Order_Pdf` | `LC_Page_Admin_Order_Pdf_action_before`<br>`LC_Page_Admin_Order_Pdf_action_after` |
| `LC_Page_Admin_Order_ProductSelect` | `LC_Page_Admin_Order_ProductSelect_action_before`<br>`LC_Page_Admin_Order_ProductSelect_action_after` |
| `LC_Page_Admin_Order_Status` | `LC_Page_Admin_Order_Status_action_before`<br>`LC_Page_Admin_Order_Status_action_after` |

### 顧客管理

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Admin_Customer` | `LC_Page_Admin_Customer_action_before`<br>`LC_Page_Admin_Customer_action_after` |
| `LC_Page_Admin_Customer_Edit` | `LC_Page_Admin_Customer_Edit_action_before`<br>`LC_Page_Admin_Customer_Edit_action_after` |
| `LC_Page_Admin_Customer_SearchCustomer` | `LC_Page_Admin_Customer_SearchCustomer_action_before`<br>`LC_Page_Admin_Customer_SearchCustomer_action_after` |

### コンテンツ管理

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Admin_Contents` | `LC_Page_Admin_Contents_action_before`<br>`LC_Page_Admin_Contents_action_after` |
| `LC_Page_Admin_Contents_CSV` | `LC_Page_Admin_Contents_CSV_action_before`<br>`LC_Page_Admin_Contents_CSV_action_after` |
| `LC_Page_Admin_Contents_CsvSql` | `LC_Page_Admin_Contents_CsvSql_action_before`<br>`LC_Page_Admin_Contents_CsvSql_action_after` |
| `LC_Page_Admin_Contents_FileManager` | `LC_Page_Admin_Contents_FileManager_action_before`<br>`LC_Page_Admin_Contents_FileManager_action_after` |
| `LC_Page_Admin_Contents_FileView` | `LC_Page_Admin_Contents_FileView_action_before`<br>`LC_Page_Admin_Contents_FileView_action_after` |
| `LC_Page_Admin_Contents_Recommend` | `LC_Page_Admin_Contents_Recommend_action_before`<br>`LC_Page_Admin_Contents_Recommend_action_after` |
| `LC_Page_Admin_Contents_RecommendSearch` | `LC_Page_Admin_Contents_RecommendSearch_action_before`<br>`LC_Page_Admin_Contents_RecommendSearch_action_after` |

### デザイン管理

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Admin_Design` | `LC_Page_Admin_Design_action_before`<br>`LC_Page_Admin_Design_action_after` |
| `LC_Page_Admin_Design_Bloc` | `LC_Page_Admin_Design_Bloc_action_before`<br>`LC_Page_Admin_Design_Bloc_action_after` |
| `LC_Page_Admin_Design_CSS` | `LC_Page_Admin_Design_CSS_action_before`<br>`LC_Page_Admin_Design_CSS_action_after` |
| `LC_Page_Admin_Design_Header` | `LC_Page_Admin_Design_Header_action_before`<br>`LC_Page_Admin_Design_Header_action_after` |
| `LC_Page_Admin_Design_MainEdit` | `LC_Page_Admin_Design_MainEdit_action_before`<br>`LC_Page_Admin_Design_MainEdit_action_after` |
| `LC_Page_Admin_Design_Template` | `LC_Page_Admin_Design_Template_action_before`<br>`LC_Page_Admin_Design_Template_action_after` |
| `LC_Page_Admin_Design_UpDown` | `LC_Page_Admin_Design_UpDown_action_before`<br>`LC_Page_Admin_Design_UpDown_action_after` |

### メール管理

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Admin_Mail` | `LC_Page_Admin_Mail_action_before`<br>`LC_Page_Admin_Mail_action_after` |
| `LC_Page_Admin_Mail_History` | `LC_Page_Admin_Mail_History_action_before`<br>`LC_Page_Admin_Mail_History_action_after` |
| `LC_Page_Admin_Mail_Preview` | `LC_Page_Admin_Mail_Preview_action_before`<br>`LC_Page_Admin_Mail_Preview_action_after` |
| `LC_Page_Admin_Mail_Template` | `LC_Page_Admin_Mail_Template_action_before`<br>`LC_Page_Admin_Mail_Template_action_after` |
| `LC_Page_Admin_Mail_TemplateInput` | `LC_Page_Admin_Mail_TemplateInput_action_before`<br>`LC_Page_Admin_Mail_TemplateInput_action_after` |

### システム設定

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Admin_System` | `LC_Page_Admin_System_action_before`<br>`LC_Page_Admin_System_action_after` |
| `LC_Page_Admin_System_AdminArea` | `LC_Page_Admin_System_AdminArea_action_before`<br>`LC_Page_Admin_System_AdminArea_action_after` |
| `LC_Page_Admin_System_Bkup` | `LC_Page_Admin_System_Bkup_action_before`<br>`LC_Page_Admin_System_Bkup_action_after` |
| `LC_Page_Admin_System_Delete` | `LC_Page_Admin_System_Delete_action_before`<br>`LC_Page_Admin_System_Delete_action_after` |
| `LC_Page_Admin_System_Input` | `LC_Page_Admin_System_Input_action_before`<br>`LC_Page_Admin_System_Input_action_after` |
| `LC_Page_Admin_System_Log` | `LC_Page_Admin_System_Log_action_before`<br>`LC_Page_Admin_System_Log_action_after` |
| `LC_Page_Admin_System_Masterdata` | `LC_Page_Admin_System_Masterdata_action_before`<br>`LC_Page_Admin_System_Masterdata_action_after` |
| `LC_Page_Admin_System_Parameter` | `LC_Page_Admin_System_Parameter_action_before`<br>`LC_Page_Admin_System_Parameter_action_after` |
| `LC_Page_Admin_System_Rank` | `LC_Page_Admin_System_Rank_action_before`<br>`LC_Page_Admin_System_Rank_action_after` |
| `LC_Page_Admin_System_System` | `LC_Page_Admin_System_System_action_before`<br>`LC_Page_Admin_System_System_action_after` |

### 売上集計

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Admin_Total` | `LC_Page_Admin_Total_action_before`<br>`LC_Page_Admin_Total_action_after` |

### オーナーズストア

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Admin_OwnersStore` | `LC_Page_Admin_OwnersStore_action_before`<br>`LC_Page_Admin_OwnersStore_action_after` |
| `LC_Page_Admin_OwnersStore_Log` | `LC_Page_Admin_OwnersStore_Log_action_before`<br>`LC_Page_Admin_OwnersStore_Log_action_after` |
| `LC_Page_Admin_OwnersStore_Module` | `LC_Page_Admin_OwnersStore_Module_action_before`<br>`LC_Page_Admin_OwnersStore_Module_action_after` |
| `LC_Page_Admin_OwnersStore_PluginHookPointList` | `LC_Page_Admin_OwnersStore_PluginHookPointList_action_before`<br>`LC_Page_Admin_OwnersStore_PluginHookPointList_action_after` |
| `LC_Page_Admin_OwnersStore_Settings` | `LC_Page_Admin_OwnersStore_Settings_action_before`<br>`LC_Page_Admin_OwnersStore_Settings_action_after` |

## API関連フックポイント

### APIページクラス

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `LC_Page_Api` | `LC_Page_Api_action_before`<br>`LC_Page_Api_action_after` |
| `LC_Page_Api_Json` | `LC_Page_Api_Json_action_before`<br>`LC_Page_Api_Json_action_after` |
| `LC_Page_Api_Php` | `LC_Page_Api_Php_action_before`<br>`LC_Page_Api_Php_action_after` |
| `LC_Page_Api_Xml` | `LC_Page_Api_Xml_action_before`<br>`LC_Page_Api_Xml_action_after` |

### API操作クラス

| クラス名 | 利用可能なフックポイント |
|---------|---------------------|
| `AddrFromZip` | `AddrFromZip_action_before`<br>`AddrFromZip_action_after` |
| `BrowseNodeLookup` | `BrowseNodeLookup_action_before`<br>`BrowseNodeLookup_action_after` |
| `CartAdd` | `CartAdd_action_before`<br>`CartAdd_action_after` |
| `CartClear` | `CartClear_action_before`<br>`CartClear_action_after` |
| `CartCreate` | `CartCreate_action_before`<br>`CartCreate_action_after` |
| `CartGet` | `CartGet_action_before`<br>`CartGet_action_after` |
| `CartModify` | `CartModify_action_before`<br>`CartModify_action_after` |
| `Default` | `Default_action_before`<br>`Default_action_after` |
| `GetVersion` | `GetVersion_action_before`<br>`GetVersion_action_after` |
| `ItemLookup` | `ItemLookup_action_before`<br>`ItemLookup_action_after` |
| `ItemSearch` | `ItemSearch_action_before`<br>`ItemSearch_action_after` |

## 特殊なフックポイント

### 特別に実装されたフックポイント

| フックポイント | 説明 | 用途 |
|-------------|------|------|
| `LC_Page_Products_Detail_action_add_favorite` | お気に入り登録（PC） | 商品お気に入り機能 |
| `LC_Page_Products_Detail_action_add_favorite_sphone` | お気に入り登録（スマートフォン） | モバイルお気に入り機能 |
| `LC_Page_Mypage_DownLoad_action_after` | ダウンロード商品処理後 | ダウンロード商品処理 |
| `SC_Response_action_exit` | レスポンス終了処理 | アプリケーション終了時 |
| `SC_Response_action_redirect` | リダイレクト処理 | ページリダイレクト時 |

### 動的に生成されるフックポイント

modeの値に基づいて動的に生成されるフックポイントの例：

| フックポイントの例 | 説明 |
|-----------------|------|
| `LC_Page_Products_Detail_action_cart` | カート追加処理 |
| `LC_Page_Shopping_Confirm_action_confirm` | 注文確認処理 |
| `LC_Page_Admin_Products_Product_action_edit` | 商品編集処理 |
| `LC_Page_Admin_Order_Edit_action_update` | 受注更新処理 |

## 使用例

### 基本的なプラグインフックポイント登録

```php
<?php
class MyPlugin extends SC_Plugin_Base
{
    public function register($objPluginHelper, $priority)
    {
        // 商品詳細ページの前処理
        $objPluginHelper->addAction('LC_Page_Products_Detail_action_before', 
                                   [$this, 'productDetailBefore'], $priority);
        
        // 全ページの前処理
        $objPluginHelper->addAction('LC_Page_preProcess', 
                                   [$this, 'allPagePreProcess'], $priority);
                                   
        // カート追加処理
        $objPluginHelper->addAction('LC_Page_Products_Detail_action_cart', 
                                   [$this, 'cartAddProcess'], $priority);
    }
    
    public function productDetailBefore($objPage)
    {
        // 商品詳細ページ前のカスタム処理
    }
    
    public function allPagePreProcess($objPage)
    {
        // 全ページ共通のカスタム処理
    }
    
    public function cartAddProcess($objPage)
    {
        // カート追加時のカスタム処理
    }
}
```

### 高度なフックポイント使用例

```php
<?php
class AdvancedPlugin extends SC_Plugin_Base
{
    public function register($objPluginHelper, $priority)
    {
        // 複数のフックポイント登録
        $hookPoints = [
            'LC_Page_Admin_Order_Edit_action_before' => 'beforeOrderEdit',
            'LC_Page_Admin_Order_Edit_action_after' => 'afterOrderEdit',
            'LC_Page_Shopping_Confirm_action_confirm' => 'onOrderConfirm',
            'outputfilterTransform' => 'transformOutput'
        ];
        
        foreach ($hookPoints as $hookPoint => $method) {
            $objPluginHelper->addAction($hookPoint, [$this, $method], $priority);
        }
    }
    
    public function beforeOrderEdit($objPage)
    {
        // 受注編集前の処理
    }
    
    public function afterOrderEdit($objPage)
    {
        // 受注編集後の処理
    }
    
    public function onOrderConfirm($objPage)
    {
        // 注文確認時の処理
    }
    
    public function transformOutput($output, $objSmarty)
    {
        // テンプレート出力の変換
        return $output;
    }
}
```

## 統計情報

- **スーパーフックポイント**: 6個
- **主要ページクラス**: 135個
- **API関連クラス**: 13個
- **理論上利用可能なローカルフックポイント**: 約836個
- **確認済み使用フックポイント**: 21個

## 注意事項

- ローカルフックポイントは、LC_Pageを継承するすべてのクラスで自動的に利用可能です
- Mode固有のフックポイントは、actionExitやsendRedirect実行時に動的に生成されます
- 一部のページ（LC_Page_Mypage_DownLoadなど）では個別にフックポイントが実装されています
- プラグインの実行順序は優先度（priority）の値によって決まります
- フックポイントを活用することで、EC-CUBE2の動作を仮想的にあらゆる処理ポイントで拡張できます

この包括的なリファレンスにより、プラグイン開発者はEC-CUBE2の機能拡張に必要なすべてのフックポイントを活用できます。