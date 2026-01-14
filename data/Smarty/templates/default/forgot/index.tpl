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
<!--{include file="`$smarty.const.TEMPLATE_REALDIR`popup_header.tpl" subtitle="パスワードを忘れた方(入力ページ)"}-->

<div id="window_area">
    <h2>パスワードの再発行</h2>
    <p class="information">ご登録時のメールアドレスを入力して「次へ」ボタンをクリックしてください。<br />
    パスワード再設定用のリンクをメールでお送りします。<br />
    <span class="attention">※リンクの有効期限は24時間です。</span></p>
    <form action="?" method="post" name="form1">
        <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
        <input type="hidden" name="mode" value="request" />

        <div id="forgot">
            <div class="contents">
                <div class="mailaddres">
                    <p class="attention"><!--{$arrErr.email}--><!--{$errmsg}--></p>
                    <p>
                        メールアドレス：&nbsp;
                        <input type="text" name="email" value="<!--{$arrForm.email|default:$tpl_login_email|h}-->" class="box300" style="<!--{$arrErr.email|sfGetErrorColor}-->; ime-mode: disabled;" />
                    </p>
                </div>
            </div>
        </div>
        <div class="btn_area">
            <ul>
                <li><input type="image" class="hover_change_image" src="<!--{$TPL_URLPATH}-->img/button/btn_next.jpg" alt="次へ" name="next" id="next" /></li>
            </ul>
        </div>
    </form>
</div>
<!--{include file="`$smarty.const.TEMPLATE_REALDIR`popup_footer.tpl"}-->

