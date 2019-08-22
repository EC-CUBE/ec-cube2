<?php
require __DIR__.'/../../tests/require.php';
// Here you can initialize variables that will be available to your tests
$config = parse_ini_file(__DIR__.'/config.ini', true);

$faker = Faker\Factory::create('ja_JP');
Codeception\Util\Fixtures::add('faker', $faker);

/** @var SC_Query $objQuery */
$objQuery = SC_Query_Ex::getSingletonInstance();

$objGenerator = new FixtureGenerator($objQuery, 'ja_JP');
Codeception\Util\Fixtures::add('objGenerator', $objGenerator);

$num = $objQuery->count('dtb_customer');

if ($num < $config['fixture_customer_num']) {
    $num = $config['fixture_customer_num'] - $num;
    echo 'Generating Customers';
    for ($i = 0; $i < $num; $i++) {
        $objGenerator->createCustomer();
        echo '.';
    }
    $objGenerator->createCustomer(null, ['status' => '1']); // non-active member
    echo '.'.PHP_EOL;
}

$num = $objQuery->count('dtb_products');
$product_ids = [];
// 受注生成件数 + 初期データの商品が生成されているはず
if ($num < ($config['fixture_product_num'] + 2)) {
    echo 'Generating Products';
    // 規格なしも含め $config['fixture_product_num'] の分だけ生成する
    for ($i = 0; $i < $config['fixture_product_num'] - 1; $i++) {
        $product_ids[] = $objGenerator->createProduct();
        echo '.';
    }
    $product_ids[] = $objGenerator->createProduct('規格なし商品', 0);
    echo '.'.PHP_EOL;

    $category_ids = $objGenerator->createCategories();
    foreach ($product_ids as $product_id) {
        $objGenerator->relateProductCategories($product_id, array_rand(array_flip($category_ids), $faker->numberBetween(2, count($category_ids) - 1)));
    }
    $objDb = new SC_Helper_DB_Ex();
    $objDb->sfCountCategory($objQuery);
}

$num = $objQuery->count('dtb_order');
$customer_ids = $objQuery->getCol('customer_id', 'dtb_customer', 'del_flg = 0');
array_unshift($customer_ids, '0'); // 非会員の注文を追加する
$product_class_ids = $objQuery->getCol('product_class_id', 'dtb_products_class', 'del_flg = 0');
if ($num < $config['fixture_order_num']) {
    echo 'Generating Orders';
    foreach ($customer_ids as $customer_id) {
        $target_product_class_ids = array_rand(array_flip($product_class_ids), $faker->numberBetween(2, count($product_class_ids) - 1));
        $charge = $faker->randomNumber(4);
        $discount = $faker->numberBetween(0, $charge);
        $order_count_per_customer = $objQuery->count('dtb_order', 'customer_id = ?', [$customer_id]);
        for ($i = $order_count_per_customer; $i < $config['fixture_order_num'] / count($customer_ids); $i++) {
            $objGenerator->createOrder($customer_id, $target_product_class_ids, 1, $charge, $discount, $faker->numberBetween(1, 7));
            echo '.';
        }
    }
    echo PHP_EOL;
}
