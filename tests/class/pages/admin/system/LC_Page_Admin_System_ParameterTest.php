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
     * 変更後のコード（[$key, $key]）ではE_USER_ERRORは発生しない
     * （ただし、EVAL_CHECKによるバリデーションエラーは返される）
     */
    public function testErrorCheckDoesNotCauseFatalErrorWithJapaneseValue()
    {
        $page = new LC_Page_Admin_System_Parameter_Ex();
        $arrKeys = ['SAMPLE_ADDRESS'];
        $arrForm = ['SAMPLE_ADDRESS' => 'サンプル住所'];

        // 変更前のコードではE_USER_ERRORが発生する
        // 変更後のコードではE_USER_ERRORは発生しない（Fatal Errorにならない）
        $arrErr = $page->errorCheck($arrKeys, $arrForm);

        // Fatal Errorが発生せず、配列が返されることを確認
        $this->assertIsArray($arrErr);
    }
}
