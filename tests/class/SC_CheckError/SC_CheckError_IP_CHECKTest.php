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

    public function testIP_CHECK()
    {
        $this->arrForm = [self::FORM_NAME => $this->faker->ipv4];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testIP_CHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testIP_CHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testIP_CHECKWithError()
    {
        $this->arrForm = [
            self::FORM_NAME => '256.123.123.123'
        ];
        $this->expected = '※ IP_CHECKに正しい形式のIPアドレスを入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testIP_CHECKWithMultiple()
    {
        $this->arrForm = [
            self::FORM_NAME => "{$this->faker->ipv4}\n{$this->faker->ipv4}\r\n{$this->faker->ipv4}\n\n{$this->faker->ipv4}"
        ];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testIP_CHECKWithMultipleError()
    {
        $this->arrForm = [
            self::FORM_NAME => "{$this->faker->ipv4}\n256.11.1.1\r\n{$this->faker->ipv4}\n\n{$this->faker->ipv4}"
        ];
        $this->expected = '※ IP_CHECKに正しい形式のIPアドレスを入力してください。<br />';

        $this->scenario();
        $this->verify();
    }
}
