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
<div style="margin:20px 10px; padding:0; width:100%; height:350px;" id="graphField">Now Loading ...</div>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {

        var data = data = google.visualization.arrayToDataTable([
          ['職業', '売上'],
        <!--{foreach from=$arrResults key="key" item="item" name="line"}-->
          ['<!--{$item.job_name}-->', <!--{$item.total|default:0}-->],
        <!--{/foreach }-->
        ]);

        var options = {
        };

        var chart = new google.visualization.PieChart(document.getElementById('graphField'));

        chart.draw(data, options);
      }
</script>

<table id="total-job" class="list">
    <tr>
        <th>順位</th>
        <th>職業</th>
        <th>購入件数</th>
        <th>購入合計</th>
        <th>購入平均</th>
    </tr>

    <!--{section name=cnt loop=$arrResults}-->
        <!--{* 色分け判定 *}-->
        <!--{assign var=type value="`$smarty.section.cnt.index%2`"}-->
        <!--{if $type == 0}-->
            <!--{* 偶数行 *}-->
            <!--{assign var=color value="even"}-->
        <!--{else}-->
            <!--{* 奇数行 *}-->
            <!--{assign var=color value="odd"}-->
        <!--{/if}-->

        <tr class="<!--{$color}-->">
            <td class="center"><!--{*順位*}--><!--{$smarty.section.cnt.iteration}--></td>
            <td class="center"><!--{*職業*}--><!--{$arrResults[cnt].job_name}--></td>
            <td class="right"><!--{*購入件数*}--><!--{$arrResults[cnt].order_count}-->件</td>
            <td class="right"><!--{*購入合計*}--><!--{$arrResults[cnt].total|n2s}-->円</td>
            <td class="right"><!--{*購入平均*}--><!--{$arrResults[cnt].total_average|n2s}-->円</td>
        </tr>
    <!--{/section}-->

    <tr>
        <th>順位</span></th>
        <th>職業</span></th>
        <th>購入件数</span></th>
        <th>購入合計</span></th>
        <th>購入平均</span></th>
    </tr>
</table>
