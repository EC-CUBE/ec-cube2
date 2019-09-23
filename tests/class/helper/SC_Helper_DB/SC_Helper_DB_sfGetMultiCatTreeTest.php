<?php

class SC_Helper_DB_sfGetMultiCatTreeTest extends SC_Helper_DB_TestBase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testSfGetMultiCatTree()
    {
        $this->objQuery->delete('dtb_category_total_count', 'category_id = ?', [4]);
        $categories_tree = SC_Helper_DB_Ex::sfGetMultiCatTree(3);

        $this->expected = [
            [1, 4],
            [6]
        ];
        $this->actual = array_map(function ($category_root) {
            return array_map(function ($category) {
                return $category['category_id'];
            }, $category_root);
        }, $categories_tree);


        $this->verify();
    }

    public function testSfGetMultiCatTreeCheckProductCount()
    {
        $this->objQuery->delete('dtb_category_total_count', 'category_id = ?', [4]);
        $categories_tree = SC_Helper_DB_Ex::sfGetMultiCatTree(3, true);

        $this->expected = [
            [1],
            [6]
        ];
        $this->actual = array_map(function ($category_root) {
            return array_map(function ($category) {
                return $category['category_id'];
            }, $category_root);
        }, $categories_tree);


        $this->verify();
    }
}
