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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<!--{$smarty.const.CHAR_CODE}-->" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="content-style-type" content="text/css" />
<link rel="stylesheet" href="css/admin_contents.css" type="text/css" media="all" />
<!--[if lt IE 9]>
<script src="../js/jquery-1.11.1.min.js"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
<script src="../js/jquery-2.1.1.min.js"></script>
<!--<![endif]-->
<script type="text/javascript" src="../js/eccube.js"></script>

<!--{if $tpl_mainpage != 'complete.tpl'}-->
<script type="text/javascript">//<![CDATA[
$(function(){
    $('.btn-next').on('click', function(e) {
        e.preventDefault();
        $('form').submit();
    });
});
$(window).on('load', function() {
    $('#loading').hide();
});
$(window).on('beforeunload',function(){
    // unload では処理されないため、beforeunload を利用している。(Chrome, Firefox で確認)
    $('#loading').show();
});
//]]></script>
<!--{/if}-->
<title>EC-CUBEインストール</title>
</head>
<body>
<!--{$GLOBAL_ERR}-->
<noscript>
    <p>JavaScript を有効にしてご利用下さい。</p>
</noscript>
<div id="outside">
    <div id="out-wrap">
        <div class="logo">
            <img src="img/logo_resize.jpg" width="99" height="15" alt="EC-CUBE" />
        </div>
        <div id="out-area">
            <div class="out-top"></div>
            <!--{include file=$tpl_mainpage}-->
        </div>
        <!--{if strlen($install_info_url) != 0}-->
        <div id="info-area">
            <iframe src="<!--{$install_info_url}-->" width="562" height="550" frameborder="no" scrolling="no">
                こちらはEC-CUBEからのお知らせです。この部分は iframe対応ブラウザでご覧下さい。
            </iframe>
        </div>
        <!--{/if}-->
    </div>
</div>
<div id="loading"><div class="inner">
    <canvas></canvas>
    <p>Loading...</p>
</div></div>
</body>
<style type="text/css">
#loading {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(32, 32, 32, 0.3);
    .inner {
        position: relative;
        display: inline-block;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 1ex;
        border-radius: 1ex;
        background-image: linear-gradient(180deg, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.3));
        canvas {
            --s: 14px;
            --g: 5px;
            width: calc(3*(1.353*var(--s) + var(--g)));
            aspect-ratio: 3;
            background:
                linear-gradient(#FC0 0 0) left/33% 100% no-repeat,
                conic-gradient(from -90deg at var(--s) calc(0.353*var(--s)), #EEE 135deg,#383B4A 0 270deg,#9C9DA5 0)
            ;
            background-blend-mode: multiply;
            mask:
                linear-gradient(to bottom right, #0000 calc(0.25*var(--s)),#000 0 calc(100% - calc(0.25*var(--s)) - 1.414*var(--g)),#0000 0),
                conic-gradient(from -90deg at right var(--g) bottom var(--g),#000 90deg,#0000 0)
            ;
            background-size: calc(100%/3) 100%;
            mask-size: calc(100%/3) 100%;
            mask-composite: intersect;
            animation: l7 steps(3) 1.5s infinite;
            position: relative;
            left: 50%;
            transform: translate(-50%, 0);
        }
    }
    p {
        color: #fff;
        letter-spacing: 0.3ex;
    }
}
@keyframes l7 {
    to {background-position: 150% 0%}
}
</style>
</html>
