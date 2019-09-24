<?php

class SC_Helper_DB_sfGetCategoryIdTest extends SC_Helper_DB_TestBase
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

    public function testSfGetCategoryId()
    {
        $this->expected = [$this->category_id];
        $this->actual = $this->objDb->sfGetCategoryId($this->product_id, $this->category_id);
        $this->verify();
    }

    public function testSfGetCategoryIdWithNotFound()
    {
        $this->expected = [];
        $this->actual = $this->objDb->sfGetCategoryId($this->product_id, PHP_INT_MAX);
        $this->verify();
    }

    public function testSfGetCategoryIdWithDeleted()
    {
        $this->objQuery->update('dtb_category', ['del_flg' => '1'], 'category_id = ?', [$this->category_id]);
        $this->expected = [];
        $this->actual = $this->objDb->sfGetCategoryId($this->product_id, $this->category_id);
        $this->verify();
    }

    public function testSfGetCategoryIdWithIncludedToDeleted()
    {
        $this->objQuery->update('dtb_category', ['del_flg' => '1'], 'category_id = ?', [$this->category_id]);
        $this->objGenerator->relateProductCategories($this->product_id, [$this->category_id]);
        $objCategory = new SC_Helper_Category_Ex();

        $this->expected = [$this->category_id];
        $this->actual = $this->objDb->sfGetCategoryId($this->product_id, $this->category_id);
        $this->verify('カテゴリが削除されている場合でも, dtb_product_categories にレコードが存在していれば category_id を返す');
    }

    public function sfGetCategoryIdWithProductVisibleProvider()
    {
        return [
            [1, true, true, '商品公開かつ closed = true はカテゴリIDを返す'],
            [2, true, true, '商品非公開かつ closed = true はカテゴリIDを返す'],
            [1, false, true, '商品公開かつ closed = false はカテゴリIDを返す'],
            [2, false, false, '商品非公開かつ closed = false は空の配列を返す']
        ];
    }
    /**
     * @dataProvider sfGetCategoryIdWithProductVisibleProvider
     *
     * @param int $product_status_id 商品公開ステータス
     * @param bool $closed 非公開の商品も含めるか
     * @param bool $actual 想定
     * @param string $message
     *
     */
    public function testSfGetCategoryIdWithProductVisible($product_status_id, $closed, $actual, $message)
    {
        $this->objQuery->update('dtb_category', ['del_flg' => '1'], 'category_id = ?', [$this->category_id]);
        $this->objQuery->update('dtb_products', ['status' => $product_status_id], 'product_id = ?', [$this->product_id]);
        $this->objGenerator->relateProductCategories($this->product_id, [$this->category_id]);
        $objCategory = new SC_Helper_Category_Ex();

        $this->expected = [$this->category_id];
        $this->actual = $this->objDb->sfGetCategoryId($this->product_id, $this->category_id, $closed);

        if ($actual === false) {
            $this->expected = [];
        }

        $this->verify($message);
    }
}
