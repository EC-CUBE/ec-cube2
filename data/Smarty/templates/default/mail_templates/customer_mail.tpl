<!--{*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *}-->
　※本メールは自動配信メールです。
　等幅フォント(MSゴシック12ポイント、Osaka-等幅など)で
　最適にご覧になれます。

┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
　※本メールは、
　<!--{$CONF.shop_name}-->より会員登録を希望された方に
　お送りしています。
　もしお心当たりが無い場合はこのままこのメールを破棄していただ
　ければ会員登録はなされません。
　またその旨 <!--{* 問い合わせ受付メール *}--><!--{$CONF.email02}--> まで
　ご連絡いただければ幸いです。
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

<!--{$arrCustomer|format_name}--> 様

<!--{$CONF.shop_name}-->でございます。

この度は会員登録依頼をいただきまして、有り難うございます。

現在は仮登録の状態です。
　　　~~~~~~
本会員登録を完了するには下記URLにアクセスしてください。
※入力されたお客様の情報はSSL暗号化通信により保護されます。

<!--{$smarty.const.HTTPS_URL}-->regist/<!--{$smarty.const.DIR_INDEX_PATH}-->?mode=regist&id=<!--{$arrCustomer.secret_key}--><!--{$etc_value}-->

上記URLにて本会員登録が完了いたしましたら改めてご登録内容ご確認
メールをお送り致します。



