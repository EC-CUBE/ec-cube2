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
<!--{include file="`$smarty.const.TEMPLATE_REALDIR`popup_header.tpl" subtitle="新しいパスワードの設定"}-->

<div id="window_area">
    <h2>新しいパスワードの設定</h2>
    <p class="information">
        新しいパスワードを入力して「次へ」ボタンをクリックしてください。<br />
        <span class="attention">※パスワードは<!--{$smarty.const.PASSWORD_MIN_LEN}-->文字以上<!--{$smarty.const.PASSWORD_MAX_LEN}-->文字以内で入力してください。</span>
    </p>
    <form action="?" method="post" name="form1">
        <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
        <input type="hidden" name="mode" value="complete" />
        <input type="hidden" name="token" value="<!--{$token|h}-->" />

        <div id="forgot">
            <div class="contents">
                <div class="password">
                    <p class="attention"><!--{$arrErr.password}--></p>
                    <p>
                        新しいパスワード：&nbsp;
                        <input type="password" name="password" value="<!--{$arrForm.password|h}-->" class="box300" maxlength="<!--{$smarty.const.PASSWORD_MAX_LEN}-->" style="<!--{$arrErr.password|sfGetErrorColor}-->; ime-mode: disabled;" />
                    </p>
                </div>
                <div class="password02">
                    <p class="attention"><!--{$arrErr.password02}--><!--{$errmsg}--></p>
                    <p>
                        新しいパスワード（確認）：&nbsp;
                        <input type="password" name="password02" value="<!--{$arrForm.password02|h}-->" class="box300" maxlength="<!--{$smarty.const.PASSWORD_MAX_LEN}-->" style="<!--{$arrErr.password02|sfGetErrorColor}-->; ime-mode: disabled;" />
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
