<?php

// require DATA_REALDIR . 'module/fpdf/fpdf.php';
require DATA_REALDIR.'module/fpdi/japanese.php';

// japanese.php のバグ回避
$GLOBALS['SJIS_widths'] = $SJIS_widths;

class SC_Helper_FPDI extends PDF_Japanese
{
    /** SJIS 変換を有効とするか */
    public $enable_conv_sjis = true;

    /**
     * PDF_Japanese の明朝フォントに加えゴシックフォントを追加定義
     *
     * @return void
     */
    public function AddSJISFont($family = 'SJIS')
    {
        parent::AddSJISFont();
        $cw = $GLOBALS['SJIS_widths'];
        $c_map = '90msp-RKSJ-H';
        $registry = ['ordering' => 'Japan1', 'supplement' => 2];
        $this->AddCIDFonts('Gothic', 'KozGoPro-Medium-Acro,MS-PGothic,Osaka', $cw, $c_map, $registry);
    }

    public function SJISMultiCell($w, $h, $txt, $border = 0, $align = 'L', $fill = false)
    {
        $txt = $this->lfConvSjis($txt);

        $bak = $this->enable_conv_sjis;
        $this->enable_conv_sjis = false;

        parent::SJISMulticell($w, $h, $txt, $border, $align, $fill);

        $this->enable_conv_sjis = $bak;
    }

    /**
     * Colored table
     */
    public function FancyTable($header, $data, $w)
    {
        $base_x = $this->x;
        // Colors, line width and bold font
        $this->SetFillColor(216, 216, 216);
        $this->SetTextColor(0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');
        // Header
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        }
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(235, 235, 235);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Data
        $fill = false;
        $h = 4;
        foreach ($data as $row) {
            $x = $base_x;
            $h = 4;
            $i = 0;
            // XXX この処理を消すと2ページ目以降でセルごとに改ページされる。
            $this->Cell(0, $h, '', 0, 0, '', 0, '');
            $product_width = $this->GetStringWidth($row[0]);
            if ($w[0] < $product_width) {
                $output_lines = (int) ($product_width / $w[0]) + 1;
                $output_height = $output_lines * $h;
                if ($this->y + $output_height >= $this->PageBreakTrigger) {
                    $this->AddPage();
                }
            }
            foreach ($row as $col) {
                // 列位置
                $this->x = $x;
                // FIXME 汎用的ではない処理。この指定は呼び出し元で行うようにしたい。
                if ($i == 0) {
                    $align = 'L';
                } else {
                    $align = 'R';
                }
                $y_before = $this->y;
                $this->SJISMultiCell($w[$i], $h, $col, 1, $align, $fill);
                $h = $this->y - $y_before;
                $this->y = $y_before;
                $x += $w[$i];
                $i++;
            }
            $this->Ln();
            $fill = !$fill;
        }
        $this->SetFillColor(255);
        $this->x = $base_x;
    }

    /**
     * @param int $x
     * @param int $y
     */
    public function Text($x, $y, $txt)
    {
        parent::Text($x, $y, $this->lfConvSjis($txt));
    }

    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        parent::Cell($w, $h, $this->lfConvSjis($txt), $border, $ln, $align, $fill, $link);
    }

    // 文字コードSJIS変換 -> japanese.phpで使用出来る文字コードはSJIS-winのみ
    public function lfConvSjis($conv_str)
    {
        if ($this->enable_conv_sjis) {
            $conv_str = mb_convert_encoding($conv_str, 'SJIS-win', CHAR_CODE);
        }

        return $conv_str;
    }

    public function _out($s)
    {
        // Add a line to the document
        if ($this->state == 2) {
            $this->pages[$this->page] .= $s."\n";
        } elseif ($this->state == 1) {
            $this->_put($s);
        } elseif ($this->state == 0) {
            $this->Error('No page has been added yet');
        } elseif ($this->state == 3) {
            $this->Error('The document is closed');
        }
    }
}
