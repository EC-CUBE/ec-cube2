<?php
require __DIR__.'/../../tests/require.php';
// Here you can initialize variables that will be available to your tests
$config = parse_ini_file(__DIR__.'/config.ini', true);

$faker = Faker\Factory::create('ja_JP');
Codeception\Util\Fixtures::add('faker', $faker);

if (!file_exists(__DIR__.'/../../data/config/config.php')
    || !defined('ECCUBE_INSTALL') || ECCUBE_INSTALL != 'ON') {
    echo 'EC-CUBE is not yet installed.';
    return;
}

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

    $category_ids = [];
    // 5件以上のカテゴリを生成する
    do {
        $category_ids = array_merge($category_ids, $objGenerator->createCategories());
    } while (count($category_ids) < 5);

    foreach ($product_ids as $product_id) {
        $num = $faker->numberBetween(2, count($category_ids) - 1);
        $objGenerator->relateProductCategories($product_id, array_rand(array_flip($category_ids), $num >= 2 ? $num : 2));
    }
    $objDb = new SC_Helper_DB_Ex();
    $objDb->sfCountCategory($objQuery);
}

$num = $objQuery->count('dtb_order');
$objQuery->setLimit($config['fixture_customer_num']);
$customer_ids = $objQuery->getCol('customer_id', 'dtb_customer', 'del_flg = 0');
array_unshift($customer_ids, '0'); // 非会員の注文を追加する
$objQuery->setLimit(10);
$product_class_ids = $objQuery->getCol('product_class_id', 'dtb_products_class', 'del_flg = 0');
if ($num < $config['fixture_order_num']) {
    echo 'Generating Orders';
    foreach ($customer_ids as $customer_id) {
        $target_product_class_ids = array_rand(array_flip($product_class_ids), $faker->numberBetween(2, count($product_class_ids) - 1));
        $charge = $faker->randomNumber(4);
        $discount = $faker->numberBetween(0, $charge);
        $order_count_per_customer = $objQuery->count('dtb_order', 'customer_id = ?', [$customer_id]);
        for ($i = $order_count_per_customer; $i < $config['fixture_order_num'] / count($customer_ids); $i++) {
            // キャンセルと決済処理中は除外して注文を生成する
            $target_statuses = [ORDER_NEW, ORDER_PAY_WAIT, ORDER_PRE_END, ORDER_BACK_ORDER, ORDER_DELIV];
            $order_status_id = $target_statuses[$faker->numberBetween(0, count($target_statuses) - 1)];
            $objGenerator->createOrder($customer_id, $target_product_class_ids, 1, $charge, $discount, $order_status_id);
            echo '.';
        }
    }
    echo PHP_EOL;
}
