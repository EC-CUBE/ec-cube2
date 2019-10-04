<?php

class SC_CustomerListTest extends Common_TestCase
{
    /** @var Faker\Generator $faker */
    protected $faker;

    /** @var FixtureGenerator */
    protected $objGenerator;

    /** @var int[] */
    protected $customer_ids;

    /** @var array */
    protected $params = [];

    protected function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create('ja_JP');
        $this->objGenerator = new FixtureGenerator();
    }

    public function testCreateInstance()
    {
        $this->setUpCustomers(1);
        $this->scenario();

        $this->assertCount(1, $this->actual);
    }

    public function testSearchCustomerId()
    {
        $this->customer_ids = $this->setUpCustomers(3);
        $this->params['search_customer_id'] = $this->customer_ids[0];

        $this->scenario();
        $this->expected = $this->getCustomerList([$this->customer_ids[0]]);
        $this->verify();
    }

    protected function scenario()
    {
        $objSelect = new SC_CustomerList_Ex($this->params, 'customer');
        $this->actual = $this->objQuery->getAll($objSelect->getList(), $objSelect->arrVal);
    }

    /**
     * 比較用の顧客の配列を返す.
     *
     * @param int[] $customer_ids customer_id の配列
     */
    protected function getCustomerList($customer_ids)
    {
        return $this->objQuery->getAll('SELECT customer_id,name01,name02,kana01,kana02,sex,email,email_mobile,tel01,tel02,tel03,pref,status,update_date,mailmaga_flg FROM dtb_customer WHERE customer_id IN ('.SC_Utils_Ex::repeatStrWithSeparator('?', count($customer_ids), ',').')', $customer_ids);
    }
    /**
     * @param int $n 生成数
     * @return int[] customer_id の配列
     */
    protected function setUpCustomers($n)
    {
        $this->objQuery->delete('dtb_customer');
        $result = [];
        for ($i = 0; $i < $n; $i++) {
            $result[] = $this->objGenerator->createCustomer();
        }
        return $result;
    }
}
