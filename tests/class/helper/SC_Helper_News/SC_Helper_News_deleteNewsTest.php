<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_News/SC_Helper_News_TestBase.php';

class SC_Helper_News_deleteNewsTest extends SC_Helper_News_TestBase
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

    public function testDeleteNewsTestニュースIDを指定した場合対象のニュース情報が削除される()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $this->setUpNews();
        $news_id = 1002;

        $this->expected = '1';

        $this->objNews->deleteNews($news_id);

        $col = 'del_flg';
        $from = 'dtb_news';
        $where = 'news_id = ?';
        $whereVal = [$news_id];
        $res = $objQuery->getCol($col, $from, $where, $whereVal);
        $this->actual = $res[0];

        $this->verify();
    }
}
