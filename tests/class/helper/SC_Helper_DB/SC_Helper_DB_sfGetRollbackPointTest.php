<?php

class SC_Helper_DB_sfGetRollbackPointTest extends SC_Helper_DB_TestBase
{
    /** @var Faker\Generator */
    protected $faker;
    /** @var int */
    protected $customer_id;
    /** @var int */
    protected $order_id;
    /** @var int */
    protected $customer_point;
    /** @var int */
    protected $order_point;
    /** @var int */
    protected $rollback_point;

    protected function setUp()
    {
        parent::setUp();
        $this->customer_id = $this->objGenerator->createCustomer();
        $this->order_id = $this->objGenerator->createOrder($this->customer_id);
        $arrCustomer = $this->objQuery->getRow('*', 'dtb_customer', 'customer_id = ?', [$this->customer_id]);
        $this->customer_point = $arrCustomer['point'];
    }

    public function sfGetRollbackPointProvider()
    {
        $faker = Faker\Factory::create('ja_JP');
        $use_point = $faker->randomNumber(5);
        $add_point = $faker->randomNumber(5);
        $random = $faker->randomNumber(5);
        return [
            [$use_point, $add_point, ORDER_NEW, $use_point, '利用ポイントのみ加算される'],
            [$use_point, $add_point, ORDER_CANCEL, 0, '変化なし'],
            [$use_point, $add_point, ORDER_PAY_WAIT, $use_point, '利用ポイントのみ加算される'],
            [$use_point, $add_point, ORDER_BACK_ORDER, $use_point, '利用ポイントのみ加算される'],
            [$use_point, $add_point, ORDER_DELIV, $use_point - $add_point, '利用ポイントは加算, 加算ポイントは減算される'],
            [$use_point, $add_point, ORDER_PRE_END, $use_point, '利用ポイントのみ加算される'],
            [$use_point, $add_point, ORDER_PENDING, $use_point, '利用ポイントのみ加算される']
        ];
    }

    /**
     * @dataProvider sfGetRollbackPointProvider
     *
     * @param int $use_point 使用ポイント
     * @param int $add_point 加算ポイント
     * @param int $order_status 受注ステータス
     * @param int $expected 会員のポイント + rollback_point の想定値
     * @param string $message
     */
    public function testSfGetRollbackPoint($use_point, $add_point, $order_status, $expected, $message)
    {
        $this->secenario($use_point, $add_point, $order_status);

        $this->assertEquals($this->customer_point, $this->order_point);
        $this->assertEquals($this->customer_point + $expected, $this->rollback_point, $message);
    }

    public function testSfGetRollbackPointNonCustomer()
    {
        $this->order_id = $this->objGenerator->createOrder();
        $this->secenario(100, 100, ORDER_CANCEL);

        $this->assertSame('', $this->order_point, '非会員の場合は空');
        $this->assertSame('', $this->rollback_point, '非会員の場合は空');
    }

    protected function secenario($use_point, $add_point, $order_status)
    {
        list($this->order_point, $this->rollback_point) = SC_Helper_DB_Ex::sfGetRollbackPoint($this->order_id, $use_point, $add_point, $order_status);
    }
}
