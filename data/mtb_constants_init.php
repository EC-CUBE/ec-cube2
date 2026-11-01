<?php
/** フロント表示関連 */
SC_Initial_Ex::defineIfNotDefined('SAMPLE_ADDRESS1', "市区町村名 (例：千代田区神田神保町)");
/** フロント表示関連 */
SC_Initial_Ex::defineIfNotDefined('SAMPLE_ADDRESS2', "番地・ビル名 (例：1-3-5)");
/** ユーザファイル保存先 */
SC_Initial_Ex::defineIfNotDefined('USER_DIR', "user_data/");
/** ユーザファイル保存先 */
SC_Initial_Ex::defineIfNotDefined('USER_REALDIR', HTML_REALDIR . USER_DIR);
/** ユーザー作成ページ等 */
SC_Initial_Ex::defineIfNotDefined('USER_URL', HTTP_URL . USER_DIR);
/** 認証方式 */
SC_Initial_Ex::defineIfNotDefined('AUTH_TYPE', "HMAC");
/** テンプレートファイル保存先 */
SC_Initial_Ex::defineIfNotDefined('USER_PACKAGE_DIR', "packages/");
/** テンプレートファイル保存先 */
SC_Initial_Ex::defineIfNotDefined('USER_TEMPLATE_REALDIR', USER_REALDIR . USER_PACKAGE_DIR);
/** テンプレートファイル一時保存先 */
SC_Initial_Ex::defineIfNotDefined('TEMPLATE_TEMP_REALDIR', HTML_REALDIR . "upload/temp_template/");
/** ユーザー作成画面のデフォルトPHPファイル */
SC_Initial_Ex::defineIfNotDefined('USER_DEF_PHP_REALFILE', DATA_REALDIR . "__default.php");
/** ダウンロードモジュール保存ディレクトリ */
SC_Initial_Ex::defineIfNotDefined('MODULE_DIR', "downloads/module/");
/** ダウンロードモジュール保存ディレクトリ */
SC_Initial_Ex::defineIfNotDefined('MODULE_REALDIR', DATA_REALDIR . MODULE_DIR);
/** DBセッションの有効期限(秒) */
SC_Initial_Ex::defineIfNotDefined('MAX_LIFETIME', 7200);
/** マスターデータキャッシュディレクトリ */
SC_Initial_Ex::defineIfNotDefined('MASTER_DATA_REALDIR', DATA_REALDIR . "cache/");
/** アップデート管理用ファイル格納場所 */
SC_Initial_Ex::defineIfNotDefined('UPDATE_HTTP', "http://www.ec-cube.net/info/index.php");
/** 文字コード */
SC_Initial_Ex::defineIfNotDefined('CHAR_CODE', "UTF-8");
/** ロケール設定 */
SC_Initial_Ex::defineIfNotDefined('LOCALE', "ja_JP.UTF-8");
/** 決済モジュール付与文言 */
SC_Initial_Ex::defineIfNotDefined('ECCUBE_PAYMENT', "EC-CUBE");
/** PEAR::DBのデバッグモード */
SC_Initial_Ex::defineIfNotDefined('PEAR_DB_DEBUG', 0);
/** PEAR::DBの持続的接続オプション */
SC_Initial_Ex::defineIfNotDefined('PEAR_DB_PERSISTENT', false);
/** 締め日の指定(末日の場合は、31を指定してください。) */
SC_Initial_Ex::defineIfNotDefined('CLOSE_DAY', 31);
/** 一般サイトエラー */
SC_Initial_Ex::defineIfNotDefined('FAVORITE_ERROR', 13);
/** グラフ格納ディレクトリ */
SC_Initial_Ex::defineIfNotDefined('GRAPH_REALDIR', HTML_REALDIR . "upload/graph_image/");
/** グラフURL */
SC_Initial_Ex::defineIfNotDefined('GRAPH_URLPATH', ROOT_URLPATH . "upload/graph_image/");
/** 円グラフ最大表示数 */
SC_Initial_Ex::defineIfNotDefined('GRAPH_PIE_MAX', 10);
/** グラフのラベルの文字数 */
SC_Initial_Ex::defineIfNotDefined('GRAPH_LABEL_MAX', 40);
/** 商品集計で何位まで表示するか */
SC_Initial_Ex::defineIfNotDefined('PRODUCTS_TOTAL_MAX', 15);
/** 1:公開 2:非公開 */
SC_Initial_Ex::defineIfNotDefined('DEFAULT_PRODUCT_DISP', 2);
/** 送料無料購入数量 (0の場合は、いくつ買っても無料にならない) */
SC_Initial_Ex::defineIfNotDefined('DELIV_FREE_AMOUNT', 0);
/** 配送料の設定画面表示(有効:1 無効:0) */
SC_Initial_Ex::defineIfNotDefined('INPUT_DELIV_FEE', 1);
/** 商品ごとの送料設定(有効:1 無効:0) */
SC_Initial_Ex::defineIfNotDefined('OPTION_PRODUCT_DELIV_FEE', 0);
/** 配送業者ごとの配送料を加算する(有効:1 無効:0) */
SC_Initial_Ex::defineIfNotDefined('OPTION_DELIV_FEE', 1);
/** おすすめ商品登録(有効:1 無効:0) */
SC_Initial_Ex::defineIfNotDefined('OPTION_RECOMMEND', 1);
/** 商品規格登録(有効:1 無効:0) */
SC_Initial_Ex::defineIfNotDefined('OPTION_CLASS_REGIST', 1);
/** 会員登録変更(マイページ)パスワード用 */
SC_Initial_Ex::defineIfNotDefined('DEFAULT_PASSWORD', "********");
/** 別のお届け先最大登録数 */
SC_Initial_Ex::defineIfNotDefined('DELIV_ADDR_MAX', 20);
/** 対応状況管理画面の一覧表示件数 */
SC_Initial_Ex::defineIfNotDefined('ORDER_STATUS_MAX', 50);
/** フロントレビュー書き込み最大数 */
SC_Initial_Ex::defineIfNotDefined('REVIEW_REGIST_MAX', 5);
/** デバッグモード(true：sfPrintRやDBのエラーメッセージ、ログレベルがDebugのログを出力する、false：出力しない) */
SC_Initial_Ex::defineIfNotDefined('DEBUG_MODE', false);
/** ログを冗長とするか(true:利用する、false:利用しない) */
SC_Initial_Ex::defineIfNotDefined('USE_VERBOSE_LOG', DEBUG_MODE);
/** 管理ユーザID(メンテナンス用表示されない。) */
SC_Initial_Ex::defineIfNotDefined('ADMIN_ID', "1");
/** 会員登録時に仮会員確認メールを送信するか (true:仮会員、false:本会員) */
SC_Initial_Ex::defineIfNotDefined('CUSTOMER_CONFIRM_MAIL', false);
/** ログイン画面フレーム */
SC_Initial_Ex::defineIfNotDefined('LOGIN_FRAME', "login_frame.tpl");
/** 管理画面フレーム */
SC_Initial_Ex::defineIfNotDefined('MAIN_FRAME', "main_frame.tpl");
/** 一般サイト画面フレーム */
SC_Initial_Ex::defineIfNotDefined('SITE_FRAME', "site_frame.tpl");
/** 認証文字列 */
SC_Initial_Ex::defineIfNotDefined('CERT_STRING', "7WDhcBTF");
/** 生年月日登録開始年 */
SC_Initial_Ex::defineIfNotDefined('BIRTH_YEAR', 1901);
/** 本システムの稼働開始年 */
SC_Initial_Ex::defineIfNotDefined('RELEASE_YEAR', 2005);
/** クレジットカードの期限＋何年 */
SC_Initial_Ex::defineIfNotDefined('CREDIT_ADD_YEAR', 10);
/** ポイントの計算ルール(1:四捨五入、2:切り捨て、3:切り上げ) */
SC_Initial_Ex::defineIfNotDefined('POINT_RULE', 2);
/** 1ポイント当たりの値段(円) */
SC_Initial_Ex::defineIfNotDefined('POINT_VALUE', 1);
/** 管理モード 1:有効　0:無効(納品時) */
SC_Initial_Ex::defineIfNotDefined('ADMIN_MODE', 0);
/** ログファイル最大数(ログテーション) */
SC_Initial_Ex::defineIfNotDefined('MAX_LOG_QUANTITY', 5);
/** 1つのログファイルに保存する最大容量(byte) */
SC_Initial_Ex::defineIfNotDefined('MAX_LOG_SIZE', "1000000");
/** トランザクションID の名前 */
SC_Initial_Ex::defineIfNotDefined('TRANSACTION_ID_NAME', "transactionid");
/** パスワード忘れの確認メールを送付するか否か。(0:送信しない、1:送信する) */
SC_Initial_Ex::defineIfNotDefined('FORGOT_MAIL', 0);
/** 誕生日月ポイント */
SC_Initial_Ex::defineIfNotDefined('BIRTH_MONTH_POINT', 0);
/** 拡大画像横 */
SC_Initial_Ex::defineIfNotDefined('LARGE_IMAGE_WIDTH', 500);
/** 拡大画像縦 */
SC_Initial_Ex::defineIfNotDefined('LARGE_IMAGE_HEIGHT', 500);
/** 一覧画像横 */
SC_Initial_Ex::defineIfNotDefined('SMALL_IMAGE_WIDTH', 130);
/** 一覧画像縦 */
SC_Initial_Ex::defineIfNotDefined('SMALL_IMAGE_HEIGHT', 130);
/** 通常画像横 */
SC_Initial_Ex::defineIfNotDefined('NORMAL_IMAGE_WIDTH', 260);
/** 通常画像縦 */
SC_Initial_Ex::defineIfNotDefined('NORMAL_IMAGE_HEIGHT', 260);
/** 通常サブ画像横 */
SC_Initial_Ex::defineIfNotDefined('NORMAL_SUBIMAGE_WIDTH', 200);
/** 通常サブ画像縦 */
SC_Initial_Ex::defineIfNotDefined('NORMAL_SUBIMAGE_HEIGHT', 200);
/** 拡大サブ画像横 */
SC_Initial_Ex::defineIfNotDefined('LARGE_SUBIMAGE_WIDTH', 500);
/** 拡大サブ画像縦 */
SC_Initial_Ex::defineIfNotDefined('LARGE_SUBIMAGE_HEIGHT', 500);
/** 画像サイズ制限(KB) */
SC_Initial_Ex::defineIfNotDefined('IMAGE_SIZE', 1000);
/** CSVサイズ制限(KB) */
SC_Initial_Ex::defineIfNotDefined('CSV_SIZE', 2000);
/** CSVアップロード1行あたりの最大文字数 */
SC_Initial_Ex::defineIfNotDefined('CSV_LINE_MAX', 10000);
/** ファイル管理画面アップ制限(KB) */
SC_Initial_Ex::defineIfNotDefined('FILE_SIZE', 10000);
/** アップできるテンプレートファイル制限(KB) */
SC_Initial_Ex::defineIfNotDefined('TEMPLATE_SIZE', 10000);
/** カテゴリの最大階層 */
SC_Initial_Ex::defineIfNotDefined('LEVEL_MAX', 5);
/** 最大カテゴリ登録数 */
SC_Initial_Ex::defineIfNotDefined('CATEGORY_MAX', 1000);
/** 管理機能タイトル */
SC_Initial_Ex::defineIfNotDefined('ADMIN_TITLE', "EC-CUBE 管理機能");
/** 編集時強調表示色 */
SC_Initial_Ex::defineIfNotDefined('SELECT_RGB', "#ffffdf");
/** 入力項目無効時の表示色 */
SC_Initial_Ex::defineIfNotDefined('DISABLED_RGB', "#C9C9C9");
/** エラー時表示色 */
SC_Initial_Ex::defineIfNotDefined('ERR_COLOR', "#ffe8e8");
/** 親カテゴリ表示文字 */
SC_Initial_Ex::defineIfNotDefined('CATEGORY_HEAD', ">");
/** 生年月日初期選択年 */
SC_Initial_Ex::defineIfNotDefined('START_BIRTH_YEAR', 1970);
/** 価格名称 */
SC_Initial_Ex::defineIfNotDefined('NORMAL_PRICE_TITLE', "通常価格");
/** 価格名称 */
SC_Initial_Ex::defineIfNotDefined('SALE_PRICE_TITLE', "販売価格");
/** 標準ログファイル */
SC_Initial_Ex::defineIfNotDefined('LOG_REALFILE', DATA_REALDIR . "logs/site.log");
/** 会員ログイン ログファイル */
SC_Initial_Ex::defineIfNotDefined('CUSTOMER_LOG_REALFILE', DATA_REALDIR . "logs/customer.log");
/** 管理機能ログファイル */
SC_Initial_Ex::defineIfNotDefined('ADMIN_LOG_REALFILE', DATA_REALDIR . "logs/admin.log");
/** デバッグログファイル(未入力:標準ログファイル・管理画面ログファイル) */
SC_Initial_Ex::defineIfNotDefined('DEBUG_LOG_REALFILE', "");
/** エラーログファイル(未入力:標準ログファイル・管理画面ログファイル) */
SC_Initial_Ex::defineIfNotDefined('ERROR_LOG_REALFILE', DATA_REALDIR . "logs/error.log");
/** DBログファイル */
SC_Initial_Ex::defineIfNotDefined('DB_LOG_REALFILE', DATA_REALDIR . "logs/db.log");
/** プラグインログファイル */
SC_Initial_Ex::defineIfNotDefined('PLUGIN_LOG_REALFILE', DATA_REALDIR . "logs/plugin.log");
/** 画像一時保存 */
SC_Initial_Ex::defineIfNotDefined('IMAGE_TEMP_REALDIR', HTML_REALDIR . "upload/temp_image/");
/** 画像保存先 */
SC_Initial_Ex::defineIfNotDefined('IMAGE_SAVE_REALDIR', HTML_REALDIR . "upload/save_image/");
/** 画像一時保存URL */
SC_Initial_Ex::defineIfNotDefined('IMAGE_TEMP_URLPATH', ROOT_URLPATH . "upload/temp_image/");
/** 画像保存先URL */
SC_Initial_Ex::defineIfNotDefined('IMAGE_SAVE_URLPATH', ROOT_URLPATH . "upload/save_image/");
/** RSS用画像一時保存URL */
SC_Initial_Ex::defineIfNotDefined('IMAGE_TEMP_RSS_URL', HTTP_URL . "upload/temp_image/");
/** RSS用画像保存先URL */
SC_Initial_Ex::defineIfNotDefined('IMAGE_SAVE_RSS_URL', HTTP_URL . "upload/save_image/");
/** エンコードCSVの一時保存先 */
SC_Initial_Ex::defineIfNotDefined('CSV_TEMP_REALDIR', DATA_REALDIR . "upload/csv/");
/** 画像がない場合に表示 */
SC_Initial_Ex::defineIfNotDefined('NO_IMAGE_REALFILE', USER_TEMPLATE_REALDIR . "default/img/picture/img_blank.gif");
/** システム管理トップ */
SC_Initial_Ex::defineIfNotDefined('ADMIN_SYSTEM_URLPATH', ROOT_URLPATH . ADMIN_DIR . "system/" . DIR_INDEX_PATH);
/** 郵便番号入力 */
SC_Initial_Ex::defineIfNotDefined('INPUT_ZIP_URLPATH', ROOT_URLPATH . "input_zip.php");
/** ホーム */
SC_Initial_Ex::defineIfNotDefined('ADMIN_HOME_URLPATH', ROOT_URLPATH . ADMIN_DIR . "home.php");
/** ログインページ */
SC_Initial_Ex::defineIfNotDefined('ADMIN_LOGIN_URLPATH', ROOT_URLPATH . ADMIN_DIR . DIR_INDEX_PATH);
/** 商品検索ページ */
SC_Initial_Ex::defineIfNotDefined('ADMIN_PRODUCTS_URLPATH', ROOT_URLPATH . ADMIN_DIR . "products/" . DIR_INDEX_PATH);
/** 注文編集ページ */
SC_Initial_Ex::defineIfNotDefined('ADMIN_ORDER_EDIT_URLPATH', ROOT_URLPATH . ADMIN_DIR . "order/edit.php");
/** 注文編集ページ */
SC_Initial_Ex::defineIfNotDefined('ADMIN_ORDER_URLPATH', ROOT_URLPATH . ADMIN_DIR . "order/" . DIR_INDEX_PATH);
/** 注文編集ページ */
SC_Initial_Ex::defineIfNotDefined('ADMIN_ORDER_MAIL_URLPATH', ROOT_URLPATH . ADMIN_DIR . "order/mail.php");
/** ログアウトページ */
SC_Initial_Ex::defineIfNotDefined('ADMIN_LOGOUT_URLPATH', ROOT_URLPATH . ADMIN_DIR . "logout.php");
/** メンバー管理ページ表示行数 */
SC_Initial_Ex::defineIfNotDefined('MEMBER_PMAX', 10);
/** 検索ページ表示行数 */
SC_Initial_Ex::defineIfNotDefined('SEARCH_PMAX', 10);
/** ページ番号の最大表示数量 */
SC_Initial_Ex::defineIfNotDefined('NAVI_PMAX', 4);
/** 商品サブ情報最大数 */
SC_Initial_Ex::defineIfNotDefined('PRODUCTSUB_MAX', 5);
/** お届け時間の最大表示数 */
SC_Initial_Ex::defineIfNotDefined('DELIVTIME_MAX', 16);
/** 配送料金の最大表示数 */
SC_Initial_Ex::defineIfNotDefined('DELIVFEE_MAX', 47);
/** 短い項目の文字数 (名前など) */
SC_Initial_Ex::defineIfNotDefined('STEXT_LEN', 50);
SC_Initial_Ex::defineIfNotDefined('SMTEXT_LEN', 100);
/** 長い項目の文字数 (住所など) */
SC_Initial_Ex::defineIfNotDefined('MTEXT_LEN', 200);
/** 長中文の文字数 (問い合わせなど) */
SC_Initial_Ex::defineIfNotDefined('MLTEXT_LEN', 1000);
/** 長文の文字数 */
SC_Initial_Ex::defineIfNotDefined('LTEXT_LEN', 3000);
/** 超長文の文字数 (メルマガなど) */
SC_Initial_Ex::defineIfNotDefined('LLTEXT_LEN', 99999);
/** URLの文字長 */
SC_Initial_Ex::defineIfNotDefined('URL_LEN', 1024);
/** 管理画面用：ID・パスワードの最大文字数 */
SC_Initial_Ex::defineIfNotDefined('ID_MAX_LEN', STEXT_LEN);
/** 管理画面用：ID・パスワードの最小文字数 */
SC_Initial_Ex::defineIfNotDefined('ID_MIN_LEN', 4);
/** 金額桁数 */
SC_Initial_Ex::defineIfNotDefined('PRICE_LEN', 8);
/** 率桁数 */
SC_Initial_Ex::defineIfNotDefined('PERCENTAGE_LEN', 3);
/** 在庫数、販売制限数 */
SC_Initial_Ex::defineIfNotDefined('AMOUNT_LEN', 6);
/** 郵便番号1 */
SC_Initial_Ex::defineIfNotDefined('ZIP01_LEN', 3);
/** 郵便番号2 */
SC_Initial_Ex::defineIfNotDefined('ZIP02_LEN', 4);
/** 電話番号各項目制限 */
SC_Initial_Ex::defineIfNotDefined('TEL_ITEM_LEN', 6);
/** 電話番号総数 */
SC_Initial_Ex::defineIfNotDefined('TEL_LEN', 12);
/** フロント画面用：パスワードの最小文字数 */
SC_Initial_Ex::defineIfNotDefined('PASSWORD_MIN_LEN', 8);
/** フロント画面用：パスワードの最大文字数 */
SC_Initial_Ex::defineIfNotDefined('PASSWORD_MAX_LEN', SMTEXT_LEN);
/** 検査数値用桁数(INT) */
SC_Initial_Ex::defineIfNotDefined('INT_LEN', 9);
/** クレジットカードの文字数 (*モジュールで使用) */
SC_Initial_Ex::defineIfNotDefined('CREDIT_NO_LEN', 4);
/** 検索カテゴリ最大表示文字数(byte) */
SC_Initial_Ex::defineIfNotDefined('SEARCH_CATEGORY_LEN', 18);
/** ファイル名表示文字数 */
SC_Initial_Ex::defineIfNotDefined('FILE_NAME_LEN', 10);
/** クッキー保持期限(日) */
SC_Initial_Ex::defineIfNotDefined('COOKIE_EXPIRE', 365);
/** カテゴリ区切り文字 */
SC_Initial_Ex::defineIfNotDefined('SEPA_CATNAVI', " > ");
/** 会員情報入力 */
SC_Initial_Ex::defineIfNotDefined('SHOPPING_URL', HTTPS_URL . "shopping/" . DIR_INDEX_PATH);
/** 会員登録ページTOP */
SC_Initial_Ex::defineIfNotDefined('ENTRY_URL', HTTPS_URL . "entry/" . DIR_INDEX_PATH);
/** サイトトップ */
SC_Initial_Ex::defineIfNotDefined('TOP_URL', HTTP_URL . DIR_INDEX_PATH);
/** カートトップ */
SC_Initial_Ex::defineIfNotDefined('CART_URL', HTTP_URL . "cart/" . DIR_INDEX_PATH);
/** お届け先設定 */
SC_Initial_Ex::defineIfNotDefined('DELIV_URLPATH', ROOT_URLPATH . "shopping/deliv.php");
/** 複数お届け先設定 */
SC_Initial_Ex::defineIfNotDefined('MULTIPLE_URLPATH', ROOT_URLPATH . "shopping/multiple.php");
/** 購入確認ページ */
SC_Initial_Ex::defineIfNotDefined('SHOPPING_CONFIRM_URLPATH', ROOT_URLPATH . "shopping/confirm.php");
/** お支払い方法選択ページ */
SC_Initial_Ex::defineIfNotDefined('SHOPPING_PAYMENT_URLPATH', ROOT_URLPATH . "shopping/payment.php");
/** 購入完了画面 */
SC_Initial_Ex::defineIfNotDefined('SHOPPING_COMPLETE_URLPATH', ROOT_URLPATH . "shopping/complete.php");
/** モジュール追加用画面 */
SC_Initial_Ex::defineIfNotDefined('SHOPPING_MODULE_URLPATH', ROOT_URLPATH . "shopping/load_payment_module.php");
/** 商品詳細(HTML出力) */
SC_Initial_Ex::defineIfNotDefined('P_DETAIL_URLPATH', ROOT_URLPATH . "products/detail.php?product_id=");
/** マイページお届け先URL */
SC_Initial_Ex::defineIfNotDefined('MYPAGE_DELIVADDR_URLPATH', ROOT_URLPATH . "mypage/delivery.php");
/** 新着情報管理画面 開始年(西暦) */
SC_Initial_Ex::defineIfNotDefined('ADMIN_NEWS_STARTYEAR', 2005);
/** 再入会制限時間 (単位: 時間) */
SC_Initial_Ex::defineIfNotDefined('ENTRY_LIMIT_HOUR', 1);
/** 関連商品表示数 */
SC_Initial_Ex::defineIfNotDefined('RECOMMEND_PRODUCT_MAX', 6);
/** おすすめ商品表示数 */
SC_Initial_Ex::defineIfNotDefined('RECOMMEND_NUM', 8);
/** お届け可能日以降のプルダウン表示最大日数 */
SC_Initial_Ex::defineIfNotDefined('DELIV_DATE_END_MAX', 21);
/** 支払期限 (*モジュールで使用) */
SC_Initial_Ex::defineIfNotDefined('CV_PAYMENT_LIMIT', 14);
/** 商品レビューでURL書き込みを許可するか否か */
SC_Initial_Ex::defineIfNotDefined('REVIEW_ALLOW_URL', 0);
/** アップデート時にサイト情報を送出するか */
SC_Initial_Ex::defineIfNotDefined('UPDATE_SEND_SITE_INFO', false);
/** ポイントを利用するか(true:利用する、false:利用しない) (false は一部対応) */
SC_Initial_Ex::defineIfNotDefined('USE_POINT', true);
/** 在庫無し商品の非表示(true:非表示、false:表示) */
SC_Initial_Ex::defineIfNotDefined('NOSTOCK_HIDDEN', false);
/** モバイルサイトを利用するか(true:利用する、false:利用しない) (false は一部対応) (*モジュールで使用) */
SC_Initial_Ex::defineIfNotDefined('USE_MOBILE', true);
/** 複数配送先指定機能を利用するか(true:利用する、false:利用しない) */
SC_Initial_Ex::defineIfNotDefined('USE_MULTIPLE_SHIPPING', true);
/** 短文の文字数 */
SC_Initial_Ex::defineIfNotDefined('SLTEXT_LEN', 500);
/** デフォルトテンプレート名(PC) */
SC_Initial_Ex::defineIfNotDefined('DEFAULT_TEMPLATE_NAME', "default");
/** デフォルトテンプレート名(モバイル) */
SC_Initial_Ex::defineIfNotDefined('MOBILE_DEFAULT_TEMPLATE_NAME', "mobile");
/** デフォルトテンプレート名(スマートフォン) */
SC_Initial_Ex::defineIfNotDefined('SMARTPHONE_DEFAULT_TEMPLATE_NAME', "sphone");
/** テンプレート名 */
SC_Initial_Ex::defineIfNotDefined('TEMPLATE_NAME', "default");
/** モバイルテンプレート名 */
SC_Initial_Ex::defineIfNotDefined('MOBILE_TEMPLATE_NAME', "mobile");
/** スマートフォンテンプレート名 */
SC_Initial_Ex::defineIfNotDefined('SMARTPHONE_TEMPLATE_NAME', "sphone");
/** SMARTYテンプレート */
SC_Initial_Ex::defineIfNotDefined('SMARTY_TEMPLATES_REALDIR',  DATA_REALDIR . "Smarty/templates/");
/** SMARTYテンプレート(PC) */
SC_Initial_Ex::defineIfNotDefined('TEMPLATE_REALDIR', SMARTY_TEMPLATES_REALDIR . TEMPLATE_NAME . "/");
/** SMARTYテンプレート(管理機能) */
SC_Initial_Ex::defineIfNotDefined('TEMPLATE_ADMIN_REALDIR', SMARTY_TEMPLATES_REALDIR . "admin/");
/** SMARTYコンパイル */
SC_Initial_Ex::defineIfNotDefined('COMPILE_REALDIR', DATA_REALDIR . "Smarty/templates_c/" . TEMPLATE_NAME . "/");
/** SMARTYコンパイル(管理機能) */
SC_Initial_Ex::defineIfNotDefined('COMPILE_ADMIN_REALDIR', DATA_REALDIR . "Smarty/templates_c/admin/");
/** ブロックファイル保存先 */
SC_Initial_Ex::defineIfNotDefined('BLOC_DIR', "frontparts/bloc/");
/** SMARTYテンプレート(mobile) */
SC_Initial_Ex::defineIfNotDefined('MOBILE_TEMPLATE_REALDIR', SMARTY_TEMPLATES_REALDIR . MOBILE_TEMPLATE_NAME . "/");
/** SMARTYコンパイル(mobile) */
SC_Initial_Ex::defineIfNotDefined('MOBILE_COMPILE_REALDIR', DATA_REALDIR . "Smarty/templates_c/" . MOBILE_TEMPLATE_NAME . "/");
/** SMARTYテンプレート(smart phone) */
SC_Initial_Ex::defineIfNotDefined('SMARTPHONE_TEMPLATE_REALDIR', SMARTY_TEMPLATES_REALDIR . SMARTPHONE_TEMPLATE_NAME . "/");
/** SMARTYコンパイル(smartphone) */
SC_Initial_Ex::defineIfNotDefined('SMARTPHONE_COMPILE_REALDIR', DATA_REALDIR . "Smarty/templates_c/" . SMARTPHONE_TEMPLATE_NAME . "/");
/** EメールアドレスチェックをRFC準拠にするか(true:準拠する、false:準拠しない) */
SC_Initial_Ex::defineIfNotDefined('RFC_COMPLIANT_EMAIL_CHECK', false);
/** モバイルサイトのセッションの存続時間 (秒) */
SC_Initial_Ex::defineIfNotDefined('MOBILE_SESSION_LIFETIME', 1800);
/** 携帯電話向け変換画像保存ディレクトリ */
SC_Initial_Ex::defineIfNotDefined('MOBILE_IMAGE_REALDIR', HTML_REALDIR . "upload/mobile_image/");
/** 携帯電話向け変換画像保存ディレクトリ */
SC_Initial_Ex::defineIfNotDefined('MOBILE_IMAGE_URLPATH', ROOT_URLPATH . "upload/mobile_image/");
/** モバイルURL */
SC_Initial_Ex::defineIfNotDefined('MOBILE_TOP_URLPATH', ROOT_URLPATH . DIR_INDEX_PATH);
/** カートトップ */
SC_Initial_Ex::defineIfNotDefined('MOBILE_CART_URLPATH', ROOT_URLPATH . "cart/" . DIR_INDEX_PATH);
/** 購入確認ページ */
SC_Initial_Ex::defineIfNotDefined('MOBILE_SHOPPING_CONFIRM_URLPATH', ROOT_URLPATH . "shopping/confirm.php");
/** お支払い方法選択ページ */
SC_Initial_Ex::defineIfNotDefined('MOBILE_SHOPPING_PAYMENT_URLPATH', ROOT_URLPATH . "shopping/payment.php");
/** 商品詳細(HTML出力) */
SC_Initial_Ex::defineIfNotDefined('MOBILE_P_DETAIL_URLPATH', ROOT_URLPATH . "products/detail.php?product_id=");
/** 購入完了画面 (*モジュールで使用) */
SC_Initial_Ex::defineIfNotDefined('MOBILE_SHOPPING_COMPLETE_URLPATH', ROOT_URLPATH . "shopping/complete.php");
/** セッション維持方法："useCookie"|"useRequest" */
SC_Initial_Ex::defineIfNotDefined('SESSION_KEEP_METHOD', "useCookie");
/** セッションの存続時間 (秒) */
SC_Initial_Ex::defineIfNotDefined('SESSION_LIFETIME', 1800);
/** オーナーズストアURL */
SC_Initial_Ex::defineIfNotDefined('OSTORE_URL', "http://www.ec-cube.net/");
/** オーナーズストアURL */
SC_Initial_Ex::defineIfNotDefined('OSTORE_SSLURL', "https://www.ec-cube.net/");
/** オーナーズストアログパス */
SC_Initial_Ex::defineIfNotDefined('OSTORE_LOG_REALFILE', DATA_REALDIR . "logs/ownersstore.log");
/** お気に入り商品登録(有効:1 無効:0) */
SC_Initial_Ex::defineIfNotDefined('OPTION_FAVORITE_PRODUCT', 1);
/** 画像リネーム設定 (商品画像のみ) (true:リネームする、false:リネームしない) */
SC_Initial_Ex::defineIfNotDefined('IMAGE_RENAME', true);
/** (2.11用)プラグインディレクトリ(モジュールで使用) */
SC_Initial_Ex::defineIfNotDefined('PLUGIN_DIR', "plugins/");
/** (2.11用)プラグイン保存先(モジュールで使用) */
SC_Initial_Ex::defineIfNotDefined('PLUGIN_REALDIR', USER_REALDIR . PLUGIN_DIR);
/** プラグイン保存先ディレクトリ */
SC_Initial_Ex::defineIfNotDefined('PLUGIN_UPLOAD_REALDIR', DATA_REALDIR . "downloads/plugin/");
/** プラグイン保存先ディレクトリ(html) */
SC_Initial_Ex::defineIfNotDefined('PLUGIN_HTML_REALDIR', HTML_REALDIR . "plugin/");
/** プラグインファイル一時保存先 */
SC_Initial_Ex::defineIfNotDefined('PLUGIN_TEMP_REALDIR', HTML_REALDIR . "upload/temp_plugin/");
/** プラグインファイル登録可能拡張子(カンマ区切り) */
SC_Initial_Ex::defineIfNotDefined('PLUGIN_EXTENSION', "tar,tar.gz");
/** プラグイン一時展開用ディレクトリ（アップデート用） */
SC_Initial_Ex::defineIfNotDefined('DOWNLOADS_TEMP_PLUGIN_UPDATE_DIR', DATA_REALDIR . "downloads/tmp/plugin_update/");
/** プラグイン一時展開用ディレクトリ（インストール用） */
SC_Initial_Ex::defineIfNotDefined('DOWNLOADS_TEMP_PLUGIN_INSTALL_DIR', DATA_REALDIR . "downloads/tmp/plugin_install/");
/** プラグインURL */
SC_Initial_Ex::defineIfNotDefined('PLUGIN_HTML_URLPATH', ROOT_URLPATH . "plugin/");
/** 日数桁数 */
SC_Initial_Ex::defineIfNotDefined('DOWNLOAD_DAYS_LEN', 3);
/** ダウンロードファイル登録可能拡張子(カンマ区切り) */
SC_Initial_Ex::defineIfNotDefined('DOWNLOAD_EXTENSION', "zip,lzh,jpg,jpeg,gif,png,mp3,pdf,csv");
/** ダウンロード販売ファイル用サイズ制限(KB) */
SC_Initial_Ex::defineIfNotDefined('DOWN_SIZE', 50000);
/** 1:実商品 2:ダウンロード */
SC_Initial_Ex::defineIfNotDefined('DEFAULT_PRODUCT_DOWN', 1);
/** ダウンロードファイル一時保存 */
SC_Initial_Ex::defineIfNotDefined('DOWN_TEMP_REALDIR', DATA_REALDIR . "download/temp/");
/** ダウンロードファイル保存先 */
SC_Initial_Ex::defineIfNotDefined('DOWN_SAVE_REALDIR', DATA_REALDIR . "download/save/");
/** ダウンロード販売機能 ダウンロードファイル読み込みバイト(KB) */
SC_Initial_Ex::defineIfNotDefined('DOWNLOAD_BLOCK', 1024);
/** 新規注文 */
SC_Initial_Ex::defineIfNotDefined('ORDER_NEW', 1);
/** 入金待ち */
SC_Initial_Ex::defineIfNotDefined('ORDER_PAY_WAIT', 2);
/** 入金済み */
SC_Initial_Ex::defineIfNotDefined('ORDER_PRE_END', 6);
/** キャンセル */
SC_Initial_Ex::defineIfNotDefined('ORDER_CANCEL', 3);
/** 取り寄せ中 */
SC_Initial_Ex::defineIfNotDefined('ORDER_BACK_ORDER', 4);
/** 発送済み */
SC_Initial_Ex::defineIfNotDefined('ORDER_DELIV', 5);
/** 決済処理中 */
SC_Initial_Ex::defineIfNotDefined('ORDER_PENDING', 7);
/** 通常商品 */
SC_Initial_Ex::defineIfNotDefined('PRODUCT_TYPE_NORMAL', 1);
/** ダウンロード商品 */
SC_Initial_Ex::defineIfNotDefined('PRODUCT_TYPE_DOWNLOAD', 2);
/** DBログの記録モード (0:記録しない, 1:遅延時のみ記録する, 2:常に記録する) */
SC_Initial_Ex::defineIfNotDefined('SQL_QUERY_LOG_MODE', 1);
/** DBログで遅延とみなす実行時間(秒) */
SC_Initial_Ex::defineIfNotDefined('SQL_QUERY_LOG_MIN_EXEC_TIME', 2);
/** ページ表示時間のログを取得するフラグ(1:表示, 0:非表示) */
SC_Initial_Ex::defineIfNotDefined('PAGE_DISPLAY_TIME_LOG_MODE', 1);
/** ページ表示時間のログを取得する時間設定(設定値以上かかった場合に取得) */
SC_Initial_Ex::defineIfNotDefined('PAGE_DISPLAY_TIME_LOG_MIN_EXEC_TIME', 2);
/** 端末種別: モバイル */
SC_Initial_Ex::defineIfNotDefined('DEVICE_TYPE_MOBILE', 1);
/** 端末種別: スマートフォン */
SC_Initial_Ex::defineIfNotDefined('DEVICE_TYPE_SMARTPHONE', 2);
/** 端末種別: PC */
SC_Initial_Ex::defineIfNotDefined('DEVICE_TYPE_PC', 10);
/** 端末種別: 管理画面 */
SC_Initial_Ex::defineIfNotDefined('DEVICE_TYPE_ADMIN', 99);
/** EC-CUBE更新情報取得 (true:取得する false:取得しない) */
SC_Initial_Ex::defineIfNotDefined('ECCUBE_INFO', true);
/** 外部サイトHTTP取得タイムアウト時間(秒) */
SC_Initial_Ex::defineIfNotDefined('HTTP_REQUEST_TIMEOUT', "5");
/** 郵便番号CSVのZIPアーカイブファイルの取得元 */
SC_Initial_Ex::defineIfNotDefined('ZIP_DOWNLOAD_URL', "https://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip");
/** フックポイント(プレプロセス) */
SC_Initial_Ex::defineIfNotDefined('HOOK_POINT_PREPROCESS', "LC_Page_preProcess");
/** フックポイント(プロセス) */
SC_Initial_Ex::defineIfNotDefined('HOOK_POINT_PROCESS', "LC_Page_process");
/** プラグインのロード可否フラグ) */
SC_Initial_Ex::defineIfNotDefined('PLUGIN_ACTIVATE_FLAG', true);
/** SMARTYコンパイルモード */
SC_Initial_Ex::defineIfNotDefined('SMARTY_FORCE_COMPILE_MODE', false);
/** ログイン失敗時の遅延時間(秒)(ブルートフォースアタック対策) */
SC_Initial_Ex::defineIfNotDefined('LOGIN_RETRY_INTERVAL', 0);
/** MYページ：ご注文状況表示フラグ */
SC_Initial_Ex::defineIfNotDefined('MYPAGE_ORDER_STATUS_DISP_FLAG', true);
/** デフォルト国コード ISO_3166-1に準拠 */
SC_Initial_Ex::defineIfNotDefined('DEFAULT_COUNTRY_ID', 392);
/** ホスト名を正規化するか (true:する false:しない) */
SC_Initial_Ex::defineIfNotDefined('USE_NORMALIZE_HOSTNAME', true);
/** 各種フォームで国の指定を有効にする(true:有効 false:無効) */
SC_Initial_Ex::defineIfNotDefined('FORM_COUNTRY_ENABLE', false);
/** 商品ごとの税率設定(軽減税率対応 有効:1 無効:0) */
SC_Initial_Ex::defineIfNotDefined('OPTION_PRODUCT_TAX_RULE', 0);
/** 複数箇所の税率設定時における優先度設定。カンマ区切りスペース不可で記述。後に書いてあるキーに一致するほど優先される。デフォルト：'product_id,product_class_id,pref_id,country_id'（国＞地域（県）＞規格単位＞商品単位） */
SC_Initial_Ex::defineIfNotDefined('TAX_RULE_PRIORITY', "product_id,product_class_id,pref_id,country_id");
/** 決済処理中ステータスのロールバックを行う時間の設定(秒) */
SC_Initial_Ex::defineIfNotDefined('PENDING_ORDER_CANCEL_TIME', 900);
/** 決済処理中ステータスのロールバックをするか(true:する false:しない) */
SC_Initial_Ex::defineIfNotDefined('PENDING_ORDER_CANCEL_FLAG', true);
/** API機能を有効にする(true:する false:しない) */
SC_Initial_Ex::defineIfNotDefined('API_ENABLE_FLAG', false);
/** UTF-8依存文字が入力された際に表示する文字(Unicode値の整数 デフォルト: ?) */
SC_Initial_Ex::defineIfNotDefined('SUBSTITUTE_CHAR', 63);
