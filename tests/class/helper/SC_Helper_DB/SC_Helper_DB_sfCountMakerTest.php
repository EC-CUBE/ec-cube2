<?php

class SC_Helper_DB_sfCountMakerTest extends SC_Helper_DB_TestBase
{
    /** @var SC_Helper_DB_Ex */
    protected $objDb;

    /** @var int[] */
    protected $product_ids = [];
    /** @var array */
    protected $makers;
    /** @var Faker\Generator */
    protected $faker;

    protected function setUp()
    {
        parent::setUp();
        $this->objDb = new SC_Helper_DB_Ex();
        $this->faker = Faker\Factory::create('ja_JP');
        $this->setUpMakers();
    }

    public function testSfCountMaker()
    {
        $this->objDb->sfCountMaker($this->objQuery);
        $maker_counts = $this->objQuery->select('*', 'dtb_maker_count');
        $this->assertCount(5, $maker_counts);
        foreach ($maker_counts as $arrMakerCount) {
            $this->assertTrue(in_array($arrMakerCount['maker_id'], array_keys($this->makers)));
            $this->assertEquals(1, $arrMakerCount['product_count']);
        }
    }

    public function testSfGetMakerList()
    {
        $this->expected = $this->makers;
        $this->actual = $this->objDb->sfGetMakerList();
        $this->verify();
    }

    public function testSfGetMakerListWithWhere()
    {
        $this->expected = array_slice($this->makers, 0, 1, true);
        $this->actual = $this->objDb->sfGetMakerList('rank = 0');
        $this->verify();
    }

    public function testSfGetMakerListWithProductCheck()
    {
        $this->objDb->sfCountMaker($this->objQuery);
        $this->expected = $this->makers;
        $this->actual = $this->objDb->sfGetMakerList('', true);
        $this->verify();
    }

    public function setUpMakers()
    {
        $delete_tables = ['dtb_maker'];
        foreach ($delete_tables as $table) {
            $this->objQuery->delete($table);
        }
        for ($i = 0; $i < 5; $i++) {
            $this->product_ids[$i] = $this->objGenerator->createProduct();
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
            $this->objQuery->update('dtb_products', ['maker_id' => $maker['maker_id']], 'product_id = ?', [$this->product_ids[$i]]);
            $this->makers[$maker['maker_id']] = $maker['name'];
        }
    }
}
