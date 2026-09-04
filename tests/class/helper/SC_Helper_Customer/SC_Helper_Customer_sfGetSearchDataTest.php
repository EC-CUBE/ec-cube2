<?php

/**
 * SC_Helper_Customer::sfGetSearchData() のテスト.
 *
 * Issue #1398: 管理画面の会員一覧で LIMIT が SQL に反映されず
 * 全件取得される回帰 (PR #1116 起因) の再現テスト.
 */
class SC_Helper_Customer_sfGetSearchDataTest extends Common_TestCase
{
    /** @var string 投入する会員を識別するための email prefix */
    private $emailPrefix;

    /** @var int 投入する会員件数 (page_max を超える件数にすること) */
    private $totalCount = 30;

    /** @var int 1 ページあたりの最大表示件数 */
    private $pageMax = 10;

    protected function setUp(): void
    {
        parent::setUp();

        $this->emailPrefix = 'issue1398-'.uniqid().'-';
        for ($i = 0; $i < $this->totalCount; $i++) {
            $this->objGenerator->createCustomer(
                sprintf('%s%02d@example.com', $this->emailPrefix, $i)
            );
        }
    }

    /**
     * @param array $overrides 任意の上書きパラメータ
     *
     * @return array sfGetSearchData() に渡す検索パラメータ
     */
    private function buildSearchParam(array $overrides = [])
    {
        return array_merge([
            'search_page_max' => (string) $this->pageMax,
            'search_pageno' => '1',
            'search_email' => $this->emailPrefix,
        ], $overrides);
    }

    /**
     * page_max を指定したとき, 結果の件数が page_max と一致すること.
     * (修正前は LIMIT が SQL に反映されず, 投入件数全件が返る)
     */
    public function testSfGetSearchDataRespectsLimit()
    {
        [, $arrData] = SC_Helper_Customer_Ex::sfGetSearchData(
            $this->buildSearchParam()
        );

        $this->expected = $this->pageMax;
        $this->actual = count($arrData);
        $this->verify('LIMIT が SQL に反映され, page_max 件数で返却されること');
    }

    /**
     * 総件数 (linemax) は LIMIT の影響を受けず全件数を返すこと.
     */
    public function testSfGetSearchDataLineMaxReturnsTotalCount()
    {
        [$linemax] = SC_Helper_Customer_Ex::sfGetSearchData(
            $this->buildSearchParam()
        );

        $this->expected = $this->totalCount;
        $this->actual = (int) $linemax;
        $this->verify('総件数は LIMIT の影響を受けず全件数を返すこと');
    }

    /**
     * ページ遷移時に異なる customer_id が返ること (OFFSET の検証).
     */
    public function testSfGetSearchDataPaginationReturnsDifferentRows()
    {
        [, $arrPage1] = SC_Helper_Customer_Ex::sfGetSearchData(
            $this->buildSearchParam(['search_pageno' => '1'])
        );
        [, $arrPage2] = SC_Helper_Customer_Ex::sfGetSearchData(
            $this->buildSearchParam(['search_pageno' => '2'])
        );

        $ids1 = array_column($arrPage1, 'customer_id');
        $ids2 = array_column($arrPage2, 'customer_id');

        $this->expected = [];
        $this->actual = array_values(array_intersect($ids1, $ids2));
        $this->verify('OFFSET により 1 ページ目と 2 ページ目で重複が無いこと');
    }

    /**
     * $limitMode が空文字以外のとき, LIMIT を付与せず全件返ること (既存挙動の維持).
     */
    public function testSfGetSearchDataLimitModeBypassesLimit()
    {
        [, $arrData] = SC_Helper_Customer_Ex::sfGetSearchData(
            $this->buildSearchParam(),
            'all'
        );

        $this->expected = $this->totalCount;
        $this->actual = count($arrData);
        $this->verify('limitMode 指定時は LIMIT を付けず全件返却すること');
    }
}
