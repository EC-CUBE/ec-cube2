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
<!--{include file="`$smarty.const.TEMPLATE_REALDIR`popup_header.tpl" subtitle="パスワードを忘れた方(完了ページ)"}-->

<div id="window_area">
    <h2 class="title">パスワード変更完了</h2>
    <p class="information">
        パスワードの変更が完了いたしました。<br />
        新しいパスワードでログインしてください。<br />
        <br />
        確認のため、パスワード変更完了のメールをお送りしました。
    </p>
    <form action="?" method="post" name="form1">
        <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
        <div id="forgot">
            <p>新しいパスワードでログインできます。</p>
        </div>
        <div class="btn_area">
            <ul>
                <li><a href="javascript:window.close()"><img class="hover_change_image" src="<!--{$TPL_URLPATH}-->img/button/btn_close.jpg" alt="閉じる" /></a></li>
            </ul>
        </div>
    </form>
</div>

<!--{include file="`$smarty.const.TEMPLATE_REALDIR`popup_footer.tpl"}-->
