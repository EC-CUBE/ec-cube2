<?php

class SC_Helper_DB_sfGetLevelCatListTest extends SC_Helper_DB_TestBase
{
    protected function setUp()
    {
        parent::setUp();
        $this->objQuery->delete('dtb_category');
        $this->createCategory();
    }

    public function testSfGetLevelCatList()
    {
        $this->expected = [
            0 => ['9', '', '', '', '', '', '', '',''],
            1 =>
            [
                0 => '>カテゴリ1>カテゴリ3>カテゴリ7>カテゴリ8>カテゴリ9',
                1 => '>カテゴリ1>カテゴリ3>カテゴリ7>カテゴリ8',
                2 => '>カテゴリ1>カテゴリ3>カテゴリ7',
                3 => '>カテゴリ1>カテゴリ6',
                4 => '>カテゴリ1>カテゴリ5',
                5 => '>カテゴリ1>カテゴリ4',
                6 => '>カテゴリ1>カテゴリ3',
                7 => '>カテゴリ2',
                8 => '>カテゴリ1',
            ],
        ];
        $this->actual = SC_Helper_DB_Ex::sfGetLevelCatList(true);
        $this->verify();
    }

    public function testSfGetLevelCatListWithArgurmentFalse()
    {
        $this->expected = [
            0 => ['9', '8', '7', '6', '5', '4', '3', '2','1'],
            1 =>
            [
                0 => '>カテゴリ1>カテゴリ3>カテゴリ7>カテゴリ8>カテゴリ9',
                1 => '>カテゴリ1>カテゴリ3>カテゴリ7>カテゴリ8',
                2 => '>カテゴリ1>カテゴリ3>カテゴリ7',
                3 => '>カテゴリ1>カテゴリ6',
                4 => '>カテゴリ1>カテゴリ5',
                5 => '>カテゴリ1>カテゴリ4',
                6 => '>カテゴリ1>カテゴリ3',
                7 => '>カテゴリ2',
                8 => '>カテゴリ1',
            ],
        ];
        $this->actual = SC_Helper_DB_Ex::sfGetLevelCatList(false);
        $this->verify();
    }

    private function createCategory()
    {
        $categories = [
            0 => ['category_id' => 1, 'category_name' => 'カテゴリ1', 'parent_category_id' => 0, 'level' => 1, 'rank' => 1, 'creator_id' => 2, 'update_date' => 'CURRENT_TIMESTAMP', 'create_date' => 'CURRENT_TIMESTAMP', 'del_flg' => 0],
            1 => ['category_id' => 2, 'category_name' => 'カテゴリ2', 'parent_category_id' => 0, 'level' => 1, 'rank' => 2, 'creator_id' => 2, 'update_date' => 'CURRENT_TIMESTAMP', 'create_date' => 'CURRENT_TIMESTAMP', 'del_flg' => 0],
            2 => ['category_id' => 3, 'category_name' => 'カテゴリ3', 'parent_category_id' => 1, 'level' => 2, 'rank' => 3, 'creator_id' => 2, 'update_date' => 'CURRENT_TIMESTAMP', 'create_date' => 'CURRENT_TIMESTAMP', 'del_flg' => 0],
            3 => ['category_id' => 4, 'category_name' => 'カテゴリ4', 'parent_category_id' => 1, 'level' => 2, 'rank' => 4, 'creator_id' => 2, 'update_date' => 'CURRENT_TIMESTAMP', 'create_date' => 'CURRENT_TIMESTAMP', 'del_flg' => 0],
            4 => ['category_id' => 5, 'category_name' => 'カテゴリ5', 'parent_category_id' => 1, 'level' => 2, 'rank' => 5, 'creator_id' => 2, 'update_date' => 'CURRENT_TIMESTAMP', 'create_date' => 'CURRENT_TIMESTAMP', 'del_flg' => 0],
            5 => ['category_id' => 6, 'category_name' => 'カテゴリ6', 'parent_category_id' => 1, 'level' => 2, 'rank' => 6, 'creator_id' => 2, 'update_date' => 'CURRENT_TIMESTAMP', 'create_date' => 'CURRENT_TIMESTAMP', 'del_flg' => 0],
            6 => ['category_id' => 7, 'category_name' => 'カテゴリ7', 'parent_category_id' => 3, 'level' => 3, 'rank' => 7, 'creator_id' => 2, 'update_date' => 'CURRENT_TIMESTAMP', 'create_date' => 'CURRENT_TIMESTAMP', 'del_flg' => 0],
            7 => ['category_id' => 8, 'category_name' => 'カテゴリ8', 'parent_category_id' => 7, 'level' => 4, 'rank' => 8, 'creator_id' => 2, 'update_date' => 'CURRENT_TIMESTAMP', 'create_date' => 'CURRENT_TIMESTAMP', 'del_flg' => 0],
            8 => ['category_id' => 9, 'category_name' => 'カテゴリ9', 'parent_category_id' => 8, 'level' => 5, 'rank' => 9, 'creator_id' => 2, 'update_date' => 'CURRENT_TIMESTAMP', 'create_date' => 'CURRENT_TIMESTAMP', 'del_flg' => 0]
        ];
        foreach ($categories as $category) {
            $this->objQuery->insert('dtb_category', $category);
        }
    }
}
