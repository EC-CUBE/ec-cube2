<?php

class SC_Helper_DB_updateProductCategoriesTest extends SC_Helper_DB_TestBase
{
    /** @var SC_Helper_DB_Ex */
    protected $objDb;

    /** @var int */
    protected $product_id;
    /** @var int */
    protected $category_id;

    protected function setUp()
    {
        parent::setUp();
        $this->objDb = new SC_Helper_DB_Ex();
        $this->product_id = $this->objGenerator->createProduct();
        $this->category_id = $this->objGenerator->createCategory();
    }

    public function testAddProductBeforCategories()
    {
        $this->objDb->addProductBeforCategories($this->category_id, $this->product_id);
        // 重複して実行してもエラーにならない
        $this->objDb->addProductBeforCategories($this->category_id, $this->product_id);
        $this->objDb->addProductBeforCategories($this->category_id, $this->product_id);
        $this->assertTrue($this->objQuery->exists('dtb_product_categories', 'product_id = ? AND category_id = ?', [$this->product_id, $this->category_id]));
    }

    public function testRemoveProductByCategories()
    {
        $this->objDb->addProductBeforCategories($this->category_id, $this->product_id);
        $this->objDb->removeProductByCategories($this->category_id, $this->product_id);
        $this->assertFalse($this->objQuery->exists('dtb_product_categories', 'product_id = ? AND category_id = ?', [$this->product_id, $this->category_id]));
    }

    public function testUpdateProductCategories()
    {
        $this->objDb->addProductBeforCategories($this->category_id, $this->product_id);

        $category_ids = $this->objGenerator->createCategories();
        $this->objDb->updateProductCategories($category_ids, $this->product_id);

        $actual_categories = $this->objQuery->getCol('category_id', 'dtb_product_categories', 'category_id IN ('.SC_Utils_Ex::repeatStrWithSeparator('?', count($category_ids)).')', $category_ids);

        $this->assertFalse(in_array($this->category_id, $actual_categories), '古いカテゴリは削除されているはず');

        $actual = array_diff($category_ids, $actual_categories);
        $this->assertEmpty($actual, 'すべてのカテゴリが登録されているはず');
    }
}
