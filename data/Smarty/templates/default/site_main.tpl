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

<!--{strip}-->
    <body class="<!--{$tpl_page_class_name|h}-->">
        <!--{$GLOBAL_ERR}-->
        <noscript>
            <p>JavaScript を有効にしてご利用下さい.</p>
        </noscript>

        <div class="frame_outer">
            <a name="top" id="top"></a>

            <!--{* ▼HeaderHeaderTop COLUMN*}-->
            <!--{if !empty($arrPageLayout.HeaderTopNavi)}-->
                <div id="headertopcolumn">
                    <!--{* ▼上ナビ *}-->
                    <!--{foreach key=HeaderTopNaviKey item=HeaderTopNaviItem from=$arrPageLayout.HeaderTopNavi}-->
                        <!-- ▼<!--{$HeaderTopNaviItem.bloc_name}--> -->
                        <!--{if $HeaderTopNaviItem.php_path != ""}-->
                            <!--{include_php_ex file=$HeaderTopNaviItem.php_path items=$HeaderTopNaviItem}-->
                        <!--{else}-->
                            <!--{include file=$HeaderTopNaviItem.tpl_path items=$HeaderTopNaviItem}-->
                        <!--{/if}-->
                        <!-- ▲<!--{$HeaderTopNaviItem.bloc_name}--> -->
                    <!--{/foreach}-->
                    <!--{* ▲上ナビ *}-->
                </div>
            <!--{/if}-->
            <!--{* ▲HeaderHeaderTop COLUMN*}-->
            <!--{* ▼HEADER *}-->
            <!--{if $arrPageLayout.header_chk != 2}-->
                <!--{include file= $header_tpl}-->
            <!--{/if}-->
            <!--{* ▲HEADER *}-->

            <div id="container" class="clearfix">

                <!--{* ▼TOP COLUMN*}-->
                <!--{if !empty($arrPageLayout.TopNavi)}-->
                    <div id="topcolumn">
                        <!--{* ▼上ナビ *}-->
                        <!--{foreach key=TopNaviKey item=TopNaviItem from=$arrPageLayout.TopNavi}-->
                            <!-- ▼<!--{$TopNaviItem.bloc_name}--> -->
                            <!--{if $TopNaviItem.php_path != ""}-->
                                <!--{include_php_ex file=$TopNaviItem.php_path items=$TopNaviItem}-->
                            <!--{else}-->
                                <!--{include file=$TopNaviItem.tpl_path items=$TopNaviItem}-->
                            <!--{/if}-->
                            <!-- ▲<!--{$TopNaviItem.bloc_name}--> -->
                        <!--{/foreach}-->
                        <!--{* ▲上ナビ *}-->
                    </div>
                <!--{/if}-->
                <!--{* ▲TOP COLUMN*}-->

                <div id="main_column_frame">
                <!--{* ▼CENTER COLUMN *}-->
                <div id="main_column" <!--{**}-->
                    class="colnum_order colnum<!--{$tpl_column_num|h}-->
                        <!--{if $tpl_column_num == 2}-->
                            <!--{" "}--><!--{if empty($arrPageLayout.LeftNavi)}-->left<!--{else}-->right<!--{/if}-->
                        <!--{/if}-->
                    "
                >
                    <!--{* ▼メイン上部 *}-->
                    <!--{if !empty($arrPageLayout.MainHead)}-->
                        <!--{foreach key=MainHeadKey item=MainHeadItem from=$arrPageLayout.MainHead}-->
                            <!-- ▼<!--{$MainHeadItem.bloc_name}--> -->
                            <!--{if $MainHeadItem.php_path != ""}-->
                                <!--{include_php_ex file=$MainHeadItem.php_path items=$MainHeadItem}-->
                            <!--{else}-->
                                <!--{include file=$MainHeadItem.tpl_path items=$MainHeadItem}-->
                            <!--{/if}-->
                            <!-- ▲<!--{$MainHeadItem.bloc_name}--> -->
                        <!--{/foreach}-->
                    <!--{/if}-->
                    <!--{* ▲メイン上部 *}-->

                    <!-- ▼メイン -->
                    <!--{include file=$tpl_mainpage}-->
                    <!-- ▲メイン -->

                    <!--{* ▼メイン下部 *}-->
                    <!--{if !empty($arrPageLayout.MainFoot)}-->
                        <!--{foreach key=MainFootKey item=MainFootItem from=$arrPageLayout.MainFoot}-->
                            <!-- ▼<!--{$MainFootItem.bloc_name}--> -->
                            <!--{if $MainFootItem.php_path != ""}-->
                                <!--{include_php_ex file=$MainFootItem.php_path items=$MainFootItem}-->
                            <!--{else}-->
                                <!--{include file=$MainFootItem.tpl_path items=$MainFootItem}-->
                            <!--{/if}-->
                            <!-- ▲<!--{$MainFootItem.bloc_name}--> -->
                        <!--{/foreach}-->
                    <!--{/if}-->
                    <!--{* ▲メイン下部 *}-->
                </div>
                <!--{* ▲CENTER COLUMN *}-->

                <!--{* ▼LEFT COLUMN *}-->
                <!--{if !empty($arrPageLayout.LeftNavi)}-->
                    <div id="leftcolumn" class="colnum_order side_column">
                        <!--{* ▼左ナビ *}-->
                        <!--{foreach key=LeftNaviKey item=LeftNaviItem from=$arrPageLayout.LeftNavi}-->
                            <!-- ▼<!--{$LeftNaviItem.bloc_name}--> -->
                            <!--{if $LeftNaviItem.php_path != ""}-->
                                <!--{include_php_ex file=$LeftNaviItem.php_path items=$LeftNaviItem}-->
                            <!--{else}-->
                                <!--{include file=$LeftNaviItem.tpl_path items=$LeftNaviItem}-->
                            <!--{/if}-->
                            <!-- ▲<!--{$LeftNaviItem.bloc_name}--> -->
                        <!--{/foreach}-->
                        <!--{* ▲左ナビ *}-->
                    </div>
                <!--{/if}-->
                <!--{* ▲LEFT COLUMN *}-->

                <!--{* ▼RIGHT COLUMN *}-->
                <!--{if !empty($arrPageLayout.RightNavi)}-->
                    <div id="rightcolumn" class="colnum_order side_column">
                        <!--{* ▼右ナビ *}-->
                        <!--{foreach key=RightNaviKey item=RightNaviItem from=$arrPageLayout.RightNavi}-->
                            <!-- ▼<!--{$RightNaviItem.bloc_name}--> -->
                            <!--{if $RightNaviItem.php_path != ""}-->
                                <!--{include_php_ex file=$RightNaviItem.php_path items=$RightNaviItem}-->
                            <!--{else}-->
                                <!--{include file=$RightNaviItem.tpl_path items=$RightNaviItem}-->
                            <!--{/if}-->
                            <!-- ▲<!--{$RightNaviItem.bloc_name}--> -->
                        <!--{/foreach}-->
                        <!--{* ▲右ナビ *}-->
                    </div>
                <!--{/if}-->
                <!--{* ▲RIGHT COLUMN *}-->
                </div>

                <!--{* ▼BOTTOM COLUMN*}-->
                <!--{if !empty($arrPageLayout.BottomNavi)}-->
                    <div id="bottomcolumn">
                        <!--{* ▼下ナビ *}-->
                        <!--{foreach key=BottomNaviKey item=BottomNaviItem from=$arrPageLayout.BottomNavi}-->
                            <!-- ▼<!--{$BottomNaviItem.bloc_name}--> -->
                            <!--{if $BottomNaviItem.php_path != ""}-->
                                <!--{include_php_ex file=$BottomNaviItem.php_path items=$BottomNaviItem}-->
                            <!--{else}-->
                                <!--{include file=$BottomNaviItem.tpl_path items=$BottomNaviItem}-->
                            <!--{/if}-->
                            <!-- ▲<!--{$BottomNaviItem.bloc_name}--> -->
                        <!--{/foreach}-->
                        <!--{* ▲下ナビ *}-->
                    </div>
                <!--{/if}-->
                <!--{* ▲BOTTOM COLUMN*}-->

            </div>

            <!--{* ▼FOOTER *}-->
            <!--{if $arrPageLayout.footer_chk != 2}-->
                <!--{include file=$footer_tpl}-->
            <!--{/if}-->
            <!--{* ▲FOOTER *}-->
            <!--{* ▼FooterBottom COLUMN*}-->
            <!--{if !empty($arrPageLayout.FooterBottomNavi)}-->
                <div id="footerbottomcolumn">
                    <!--{* ▼上ナビ *}-->
                    <!--{foreach key=FooterBottomNaviKey item=FooterBottomNaviItem from=$arrPageLayout.FooterBottomNavi}-->
                        <!-- ▼<!--{$FooterBottomNaviItem.bloc_name}--> -->
                        <!--{if $FooterBottomNaviItem.php_path != ""}-->
                            <!--{include_php_ex file=$FooterBottomNaviItem.php_path items=$FooterBottomNaviItem}-->
                        <!--{else}-->
                            <!--{include file=$FooterBottomNaviItem.tpl_path items=$FooterBottomNaviItem}-->
                        <!--{/if}-->
                        <!-- ▲<!--{$FooterBottomNaviItem.bloc_name}--> -->
                    <!--{/foreach}-->
                    <!--{* ▲上ナビ *}-->
                </div>
            <!--{/if}-->
            <!--{* ▲FooterBottom COLUMN*}-->
        </div>

    </body>
<!--{/strip}-->
