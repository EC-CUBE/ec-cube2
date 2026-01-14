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

<section id="windowcolumn">
    <h2 class="title">新しいパスワードの設定</h2>
    <form action="?" method="post" name="form1">
        <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
        <input type="hidden" name="mode" value="complete" />
        <input type="hidden" name="token" value="<!--{$token|h}-->" />
        <div class="intro">
            <p>新しいパスワードを入力して「次へ」ボタンをクリックしてください。</p>
        </div>
        <div class="window_area clearfix">
            <p>
                新しいパスワード<br />
                <span class="attention"><!--{$arrErr.password}--></span>
                <input type="password" name="password"
                value="<!--{$arrForm.password|h}-->"
                style="<!--{$arrErr.password|sfGetErrorColor}-->;"
                maxlength="<!--{$smarty.const.PASSWORD_MAX_LEN}-->" class="text boxLong data-role-none" />
            </p>
            <hr />
            <p>
                新しいパスワード（確認）<br />
                <span class="attention"><!--{$arrErr.password02}--><!--{$errmsg}--></span>
                <input type="password" name="password02"
                value="<!--{$arrForm.password02|h}-->"
                style="<!--{$arrErr.password02|sfGetErrorColor}-->;"
                maxlength="<!--{$smarty.const.PASSWORD_MAX_LEN}-->" class="text boxLong data-role-none" />
            </p>
            <hr />
            <p class="attentionSt">※パスワードは<!--{$smarty.const.PASSWORD_MIN_LEN}-->文字以上<!--{$smarty.const.PASSWORD_MAX_LEN}-->文字以内で入力してください。</p>
        </div>

        <div class="btn_area"><p><input class="btn data-role-none" type="submit" value="次へ" /></p></div>
    </form>
</section>
