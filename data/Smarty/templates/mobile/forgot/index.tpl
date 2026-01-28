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
    <font color="#ff0000">※リンクの有効期限は24時間です。</font>
    <br>
    <br>

    <!--{if $errmsg}-->
        <font color="#ff0000"><!--{$errmsg|h}--></font><br>
    <!--{/if}-->

    ご登録時のメールアドレスを入力して「次へ」ボタンをクリックしてください。<br>
    パスワード再設定用のリンクをメールでお送りします。<br>
    <br>

    <form action="?" method="post">
        <input type="hidden" name="mode" value="request">
        <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->">

        メールアドレス：<font color="#FF0000"><!--{$arrErr.email}--></font><br>
        <input type="text" name="email" value="<!--{$arrForm.email|default:$tpl_login_email|h}-->" size="50" istyle="3"><br>

        <br>
        <center><input type="submit" value="次へ" name="next"></center>
    </form>
<!--{/strip}-->
