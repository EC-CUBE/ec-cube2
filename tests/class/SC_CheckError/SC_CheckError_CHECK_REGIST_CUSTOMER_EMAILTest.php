<?php

class SC_CheckError_CHECK_REGIST_CUSTOMER_EMAILTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;
    /** @var string */
    protected $email;
    /** @var int */
    protected $customer_id;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'CHECK_REGIST_CUSTOMER_EMAIL';
        $this->faker = Faker\Factory::create('ja_JP');
        $this->email = $this->faker->safeEmail;
        $this->customer_id = SC_Helper_Customer_Ex::sfEditCustomerData(
            [
                'name01' => $this->faker->lastName,
                'name02' => $this->faker->firstName,
                'email' => $this->email,
                'secret_key' => uniqid(),
            ]
        );
    }

    protected function tearDown()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->delete('dtb_customer', 'customer_id = ?', [$this->customer_id]);
        parent::tearDown();
    }

    public function testCHECKREGISTCUSTOMEREMAIL()
    {
        $this->arrForm = [self::FORM_NAME => $this->faker->freeEmail];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testCHECKREGISTCUSTOMEREMAILWithExists()
    {
        $this->arrForm = [self::FORM_NAME => $this->email];
        $this->expected = '※ すでに会員登録で使用されているCHECK_REGIST_CUSTOMER_EMAILです。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testCHECKREGISTCUSTOMEREMAILWithExpired()
    {
        SC_Helper_Customer_Ex::sfEditCustomerData(
            [
                'del_flg' => 1,
            ],
            $this->customer_id
        );
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->update('dtb_customer', ['update_date' => date('Y-m-d H:i:s')], 'customer_id = ?', [$this->customer_id]);

        $this->arrForm = [self::FORM_NAME => $this->email];
        $this->expected = '※ 退会から一定期間の間は、同じCHECK_REGIST_CUSTOMER_EMAILを使用することはできません。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testCHECKREGISTCUSTOMEREMAILWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testCHECKREGISTCUSTOMEREMAILWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }
}
