<?php

class SC_CartSessionTest extends Common_TestCase
{
    /**
     * @var SC_CartSession
     */
    protected $objCartSession;

    protected function setUp()
    {
        parent::setUp();
        $this->objCartSession = new SC_CartSession_Ex();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $objDb = new SC_Helper_DB_Ex();
        $objDb->sfGetBasisData(true);
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('SC_CartSession', $this->objCartSession);
        $this->assertNotNull($this->objCartSession->cartSession);
    }

    public function testAddProduct()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProductClass = $objQuery->getRow('*', 'dtb_products_class', 'del_flg = ? AND product_type_id = ?', ['0', PRODUCT_TYPE_NORMAL]);
        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 1);
        // duplicate
        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 1);

        $product_type_ids = $this->objCartSession->getKeys();
        $this->assertEquals([PRODUCT_TYPE_NORMAL], $product_type_ids, '商品種別は通常商品');

        foreach ($product_type_ids as $cartKey) {
            $quantity = $this->objCartSession->getTotalQuantity($cartKey);
            $this->assertEquals(2, $quantity);
        }
    }

    public function testAddProductWithMultipleProductType()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProductClassNormal = $objQuery->getRow('*', 'dtb_products_class', 'del_flg = ? AND product_type_id = ?', ['0', PRODUCT_TYPE_NORMAL]);
        $this->objCartSession->addProduct($arrProductClassNormal['product_class_id'], 1);

        $arrProductClassDownload = $objQuery->getRow('*', 'dtb_products_class', 'del_flg = ? AND product_type_id = ?', ['0', PRODUCT_TYPE_DOWNLOAD]);
        $this->objCartSession->addProduct($arrProductClassDownload['product_class_id'], 2);

        $this->assertTrue($this->objCartSession->isMultiple());
        $product_type_ids = $this->objCartSession->getKeys();
        $this->assertEquals(
            [
                PRODUCT_TYPE_NORMAL,
                PRODUCT_TYPE_DOWNLOAD
            ],
            $product_type_ids,
            '商品種別は通常商品とダウンロード商品'
        );

        $this->assertTrue($this->objCartSession->hasProductType(PRODUCT_TYPE_NORMAL));
        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(PRODUCT_TYPE_NORMAL));
        $this->assertTrue($this->objCartSession->hasProductType(PRODUCT_TYPE_DOWNLOAD));
        $this->assertEquals(2, $this->objCartSession->getTotalQuantity(PRODUCT_TYPE_DOWNLOAD));
    }

    public function testAddProductWithEmptyProductType()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProductClass = $objQuery->getRow('*', 'dtb_products_class', 'del_flg = ? AND product_type_id = ?', ['0', PRODUCT_TYPE_NORMAL]);
        // quantity of zero
        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 0);

        $product_type_ids = $this->objCartSession->getKeys();
        $this->assertEmpty($product_type_ids, '数量0の商品種別は削除される');
    }

    public function testSaveCurrentCart()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProductClass = $objQuery->getRow('*', 'dtb_products_class', 'del_flg = ? AND product_type_id = ?', ['0', PRODUCT_TYPE_NORMAL]);
        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 1);

        // duplicate
        $this->objCartSession->saveCurrentCart('xxxxx', PRODUCT_TYPE_NORMAL);

        $objSiteSess = new SC_SiteSession_Ex();
        $uniqid = $objSiteSess->getUniqId();
        $this->objCartSession->registerKey(PRODUCT_TYPE_NORMAL);
        $this->objCartSession->saveCurrentCart($uniqid, PRODUCT_TYPE_NORMAL);

        $this->assertFalse($this->objCartSession->checkChangeCart(PRODUCT_TYPE_NORMAL));
    }

    public function testCheckChangeCartWithChange()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProductClass = $objQuery->getRow('*', 'dtb_products_class', 'del_flg = ? AND product_type_id = ? AND product_class_id <> ?', ['0', PRODUCT_TYPE_NORMAL, 2]);
        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 1);

        $objSiteSess = new SC_SiteSession_Ex();
        $uniqid = $objSiteSess->getUniqId();
        $this->objCartSession->registerKey(PRODUCT_TYPE_NORMAL);
        $this->objCartSession->saveCurrentCart($uniqid, PRODUCT_TYPE_NORMAL);

        $this->assertFalse($this->objCartSession->checkChangeCart(PRODUCT_TYPE_NORMAL));

        // Change to cart
        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 1);

        $this->assertTrue($this->objCartSession->checkChangeCart(PRODUCT_TYPE_NORMAL));
    }

    public function testCheckChangeCartWithProductChange()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProductClass = $objQuery->getRow('*', 'dtb_products_class', 'del_flg = ? AND product_type_id = ? AND product_class_id <> ?', ['0', PRODUCT_TYPE_NORMAL, 2]);
        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 1);

        $objSiteSess = new SC_SiteSession_Ex();
        $uniqid = $objSiteSess->getUniqId();
        $this->objCartSession->registerKey(PRODUCT_TYPE_NORMAL);
        $this->objCartSession->saveCurrentCart($uniqid, PRODUCT_TYPE_NORMAL);

        $this->assertFalse($this->objCartSession->checkChangeCart(PRODUCT_TYPE_NORMAL));

        // Change to cart
        $max = $this->objCartSession->getMax(PRODUCT_TYPE_NORMAL);
        $this->objCartSession->delProduct($max, PRODUCT_TYPE_NORMAL);
        $this->objCartSession->addProduct(2, 1);

        $this->assertTrue($this->objCartSession->checkChangeCart(PRODUCT_TYPE_NORMAL));
        $this->assertTrue($this->objCartSession->getCancelPurchase(PRODUCT_TYPE_NORMAL));
    }

    public function testSetProductValue()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProductClass = $objQuery->getRow('*', 'dtb_products_class', 'del_flg = ? AND product_type_id = ?', ['0', PRODUCT_TYPE_NORMAL]);
        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 1);

        $this->objCartSession->setProductValue(
            $arrProductClass['product_class_id'],
            'quantity',
            5,
            PRODUCT_TYPE_NORMAL
        );

        $quantity = $this->objCartSession->getTotalQuantity(PRODUCT_TYPE_NORMAL);
        $this->assertEquals(5, $quantity);
    }

    public function testGetAllProductClassId()
    {
        $this->objCartSession->addProduct(2, 1);
        $this->objCartSession->addProduct(3, 1);

        $this->assertEquals([2, 3], $this->objCartSession->getAllProductClassID(PRODUCT_TYPE_NORMAL));
    }

    public function testChangeProductQuantity()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProductClass = $objQuery->getRow('*', 'dtb_products_class', 'del_flg = ? AND product_type_id = ?', ['0', PRODUCT_TYPE_NORMAL]);
        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 1);

        $this->objCartSession->upQuantity(1, PRODUCT_TYPE_NORMAL);
        $this->assertEquals(2, $this->objCartSession->getTotalQuantity(PRODUCT_TYPE_NORMAL));

        $this->objCartSession->downQuantity(1, PRODUCT_TYPE_NORMAL);
        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(PRODUCT_TYPE_NORMAL));
    }

    public function testGetAllProductsTotal()
    {
        $this->objCartSession->addProduct(2, 1);
        $this->objCartSession->addProduct(3, 1);

        $this->objCartSession->checkProducts(PRODUCT_TYPE_NORMAL);
        $this->assertEquals(2016, $this->objCartSession->getAllProductsTotal(PRODUCT_TYPE_NORMAL));

        $this->assertEquals(150, $this->objCartSession->getAllProductsTax(PRODUCT_TYPE_NORMAL));
        $this->assertEquals(186, $this->objCartSession->getAllProductsPoint(PRODUCT_TYPE_NORMAL));
    }

    public function testCheckProductsWithInVisible()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProductClass = $objQuery->getRow('*', 'dtb_products_class', 'del_flg = ? AND product_type_id = ?', ['0', PRODUCT_TYPE_NORMAL]);
        // visibility to hidden
        $objQuery->update('dtb_products', ['status' => 2], 'product_id = ?', [$arrProductClass['product_id']]);

        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 1);

        $this->expected = "※ 現時点で販売していない商品が含まれておりました。該当商品をカートから削除しました。\n";
        $this->actual = $this->objCartSession->checkProducts(PRODUCT_TYPE_NORMAL);
        $this->verify();
    }

    public function testCheckProductsWithNotReadyDelivery()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProductClass = $objQuery->getRow(
            '*, name AS product_name',
            'dtb_products_class JOIN dtb_products ON dtb_products_class.product_id = dtb_products.product_id',
            'dtb_products_class.del_flg = ? AND product_type_id = ?',
            ['0', PRODUCT_TYPE_NORMAL]
        );

        $objQuery->update('dtb_deliv', ['product_type_id' => PRODUCT_TYPE_DOWNLOAD]);

        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 1);

        $this->expected = "※「" . $arrProductClass['product_name'] . "」はまだ配送の準備ができておりません。恐れ入りますがお問い合わせページよりお問い合わせください。\n";
        $this->actual = $this->objCartSession->checkProducts(PRODUCT_TYPE_NORMAL);
        $this->verify();
    }

    public function testCheckProductsWithSalesLimit()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProductClass = $objQuery->getRow(
            '*, name AS product_name',
            'dtb_products_class JOIN dtb_products ON dtb_products_class.product_id = dtb_products.product_id',
            'dtb_products_class.del_flg = ? AND product_type_id = ?',
            ['0', PRODUCT_TYPE_NORMAL]
        );

        // set to sale limit
        $objQuery->update('dtb_products_class', ['sale_limit' => 1], 'product_class_id = ?', [$arrProductClass['product_class_id']]);

        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 2);
        $this->assertEquals(2, $this->objCartSession->getTotalQuantity(PRODUCT_TYPE_NORMAL));

        $this->expected = "※「" . $arrProductClass['product_name'] . "」は販売制限(または在庫が不足)しております。一度に数量1を超える購入はできません。\n";
        $this->actual = $this->objCartSession->checkProducts(PRODUCT_TYPE_NORMAL);
        $this->verify();

        // adjust to stock
        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(PRODUCT_TYPE_NORMAL));
    }

    public function testCheckProductsWithSoldout()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProductClass = $objQuery->getRow(
            '*, name AS product_name',
            'dtb_products_class JOIN dtb_products ON dtb_products_class.product_id = dtb_products.product_id',
            'dtb_products_class.del_flg = ? AND product_type_id = ?',
            ['0', PRODUCT_TYPE_NORMAL]
        );

        // soldout
        $objQuery->update(
            'dtb_products_class',
            ['stock' => 0, 'stock_unlimited' => 0],
            'product_class_id = ?',
            [$arrProductClass['product_class_id']]
        );

        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 2);
        $this->assertEquals(2, $this->objCartSession->getTotalQuantity(PRODUCT_TYPE_NORMAL));

        $this->expected = "※「" . $arrProductClass['product_name'] . "」は売り切れました。\n";
        $this->actual = $this->objCartSession->checkProducts(PRODUCT_TYPE_NORMAL);
        $this->verify();

        // remove product to cart
        $this->assertEquals(0, $this->objCartSession->getTotalQuantity(PRODUCT_TYPE_NORMAL));
    }

    public function testUnsetKey()
    {
        $this->objCartSession->registerKey(PRODUCT_TYPE_NORMAL);
        $this->assertEquals(PRODUCT_TYPE_NORMAL, $this->objCartSession->getKey(), '商品種別は通常商品');

        $this->objCartSession->unsetKey();
        $this->assertNull($this->objCartSession->getKey());
    }

    public function testIsDelivFee()
    {
        $this->objCartSession->addProduct(2, 1);
        $this->objCartSession->addProduct(3, 1);
        $this->objCartSession->checkProducts(PRODUCT_TYPE_NORMAL);

        $this->assertFalse($this->objCartSession->isDelivFree(PRODUCT_TYPE_NORMAL));
    }

    public function testIsDelivFeeWithFree()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProductClass = $objQuery->getRow('*', 'dtb_products_class', 'del_flg = ? AND product_type_id = ?', ['0', PRODUCT_TYPE_NORMAL]);

        $this->objCartSession->addProduct($arrProductClass['product_class_id'], 1);

        $this->objCartSession->checkProducts(PRODUCT_TYPE_NORMAL);
        $total = $this->objCartSession->getAllProductsTotal(PRODUCT_TYPE_NORMAL);

        $objQuery->update('dtb_baseinfo', ['free_rule' => $total]);
        $objDb = new SC_Helper_DB_Ex();
        $objDb->sfGetBasisData(true);

        $this->assertTrue($this->objCartSession->isDelivFree(PRODUCT_TYPE_NORMAL));
    }

    public function testGetValue()
    {
        $this->objCartSession->setValue(
            1,
            [],
            PRODUCT_TYPE_NORMAL
        );
        $this->expected = [];
        $this->actual = $this->objCartSession->getValue(1, PRODUCT_TYPE_NORMAL);
        $this->verify();
    }

    public function testGetPrevURL()
    {
        $this->objCartSession->setPrevURL('/cart');
        $this->objCartSession->setPrevURL('/shopping/index.php');
        $this->expected = '/cart';
        $this->actual = $this->objCartSession->getPrevURL();
        $this->verify();
    }

    public function testCalculate()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->update(
            'dtb_baseinfo',
            ['free_rule' => 2016, 'point_rate' => 10]
        );
        $objDb = new SC_Helper_DB_Ex();
        $objDb->sfGetBasisData(true);

        $this->objCartSession->addProduct(2, 1);
        $this->objCartSession->addProduct(3, 1);

        $this->objCartSession->checkProducts(PRODUCT_TYPE_NORMAL);

        $use_point = 2017;
        $result = $this->objCartSession->calculate(PRODUCT_TYPE_NORMAL, new SC_CUstomer_Ex(), $use_point);
        $this->assertEquals(150, $result['tax']);
        $this->assertEquals(2016, $result['subtotal']);
        $this->assertEquals(0, $result['deliv_fee']);
        $this->assertEquals(2016, $result['total']);
        $this->assertEquals(-1, $result['payment_total']);
        $this->assertEquals(0, $result['add_point']);
    }
}
