<?php

class SC_Helper_DB_sfGetCatTreeTest extends SC_Helper_DB_TestBase
{
    /** @var int[] */
    protected $category_ids;

    protected function setUp()
    {
        parent::setUp();
        $this->category_ids = $this->objGenerator->createCategories();
    }

    public function testSfGetCatTree()
    {
        $arrCategories = SC_Helper_DB_Ex::sfGetCatTree($this->category_ids[count($this->category_ids) - 1], false);

        $this->objQuery->setOrder('rank DESC');
        $this->expected = $this->objQuery->getCol('category_id', 'dtb_category', 'del_flg = 0');
        $this->actual = array_map(function ($arrCategory) {
            return $arrCategory['category_id'];
        }, $arrCategories);

        $this->verify();
    }

    public function testSfGetCatTreeCheckProductCount()
    {
        $arrCategories = SC_Helper_DB_Ex::sfGetCatTree($this->category_ids[count($this->category_ids) - 1], true);

        $this->objQuery->setOrder('category_id DESC');
        $this->expected = $this->objQuery->getCol('category_id', 'dtb_category_total_count', 'product_count > 0');
        $this->actual = array_map(function ($arrCategory) {
            return $arrCategory['category_id'];
        }, $arrCategories);
        rsort($this->actual);

        $this->verify();
    }
}
