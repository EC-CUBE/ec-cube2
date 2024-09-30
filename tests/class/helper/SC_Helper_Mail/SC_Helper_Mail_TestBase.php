<?php

$HOME = realpath(__DIR__).'/../../../../';
require_once $HOME.'/tests/class/Common_TestCase.php';

class SC_Helper_Mail_TestBase extends Common_TestCase
{
    /** @var int */
    protected $customer_id;

    /** @var Faker\Generator */
    protected $faker;

    /** @var string */
    protected $email;

    /** @var array */
    protected $arrCustomer;

    /** @var SC_Customer_Ex */
    protected $objCustomer;

    /** @var SC_Helper_Mail_Ex */
    protected $objHelperMail;

    protected function setUp()
    {
        parent::setUp();
        $this->checkMailCatcherStatus();
        $this->objHelperMail = new SC_Helper_Mail_Ex();

        $this->faker = Faker\Factory::create('ja_JP');
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    protected function setUpCustomer($properties = [])
    {
        $this->email = $this->faker->safeEmail;
        $this->customer_id = $this->objGenerator->createCustomer($this->email, $properties);
        $this->arrCustomer = $this->objQuery->getRow('*', 'dtb_customer', 'customer_id = ?', [$this->customer_id]);
        $this->objCustomer = new SC_Customer_Ex();
    }
}
