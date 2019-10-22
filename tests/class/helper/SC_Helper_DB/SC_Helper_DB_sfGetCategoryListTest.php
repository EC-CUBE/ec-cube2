<?php

class SC_Helper_DB_sfGetCategoryListTest extends SC_Helper_DB_TestBase
{
    /** @var SC_Helper_DB */
    protected $objDb;

    protected function setUp()
    {
        parent::setUp();
        $this->objDb = new SC_Helper_DB_Ex();
    }

    public function testSfGetCategoryList()
    {
        $category_id = 3;
        $this->objQuery->delete('dtb_category_total_count', 'category_id = ?', [$category_id]);

        $this->actual = $this->objDb->sfGetCategoryList('category_id = '.$category_id);
        $this->expected = [
            $category_id => '>>お菓子'
        ];

        $this->verify();
    }

    public function testSfGetCategoryListCheckProductCount()
    {
        $category_ids = [1, 2, 3];
        $this->actual = $this->objDb->sfGetCategoryList('T1.category_id IN ('.implode(',', $category_ids).')', true);

        $this->expected = [
            1 => '>食品',
            3 => '>>お菓子'
        ];

        $this->verify();
    }

    public function testSfGetCategoryListChangeHeader()
    {
        $category_ids = [1, 2, 3];
        $this->actual = $this->objDb->sfGetCategoryList('T1.category_id IN ('.implode(',', $category_ids).')', true, '+');

        $this->expected = [
            1 => '+食品',
            3 => '++お菓子'
        ];

        $this->verify();
    }
}
