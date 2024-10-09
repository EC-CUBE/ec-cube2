<?php

class SC_CheckError_IP_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'IP_CHECK';
        $this->faker = Faker\Factory::create('ja_JP');
    }

    public function testIPCHECK()
    {
        $this->arrForm = [self::FORM_NAME => $this->faker->ipv4];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testIPCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testIPCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testIPCHECKWithError()
    {
        $this->arrForm = [
            self::FORM_NAME => '256.123.123.123',
        ];
        $this->expected = '※ IP_CHECKに正しい形式のIPアドレスを入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testIPCHECKWithMultiple()
    {
        $this->arrForm = [
            self::FORM_NAME => "{$this->faker->ipv4}\n{$this->faker->ipv4}\r\n{$this->faker->ipv4}\n\n{$this->faker->ipv4}",
        ];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testIPCHECKWithMultipleError()
    {
        $this->arrForm = [
            self::FORM_NAME => "{$this->faker->ipv4}\n256.11.1.1\r\n{$this->faker->ipv4}\n\n{$this->faker->ipv4}",
        ];
        $this->expected = '※ IP_CHECKに正しい形式のIPアドレスを入力してください。<br />';

        $this->scenario();
        $this->verify();
    }
}
