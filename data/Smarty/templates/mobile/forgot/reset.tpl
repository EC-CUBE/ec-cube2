<!--{*
/*
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
 */
*}-->

<!--{strip}-->
    <font color="#ff0000">※パスワードは<!--{$smarty.const.PASSWORD_MIN_LEN}-->文字以上<!--{$smarty.const.PASSWORD_MAX_LEN}-->文字以内で入力してください。</font>
    <br>
    <br>

    <!--{if $errmsg}-->
        <font color="#ff0000"><!--{$errmsg}--></font><br>
    <!--{/if}-->

    新しいパスワードを入力して「次へ」ボタンをクリックしてください。<br>
    <br>

    <form action="?" method="post">
        <input type="hidden" name="mode" value="complete">
        <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->">
        <input type="hidden" name="token" value="<!--{$token|h}-->">

        新しいパスワード：<font color="#FF0000"><!--{$arrErr.password}--></font><br>
        <input type="password" name="password" value="<!--{$arrForm.password|h}-->" maxlength="<!--{$smarty.const.PASSWORD_MAX_LEN}-->" istyle="4"><br>
        <br>
        新しいパスワード（確認）：<font color="#FF0000"><!--{$arrErr.password02}--></font><br>
        <input type="password" name="password02" value="<!--{$arrForm.password02|h}-->" maxlength="<!--{$smarty.const.PASSWORD_MAX_LEN}-->" istyle="4"><br>

        <br>
        <center><input type="submit" value="次へ" name="next"></center>
    </form>
<!--{/strip}-->
