<?php

class SC_FpdfTest extends Common_TestCase
{
    /** @var FixtureGenerator */
    protected $objGenerator;

    protected function setUp()
    {
        parent::setUp();
        $this->objGenerator = new FixtureGenerator();
    }

    public function test_正しいMediaBox情報が出力される()
    {
        $order_id = $this->objGenerator->createOrder(0, []);
        $objFormParam = new SC_FormParam_Ex();

        $fpdf = new \SC_Fpdf(true, "");
        $objFormParam->setParam(
            [
                'order_id' => $order_id,
                'year'  >= date('Y'),
                'month' => date('n'),
                'day'  => date('j'),

                'msg1' => 'このたびはお買上げいただきありがとうございます。',
                'msg2' => '下記の内容にて納品させていただきます。',
                'msg3' => 'ご確認くださいますよう、お願いいたします。',
            ]
        );

        ob_start();
        $fpdf->createPdf($objFormParam);
        $pdfContent = ob_get_clean();

        preg_match("|/MediaBox.+|", $pdfContent, $matches);
        $mediaBoxLine = $matches[0];

        // 不正な出力。
        $this->assertNotEquals('/MediaBox [0 0 0.00 0.00]', $mediaBoxLine);
        // 前行のテストをすり抜けた場合のダブルチェック。（本来の出力）
        $this->assertEquals('/MediaBox [0 0 595.28 841.89]', $mediaBoxLine);        
    }
}
