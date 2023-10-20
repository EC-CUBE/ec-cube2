<!--{* ★ ページャここから ★ *}-->
<div class="pager">
    <ul>
    <!--{foreach from=$arrPagenavi.arrPageno key="key" item="item"}-->
        <li<!--{if $arrPagenavi.now_page == $item}--> class="on"<!--{/if}-->><a href="#" onclick="eccube.moveNaviPage(<!--{$item|h}-->, '<!--{$arrPagenavi.mode|h}-->'); return false;"><span><!--{$item|h}--></span></a></li>
    <!--{/foreach}-->
    </ul>
</div>
<!--{* ★ ページャここまで ★ *}-->
