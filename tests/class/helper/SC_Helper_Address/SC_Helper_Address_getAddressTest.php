<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_Address/SC_Helper_Address_TestBase.php';

class SC_Helper_Address_getAddressTest extends SC_Helper_Address_TestBase
{
    protected SC_Helper_Address $objAddress;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objAddress = new SC_Helper_Address_Ex();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////

    public function testgetAddressTest会員の登録配送先が該当テーブルに存在しない場合FALSEを返す()
    {
        $this->setUpAddress();
        $other_deliv_id = '999';
        $customer_id = 1;
        $this->expected = false;
        $this->actual = $this->objAddress->getAddress($other_deliv_id, $customer_id);

        $this->verify('登録配送先取得');
    }

    public function testgetAddressTest不正な会員IDの場合FALSEを返す()
    {
        $this->setUpAddress();
        $other_deliv_id = '1001';
        $this->expected = false;
        $this->actual = $this->objAddress->getAddress($other_deliv_id, '');

        $this->verify('登録配送先取得');
    }

    public function testgetAddressTest会員の登録配送先が該当テーブルに存在する場合2を返す()
    {
        $this->setUpAddress();
        $other_deliv_id = '1001';
        $customer_id = 1;
        $this->expected = [
            'other_deliv_id' => '1001',
            'customer_id' => '1',
            'name01' => 'テスト',
            'name02' => 'に',
            'kana01' => 'テスト',
            'kana02' => 'ニ',
            'zip01' => '222',
            'zip02' => '2222',
            'pref' => '2',
            'addr01' => 'テスト1',
            'addr02' => 'テスト2',
            'tel01' => '000',
            'tel02' => '0000',
            'tel03' => '0000',
            'fax01' => '111',
            'fax02' => '1111',
            'fax03' => '1111',
            'country_id' => null,
            'company_name' => null,
            'zipcode' => null,
        ];
        $this->actual = $this->objAddress->getAddress($other_deliv_id, $customer_id);

        $this->verify('登録配送先取得');
    }
}
