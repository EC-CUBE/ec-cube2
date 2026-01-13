<?php

class LC_Page_Admin_System_ParameterTest extends Common_TestCase
{
    /**
     * PR #1157 によるデグレの再現テスト
     *
     * @see https://github.com/EC-CUBE/ec-cube2/pull/1296
     *
     * 変更前のコード（[$key, $arrForm[$key]]）では、日本語値が$value[1]に渡され、
     * createParam()で「判定対象配列キーに使用不可文字を含む」E_USER_ERRORが発生する
     *
     * 変更後のコード（[$key, $key]）では正常に動作する
     */
    public function testErrorCheckWithJapaneseParameterValue()
    {
        $page = new LC_Page_Admin_System_Parameter_Ex();
        $arrKeys = ['SAMPLE_ADDRESS'];
        $arrForm = ['SAMPLE_ADDRESS' => 'サンプル住所'];

        // 変更前のコードではE_USER_ERRORが発生する
        // 変更後のコードでは正常に動作する
        $arrErr = $page->errorCheck($arrKeys, $arrForm);

        // エラーがないことを確認
        $this->assertEmpty($arrErr);
    }
}
