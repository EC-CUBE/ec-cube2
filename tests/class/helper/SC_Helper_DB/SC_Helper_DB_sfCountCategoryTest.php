<?php

class SC_Helper_DB_sfCountCategoryTest extends SC_Helper_DB_TestBase
{
    /** @var SC_Helper_DB_Ex */
    protected $objDb;

    /** @var int */
    protected $product_id;
    /** @var int[] */
    protected $category_ids;

    protected function setUp()
    {
        parent::setUp();
        $this->objDb = new SC_Helper_DB_Ex();
        $this->setUpCategories();
    }

    public function testSfCountCategory()
    {
        $this->objDb->updateProductCategories($this->category_ids, $this->product_id);
        $this->objDb->sfCountCategory($this->objQuery);

        $category_counts = $this->objQuery->select('*', 'dtb_category_count');
        foreach ($category_counts as $arrCategoryCount) {
            $this->assertTrue(in_array($arrCategoryCount['category_id'], $this->category_ids));
            $this->assertEquals(1, $arrCategoryCount['product_count']);
        }
    }

    public function testSfCountCategoryWithTotalCount()
    {
        $this->objDb->addProductBeforCategories($this->category_ids[0], $this->product_id);
        $this->objDb->sfCountCategory($this->objQuery);
        $category_total_counts = $this->objQuery->select('*', 'dtb_category_total_count');
        $this->assertCount(1, $category_total_counts);
        foreach ($category_total_counts as $arrCategoryTotalCount) {
            $this->assertTrue(in_array($arrCategoryTotalCount['category_id'], $this->category_ids));
            $this->assertEquals(1, $arrCategoryTotalCount['product_count']);
        }
    }

    public function testSfCountCategoryWithNoStockHidden()
    {
        $this->objQuery->update(
            'dtb_products_class',
            [
                'stock' => 0,
                'stock_unlimited' => 0
            ],
            'product_id = ?', [$this->product_id]);
        $this->objDb->updateProductCategories($this->category_ids, $this->product_id);
        $this->objDb->sfCountCategory($this->objQuery, true, true);
        $category_counts = $this->objQuery->select('*', 'dtb_category_count');
        $this->assertEmpty($category_counts);
        $category_total_counts = $this->objQuery->select('*', 'dtb_category_total_count');
        $this->assertEmpty($category_total_counts);
    }

    public function testSfCountCategoryWithNotForceAllCount()
    {
        $this->objDb->updateProductCategories($this->category_ids, $this->product_id);
        $this->objDb->sfCountCategory($this->objQuery);

        $product_id2 = $this->objGenerator->createProduct();
        $this->objDb->updateProductCategories($this->category_ids, $product_id2);
        $this->objDb->sfCountCategory($this->objQuery, false);

        $category_counts = $this->objQuery->select('*', 'dtb_category_count');
        foreach ($category_counts as $arrCategoryCount) {
            $this->assertTrue(in_array($arrCategoryCount['category_id'], $this->category_ids));
            $this->assertEquals(2, $arrCategoryCount['product_count']);
        }
    }

    public function setUpCategories()
    {
        $delete_tables = ['dtb_category', 'dtb_product_categories', 'dtb_category_total_count', 'dtb_category_count'];
        foreach ($delete_tables as $table) {
            $this->objQuery->delete($table);

        }

        $this->product_id = $this->objGenerator->createProduct();
        $this->category_ids = $this->objGenerator->createCategories();
    }
}
