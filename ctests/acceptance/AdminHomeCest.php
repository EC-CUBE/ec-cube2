<?php


class AdminHomeCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }


    public function testLogin(AcceptanceTester $I)
    {
        $I->wantTo('管理画面に正常にログインできるかを確認する');
        $I->amOnPage('/admin');

        $I->fillField('input[name=login_id]', 'admin');
        $I->fillField('input[name=password]', 'password');
        $I->click(['css' => '.btn-tool-format']);

        $I->see('ホーム');
    }
}
