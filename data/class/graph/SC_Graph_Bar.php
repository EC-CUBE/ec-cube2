<?php
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

// 棒グラフ生成クラス
class SC_Graph_Bar extends SC_Graph_Line_Ex
{
    // コンストラクタ
    public function __construct(
        $bgw = BG_WIDTH,
        $bgh = BG_HEIGHT,
        $left = LINE_LEFT,
        $top = LINE_TOP,
        $area_width = LINE_AREA_WIDTH,
        $area_height = LINE_AREA_HEIGHT
    ) {
        parent::__construct($bgw, $bgh, $left, $top, $area_width, $area_height);
    }

    // グラフの描画
    public function drawGraph()
    {
        $this->drawYLine();
        $this->drawXLine(true);

        // 棒グラフの描画
        for ($i = 0; $i < $this->line_max; $i++) {
            $this->drawBar($i);
        }

        // ラベルの描画
        for ($i = 0; $i < $this->line_max; $i++) {
            $this->drawLabel($i);
        }

        // 凡例の描画
        $this->drawLegend();
    }

    // 棒グラフの描画

    /**
     * @param int $line_no
     */
    public function drawBar($line_no)
    {
        $arrPointList = $this->arrPointList[$line_no];
        // データ数を数える
        $count = count($arrPointList);
        // 半目盛りの幅を求める
        $half_scale = (int) ($this->area_width / ($count + 1) / 2);
        // 目盛りの幅を求める
        $scale_width = (int) ($this->area_width / ($count + 1));
        // 棒グラフのサイズを求める
        $bar_width = (int) (($scale_width - (BAR_PAD * 2)) / $this->line_max);
        // 色数の取得
        $c_max = count($this->arrColor);
        for ($i = 0; $i < $count; $i++) {
            $left = $arrPointList[$i][0] - $half_scale + BAR_PAD + ($bar_width * $line_no);
            $top = $arrPointList[$i][1];
            $right = $left + $bar_width;
            $bottom = $this->top + $this->area_height;

            // 影の描画
            if ($this->shade_on) {
                imagefilledrectangle($this->image, $left + 2, $top + 2, $right + 2, $bottom, $this->shade_color);
            }
            // imagefilledrectangle($this->image, $left, $top, $right, $bottom, $this->arrColor[($i % $c_max)]);
            imagefilledrectangle($this->image, $left, $top, $right, $bottom, $this->arrColor[$line_no]);
            imagerectangle($this->image, $left, $top, $right, $bottom, $this->flame_color);
        }
    }

    // ラベルを描画する

    /**
     * @param int $line_no
     */
    public function drawLabel($line_no)
    {
        $arrData = $this->arrDataList[$line_no];
        $arrPointList = $this->arrPointList[$line_no];
        $count = count($arrPointList);
        for ($i = 0; $i < $count; $i++) {
            $x = $arrPointList[$i][0];
            $y = $arrPointList[$i][1];
            $text_width = $this->getTextWidth(number_format($arrData[$i]), FONT_SIZE);
            $y_pos = $y - FONT_SIZE - 5;
            $x_pos = $x - $text_width / 2;
            $this->setText(FONT_SIZE, $x_pos, $y_pos, number_format($arrData[$i]));
        }
    }
}
