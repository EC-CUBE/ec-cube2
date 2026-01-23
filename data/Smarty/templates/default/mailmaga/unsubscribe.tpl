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

<div id="undercolumn">
    <h2 class="title"><!--{$tpl_title|h}--></h2>
    <div id="undercolumn_mailmaga_unsubscribe">
        <div id="complete_area">
            <!--{if $tpl_success}-->
                <p class="message success"><!--{$tpl_message|h}--></p>
                <p>今後、メールマガジンは配信されません。</p>
                <div class="btn_area">
                    <ul>
                        <li>
                            <a href="<!--{$smarty.const.TOP_URL}-->"><img class="hover_change_image" src="<!--{$TPL_URLPATH}-->img/button/btn_toppage.jpg" alt="トップページへ" /></a>
                        </li>
                    </ul>
                </div>
            <!--{elseif $tpl_message}-->
                <p class="message error"><!--{$tpl_message|h}--></p>
                <div class="btn_area">
                    <ul>
                        <li>
                            <a href="<!--{$smarty.const.TOP_URL}-->"><img class="hover_change_image" src="<!--{$TPL_URLPATH}-->img/button/btn_toppage.jpg" alt="トップページへ" /></a>
                        </li>
                    </ul>
                </div>
            <!--{else}-->
                <p class="message">メールアドレス: <strong><!--{$tpl_email|h}--></strong></p>
                <p>メールマガジンの登録を解除しますか?</p>
                <form method="post" action="<!--{$smarty.server.REQUEST_URI|h}-->">
                    <input type="hidden" name="mode" value="confirm" />
                    <div class="btn_area">
                        <ul>
                            <li>
                                <button type="submit" class="btn">登録を解除する</button>
                            </li>
                        </ul>
                    </div>
                </form>
            <!--{/if}-->
        </div>
    </div>
</div>
