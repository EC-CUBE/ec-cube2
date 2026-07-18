<?php
/** フロント表示関連 */
defined('SAMPLE_ADDRESS1') or define('SAMPLE_ADDRESS1', "市区町村名 (例：千代田区神田神保町)");
/** フロント表示関連 */
defined('SAMPLE_ADDRESS2') or define('SAMPLE_ADDRESS2', "番地・ビル名 (例：1-3-5)");
/** ユーザファイル保存先 */
defined('USER_DIR') or define('USER_DIR', "user_data/");
/** ユーザファイル保存先 */
defined('USER_REALDIR') or define('USER_REALDIR', HTML_REALDIR . USER_DIR);
/** ユーザー作成ページ等 */
defined('USER_URL') or define('USER_URL', HTTP_URL . USER_DIR);
/** 認証方式 */
defined('AUTH_TYPE') or define('AUTH_TYPE', "HMAC");
/** テンプレートファイル保存先 */
defined('USER_PACKAGE_DIR') or define('USER_PACKAGE_DIR', "packages/");
/** テンプレートファイル保存先 */
defined('USER_TEMPLATE_REALDIR') or define('USER_TEMPLATE_REALDIR', USER_REALDIR . USER_PACKAGE_DIR);
/** テンプレートファイル一時保存先 */
defined('TEMPLATE_TEMP_REALDIR') or define('TEMPLATE_TEMP_REALDIR', HTML_REALDIR . "upload/temp_template/");
/** ユーザー作成画面のデフォルトPHPファイル */
defined('USER_DEF_PHP_REALFILE') or define('USER_DEF_PHP_REALFILE', DATA_REALDIR . "__default.php");
/** ダウンロードモジュール保存ディレクトリ */
defined('MODULE_DIR') or define('MODULE_DIR', "downloads/module/");
/** ダウンロードモジュール保存ディレクトリ */
defined('MODULE_REALDIR') or define('MODULE_REALDIR', DATA_REALDIR . MODULE_DIR);
/** DBセッションの有効期限(秒) */
defined('MAX_LIFETIME') or define('MAX_LIFETIME', 7200);
/** マスターデータキャッシュディレクトリ */
defined('MASTER_DATA_REALDIR') or define('MASTER_DATA_REALDIR', DATA_REALDIR . "cache/");
/** アップデート管理用ファイル格納場所 */
defined('UPDATE_HTTP') or define('UPDATE_HTTP', "http://www.ec-cube.net/info/index.php");
/** 文字コード */
defined('CHAR_CODE') or define('CHAR_CODE', "UTF-8");
/** ロケール設定 */
defined('LOCALE') or define('LOCALE', "ja_JP.UTF-8");
/** 決済モジュール付与文言 */
defined('ECCUBE_PAYMENT') or define('ECCUBE_PAYMENT', "EC-CUBE");
/** PEAR::DBのデバッグモード */
defined('PEAR_DB_DEBUG') or define('PEAR_DB_DEBUG', 0);
/** PEAR::DBの持続的接続オプション */
defined('PEAR_DB_PERSISTENT') or define('PEAR_DB_PERSISTENT', false);
/** 締め日の指定(末日の場合は、31を指定してください。) */
defined('CLOSE_DAY') or define('CLOSE_DAY', 31);
/** 一般サイトエラー */
defined('FAVORITE_ERROR') or define('FAVORITE_ERROR', 13);
/** グラフ格納ディレクトリ */
defined('GRAPH_REALDIR') or define('GRAPH_REALDIR', HTML_REALDIR . "upload/graph_image/");
/** グラフURL */
defined('GRAPH_URLPATH') or define('GRAPH_URLPATH', ROOT_URLPATH . "upload/graph_image/");
/** 円グラフ最大表示数 */
defined('GRAPH_PIE_MAX') or define('GRAPH_PIE_MAX', 10);
/** グラフのラベルの文字数 */
defined('GRAPH_LABEL_MAX') or define('GRAPH_LABEL_MAX', 40);
/** 商品集計で何位まで表示するか */
defined('PRODUCTS_TOTAL_MAX') or define('PRODUCTS_TOTAL_MAX', 15);
/** 1:公開 2:非公開 */
defined('DEFAULT_PRODUCT_DISP') or define('DEFAULT_PRODUCT_DISP', 2);
/** 送料無料購入数量 (0の場合は、いくつ買っても無料にならない) */
defined('DELIV_FREE_AMOUNT') or define('DELIV_FREE_AMOUNT', 0);
/** 配送料の設定画面表示(有効:1 無効:0) */
defined('INPUT_DELIV_FEE') or define('INPUT_DELIV_FEE', 1);
/** 商品ごとの送料設定(有効:1 無効:0) */
defined('OPTION_PRODUCT_DELIV_FEE') or define('OPTION_PRODUCT_DELIV_FEE', 0);
/** 配送業者ごとの配送料を加算する(有効:1 無効:0) */
defined('OPTION_DELIV_FEE') or define('OPTION_DELIV_FEE', 1);
/** おすすめ商品登録(有効:1 無効:0) */
defined('OPTION_RECOMMEND') or define('OPTION_RECOMMEND', 1);
/** 商品規格登録(有効:1 無効:0) */
defined('OPTION_CLASS_REGIST') or define('OPTION_CLASS_REGIST', 1);
/** 会員登録変更(マイページ)パスワード用 */
defined('DEFAULT_PASSWORD') or define('DEFAULT_PASSWORD', "********");
/** 別のお届け先最大登録数 */
defined('DELIV_ADDR_MAX') or define('DELIV_ADDR_MAX', 20);
/** 対応状況管理画面の一覧表示件数 */
defined('ORDER_STATUS_MAX') or define('ORDER_STATUS_MAX', 50);
/** フロントレビュー書き込み最大数 */
defined('REVIEW_REGIST_MAX') or define('REVIEW_REGIST_MAX', 5);
/** デバッグモード(true：sfPrintRやDBのエラーメッセージ、ログレベルがDebugのログを出力する、false：出力しない) */
defined('DEBUG_MODE') or define('DEBUG_MODE', false);
/** ログを冗長とするか(true:利用する、false:利用しない) */
defined('USE_VERBOSE_LOG') or define('USE_VERBOSE_LOG', DEBUG_MODE);
/** 管理ユーザID(メンテナンス用表示されない。) */
defined('ADMIN_ID') or define('ADMIN_ID', "1");
/** 会員登録時に仮会員確認メールを送信するか (true:仮会員、false:本会員) */
defined('CUSTOMER_CONFIRM_MAIL') or define('CUSTOMER_CONFIRM_MAIL', false);
/** ログイン画面フレーム */
defined('LOGIN_FRAME') or define('LOGIN_FRAME', "login_frame.tpl");
/** 管理画面フレーム */
defined('MAIN_FRAME') or define('MAIN_FRAME', "main_frame.tpl");
/** 一般サイト画面フレーム */
defined('SITE_FRAME') or define('SITE_FRAME', "site_frame.tpl");
/** 認証文字列 */
defined('CERT_STRING') or define('CERT_STRING', "7WDhcBTF");
/** 生年月日登録開始年 */
defined('BIRTH_YEAR') or define('BIRTH_YEAR', 1901);
/** 本システムの稼働開始年 */
defined('RELEASE_YEAR') or define('RELEASE_YEAR', 2005);
/** クレジットカードの期限＋何年 */
defined('CREDIT_ADD_YEAR') or define('CREDIT_ADD_YEAR', 10);
/** ポイントの計算ルール(1:四捨五入、2:切り捨て、3:切り上げ) */
defined('POINT_RULE') or define('POINT_RULE', 2);
/** 1ポイント当たりの値段(円) */
defined('POINT_VALUE') or define('POINT_VALUE', 1);
/** 管理モード 1:有効　0:無効(納品時) */
defined('ADMIN_MODE') or define('ADMIN_MODE', 0);
/** ログファイル最大数(ログテーション) */
defined('MAX_LOG_QUANTITY') or define('MAX_LOG_QUANTITY', 5);
/** 1つのログファイルに保存する最大容量(byte) */
defined('MAX_LOG_SIZE') or define('MAX_LOG_SIZE', "1000000");
/** トランザクションID の名前 */
defined('TRANSACTION_ID_NAME') or define('TRANSACTION_ID_NAME', "transactionid");
/** パスワード忘れの確認メールを送付するか否か。(0:送信しない、1:送信する) */
defined('FORGOT_MAIL') or define('FORGOT_MAIL', 0);
/** 誕生日月ポイント */
defined('BIRTH_MONTH_POINT') or define('BIRTH_MONTH_POINT', 0);
/** 拡大画像横 */
defined('LARGE_IMAGE_WIDTH') or define('LARGE_IMAGE_WIDTH', 500);
/** 拡大画像縦 */
defined('LARGE_IMAGE_HEIGHT') or define('LARGE_IMAGE_HEIGHT', 500);
/** 一覧画像横 */
defined('SMALL_IMAGE_WIDTH') or define('SMALL_IMAGE_WIDTH', 130);
/** 一覧画像縦 */
defined('SMALL_IMAGE_HEIGHT') or define('SMALL_IMAGE_HEIGHT', 130);
/** 通常画像横 */
defined('NORMAL_IMAGE_WIDTH') or define('NORMAL_IMAGE_WIDTH', 260);
/** 通常画像縦 */
defined('NORMAL_IMAGE_HEIGHT') or define('NORMAL_IMAGE_HEIGHT', 260);
/** 通常サブ画像横 */
defined('NORMAL_SUBIMAGE_WIDTH') or define('NORMAL_SUBIMAGE_WIDTH', 200);
/** 通常サブ画像縦 */
defined('NORMAL_SUBIMAGE_HEIGHT') or define('NORMAL_SUBIMAGE_HEIGHT', 200);
/** 拡大サブ画像横 */
defined('LARGE_SUBIMAGE_WIDTH') or define('LARGE_SUBIMAGE_WIDTH', 500);
/** 拡大サブ画像縦 */
defined('LARGE_SUBIMAGE_HEIGHT') or define('LARGE_SUBIMAGE_HEIGHT', 500);
/** 画像サイズ制限(KB) */
defined('IMAGE_SIZE') or define('IMAGE_SIZE', 1000);
/** CSVサイズ制限(KB) */
defined('CSV_SIZE') or define('CSV_SIZE', 2000);
/** CSVアップロード1行あたりの最大文字数 */
defined('CSV_LINE_MAX') or define('CSV_LINE_MAX', 10000);
/** ファイル管理画面アップ制限(KB) */
defined('FILE_SIZE') or define('FILE_SIZE', 10000);
/** アップできるテンプレートファイル制限(KB) */
defined('TEMPLATE_SIZE') or define('TEMPLATE_SIZE', 10000);
/** カテゴリの最大階層 */
defined('LEVEL_MAX') or define('LEVEL_MAX', 5);
/** 最大カテゴリ登録数 */
defined('CATEGORY_MAX') or define('CATEGORY_MAX', 1000);
/** 管理機能タイトル */
defined('ADMIN_TITLE') or define('ADMIN_TITLE', "EC-CUBE 管理機能");
/** 編集時強調表示色 */
defined('SELECT_RGB') or define('SELECT_RGB', "#ffffdf");
/** 入力項目無効時の表示色 */
defined('DISABLED_RGB') or define('DISABLED_RGB', "#C9C9C9");
/** エラー時表示色 */
defined('ERR_COLOR') or define('ERR_COLOR', "#ffe8e8");
/** 親カテゴリ表示文字 */
defined('CATEGORY_HEAD') or define('CATEGORY_HEAD', ">");
/** 生年月日初期選択年 */
defined('START_BIRTH_YEAR') or define('START_BIRTH_YEAR', 1970);
/** 価格名称 */
defined('NORMAL_PRICE_TITLE') or define('NORMAL_PRICE_TITLE', "通常価格");
/** 価格名称 */
defined('SALE_PRICE_TITLE') or define('SALE_PRICE_TITLE', "販売価格");
/** 標準ログファイル */
defined('LOG_REALFILE') or define('LOG_REALFILE', DATA_REALDIR . "logs/site.log");
/** 会員ログイン ログファイル */
defined('CUSTOMER_LOG_REALFILE') or define('CUSTOMER_LOG_REALFILE', DATA_REALDIR . "logs/customer.log");
/** 管理機能ログファイル */
defined('ADMIN_LOG_REALFILE') or define('ADMIN_LOG_REALFILE', DATA_REALDIR . "logs/admin.log");
/** デバッグログファイル(未入力:標準ログファイル・管理画面ログファイル) */
defined('DEBUG_LOG_REALFILE') or define('DEBUG_LOG_REALFILE', "");
/** エラーログファイル(未入力:標準ログファイル・管理画面ログファイル) */
defined('ERROR_LOG_REALFILE') or define('ERROR_LOG_REALFILE', DATA_REALDIR . "logs/error.log");
/** DBログファイル */
defined('DB_LOG_REALFILE') or define('DB_LOG_REALFILE', DATA_REALDIR . "logs/db.log");
/** プラグインログファイル */
defined('PLUGIN_LOG_REALFILE') or define('PLUGIN_LOG_REALFILE', DATA_REALDIR . "logs/plugin.log");
/** 画像一時保存 */
defined('IMAGE_TEMP_REALDIR') or define('IMAGE_TEMP_REALDIR', HTML_REALDIR . "upload/temp_image/");
/** 画像保存先 */
defined('IMAGE_SAVE_REALDIR') or define('IMAGE_SAVE_REALDIR', HTML_REALDIR . "upload/save_image/");
/** 画像一時保存URL */
defined('IMAGE_TEMP_URLPATH') or define('IMAGE_TEMP_URLPATH', ROOT_URLPATH . "upload/temp_image/");
/** 画像保存先URL */
defined('IMAGE_SAVE_URLPATH') or define('IMAGE_SAVE_URLPATH', ROOT_URLPATH . "upload/save_image/");
/** RSS用画像一時保存URL */
defined('IMAGE_TEMP_RSS_URL') or define('IMAGE_TEMP_RSS_URL', HTTP_URL . "upload/temp_image/");
/** RSS用画像保存先URL */
defined('IMAGE_SAVE_RSS_URL') or define('IMAGE_SAVE_RSS_URL', HTTP_URL . "upload/save_image/");
/** エンコードCSVの一時保存先 */
defined('CSV_TEMP_REALDIR') or define('CSV_TEMP_REALDIR', DATA_REALDIR . "upload/csv/");
/** 画像がない場合に表示 */
defined('NO_IMAGE_REALFILE') or define('NO_IMAGE_REALFILE', USER_TEMPLATE_REALDIR . "default/img/picture/img_blank.gif");
/** システム管理トップ */
defined('ADMIN_SYSTEM_URLPATH') or define('ADMIN_SYSTEM_URLPATH', ROOT_URLPATH . ADMIN_DIR . "system/" . DIR_INDEX_PATH);
/** 郵便番号入力 */
defined('INPUT_ZIP_URLPATH') or define('INPUT_ZIP_URLPATH', ROOT_URLPATH . "input_zip.php");
/** ホーム */
defined('ADMIN_HOME_URLPATH') or define('ADMIN_HOME_URLPATH', ROOT_URLPATH . ADMIN_DIR . "home.php");
/** ログインページ */
defined('ADMIN_LOGIN_URLPATH') or define('ADMIN_LOGIN_URLPATH', ROOT_URLPATH . ADMIN_DIR . DIR_INDEX_PATH);
/** 商品検索ページ */
defined('ADMIN_PRODUCTS_URLPATH') or define('ADMIN_PRODUCTS_URLPATH', ROOT_URLPATH . ADMIN_DIR . "products/" . DIR_INDEX_PATH);
/** 注文編集ページ */
defined('ADMIN_ORDER_EDIT_URLPATH') or define('ADMIN_ORDER_EDIT_URLPATH', ROOT_URLPATH . ADMIN_DIR . "order/edit.php");
/** 注文編集ページ */
defined('ADMIN_ORDER_URLPATH') or define('ADMIN_ORDER_URLPATH', ROOT_URLPATH . ADMIN_DIR . "order/" . DIR_INDEX_PATH);
/** 注文編集ページ */
defined('ADMIN_ORDER_MAIL_URLPATH') or define('ADMIN_ORDER_MAIL_URLPATH', ROOT_URLPATH . ADMIN_DIR . "order/mail.php");
/** ログアウトページ */
defined('ADMIN_LOGOUT_URLPATH') or define('ADMIN_LOGOUT_URLPATH', ROOT_URLPATH . ADMIN_DIR . "logout.php");
/** メンバー管理ページ表示行数 */
defined('MEMBER_PMAX') or define('MEMBER_PMAX', 10);
/** 検索ページ表示行数 */
defined('SEARCH_PMAX') or define('SEARCH_PMAX', 10);
/** ページ番号の最大表示数量 */
defined('NAVI_PMAX') or define('NAVI_PMAX', 4);
/** 商品サブ情報最大数 */
defined('PRODUCTSUB_MAX') or define('PRODUCTSUB_MAX', 5);
/** お届け時間の最大表示数 */
defined('DELIVTIME_MAX') or define('DELIVTIME_MAX', 16);
/** 配送料金の最大表示数 */
defined('DELIVFEE_MAX') or define('DELIVFEE_MAX', 47);
/** 短い項目の文字数 (名前など) */
defined('STEXT_LEN') or define('STEXT_LEN', 50);
defined('SMTEXT_LEN') or define('SMTEXT_LEN', 100);
/** 長い項目の文字数 (住所など) */
defined('MTEXT_LEN') or define('MTEXT_LEN', 200);
/** 長中文の文字数 (問い合わせなど) */
defined('MLTEXT_LEN') or define('MLTEXT_LEN', 1000);
/** 長文の文字数 */
defined('LTEXT_LEN') or define('LTEXT_LEN', 3000);
/** 超長文の文字数 (メルマガなど) */
defined('LLTEXT_LEN') or define('LLTEXT_LEN', 99999);
/** URLの文字長 */
defined('URL_LEN') or define('URL_LEN', 1024);
/** 管理画面用：ID・パスワードの最大文字数 */
defined('ID_MAX_LEN') or define('ID_MAX_LEN', STEXT_LEN);
/** 管理画面用：ID・パスワードの最小文字数 */
defined('ID_MIN_LEN') or define('ID_MIN_LEN', 4);
/** 金額桁数 */
defined('PRICE_LEN') or define('PRICE_LEN', 8);
/** 率桁数 */
defined('PERCENTAGE_LEN') or define('PERCENTAGE_LEN', 3);
/** 在庫数、販売制限数 */
defined('AMOUNT_LEN') or define('AMOUNT_LEN', 6);
/** 郵便番号1 */
defined('ZIP01_LEN') or define('ZIP01_LEN', 3);
/** 郵便番号2 */
defined('ZIP02_LEN') or define('ZIP02_LEN', 4);
/** 電話番号各項目制限 */
defined('TEL_ITEM_LEN') or define('TEL_ITEM_LEN', 6);
/** 電話番号総数 */
defined('TEL_LEN') or define('TEL_LEN', 12);
/** フロント画面用：パスワードの最小文字数 */
defined('PASSWORD_MIN_LEN') or define('PASSWORD_MIN_LEN', 8);
/** フロント画面用：パスワードの最大文字数 */
defined('PASSWORD_MAX_LEN') or define('PASSWORD_MAX_LEN', SMTEXT_LEN);
/** 検査数値用桁数(INT) */
defined('INT_LEN') or define('INT_LEN', 9);
/** クレジットカードの文字数 (*モジュールで使用) */
defined('CREDIT_NO_LEN') or define('CREDIT_NO_LEN', 4);
/** 検索カテゴリ最大表示文字数(byte) */
defined('SEARCH_CATEGORY_LEN') or define('SEARCH_CATEGORY_LEN', 18);
/** ファイル名表示文字数 */
defined('FILE_NAME_LEN') or define('FILE_NAME_LEN', 10);
/** クッキー保持期限(日) */
defined('COOKIE_EXPIRE') or define('COOKIE_EXPIRE', 365);
/** カテゴリ区切り文字 */
defined('SEPA_CATNAVI') or define('SEPA_CATNAVI', " > ");
/** 会員情報入力 */
defined('SHOPPING_URL') or define('SHOPPING_URL', HTTPS_URL . "shopping/" . DIR_INDEX_PATH);
/** 会員登録ページTOP */
defined('ENTRY_URL') or define('ENTRY_URL', HTTPS_URL . "entry/" . DIR_INDEX_PATH);
/** サイトトップ */
defined('TOP_URL') or define('TOP_URL', HTTP_URL . DIR_INDEX_PATH);
/** カートトップ */
defined('CART_URL') or define('CART_URL', HTTP_URL . "cart/" . DIR_INDEX_PATH);
/** お届け先設定 */
defined('DELIV_URLPATH') or define('DELIV_URLPATH', ROOT_URLPATH . "shopping/deliv.php");
/** 複数お届け先設定 */
defined('MULTIPLE_URLPATH') or define('MULTIPLE_URLPATH', ROOT_URLPATH . "shopping/multiple.php");
/** 購入確認ページ */
defined('SHOPPING_CONFIRM_URLPATH') or define('SHOPPING_CONFIRM_URLPATH', ROOT_URLPATH . "shopping/confirm.php");
/** お支払い方法選択ページ */
defined('SHOPPING_PAYMENT_URLPATH') or define('SHOPPING_PAYMENT_URLPATH', ROOT_URLPATH . "shopping/payment.php");
/** 購入完了画面 */
defined('SHOPPING_COMPLETE_URLPATH') or define('SHOPPING_COMPLETE_URLPATH', ROOT_URLPATH . "shopping/complete.php");
/** モジュール追加用画面 */
defined('SHOPPING_MODULE_URLPATH') or define('SHOPPING_MODULE_URLPATH', ROOT_URLPATH . "shopping/load_payment_module.php");
/** 商品詳細(HTML出力) */
defined('P_DETAIL_URLPATH') or define('P_DETAIL_URLPATH', ROOT_URLPATH . "products/detail.php?product_id=");
/** マイページお届け先URL */
defined('MYPAGE_DELIVADDR_URLPATH') or define('MYPAGE_DELIVADDR_URLPATH', ROOT_URLPATH . "mypage/delivery.php");
/** 新着情報管理画面 開始年(西暦) */
defined('ADMIN_NEWS_STARTYEAR') or define('ADMIN_NEWS_STARTYEAR', 2005);
/** 再入会制限時間 (単位: 時間) */
defined('ENTRY_LIMIT_HOUR') or define('ENTRY_LIMIT_HOUR', 1);
/** 関連商品表示数 */
defined('RECOMMEND_PRODUCT_MAX') or define('RECOMMEND_PRODUCT_MAX', 6);
/** おすすめ商品表示数 */
defined('RECOMMEND_NUM') or define('RECOMMEND_NUM', 8);
/** お届け可能日以降のプルダウン表示最大日数 */
defined('DELIV_DATE_END_MAX') or define('DELIV_DATE_END_MAX', 21);
/** 支払期限 (*モジュールで使用) */
defined('CV_PAYMENT_LIMIT') or define('CV_PAYMENT_LIMIT', 14);
/** 商品レビューでURL書き込みを許可するか否か */
defined('REVIEW_ALLOW_URL') or define('REVIEW_ALLOW_URL', 0);
/** アップデート時にサイト情報を送出するか */
defined('UPDATE_SEND_SITE_INFO') or define('UPDATE_SEND_SITE_INFO', false);
/** ポイントを利用するか(true:利用する、false:利用しない) (false は一部対応) */
defined('USE_POINT') or define('USE_POINT', true);
/** 在庫無し商品の非表示(true:非表示、false:表示) */
defined('NOSTOCK_HIDDEN') or define('NOSTOCK_HIDDEN', false);
/** モバイルサイトを利用するか(true:利用する、false:利用しない) (false は一部対応) (*モジュールで使用) */
defined('USE_MOBILE') or define('USE_MOBILE', true);
/** 複数配送先指定機能を利用するか(true:利用する、false:利用しない) */
defined('USE_MULTIPLE_SHIPPING') or define('USE_MULTIPLE_SHIPPING', true);
/** 短文の文字数 */
defined('SLTEXT_LEN') or define('SLTEXT_LEN', 500);
/** デフォルトテンプレート名(PC) */
defined('DEFAULT_TEMPLATE_NAME') or define('DEFAULT_TEMPLATE_NAME', "default");
/** デフォルトテンプレート名(モバイル) */
defined('MOBILE_DEFAULT_TEMPLATE_NAME') or define('MOBILE_DEFAULT_TEMPLATE_NAME', "mobile");
/** デフォルトテンプレート名(スマートフォン) */
defined('SMARTPHONE_DEFAULT_TEMPLATE_NAME') or define('SMARTPHONE_DEFAULT_TEMPLATE_NAME', "sphone");
/** テンプレート名 */
defined('TEMPLATE_NAME') or define('TEMPLATE_NAME', "default");
/** モバイルテンプレート名 */
defined('MOBILE_TEMPLATE_NAME') or define('MOBILE_TEMPLATE_NAME', "mobile");
/** スマートフォンテンプレート名 */
defined('SMARTPHONE_TEMPLATE_NAME') or define('SMARTPHONE_TEMPLATE_NAME', "sphone");
/** SMARTYテンプレート */
defined('SMARTY_TEMPLATES_REALDIR') or define('SMARTY_TEMPLATES_REALDIR',  DATA_REALDIR . "Smarty/templates/");
/** SMARTYテンプレート(PC) */
defined('TEMPLATE_REALDIR') or define('TEMPLATE_REALDIR', SMARTY_TEMPLATES_REALDIR . TEMPLATE_NAME . "/");
/** SMARTYテンプレート(管理機能) */
defined('TEMPLATE_ADMIN_REALDIR') or define('TEMPLATE_ADMIN_REALDIR', SMARTY_TEMPLATES_REALDIR . "admin/");
/** SMARTYコンパイル */
defined('COMPILE_REALDIR') or define('COMPILE_REALDIR', DATA_REALDIR . "Smarty/templates_c/" . TEMPLATE_NAME . "/");
/** SMARTYコンパイル(管理機能) */
defined('COMPILE_ADMIN_REALDIR') or define('COMPILE_ADMIN_REALDIR', DATA_REALDIR . "Smarty/templates_c/admin/");
/** ブロックファイル保存先 */
defined('BLOC_DIR') or define('BLOC_DIR', "frontparts/bloc/");
/** SMARTYテンプレート(mobile) */
defined('MOBILE_TEMPLATE_REALDIR') or define('MOBILE_TEMPLATE_REALDIR', SMARTY_TEMPLATES_REALDIR . MOBILE_TEMPLATE_NAME . "/");
/** SMARTYコンパイル(mobile) */
defined('MOBILE_COMPILE_REALDIR') or define('MOBILE_COMPILE_REALDIR', DATA_REALDIR . "Smarty/templates_c/" . MOBILE_TEMPLATE_NAME . "/");
/** SMARTYテンプレート(smart phone) */
defined('SMARTPHONE_TEMPLATE_REALDIR') or define('SMARTPHONE_TEMPLATE_REALDIR', SMARTY_TEMPLATES_REALDIR . SMARTPHONE_TEMPLATE_NAME . "/");
/** SMARTYコンパイル(smartphone) */
defined('SMARTPHONE_COMPILE_REALDIR') or define('SMARTPHONE_COMPILE_REALDIR', DATA_REALDIR . "Smarty/templates_c/" . SMARTPHONE_TEMPLATE_NAME . "/");
/** EメールアドレスチェックをRFC準拠にするか(true:準拠する、false:準拠しない) */
defined('RFC_COMPLIANT_EMAIL_CHECK') or define('RFC_COMPLIANT_EMAIL_CHECK', false);
/** モバイルサイトのセッションの存続時間 (秒) */
defined('MOBILE_SESSION_LIFETIME') or define('MOBILE_SESSION_LIFETIME', 1800);
/** 携帯電話向け変換画像保存ディレクトリ */
defined('MOBILE_IMAGE_REALDIR') or define('MOBILE_IMAGE_REALDIR', HTML_REALDIR . "upload/mobile_image/");
/** 携帯電話向け変換画像保存ディレクトリ */
defined('MOBILE_IMAGE_URLPATH') or define('MOBILE_IMAGE_URLPATH', ROOT_URLPATH . "upload/mobile_image/");
/** モバイルURL */
defined('MOBILE_TOP_URLPATH') or define('MOBILE_TOP_URLPATH', ROOT_URLPATH . DIR_INDEX_PATH);
/** カートトップ */
defined('MOBILE_CART_URLPATH') or define('MOBILE_CART_URLPATH', ROOT_URLPATH . "cart/" . DIR_INDEX_PATH);
/** 購入確認ページ */
defined('MOBILE_SHOPPING_CONFIRM_URLPATH') or define('MOBILE_SHOPPING_CONFIRM_URLPATH', ROOT_URLPATH . "shopping/confirm.php");
/** お支払い方法選択ページ */
defined('MOBILE_SHOPPING_PAYMENT_URLPATH') or define('MOBILE_SHOPPING_PAYMENT_URLPATH', ROOT_URLPATH . "shopping/payment.php");
/** 商品詳細(HTML出力) */
defined('MOBILE_P_DETAIL_URLPATH') or define('MOBILE_P_DETAIL_URLPATH', ROOT_URLPATH . "products/detail.php?product_id=");
/** 購入完了画面 (*モジュールで使用) */
defined('MOBILE_SHOPPING_COMPLETE_URLPATH') or define('MOBILE_SHOPPING_COMPLETE_URLPATH', ROOT_URLPATH . "shopping/complete.php");
/** セッション維持方法："useCookie"|"useRequest" */
defined('SESSION_KEEP_METHOD') or define('SESSION_KEEP_METHOD', "useCookie");
/** セッションの存続時間 (秒) */
defined('SESSION_LIFETIME') or define('SESSION_LIFETIME', 1800);
/** オーナーズストアURL */
defined('OSTORE_URL') or define('OSTORE_URL', "http://www.ec-cube.net/");
/** オーナーズストアURL */
defined('OSTORE_SSLURL') or define('OSTORE_SSLURL', "https://www.ec-cube.net/");
/** オーナーズストアログパス */
defined('OSTORE_LOG_REALFILE') or define('OSTORE_LOG_REALFILE', DATA_REALDIR . "logs/ownersstore.log");
/** お気に入り商品登録(有効:1 無効:0) */
defined('OPTION_FAVORITE_PRODUCT') or define('OPTION_FAVORITE_PRODUCT', 1);
/** 画像リネーム設定 (商品画像のみ) (true:リネームする、false:リネームしない) */
defined('IMAGE_RENAME') or define('IMAGE_RENAME', true);
/** (2.11用)プラグインディレクトリ(モジュールで使用) */
defined('PLUGIN_DIR') or define('PLUGIN_DIR', "plugins/");
/** (2.11用)プラグイン保存先(モジュールで使用) */
defined('PLUGIN_REALDIR') or define('PLUGIN_REALDIR', USER_REALDIR . PLUGIN_DIR);
/** プラグイン保存先ディレクトリ */
defined('PLUGIN_UPLOAD_REALDIR') or define('PLUGIN_UPLOAD_REALDIR', DATA_REALDIR . "downloads/plugin/");
/** プラグイン保存先ディレクトリ(html) */
defined('PLUGIN_HTML_REALDIR') or define('PLUGIN_HTML_REALDIR', HTML_REALDIR . "plugin/");
/** プラグインファイル一時保存先 */
defined('PLUGIN_TEMP_REALDIR') or define('PLUGIN_TEMP_REALDIR', HTML_REALDIR . "upload/temp_plugin/");
/** プラグインファイル登録可能拡張子(カンマ区切り) */
defined('PLUGIN_EXTENSION') or define('PLUGIN_EXTENSION', "tar,tar.gz");
/** プラグイン一時展開用ディレクトリ（アップデート用） */
defined('DOWNLOADS_TEMP_PLUGIN_UPDATE_DIR') or define('DOWNLOADS_TEMP_PLUGIN_UPDATE_DIR', DATA_REALDIR . "downloads/tmp/plugin_update/");
/** プラグイン一時展開用ディレクトリ（インストール用） */
defined('DOWNLOADS_TEMP_PLUGIN_INSTALL_DIR') or define('DOWNLOADS_TEMP_PLUGIN_INSTALL_DIR', DATA_REALDIR . "downloads/tmp/plugin_install/");
/** プラグインURL */
defined('PLUGIN_HTML_URLPATH') or define('PLUGIN_HTML_URLPATH', ROOT_URLPATH . "plugin/");
/** 日数桁数 */
defined('DOWNLOAD_DAYS_LEN') or define('DOWNLOAD_DAYS_LEN', 3);
/** ダウンロードファイル登録可能拡張子(カンマ区切り) */
defined('DOWNLOAD_EXTENSION') or define('DOWNLOAD_EXTENSION', "zip,lzh,jpg,jpeg,gif,png,mp3,pdf,csv");
/** ダウンロード販売ファイル用サイズ制限(KB) */
defined('DOWN_SIZE') or define('DOWN_SIZE', 50000);
/** 1:実商品 2:ダウンロード */
defined('DEFAULT_PRODUCT_DOWN') or define('DEFAULT_PRODUCT_DOWN', 1);
/** ダウンロードファイル一時保存 */
defined('DOWN_TEMP_REALDIR') or define('DOWN_TEMP_REALDIR', DATA_REALDIR . "download/temp/");
/** ダウンロードファイル保存先 */
defined('DOWN_SAVE_REALDIR') or define('DOWN_SAVE_REALDIR', DATA_REALDIR . "download/save/");
/** ダウンロード販売機能 ダウンロードファイル読み込みバイト(KB) */
defined('DOWNLOAD_BLOCK') or define('DOWNLOAD_BLOCK', 1024);
/** 新規注文 */
defined('ORDER_NEW') or define('ORDER_NEW', 1);
/** 入金待ち */
defined('ORDER_PAY_WAIT') or define('ORDER_PAY_WAIT', 2);
/** 入金済み */
defined('ORDER_PRE_END') or define('ORDER_PRE_END', 6);
/** キャンセル */
defined('ORDER_CANCEL') or define('ORDER_CANCEL', 3);
/** 取り寄せ中 */
defined('ORDER_BACK_ORDER') or define('ORDER_BACK_ORDER', 4);
/** 発送済み */
defined('ORDER_DELIV') or define('ORDER_DELIV', 5);
/** 決済処理中 */
defined('ORDER_PENDING') or define('ORDER_PENDING', 7);
/** 通常商品 */
defined('PRODUCT_TYPE_NORMAL') or define('PRODUCT_TYPE_NORMAL', 1);
/** ダウンロード商品 */
defined('PRODUCT_TYPE_DOWNLOAD') or define('PRODUCT_TYPE_DOWNLOAD', 2);
/** DBログの記録モード (0:記録しない, 1:遅延時のみ記録する, 2:常に記録する) */
defined('SQL_QUERY_LOG_MODE') or define('SQL_QUERY_LOG_MODE', 1);
/** DBログで遅延とみなす実行時間(秒) */
defined('SQL_QUERY_LOG_MIN_EXEC_TIME') or define('SQL_QUERY_LOG_MIN_EXEC_TIME', 2);
/** ページ表示時間のログを取得するフラグ(1:表示, 0:非表示) */
defined('PAGE_DISPLAY_TIME_LOG_MODE') or define('PAGE_DISPLAY_TIME_LOG_MODE', 1);
/** ページ表示時間のログを取得する時間設定(設定値以上かかった場合に取得) */
defined('PAGE_DISPLAY_TIME_LOG_MIN_EXEC_TIME') or define('PAGE_DISPLAY_TIME_LOG_MIN_EXEC_TIME', 2);
/** 端末種別: モバイル */
defined('DEVICE_TYPE_MOBILE') or define('DEVICE_TYPE_MOBILE', 1);
/** 端末種別: スマートフォン */
defined('DEVICE_TYPE_SMARTPHONE') or define('DEVICE_TYPE_SMARTPHONE', 2);
/** 端末種別: PC */
defined('DEVICE_TYPE_PC') or define('DEVICE_TYPE_PC', 10);
/** 端末種別: 管理画面 */
defined('DEVICE_TYPE_ADMIN') or define('DEVICE_TYPE_ADMIN', 99);
/** EC-CUBE更新情報取得 (true:取得する false:取得しない) */
defined('ECCUBE_INFO') or define('ECCUBE_INFO', true);
/** 外部サイトHTTP取得タイムアウト時間(秒) */
defined('HTTP_REQUEST_TIMEOUT') or define('HTTP_REQUEST_TIMEOUT', "5");
/** 郵便番号CSVのZIPアーカイブファイルの取得元 */
defined('ZIP_DOWNLOAD_URL') or define('ZIP_DOWNLOAD_URL', "https://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip");
/** フックポイント(プレプロセス) */
defined('HOOK_POINT_PREPROCESS') or define('HOOK_POINT_PREPROCESS', "LC_Page_preProcess");
/** フックポイント(プロセス) */
defined('HOOK_POINT_PROCESS') or define('HOOK_POINT_PROCESS', "LC_Page_process");
/** プラグインのロード可否フラグ) */
defined('PLUGIN_ACTIVATE_FLAG') or define('PLUGIN_ACTIVATE_FLAG', true);
/** SMARTYコンパイルモード */
defined('SMARTY_FORCE_COMPILE_MODE') or define('SMARTY_FORCE_COMPILE_MODE', false);
/** ログイン失敗時の遅延時間(秒)(ブルートフォースアタック対策) */
defined('LOGIN_RETRY_INTERVAL') or define('LOGIN_RETRY_INTERVAL', 0);
/** MYページ：ご注文状況表示フラグ */
defined('MYPAGE_ORDER_STATUS_DISP_FLAG') or define('MYPAGE_ORDER_STATUS_DISP_FLAG', true);
/** デフォルト国コード ISO_3166-1に準拠 */
defined('DEFAULT_COUNTRY_ID') or define('DEFAULT_COUNTRY_ID', 392);
/** ホスト名を正規化するか (true:する false:しない) */
defined('USE_NORMALIZE_HOSTNAME') or define('USE_NORMALIZE_HOSTNAME', true);
/** 各種フォームで国の指定を有効にする(true:有効 false:無効) */
defined('FORM_COUNTRY_ENABLE') or define('FORM_COUNTRY_ENABLE', false);
/** 商品ごとの税率設定(軽減税率対応 有効:1 無効:0) */
defined('OPTION_PRODUCT_TAX_RULE') or define('OPTION_PRODUCT_TAX_RULE', 0);
/** 複数箇所の税率設定時における優先度設定。カンマ区切りスペース不可で記述。後に書いてあるキーに一致するほど優先される。デフォルト：'product_id,product_class_id,pref_id,country_id'（国＞地域（県）＞規格単位＞商品単位） */
defined('TAX_RULE_PRIORITY') or define('TAX_RULE_PRIORITY', "product_id,product_class_id,pref_id,country_id");
/** 決済処理中ステータスのロールバックを行う時間の設定(秒) */
defined('PENDING_ORDER_CANCEL_TIME') or define('PENDING_ORDER_CANCEL_TIME', 900);
/** 決済処理中ステータスのロールバックをするか(true:する false:しない) */
defined('PENDING_ORDER_CANCEL_FLAG') or define('PENDING_ORDER_CANCEL_FLAG', true);
/** API機能を有効にする(true:する false:しない) */
defined('API_ENABLE_FLAG') or define('API_ENABLE_FLAG', false);
/** UTF-8依存文字が入力された際に表示する文字(Unicode値の整数 デフォルト: ?) */
defined('SUBSTITUTE_CHAR') or define('SUBSTITUTE_CHAR', 63);
