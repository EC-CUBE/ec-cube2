<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * おすすめ商品を管理するヘルパークラス.
 *
 * @author pineray
 *
 * @version $Id$
 */
class SC_Helper_BestProducts
{
    /**
     * おすすめ商品の情報を取得.
     *
     * @param  int $best_id     おすすめ商品ID
     * @param  bool $has_deleted 削除されたおすすめ商品も含む場合 true; 初期値 false
     *
     * @return array
     */
    public static function getBestProducts($best_id, $has_deleted = false)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $col = '*';
        $where = 'best_id = ?';
        if (!$has_deleted) {
            $where .= ' AND del_flg = 0';
        }
        $arrRet = $objQuery->getRow($col, 'dtb_best_products', $where, [$best_id]);

        return $arrRet;
    }

    /**
     * おすすめ商品の情報をランクから取得.
     *
     * @param  int $rank        ランク
     * @param  bool $has_deleted 削除されたおすすめ商品も含む場合 true; 初期値 false
     *
     * @return array
     */
    public static function getByRank($rank, $has_deleted = false)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $col = '*';
        $where = 'rank = ?';
        if (!$has_deleted) {
            $where .= ' AND del_flg = 0';
        }
        $arrRet = $objQuery->getRow($col, 'dtb_best_products', $where, [$rank]);

        return $arrRet;
    }

    /**
     * おすすめ商品一覧の取得.
     *
     * @param  int $dispNumber  表示件数
     * @param  int $pageNumber  ページ番号
     * @param  bool $has_deleted 削除されたおすすめ商品も含む場合 true; 初期値 false
     *
     * @return array
     */
    public static function getList($dispNumber = 0, $pageNumber = 0, $has_deleted = false)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $col = '*';
        $where = '';
        if (!$has_deleted) {
            $where .= 'del_flg = 0';
        }
        $table = 'dtb_best_products';
        $objQuery->setOrder('rank');
        if ($dispNumber > 0) {
            if ($pageNumber > 0) {
                $objQuery->setLimitOffset($dispNumber, ($pageNumber - 1) * $dispNumber);
            } else {
                $objQuery->setLimit($dispNumber);
            }
        }
        $arrRet = $objQuery->select($col, $table, $where);

        return $arrRet;
    }

    /**
     * おすすめ商品の登録.
     *
     * @param  array    $sqlval
     *
     * @return multiple 登録成功:おすすめ商品ID, 失敗:FALSE
     */
    public static function saveBestProducts($sqlval)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $best_id = $sqlval['best_id'];
        $sqlval['update_date'] = 'CURRENT_TIMESTAMP';
        // 新規登録
        if ($best_id == '') {
            // INSERTの実行
            if (!$sqlval['rank']) {
                $sqlval['rank'] = $objQuery->max('rank', 'dtb_best_products') + 1;
            }
            $sqlval['create_date'] = 'CURRENT_TIMESTAMP';
            $sqlval['best_id'] = $objQuery->nextVal('dtb_best_products_best_id');
            $ret = $objQuery->insert('dtb_best_products', $sqlval);
        // 既存編集
        } else {
            unset($sqlval['creator_id']);
            unset($sqlval['create_date']);
            $where = 'best_id = ?';
            $ret = $objQuery->update('dtb_best_products', $sqlval, $where, [$best_id]);
        }

        return ($ret) ? $sqlval['best_id'] : false;
    }

    /**
     * おすすめ商品の削除.
     *
     * @param  int $best_id おすすめ商品ID
     *
     * @return void
     */
    public static function deleteBestProducts($best_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $table = 'dtb_best_products';
        $arrVal = ['del_flg' => 1];
        $where = 'best_id = ?';
        $arrWhereVal = [$best_id];
        $objQuery->update($table, $arrVal, $where, $arrWhereVal);
    }

    /**
     * 商品IDの配列からおすすめ商品を削除.
     *
     * @param  array $productIDs 商品ID
     *
     * @return void
     */
    public static function deleteByProductIDs($productIDs)
    {
        $arrList = static::getList();
        foreach ($arrList as $recommend) {
            if (in_array($recommend['product_id'], $productIDs)) {
                static::deleteBestProducts($recommend['best_id']);
            }
        }
    }

    /**
     * おすすめ商品の表示順をひとつ上げる.
     *
     * @param  int $best_id おすすめ商品ID
     *
     * @return void
     */
    public static function rankUp($best_id)
    {
        $arrBestProducts = static::getBestProducts($best_id);
        $rank = $arrBestProducts['rank'];

        if ($rank > 1) {
            // 表示順が一つ上のIDを取得する
            $arrAboveBestProducts = static::getByRank($rank - 1);
            $above_best_id = $arrAboveBestProducts['best_id'] ?? null;

            if ($above_best_id) {
                // 一つ上のものを一つ下に下げる
                static::changeRank($above_best_id, $rank);
            } else {
                // 無ければ何もしない。(歯抜けの場合)
            }

            // 一つ上に上げる
            static::changeRank($best_id, $rank - 1);
        }
    }

    /**
     * おすすめ商品の表示順をひとつ下げる.
     *
     * @param  int $best_id おすすめ商品ID
     *
     * @return void
     */
    public static function rankDown($best_id)
    {
        $arrBestProducts = static::getBestProducts($best_id);
        $rank = $arrBestProducts['rank'];

        if ($rank < RECOMMEND_NUM) {
            // 表示順が一つ下のIDを取得する
            $arrBelowBestProducts = static::getByRank($rank + 1);
            $below_best_id = $arrBelowBestProducts['best_id'] ?? null;

            if ($below_best_id) {
                // 一つ下のものを一つ上に上げる
                static::changeRank($below_best_id, $rank);
            } else {
                // 無ければ何もしない。(歯抜けの場合)
            }

            // 一つ下に下げる
            static::changeRank($best_id, $rank + 1);
        }
    }

    /**
     * 対象IDのrankを指定値に変更する
     *
     * @param int $best_id 対象ID
     * @param int $rank 変更したいrank値
     *
     * @return void
     */
    public static function changeRank($best_id, $rank)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $table = 'dtb_best_products';
        $sqlval = ['rank' => $rank];
        $where = 'best_id = ?';
        $arrWhereVal = [$best_id];
        $objQuery->update($table, $sqlval, $where, $arrWhereVal);
    }
}
