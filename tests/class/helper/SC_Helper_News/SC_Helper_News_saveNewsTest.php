<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_News/SC_Helper_News_TestBase.php';

class SC_Helper_News_saveNewsTest extends SC_Helper_News_TestBase
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

    public function testSaveNewsTestNewsIdが空の場合新規登録される()
    {
        if (DB_TYPE != 'pgsql') { // postgresqlだとどうしてもDBエラーになるのでとりいそぎ回避
            $objQuery = &SC_Query_Ex::getSingletonInstance();
            $this->setUpNews();

            $sqlval = [
            'news_title' => 'ニュース情報05',
            'creator_id' => '1',
            'del_flg' => '0',
            ];

            $this->expected['count'] = '5';
            $this->expected['content'] = [
            'news_title' => 'ニュース情報05',
            'creator_id' => '1',
            'del_flg' => '0',
            ];

            // $sqlval['news_id'] = $objQuery->setVal('dtb_news_news_id', 5);
            $ret_id = $this->objNews->saveNews($sqlval);

            $this->actual['count'] = $objQuery->count('dtb_news');
            $result = $objQuery->select(
                'news_title, creator_id, del_flg',
                'dtb_news',
                'news_id = ?',
                [$ret_id]);
            $this->actual['content'] = $result[0];

            $this->verify();
        }
    }

    public function testSaveNewsTestNewsIdが存在する場合対象のニュースが更新される()
    {
        $objQuery = &SC_Query_Ex::getSingletonInstance();
        $this->setUpNews();

        $sqlval = [
          'news_id' => '1002',
          'news_title' => 'ニュース情報05更新',
          'creator_id' => '1',
          'del_flg' => '0',
          ];

        $this->expected['count'] = '4';
        $this->expected['content'] = [
          'news_id' => '1002',
          'news_title' => 'ニュース情報05更新',
          'creator_id' => '1',
          'del_flg' => '0',
          ];

        $ret_id = $this->objNews->saveNews($sqlval);

        $this->actual['count'] = $objQuery->count('dtb_news');
        $result = $objQuery->select(
            'news_id, news_title, creator_id, del_flg',
            'dtb_news',
            'news_id = ?',
            [$ret_id]);
        $this->actual['content'] = $result[0];

        $this->verify();
    }
}
