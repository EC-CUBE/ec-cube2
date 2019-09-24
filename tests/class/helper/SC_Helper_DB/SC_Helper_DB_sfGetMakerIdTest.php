<?php
class SC_Helper_DB_sfGetMakerIdTest extends SC_Helper_DB_TestBase
{
    /** @var SC_Helper_DB_Ex */
    protected $objDb;

    /** @var int[] */
    protected $product_ids;
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

    public function testsfGetMakerId()
    {
        $maker_id = current(array_keys($this->makers));
        $this->expected = [$maker_id];
        $this->actual = $this->objDb->sfGetMakerId($this->product_ids[0], $maker_id);
        $this->verify();
    }

    public function testsfGetMakerIdWithNotFound()
    {
        $this->expected = [];
        $this->actual = $this->objDb->sfGetMakerId(PHP_INT_MAX, PHP_INT_MAX);
        $this->verify();
    }

    public function testsfGetMakerIdWithDeleted()
    {
        $maker_id = current(array_keys($this->makers));
        $this->objQuery->update('dtb_maker', ['del_flg' => '1'], 'maker_id = ?', [$maker_id]);
        $this->objQuery->update('dtb_products', ['maker_id' => null], 'product_id = ?', [$this->product_ids[0]]);

        $this->expected = [null]; // XXX maker_id IS NULL の場合は NULL を含んだ配列を返す
        $this->actual = $this->objDb->sfGetMakerId($this->product_ids[0], $maker_id);
        $this->verify('dtb_maker に存在しなければ dtb_product.maker_id の値を返す');
    }

    public function testsfGetMakerIdWithIncludedToDeleted()
    {
        $maker_id = current(array_keys($this->makers));
        $this->objQuery->update('dtb_maker', ['del_flg' => '1'], 'maker_id = ?', [$maker_id]);

        $this->expected = [$maker_id];
        $this->actual = $this->objDb->sfGetMakerId($this->product_ids[0], $maker_id);
        $this->verify('dtb_maker が削除されている場合でも, dtb_products にレコードが存在していれば maker_id を返す');
    }

    public function sfGetMakerIdWithProductVisibleProvider()
    {
        return [
            [1, true, true, '商品公開かつ closed = true はメーカーIDを返す'],
            [2, true, true, '商品非公開かつ closed = true はメーカーIDを返す'],
            [1, false, true, '商品公開かつ closed = false はメーカーIDを返す'],
            [2, false, false, '商品非公開かつ closed = false は空の配列を返す']
        ];
    }
    /**
     * @dataProvider sfGetMakerIdWithProductVisibleProvider
     *
     * @param int $product_status_id 商品公開ステータス
     * @param bool $closed 非公開の商品も含めるか
     * @param bool $actual 想定
     * @param string $message
     *
     */
    public function testsfGetMakerIdWithProductVisible($product_status_id, $closed, $actual, $message)
    {
        $maker_id = current(array_keys($this->makers));
        $this->objQuery->update('dtb_maker', ['del_flg' => '1'], 'maker_id = ?', [$maker_id]);
        $this->objQuery->update('dtb_products', ['status' => $product_status_id], 'product_id = ?', [$this->product_ids[0]]);

        $this->expected = [$maker_id];
        $this->actual = $this->objDb->sfGetMakerId($this->product_ids[0], $maker_id, $closed);

        if ($actual === false) {
            $this->expected = [];
        }

        $this->verify($message);
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
