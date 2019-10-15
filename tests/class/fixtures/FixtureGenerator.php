<?php

class FixtureGenerator
{
    /** @var int */
    const DEFAULT_CREATOR_ID = 2;

    /** @var SC_Query */
    protected $objQuery;

    /** @var Faker\Generator */
    protected $faker;

    /**
     * @param SC_Query $objQuery
     * @param string $locale
     */
    public function __construct($objQuery = null, $locale = 'ja_JP')
    {
        if ($objQuery === null) {
            $this->objQuery = SC_Query_Ex::getSingletonInstance();
        } else {
            $this->objQuery = $objQuery;
        }
        $this->faker = Faker\Factory::create($locale);
    }

    /**
     * 会員を生成して customer_id を返す.
     *
     * @param string $email メールアドレス. null の場合は, ランダムなメールアドレスが生成される.
     * @param array $properties 任意の値を指定するプロパティの配列
     *
     * @return int customer_id
     */
    public function createCustomer($email = null, $properties = [])
    {
        $customerValues = $this->createCustomerAsArray($email, $properties);
        $customerValues['salt'] = SC_Utils_Ex::sfGetRandomString(10);
        $customerValues['password'] = SC_Utils_Ex::sfGetHashString(
            $customerValues['password'],
            $customerValues['salt']
        );
        $customerValues['reminder_answer'] = SC_Utils_Ex::sfGetHashString(
            $customerValues['reminder_answer'],
            $customerValues['salt']
        );
        $customerValues['create_date'] = 'CURRENT_TIMESTAMP';
        $customerValues['update_date'] = 'CURRENT_TIMESTAMP';
        $customerValues['del_flg'] = 0;

        $this->objQuery->insert('dtb_customer', $customerValues);
        return $customerValues['customer_id'];
    }

    /**
     * 会員のダミーデータを生成して配列で返す
     *
     * @param string $email メールアドレス. null の場合は, ランダムなメールアドレスが生成される.
     * @param array $properties 任意の値を指定するプロパティの配列
     *
     * @return array 会員ダミーデータの配列
     */
    public function createCustomerAsArray($email = null, $properties = [])
    {
        list($zip01, $zip02) = explode('-', $this->faker->postcode);
        list($tel01, $tel02, $tel03) = explode('-', $this->faker->phoneNumber);
        $email = $email ? $email : microtime(true).'.'.$this->faker->safeEmail;
        $customer_id = $this->objQuery->nextVal('dtb_customer_customer_id');
        $values = [
            'customer_id' => $customer_id,
            'name01' => $this->faker->lastName,
            'name02' => $this->faker->firstName,
            'kana01' => $this->faker->lastKanaName,
            'kana02' => $this->faker->firstKanaName,
            'zip01' => $this->faker->postcode1,
            'zip02' => $this->faker->postcode2,
            'pref' => $this->faker->numberBetween(1, 47),
            'addr01' => $this->faker->city,
            'addr02' => $this->faker->streetAddress,
            'tel01' => $tel01,
            'tel02' => $tel02,
            'tel03' => $tel03,
            'email' => $email,
            'sex' => $this->faker->numberBetween(1, 2),
            'job' => $this->faker->numberBetween(1, 18),
            'password' => 'password',
            'reminder' => $this->faker->numberBetween(1, 7),
            'reminder_answer' => $this->faker->word,
            'mailmaga_flg' => $this->faker->numberBetween(1, 2),
            'birth' => $this->faker->dateTimeThisDecade()->format('Y-m-d H:i:s'),
            'status' => '2',     // 本会員
            'secret_key' => SC_Helper_Customer_Ex::sfGetUniqSecretKey(),
            'point' => $this->faker->randomNumber()
        ];

        return $this->objQuery->extractOnlyColsOf(
            'dtb_customer',
            array_merge($values, $properties)
        );
    }

    /**
     * 商品のダミーデータを生成し配列で返す.
     *
     * @param string $product_name 商品名
     * @return array 商品のダミーデータの配列
     */
    public function createProductAsArray($product_name = null)
    {
        $product_name = $product_name ? $product_name : $this->faker->company;
        $product_id = $this->objQuery->nextVal('dtb_products_product_id');
        $values = [
            'product_id' => $product_id,
            'name' => $product_name,
            'status' => 1,
            'comment3' => $this->faker->streetName,
            'main_list_comment' => $this->faker->streetAddress,
            'main_list_image' => 'nabe130.jpg',
            'main_comment' => $this->faker->address,
            'main_image' => 'nabe260.jpg',
            'main_large_image' => 'nabe500.jpg',
            'sub_comment1' => $this->faker->streetAddress,
            'del_flg' => 0,
            'creator_id' => self::DEFAULT_CREATOR_ID,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
            'deliv_date_id' => $this->faker->numberBetween(1, 9)
        ];

        return $this->objQuery->extractOnlyColsOf('dtb_products', $values);
    }

    /**
     * 商品を生成し product_id を返す
     *
     * @param string $product_name 商品名
     * @param int $product_class_num 商品規格の生成数
     * @param int $product_type_id 商品種別ID
     * @return int product_id
     */
    public function createProduct($product_name = null, $product_class_num = 3, $product_type_id = PRODUCT_TYPE_NORMAL)
    {
        $productValues = $this->createProductAsArray($product_name);
        $this->objQuery->insert('dtb_products', $productValues);

        if ($product_class_num == 0) {
            // 0 が指定した場合はデフォルトの商品規格のみを生成する
            $this->createProductsClass($productValues['product_id'], 0, 0, 0, $product_type_id);
        } else {
            // 規格をランダムに抽出する
            $class_ids = $this->objQuery->getCol('class_id', 'dtb_class', 'del_flg = 0');
            $class_id1 = $class_ids[$this->faker->numberBetween(0, count($class_ids) - 1)];
            $class_id2 = $class_ids[$this->faker->numberBetween(0, count($class_ids) - 1)];
            if ($class_id1 === $class_id2) {
                $class_id2 = 0;
            }

            $exist_classcategory_id1 = [0];
            $exist_classcategory_id2 = [0];
            // 指定された数だけ商品規格を生成する
            for ($i = 0; $i < $product_class_num; $i++) {
                $classcategory_ids1 = $this->objQuery->getCol(
                    'classcategory_id', 'dtb_classcategory',
                    'class_id = ? AND classcategory_id NOT IN ('.implode(', ', array_pad([], count($exist_classcategory_id1), '?')).') AND del_flg = 0',
                    array_merge([$class_id1], $exist_classcategory_id1)
                );
                $classcategory_id1 = empty($classcategory_ids1) ? 0 : $classcategory_ids1[$this->faker->numberBetween(0, count($classcategory_ids1) - 1)];

                if (in_array($classcategory_id1, $exist_classcategory_id1)
                    || !$this->objQuery->exists('dtb_classcategory', 'classcategory_id = ?', [$classcategory_id1])) {
                    // 見つからない規格分類IDが指定されたら規格を生成しない
                    $classcategory_id1 = 0;
                }

                // 規格1が設定されていれば, 規格2を設定する
                if ($classcategory_id1 > 0) {
                    $exist_classcategory_id1[] = $classcategory_id1;
                    $classcategory_ids2 = $this->objQuery->getCol(
                        'classcategory_id', 'dtb_classcategory',
                        'class_id = ? AND classcategory_id NOT IN ('.implode(', ', array_pad([], count($exist_classcategory_id2), '?')).') AND del_flg = 0',
                        array_merge([$class_id2], $exist_classcategory_id2)
                    );
                    $classcategory_id2 = empty($classcategory_ids2) ? 0 : $classcategory_ids2[$this->faker->numberBetween(0, count($classcategory_ids2) - 1)];
                    if (in_array($classcategory_id2, $exist_classcategory_id2)
                        || $classcategory_id1 === $classcategory_id2
                        || !$this->objQuery->exists('dtb_classcategory', 'classcategory_id = ?', [$classcategory_id2])) {
                        $classcategory_id2 = 0;
                    }
                } else {
                    $classcategory_id2 = 0;
                }
                if ($classcategory_id2 > 0) {
                    $exist_classcategory_id2[] = $classcategory_id2;
                }
                $this->createProductsClass($productValues['product_id'], $classcategory_id1, $classcategory_id2, 0, $product_type_id);
            }
            $this->createProductsClass($productValues['product_id'], 0, 0, 1, $product_type_id);
        }

        return $productValues['product_id'];
    }

    /**
     * 商品規格を生成して配列で返す.
     *
     * @param int $product_id 商品ID
     * @param int $classcategory_id1 規格分類ID1
     * @param int $classcategory_id2 規格分類ID2
     * @param int $del_flg 削除フラグ
     * @param int $product_type_id 商品種別ID
     * @return array 商品規格のダミーデータの配列
     */
    public function createProductsClassAsArray($product_id, $classcategory_id1 = 0, $classcategory_id2 = 0, $del_flg = 0, $product_type_id = PRODUCT_TYPE_NORMAL)
    {
        $product_class_id = $this->objQuery->nextVal('dtb_products_class_product_class_id');
        $stock = $this->faker->numberBetween(0, 100);
        $price02 = $this->faker->randomNumber(INT_LEN);
        $price01 = $this->faker->numberBetween($price02, str_repeat('9', INT_LEN));
        $values = [
            'product_class_id' => $product_class_id,
            'product_id' => $product_id,
            'classcategory_id1' => $classcategory_id1,
            'classcategory_id2' => $classcategory_id2,
            'product_code' => 'CODE_'.$product_id.'_'.$classcategory_id1.'_'.$classcategory_id2,
            'product_type_id' => $product_type_id,
            'stock_unlimited' => $stock === 0 ? 1 : 0,
            'stock' => $stock,
            'price01' => $price01,
            'price02' => $price02,
            'point_rate' => 10,
            'creator_id' => self::DEFAULT_CREATOR_ID,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
            'del_flg' => $del_flg,
        ];

        return $this->objQuery->extractOnlyColsOf('dtb_products_class', $values);
    }

    /**
     * 商品規格を生成する.
     *
     * @param int $product_id 商品ID
     * @param int $classcategory_id1 規格分類ID1
     * @param int $classcategory_id2 規格分類ID2
     * @param int $del_flg 削除フラグ
     * @param int $product_type_id 商品種別ID
     * @return int 商品規格ID
     */
    public function createProductsClass($product_id, $classcategory_id1 = 0, $classcategory_id2 = 0, $del_flg = 0, $product_type_id = PRODUCT_TYPE_NORMAL)
    {

        $values = $this->createProductsClassAsArray($product_id, $classcategory_id1, $classcategory_id2, $del_flg, $product_type_id);
        $this->objQuery->insert('dtb_products_class', $values);

        return $values['product_class_id'];
    }

    /**
     * 規格を生成して配列を返す.
     *
     * @param string $class_name 規格名
     * @param int $rank 並び順
     *
     * @return array 規格のダミーデータの配列
     */
    public function createClassAsArray($class_name = null, $rank = 0)
    {
        $class_name = $class_name ? $class_name : $this->faker->lastName;
        $class_id = $this->objQuery->nextVal('dtb_class_class_id');
        $values = [
            'class_id' => $class_id,
            'name' => $class_name,
            'rank' => $rank,
            'creator_id' => self::DEFAULT_CREATOR_ID,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
            'del_flg' => '0',
        ];

        return $this->objQuery->extractOnlyColsOf('dtb_class', $values);
    }

    /**
     * 規格を生成して規格IDを返す.
     *
     * @param string $class_name 規格名
     * @return int class_id
     */
    public function createClass($class_name = null)
    {
        $values = $this->createClassAsArray($class_name);
        $subQueries = [];
        if ($values['rank'] === 0) {
            $subQueries['rank'] = "(SELECT x.rank FROM (SELECT CASE
                                      WHEN max(rank) + 1 IS NULL THEN 1
                                      ELSE max(rank) + 1
                                    END as rank
                               FROM dtb_class
                              WHERE del_flg = 0) as x)";
            unset($values['rank']);
        }
        $this->objQuery->insert('dtb_class', $values, $subQueries);

        return $values['class_id'];
    }

    /**
     * 規格分類を生成して配列を返す.
     *
     * @param int $class_id 規格ID
     * @param string $classcategory_name 規格分類名
     * @param int $rank 並び順
     *
     * @return array 規格分類の配列
     */
    public function createClassCategoryAsArray($class_id, $classcategory_name = null, $rank = 0)
    {
        $classcategory_id = $this->objQuery->nextVal('dtb_classcategory_classcategory_id');
        $classcategory_name = $classcategory_name ? $classcategory_name : $this->faker->firstKanaName;
        $values = [
            'classcategory_id' => $classcategory_id,
            'name' => $classcategory_name,
            'class_id' => $class_id,
            'rank' => $rank,
            'creator_id' => self::DEFAULT_CREATOR_ID,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
            'del_flg' => 0
        ];

        return $this->objQuery->extractOnlyColsOf('dtb_classcategory', $values);
    }

    /**
     * 規格分類を生成して規格分類IDを返す.
     *
     * @param int $class_id 規格ID
     * @param string $classcategory_name 規格分類名
     * @return int 規格分類ID
     */
    public function createClassCategory($class_id, $classcategory_name = null)
    {
        $values = $this->createClassCategoryAsArray($class_id, $classcategory_name);
        $subQueries = [];
        if ($values['rank'] === 0) {
            $subQueries['rank'] = sprintf("(SELECT x.rank FROM (SELECT CASE
                                              WHEN max(rank) + 1 IS NULL THEN 1
                                              ELSE max(rank) + 1
                                            END as rank
                                       FROM dtb_classcategory
                                      WHERE del_flg = 0
                                        AND class_id = %d) as x)", $class_id);
        }

        $this->objQuery->insert('dtb_classcategory', $values, $subQueries);

        return $values['classcategory_id'];
    }

    /**
     * カテゴリを生成する.
     *
     * 以下のように, ツリー状のカテゴリを生成する
     *
     *  大カテゴリ -- 中カテゴリ -- 小カテゴリ
     *             |             |- 小カテゴリ
     *             |             |- 小カテゴリ
     *             |
     *             |- 中カテゴリ -- 小カテゴリ
     *                            |- 小カテゴリ
     *                            |- 小カテゴリ
     *
     * 引数 $max_depth, $max_generations に応じてランダムに生成する.
     *
     * @param int $max_depth 最大階層深度
     * @param int $max_generations 最大生成数
     * @return array 生成したカテゴリIDの配列
     */
    public function createCategories($max_depth = 5, $max_generations = 30)
    {
        $depth = $this->faker->numberBetween(1, $max_depth);
        $generations = $this->faker->numberBetween($depth, $max_generations);
        $hierarchy = array_pad([], $depth, '');
        $faker = $this->faker;
        $objQuery = $this->objQuery;

        // 階層ごとのカテゴリIDを生成する
        $category_ids = array_filter(array_map(function ($val) use (&$generations, $objQuery, $faker) {
            $n = $faker->numberBetween(1, $generations);
            $generations -= $n;
            if ($generations < 1) {
                return [];
            }
            $c = array_pad([], $n, '');
            return array_map(function ($id) use ($objQuery) {
                $id = $objQuery->nextVal('dtb_category_category_id');
                return $id;
            }, $c);
        }, $hierarchy));

        $result = [];
        foreach ($category_ids as $level => $ids) {
            foreach ($ids as $id) {
                $level = (int) $level;
                // root カテゴリ以外は, 1階層上のカテゴリをランダムに親とする
                $parent_category_id = $level === 0
                    ? 0
                    : $category_ids[$level - 1][$this->faker->numberBetween(0, count($category_ids[$level - 1]) - 1)];
                $result[] = $this->createCategory($id, $parent_category_id, $level + 1);
            }
        }

        if (empty($result)) {
            // カテゴリが生成されなかったら生成しておく
            $id = $objQuery->nextVal('dtb_category_category_id');
            $result[] = $this->createCategory($id, 0, 1);
        }

        return $result;
    }

    /**
     * カテゴリを生成し配列を返す.
     *
     * @param int $category_id カテゴリID
     * @param int $parent_category_id 親カテゴリID
     * @param int $level 階層
     * @param string $category_name カテゴリ名
     * @return array カテゴリのダミーデータの配列
     */
    public function createCategoryAsArray($category_id = null, $parent_category_id = 0, $level = 1, $category_name = null)
    {
        $category_id = $category_id ? $category_id : $this->objQuery->nextVal('dtb_category_category_id');
        $max_rank = $this->objQuery->max('rank', 'dtb_category');
        $values = [
            'category_id' => $category_id,
            'parent_category_id' => $parent_category_id,
            'category_name' => $category_name ? $category_name : $this->faker->streetAddress,
            'level' => $level,
            'rank' => $max_rank + 1,
            'creator_id' => self::DEFAULT_CREATOR_ID,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
            'del_flg' => 0
        ];
        return $this->objQuery->extractOnlyColsOf('dtb_category', $values);
    }

    /**
     * カテゴリを生成し category_id を返す.
     *
     * @param int $category_id カテゴリID
     * @param int $parent_category_id 親カテゴリID
     * @param int $level 階層
     * @param string $category_name カテゴリ名
     * @return int カテゴリID
     */
    public function createCategory($category_id = null, $parent_category_id = 0, $level = 1, $category_name = null)
    {
        $values = $this->createCategoryAsArray($category_id, $parent_category_id, $level, $category_name);
        $this->objQuery->insert('dtb_category', $values);

        return $values['category_id'];
    }

    /**
     * 商品とカテゴリを関連づけする.
     *
     * @param int $product_id 商品ID
     * @param array $category_ids カテゴリIDの配列
     * @return array 関連づけ済みのカテゴリIDの配列
     */
    public function relateProductCategories($product_id, $category_ids = [])
    {
        return array_map(function ($category_id) use ($product_id) {
            $rank = $this->objQuery->max('rank', 'dtb_product_categories');
            $this->objQuery->insert('dtb_product_categories', [
                'product_id' => $product_id,
                'category_id' => $category_id,
                'rank' => $rank
            ]);
            return $category_id;
        }, $category_ids);
    }

    /**
     * 商品規格をもとに注文明細の配列を生成して返す.
     *
     * @param int $product_class_id 商品規格ID
     * @param int $order_id order_id
     *
     * @return array 注文明細のダミーデータの配列
     */
    public function createOrderDetailAsArray($product_class_id, $order_id)
    {
        $productsClassValues = $this->objQuery->getRow('*', 'dtb_products_class', 'product_class_id = ?', [$product_class_id]);
        $productsValues = $this->objQuery->getRow('*', 'dtb_products', 'product_id = ?', [$productsClassValues['product_id']]);

        $classcategory_name1 = $this->objQuery->get('name', 'dtb_classcategory', 'classcategory_id = ?', [$productsClassValues['classcategory_id1']]);

        $classcategory_name2 = $this->objQuery->get('name', 'dtb_classcategory', 'classcategory_id = ?', [$productsClassValues['classcategory_id2']]);
        $taxRuleValues = SC_Helper_TaxRule_Ex::getTaxRule($productsValues['product_id'], $productsClassValues['product_class_id']);
        $values = [
            'order_detail_id' => $this->objQuery->nextVal('dtb_order_detail_order_detail_id'),
            'order_id' => $order_id,
            'product_id' => $productsClassValues['product_id'],
            'product_class_id' => $product_class_id,
            'product_name' => $productsValues['name'],
            'product_code' => $productsClassValues['product_code'],
            'classcategory_name1' => $classcategory_name1,
            'classcategory_name2' => $classcategory_name2,
            'price' => SC_Helper_TaxRule_Ex::sfCalcIncTax(
                $productsClassValues['price02'],
                $productsValues['product_id'],
                $productsClassValues['product_class_id']
            ),
            'quantity' => $this->faker->numberBetween(1, 100),
            'point_rate' => 10,
            'tax_rate' => $taxRuleValues['tax_rate'],
            'tax_rule' => $taxRuleValues['tax_rule_id']
        ];

        return $this->objQuery->extractOnlyColsOf('dtb_order_detail', $values);
    }

    /**
     * dtb_order_temp のデータを生成し配列を返す.
     *
     * @param int $order_id 注文ID
     * @param int $subtotal 小計
     * @param int $customer_id 顧客ID. 0 の場合はゲスト購入の注文を生成する
     * @param int $deliv_id 配送業者ID
     * @param int $add_charge 手数料
     * @param int $add_discount 値引
     * @param int $order_status_id 指定する注文ステータス
     * @return array dtb_order_temp のダミーデータの配列
     */
    public function createOrderTempAsArray($order_id = 0, $subtotal = 0, $customer_id = 0, $deliv_id = 0, $add_charge = 0, $add_discount = 0, $order_status_id = ORDER_NEW)
    {
        $customerValues = $this->objQuery->getRow('*', 'dtb_customer', 'customer_id = ?', [$customer_id]);
        if (SC_Utils_Ex::isBlank($customerValues)) {
            $customerValues = $this->createCustomerAsArray();
        }
        $delivValues = $this->objQuery->getRow('*', 'dtb_deliv', 'deliv_id = ?', [$deliv_id]);
        $deliv_fee = $this->objQuery->get('fee', 'dtb_delivfee', 'deliv_id = ? AND pref = ?', [$deliv_id, $customerValues['pref']]);
        $discount = $add_discount === 0 ? $this->faker->numberBetween(0, $subtotal) : $add_discount;
        $charge = $add_charge === 0 ? $this->faker->numberBetween(0, $subtotal) : $add_charge;
        $payment_ids = $this->objQuery->getCol('payment_id', 'dtb_payment_options', 'deliv_id = ?', [$deliv_id]);
        $paymentValues = $this->objQuery->getRow('*', 'dtb_payment', 'payment_id = ?', [$this->faker->numberBetween(0, count($payment_ids) - 1)]);

        $values = [
            'order_temp_id' => SC_Utils_Ex::sfGetRandomString(10),
            'customer_id' => $customer_id,
            'message' => $this->faker->address,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'deliv_id' => $deliv_id,
            'deliv_fee' => $deliv_fee,
            'charge' => $charge,
            'use_point' => 0,
            'add_point' => 0,
            'birth_point' => 0,
            'tax' => SC_Helper_TaxRule_Ex::sfTax($subtotal),
            'total' => $subtotal + $deliv_fee + $charge - $discount,
            'payment_id' => $paymentValues['payment_id'],
            'payment_method' => $paymentValues['payment_method'],
            'note' => $this->faker->address,
            'mail_flag' => 0,
            'status' => $order_status_id,
            'deliv_check' => 0,
            'point_check' => 0,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
            'device_type_id' => 1,
            'del_flg' => 1,
            'order_id' => $order_id
        ];
        $values['payment_total'] =  $values['total'];
        foreach ($customerValues as $key => $value) {
            $values['order_'.$key] = $value;
        }

        return $this->objQuery->extractOnlyColsOf('dtb_order_temp', $values);
    }

    /**
     * dtb_order の配列をもとに dtb_shipping の配列を生成する.
     *
     * @param array $orderValues dtb_order の配列
     * @param int $shipping_id shipping_id
     * @return array dtb_shipping のダミーデータの配列
     */
    public function createShippingFromOrderAsArray($orderValues, $shipping_id = 0)
    {
        $delivTimeValues = $this->objQuery->select('*', 'dtb_delivtime', 'deliv_id = ?', [$orderValues['deliv_id']]);
        $deliv_time_key = $this->faker->numberBetween(0, count($delivTimeValues) - 1);
        $shipping_id = 0;
        $shippingValues = [
            'shipping_id' => $shipping_id,
            'order_id' => $orderValues['order_id'],
            'time_id' => $delivTimeValues[$deliv_time_key]['time_id'],
            'shipping_time' => $delivTimeValues[$deliv_time_key]['deliv_time'],
            'rank' => 0,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
            'del_flg' => 0,
        ];

        /** @var SC_Helper_Purchase_Ex $objPurchase */
        $objPurchase = new SC_Helper_Purchase_Ex();
        $objPurchase->copyFromOrder($shippingValues, $orderValues);

        return $this->objQuery->extractOnlyColsOf('dtb_shipping', $shippingValues);
    }

    /**
     * dtb_order_detail から dtb_shipment_item の配列を生成します.
     *
     * @param array $orderDetailValues dtb_order_detail の配列
     * @param int $shipping_id shipping_id
     * @return array dtb_shipment_item の配列
     */
    public function creatShipmentItemFromOrderDetailAsArray($orderDetailValues, $shipping_id = 0)
    {
        $values = $orderDetailValues;
        $values['shipping_id'] = $shipping_id;

        return $this->objQuery->extractOnlyColsOf('dtb_shipment_item', $values);
    }

    /**
     * 受注を生成し order_id を返します.
     *
     * 生成されるデータは以下の通り
     * - dtb_order
     * - dtb_order_detail
     * - dtb_shipping
     * - dtb_shipment_item
     *
     * @param int $customer_id 顧客ID. 0 の場合はゲスト購入の注文を生成する
     * @param array $product_class_ids 注文明細に使用する商品規格IDの配列
     * @param int $deliv_id 配送業者ID
     * @param int $add_charge 手数料
     * @param int $add_discount 値引
     * @param int $order_status_id 指定する注文ステータス
     * @return int order_id
     */
    public function createOrder($customer_id = 0, $product_class_ids = [], $deliv_id = 1, $add_charge = 0, $add_discount = 0, $order_status_id = ORDER_NEW)
    {
        $order_id = $this->objQuery->nextVal('dtb_order_order_id');

        if (empty($product_class_ids)) {
            $where = 'product_type_id = 1 AND del_flg = 0 AND EXISTS (SELECT * FROM dtb_products WHERE del_flg = 0 AND product_id = dtb_products_class.product_id)';
            if ($this->objQuery->count('dtb_products_class', $where) < 1) {
                $this->createProduct();
            }
            // 既存の商品規格から選択する
            $this->objQuery->setLimit(3);
            $product_class_ids = $this->objQuery->getCol(
                'product_class_id',
                'dtb_products_class',
                $where);
        }

        $orderDetails = array_map(function ($product_class_id) use ($order_id) {
            return $this->createOrderDetailAsArray($product_class_id, $order_id);
        }, $product_class_ids);

        $subtotal = array_reduce(
            $orderDetails,
            function ($carry, $orderDetail) {
                return $carry + ($orderDetail['price'] * $orderDetail['quantity']);
            });

        $orderTempValues = $this->createOrderTempAsArray($order_id, $subtotal, $customer_id, $deliv_id, $add_charge, $add_discount, $order_status_id);
        $this->objQuery->insert('dtb_order_temp', $orderTempValues);

        $orderValues = $orderTempValues;
        $orderValues['del_flg'] = 0;
        $this->objQuery->insert('dtb_order', $this->objQuery->extractOnlyColsOf('dtb_order', $orderValues));

        array_map(function ($detailValues) {
            $this->objQuery->insert('dtb_order_detail', $detailValues);
            return $detailValues;
        }, $orderDetails);

        $shippingValues = $this->createShippingFromOrderAsArray($orderValues);
        $this->objQuery->insert('dtb_shipping', $shippingValues);

        array_map(function ($detailValues) {
            $this->objQuery->insert('dtb_shipment_item', $this->creatShipmentItemFromOrderDetailAsArray($detailValues));
            return $detailValues;
        }, $orderDetails);

        return $order_id;
    }
}
