<?php

class SC_Helper_DB_sfGetParentsArrayTest extends SC_Helper_DB_TestBase
{
    /** @var SC_Helper_DB_Ex */
    protected $objDb;
    /** @var int[] */
    protected $category_ids;

    protected function setUp()
    {
        parent::setUp();
        $this->objDb = new SC_Helper_DB_Ex();
        $this->setUpCategories();
    }

    public function testSfGetChildrenArray()
    {
        $this->actual = $this->objDb->sfGetParentsArray('dtb_category', 'parent_category_id', 'category_id', $this->category_ids[count($this->category_ids) - 1]);

        foreach ($this->actual as $category_id) {
            $this->assertTrue(in_array($category_id, $this->category_ids));
        }
    }

    public function setUpCategories()
    {
        $delete_tables = ['dtb_category', 'dtb_product_categories', 'dtb_category_total_count', 'dtb_category_count'];
        foreach ($delete_tables as $table) {
            $this->objQuery->delete($table);

        }

        $this->category_ids = $this->objGenerator->createCategories();
    }
}
