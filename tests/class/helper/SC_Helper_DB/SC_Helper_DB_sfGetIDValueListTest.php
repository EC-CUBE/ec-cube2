<?php

class SC_Helper_DB_sfGetIDValueListTest extends SC_Helper_DB_TestBase
{
    /** @var SC_Helper_DB_Ex */
    protected $objDb;
    /** @var array<int,string> */
    protected $makers;
    /** @var Faker\Generator */
    protected $faker;

    protected function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create('ja_JP');
        $this->setUpMaker();
    }

    public function testSfGetIDValueList()
    {
        $this->expected = $this->makers;
        $this->actual = SC_Helper_DB_Ex::sfGetIDValueList('dtb_maker', 'maker_id', 'name');
        $this->verify();
    }

    public function testSfGetIDValueListWithWhere()
    {
        $this->expected = array_slice($this->makers, 0, 3, true);
        $this->actual = SC_Helper_DB_Ex::sfGetIDValueList('dtb_maker', 'maker_id', 'name', 'maker_id IN (?, ?, ?)', array_keys($this->expected));
        $this->verify();
    }

    public function setUpMaker()
    {
        $delete_tables = ['dtb_maker'];
        foreach ($delete_tables as $table) {
            $this->objQuery->delete($table);

        }

        for ($i = 0; $i < 5; $i++) {
            $maker = [
                'maker_id' => $this->objQuery->nextVal('dtb_maker_maker_id'),
                'name' => $this->faker->company,
                'rank' => $i,
                'creator_id' => 2,
                'create_date' => 'CURRENT_TIMESTAMP',
                'update_date' => 'CURRENT_TIMESTAMP',
                'del_flg' => '0'
            ];
            $this->objQuery->insert('dtb_maker', $maker);
            $this->makers[$maker['maker_id']] = $maker['name'];
        }
    }
}
