<!DOCTYPE HTML>
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

<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=<!--{$smarty.const.CHAR_CODE}-->" />

    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <!--{* 共通CSS *}-->
    <link rel="stylesheet" media="only screen" href="<!--{$TPL_URLPATH}-->css/import.css" />

    <script src="<!--{$smarty.const.ROOT_URLPATH}-->js/eccube.js"></script>
    <script src="<!--{$TPL_URLPATH}-->js/eccube.sphone.js"></script>
    <script src="<!--{$TPL_URLPATH}-->js/jquery.biggerlink.js"></script>
    <script type="text/javascript">
        $(function(){
            $('.recommendblock, .list_area, .newslist li, .bubbleBox, .arrowBox').biggerlink();
        });
    </script>

    <!--{* スマートフォンカスタマイズ用JS *}-->
    <script src="<!--{$TPL_URLPATH}-->js/jquery.autoResizeTextAreaQ-0.1.js"></script>

    <title><!--{$arrSiteInfo.shop_name|h}--><!--{if $tpl_subtitle|strlen >= 1}--> / <!--{$tpl_subtitle|h}--><!--{elseif $tpl_title|strlen >= 1}--> / <!--{$tpl_title|h}--><!--{/if}--></title>
    <!--{if $arrPageLayout.author|strlen >= 1}-->
        <meta name="author" content="<!--{$arrPageLayout.author|h}-->" />
    <!--{/if}-->
    <!--{if $arrPageLayout.description|strlen >= 1}-->
        <meta name="description" content="<!--{$arrPageLayout.description|h}-->" />
    <!--{/if}-->
    <!--{if $arrPageLayout.keyword|strlen >= 1}-->
        <meta name="keywords" content="<!--{$arrPageLayout.keyword|h}-->" />
    <!--{/if}-->
    <link rel="shortcut icon" href="<!--{$TPL_URLPATH}-->img/common/favicon.ico" />
    <link rel="icon" type="image/vnd.microsoft.icon" href="<!--{$TPL_URLPATH}-->img/common/favicon.ico" />
    <!--{* iPhone用アイコン画像 *}-->
    <link rel="apple-touch-icon" href="<!--{$TPL_URLPATH}-->img/common/apple-touch-icon.png" />

    <script type="text/javascript">//<![CDATA[
        <!--{$tpl_javascript nofilter}-->
        $(function(){
            <!--{$tpl_onload}-->
        });
    //]]></script>
</head>

<body>
    <noscript>
        <p><em>JavaScriptを有効にしてご利用下さい.</em></p>
    </noscript>

    <a name="top" id="top"></a>

        <!--{if !$disable_wincol}--><div id="windowcolumn"><!--{/if}-->
