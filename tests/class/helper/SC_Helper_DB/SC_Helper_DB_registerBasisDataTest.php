<?php
class SC_Helper_DB_registerBasisDataTest extends SC_Helper_DB_TestBase
{
    /** @var array */
    protected $BaseInfo;

    /** @var SC_Helper_DB_Ex */
    protected $objDb;

    /** @var Faker\Generator */
    protected $faker;

    protected function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create('ja_JP');
        $this->objDb = new SC_Helper_DB_Ex();
        $this->BaseInfo = $this->objDb->sfGetBasisData(true);
    }
    public function testRegisterBasisData()
    {
        $this->BaseInfo['company_name'] = $this->faker->company;
        SC_Helper_DB_Ex::registerBasisData($this->BaseInfo);

        $this->expected = $this->BaseInfo;
        $this->actual = $this->objDb->sfGetBasisData(true);

        $this->assertEquals($this->expected['company_name'], $this->actual['company_name']);
        $this->assertGreaterThan($this->expected['update_date'], $this->actual['update_date'], 'update_date が更新されているはず');
    }

    public function testRegisterBasisDataWithInsert()
    {
        $this->objQuery->delete('dtb_baseinfo');
        $this->assertEmpty($this->objDb->sfGetBasisData(true));

        $this->BaseInfo['company_name'] = $this->faker->company;
        $this->assertEquals($this->expected['company_name'], $this->actual['company_name']);
        SC_Helper_DB_Ex::registerBasisData($this->BaseInfo);

        $this->expected = $this->BaseInfo;
        $this->actual = $this->objDb->sfGetBasisData(true);
        $this->assertGreaterThan($this->expected['update_date'], $this->actual['update_date'], 'update_date が更新されているはず');
    }
}
