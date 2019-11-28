<!--{*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2014 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
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

<!--{strip}-->
<section id="news_section">
    <div id="news_area">
        <h2>新着情報</h2>
        <div class="block_body">
            <div class="news_contents accordion">
                <!--{section name=data loop=$arrNews}-->
                <!--{assign var="date_array" value="-"|explode:$arrNews[data].cast_news_date}-->
                <dl class="newslist">
                    <dt><span class="date"><!--{$date_array[0]}-->.<!--{$date_array[1]}-->.
                            <!--{$date_array[2]}--></span><span class="news_title"><a
                            <!--{if $arrNews[data].news_url}--> href="<!--{$arrNews[data].news_url}-->"
                            <!--{if $arrNews[data].link_method eq "2"}--> target="_blank"
                            <!--{/if}-->
                            <!--{/if}-->
                        >
                            <!--{$arrNews[data].news_title|h|nl2br}--></a></span></dt>
                    <dd><!--{$arrNews[data].news_comment|h|nl2br}--></dd>
                </dl>
                <!--{/section}-->
            </div>
        </div>
    </div>
    <div class="txt_bnr_area">
        <div class="txt_bnr">
            <strong>
                5,000以上の購入で
                <br/>
                <strong>配送料無料</strong>
            </strong>
            <br/>
            一部地域は除く
        </div>
    </div>
</section>
<!--{/strip}-->
