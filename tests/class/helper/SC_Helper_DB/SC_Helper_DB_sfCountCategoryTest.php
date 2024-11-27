<?php

class SC_Helper_DB_sfCountCategoryTest extends SC_Helper_DB_TestBase
{
    /** @var SC_Helper_DB_Ex */
    protected $objDb;

    /** @var int */
    protected $product_id;
    /** @var int[] */
    protected $category_ids;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objDb = new SC_Helper_DB_Ex();
        $this->setUpCategories();
    }

    public function testSfCountCategory()
    {
        // 全カテゴリに1商品を割り当てる
        $this->objDb->updateProductCategories($this->category_ids, $this->product_id);
        $this->objDb->sfCountCategory($this->objQuery);

        // 検証
        $category_counts = $this->objQuery->select('*', 'dtb_category_count');
        $category_ids = [];
        foreach ($category_counts as $arrCategoryCount) {
            $category_ids[] = $arrCategoryCount['category_id'];
            $this->assertEquals(1, $arrCategoryCount['product_count']);
        }

        // カテゴリに過不足がないことを検証する。
        // TODO: phpUnit 7.5 以降になったら、assertEqualsCanonicalizing を使うべき。ただ、型の違いが無視されるか分からない。(array_diff は無視されるので都合が良い。)
        $this->assertSame(
            array_diff($category_ids, $this->category_ids),   // 過剰を検出
            array_diff($this->category_ids, $category_ids),   // 不足を検出
            '不足 (that 側に出力) または過剰 (to 側に出力) がある。'
        );
    }

    public function testSfCountCategoryWithTotalCount()
    {
        // できるだけ深い階層のカテゴリをテスト対象とする。
        $arrCategory = $this->objQuery->getRow('*', 'dtb_category', '0=0 ORDER BY level DESC LIMIT 1');

        // 商品カテゴリ登録
        $this->objDb->addProductBeforCategories($arrCategory['category_id'], $this->product_id);
        $this->objDb->sfCountCategory($this->objQuery);
        // 検証
        $category_total_counts = $this->objQuery->select('*', 'dtb_category_total_count');
        $this->assertCount((int) $arrCategory['level'], $category_total_counts);
        foreach ($category_total_counts as $arrCategoryTotalCount) {
            $this->assertTrue(in_array($arrCategoryTotalCount['category_id'], $this->category_ids));
            $this->assertEquals(1, $arrCategoryTotalCount['product_count']);
        }

        // 商品カテゴリ削除
        $this->objDb->removeProductByCategories($arrCategory['category_id'], $this->product_id);
        $this->objDb->sfCountCategory($this->objQuery);
        // 検証
        $category_ids = $this->objQuery->getCol('category_id', 'dtb_category_total_count');
        $this->assertEmpty($category_ids, 'dtb_category_total_count にデータが残っている。: '.var_export($category_ids, true));
    }

    public function testSfCountCategoryWithNoStockHidden()
    {
        $this->objQuery->update(
            'dtb_products_class',
            [
                'stock' => 0,
                'stock_unlimited' => 0,
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
