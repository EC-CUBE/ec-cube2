<?php

class SC_CustomerTest extends Common_TestCase
{
    /** @var int */
    protected $customer_id;

    /** @var Faker\Generator $faker */
    protected $faker;

    /** @var FixtureGenerator */
    protected $objGenerator;

    /** @var string */
    protected $email;

    /** @var array */
    protected $arrCustomer;

    /** @var SC_Customer_Ex */
    protected $objCustomer;

    protected function setUp()
    {
        parent::setUp();
        $this->objGenerator = new FixtureGenerator($this->objQuery);
        $this->faker = Faker\Factory::create('ja_JP');
        $this->setUpCustomer();
    }

    public function testGetCustomerDataFromEmailPass()
    {
        $this->assertTrue($this->objCustomer->getCustomerDataFromEmailPass('password', $this->email, true));

        $this->expected = $this->arrCustomer;
        $this->actual = $this->objCustomer->customer_data;
        $this->verify();
    }

    public function testGetCustomerDataFromEmailPassWithFailure()
    {
        $this->assertFalse($this->objCustomer->getCustomerDataFromEmailPass('XXXXX', $this->email, true));
        $this->assertNull($this->objCustomer->customer_data);
    }

    public function testGetCustomerDataFromEmailPassWithFailure2()
    {
        $this->assertFalse($this->objCustomer->getCustomerDataFromEmailPass('password', 'bad@example.com'));
        $this->assertNull($this->objCustomer->customer_data);
    }

    public function testSetLogin()
    {
        $this->objCustomer->setLogin($this->email);
        $this->assertNotEmpty($this->objCustomer->customer_data);
        $this->assertTrue(is_array($this->objCustomer->customer_data));
        $this->expected = $this->email;
        $this->actual = $this->objCustomer->getValue('email');
        $this->verify();
    }

    public function testSetLoginWithFailure()
    {
        $this->objCustomer->setLogin('XXX');
        $this->assertEmpty($this->objCustomer->customer_data);
        $this->assertTrue(is_array($this->objCustomer->customer_data));
    }

    public function testUpdateSession()
    {
        // do login
        $this->objCustomer->setLogin($this->email);
        $this->assertNotEmpty($this->objCustomer->customer_data);

        // leave to customer
        $this->objQuery->update('dtb_customer', ['del_flg' => 1], 'customer_id = ?', [$this->customer_id]);
        $this->objCustomer->updateSession();
        $this->assertEmpty($this->objCustomer->customer_data);
    }

    public function testEndSession()
    {
        // do login
        $this->objCustomer->setLogin($this->email);
        $this->assertNotEmpty($this->objCustomer->customer_data);

        $this->objCustomer->EndSession();
        $this->assertFalse($this->objCustomer->isLoginSuccess());
    }

    public function testIsLoginSuccess()
    {
        $this->assertFalse($this->objCustomer->isLoginSuccess());
        $this->objCustomer->setLogin($this->email);
        $this->assertTrue($this->objCustomer->isLoginSuccess());

        $this->objQuery->update(
            'dtb_customer',
            ['email' => microtime(true).'.'.$this->faker->safeEmail],
            'customer_id = ?',
            [$this->customer_id]
        );
        $this->assertFalse($this->objCustomer->isLoginSuccess());
    }

    public function testGetValue()
    {
        $this->objCustomer->setLogin($this->email);
        $this->expected = $this->arrCustomer['point'];
        $this->actual = $this->objCustomer->getValue('point');
        $this->verify();

        $this->assertEmpty($this->objCustomer->getValue('XXXXX'));
    }

    public function testSetValue()
    {
        $value = $this->faker->word;
        $this->objCustomer->setLogin($this->email);
        $this->assertFalse($this->objCustomer->hasValue('test'));

        $this->objCustomer->setValue('test', $value);
        $this->assertTrue($this->objCustomer->hasValue('test'));
        $this->expected = $value;
        $this->actual = $this->objCustomer->getValue('test');
        $this->verify();
    }

    public function testIsBirth()
    {
        $this->objQuery->update(
            'dtb_customer',
            ['birth' => date('Y-m-d H:i:s')],
            'customer_id = ?',
            [$this->customer_id]
        );
        $this->objCustomer->setLogin($this->email);
        $this->assertTrue($this->objCustomer->isBirthMonth());
    }

    public function testIsBirthWithFalse()
    {
        $this->objQuery->update(
            'dtb_customer',
            ['birth' => null],
            'customer_id = ?',
            [$this->customer_id]
        );
        $this->objCustomer->setLogin($this->email);
        $this->assertFalse($this->objCustomer->isBirthMonth());
    }

    public function testGetRemoteHost()
    {
        $this->assertEmpty($this->objCustomer->getRemoteHost());
        $_SERVER['REMOTE_ADDR'] = $this->faker->ipv4;
        $this->assertEquals($_SERVER['REMOTE_ADDR'], $this->objCustomer->getRemoteHost());
        $_SERVER['REMOTE_HOST'] = $this->faker->domainName;
        $this->assertEquals($_SERVER['REMOTE_HOST'], $this->objCustomer->getRemoteHost());
    }

    public function testUpdateOrderSummary()
    {
        $this->assertEquals(0, $this->arrCustomer['buy_total']);
        $this->assertEquals(0, $this->arrCustomer['buy_times']);
        $this->assertEquals('', $this->arrCustomer['last_buy_date']);
        $this->assertEquals('', $this->arrCustomer['first_buy_date']);

        $this->objGenerator->createOrder($this->customer_id);
        $this->objCustomer->updateOrderSummary($this->customer_id);
        $this->objCustomer->setLogin($this->email);

        $this->assertNotEquals(0, $this->objCustomer->getValue('buy_total'));
        $this->assertNotEquals(0, $this->objCustomer->getValue('buy_times'));
        $this->assertNotEquals('', $this->objCustomer->getValue('last_buy_date'));
        $this->assertNotEquals('', $this->objCustomer->getValue('first_buy_date'));
    }

    private function setUpCustomer()
    {
        $this->email = $this->faker->safeEmail;
        $this->customer_id = $this->objGenerator->createCustomer($this->email);
        $this->arrCustomer = $this->objQuery->getRow('*', 'dtb_customer', 'customer_id = ?', [$this->customer_id]);
        $this->objCustomer = new SC_Customer_Ex();
    }
}
