<?php

$HOME = realpath(__DIR__) . '/../../../..';
require_once $HOME . '/tests/class/helper/SC_Helper_News/SC_Helper_News_TestBase.php';

class SC_Helper_News_rankDownTest extends SC_Helper_News_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->objNews = new SC_Helper_News_Ex();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////

    public function testRankDownTestニュースIDを指定した場合対象のランクが1減少する()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $this->setUpNews();
        $news_id = 1003;

        $this->expected = '2';

        $this->objNews->rankDown($news_id);

        $col = 'rank';
        $from = 'dtb_news';
        $where = 'news_id = ?';
        $whereVal = [$news_id];
        $res = $objQuery->getCol($col, $from, $where, $whereVal);
        $this->actual = $res[0];

        $this->verify();
    }
}
