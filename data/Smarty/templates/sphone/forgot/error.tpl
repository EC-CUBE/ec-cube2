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
    <h2 class="title">エラー</h2>
    <div class="intro">
        <p class="attention"><!--{$errmsg|h}--></p>
    </div>

    <div class="window_area clearfix">
        <p>パスワード再設定リンクが無効または期限切れです。<br />
        お手数ですが、最初からやり直してください。</p>
    </div>

    <div class="btn_area">
        <p><a href="<!--{$smarty.const.HTTPS_URL}-->forgot/" class="btn_sub">戻る</a></p>
    </div>
</section>
