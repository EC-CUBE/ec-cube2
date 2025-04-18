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
 * ニュースを管理するヘルパークラス.
 *
 * @author pineray
 *
 * @version $Id$
 */
class SC_Helper_News
{
    /**
     * ニュースの情報を取得.
     *
     * @param  int $news_id     ニュースID
     * @param  bool $has_deleted 削除されたニュースも含む場合 true; 初期値 false
     *
     * @return array
     */
    public static function getNews($news_id, $has_deleted = false)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $col = '*, cast(news_date as date) as cast_news_date';
        $where = 'news_id = ?';
        if (!$has_deleted) {
            $where .= ' AND del_flg = 0';
        }
        $arrRet = $objQuery->getRow($col, 'dtb_news', $where, [$news_id]);

        return $arrRet;
    }

    /**
     * ニュース一覧の取得.
     *
     * @param  int $dispNumber  表示件数
     * @param  int $pageNumber  ページ番号
     * @param  bool $has_deleted 削除されたニュースも含む場合 true; 初期値 false
     *
     * @return array
     */
    public function getList($dispNumber = 0, $pageNumber = 0, $has_deleted = false)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $col = '*, cast(news_date as date) as cast_news_date';
        $where = '';
        if (!$has_deleted) {
            $where .= 'del_flg = 0';
        }
        $table = 'dtb_news';
        $objQuery->setOrder('rank DESC');
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
     * ニュースの登録.
     *
     * @param  array    $sqlval
     *
     * @return multiple 登録成功:ニュースID, 失敗:FALSE
     */
    public function saveNews($sqlval)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $news_id = $sqlval['news_id'];
        $sqlval['update_date'] = 'CURRENT_TIMESTAMP';
        // 新規登録
        if ($news_id == '') {
            // INSERTの実行
            $sqlval['rank'] = $objQuery->max('rank', 'dtb_news') + 1;
            $sqlval['create_date'] = 'CURRENT_TIMESTAMP';
            $sqlval['news_id'] = $objQuery->nextVal('dtb_news_news_id');
            $ret = $objQuery->insert('dtb_news', $sqlval);
        // 既存編集
        } else {
            unset($sqlval['creator_id']);
            unset($sqlval['create_date']);
            $where = 'news_id = ?';
            $ret = $objQuery->update('dtb_news', $sqlval, $where, [$news_id]);
        }

        return ($ret) ? $sqlval['news_id'] : false;
    }

    /**
     * ニュースの削除.
     *
     * @param  int $news_id ニュースID
     *
     * @return void
     */
    public function deleteNews($news_id)
    {
        $objDb = new SC_Helper_DB_Ex();
        // ランク付きレコードの削除
        $objDb->sfDeleteRankRecord('dtb_news', 'news_id', $news_id);
    }

    /**
     * ニュースの表示順をひとつ上げる.
     *
     * @param  int $news_id ニュースID
     *
     * @return void
     */
    public function rankUp($news_id)
    {
        $objDb = new SC_Helper_DB_Ex();
        $objDb->sfRankUp('dtb_news', 'news_id', $news_id);
    }

    /**
     * ニュースの表示順をひとつ下げる.
     *
     * @param  int $news_id ニュースID
     *
     * @return void
     */
    public function rankDown($news_id)
    {
        $objDb = new SC_Helper_DB_Ex();
        $objDb->sfRankDown('dtb_news', 'news_id', $news_id);
    }

    /**
     * ニュースの表示順を指定する.
     *
     * @param  int $news_id ニュースID
     * @param  int $rank    移動先の表示順
     *
     * @return void
     */
    public function moveRank($news_id, $rank)
    {
        $objDb = new SC_Helper_DB_Ex();
        $objDb->sfMoveRank('dtb_news', 'news_id', $news_id, $rank);
    }

    /**
     * ニュース記事数を計算.
     *
     * @param  bool $has_deleted 削除されたニュースも含む場合 true; 初期値 false
     *
     * @return int ニュース記事数
     */
    public function getCount($has_deleted = false)
    {
        $objDb = new SC_Helper_DB_Ex();
        if (!$has_deleted) {
            $where = 'del_flg = 0';
        } else {
            $where = '';
        }

        return $objDb->countRecords('dtb_news', $where);
    }
}
