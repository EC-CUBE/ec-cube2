<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_Kiyaku/SC_Helper_Kiyaku_TestBase.php';

class SC_Helper_Kiyaku_isTitleExistTest extends SC_Helper_Kiyaku_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->objKiyaku = new SC_Helper_Kiyaku_Ex();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////

    public function testisTitleExistTestタイトルが重複しているものがある場合Trueを返す()
    {
        $this->setUpKiyaku();
        $title = 'test1';
        $kiyaku_id = 1001;

        $this->expected = true;

        $this->actual = $this->objKiyaku->isTitleExist($title, $kiyaku_id);

        $this->verify('規約タイトル重複');
    }

    public function testisTitleExistTest新規登録でタイトルが重複しているものがある場合Trueを返す()
    {
        $this->setUpKiyaku();
        $title = 'test1';

        $this->expected = true;

        $this->actual = $this->objKiyaku->isTitleExist($title);

        $this->verify('規約タイトル重複');
    }

    public function testisTitleExistTestタイトルが重複していない場合Falseを返す()
    {
        $this->setUpKiyaku();
        $title = 'xxxx';
        $kiyaku_id = 1001;

        $this->expected = false;

        $this->actual = $this->objKiyaku->isTitleExist($title, $kiyaku_id);

        $this->verify('規約タイトル重複');
    }

    public function testisTitleExistTest新規登録でタイトルが重複していない場合Falseを返す()
    {
        $this->setUpKiyaku();
        $title = 'xxx';

        $this->expected = false;

        $this->actual = $this->objKiyaku->isTitleExist($title);

        $this->verify('規約タイトル重複');
    }
}
