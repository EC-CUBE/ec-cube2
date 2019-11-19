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
 * SQL文を作るクラス
 *
 * SC_CustomerList からのみ参照されている
 */
class SC_SelectSql
{
    public $sql;

    public $select;
    public $where;
    public $order;
    public $group;
    public $arrSql;
    public $arrVal;

    /**
     * コンストラクタ
     *
     * @param array $array
     */
    public function __construct($array = [])
    {
        if (is_array($array)) {
            $this->arrSql = $array;
        }
    }

    /**
     * SQL文生成
     *
     * @param string $mode
     * @return string
     */
    public function getSql($mode = '')
    {
        $this->sql = $this->select .' '. $this->where .' '. $this->group .' ';

        if ($mode == 2) {
            $this->sql .= $this->order;
        }

        return $this->sql;
    }

    /**
     * @deprecated 本体で使用していないため非推奨
     */
    public function addSearchStr($val)
    {
        $return = '%' .$val. '%';

        return $return;
    }

    /**
     * 範囲検索
     *
     * @param int $from
     * @param int $to
     * @param string $column
     * @return array
     */
    public function selectRange($from, $to, $column)
    {
        // ある単位のみ検索($from = $to)
        if ($from == $to) {
            $this->setWhere($column .' = ?');
            $return = array($from);
        //　~$toまで検索
        } elseif (strlen($from) == 0 && strlen($to) > 0) {
            $this->setWhere($column .' <= ? ');
            $return = array($to);
        //　~$from以上を検索
        } elseif (strlen($from) > 0 && strlen($to) == 0) {
            $this->setWhere($column .' >= ? ');
            $return = array($from);
        //　$from~$toの検索
        } else {
            $this->setWhere($column .' BETWEEN ? AND ?');
            $return = array($from, $to);
        }

        return $return;
    }

    /**
     * 期間検索（○年○月○日か~○年○月○日まで）
     *
     * @param int $from_year
     * @param int $from_month
     * @param int $from_day
     * @param int $to_year
     * @param int $to_month
     * @param int $to_day
     * @param string $column
     * @return array
     */
    public function selectTermRange($from_year, $from_month, $from_day, $to_year, $to_month, $to_day, $column)
    {
        $return = array();

        // 開始期間の構築
        $date1 = $from_year . '/' . $from_month . '/' . $from_day;

        // 終了期間の構築
        // @see http://svn.ec-cube.net/open_trac/ticket/328
        // FIXME とりあえずintvalで対策...
        $date2 = mktime(0, 0, 0, intval($to_month), intval($to_day), intval($to_year));
        $date2 = $date2 + 86400;
        // SQL文のdate関数に与えるフォーマットは、yyyy/mm/ddで指定する。
        $date2 = date('Y/m/d', $date2);

        // 開始期間だけ指定の場合
        if (($from_year != '') && ($from_month != '') && ($from_day != '') && ($to_year == '') && ($to_month == '') && ($to_day == '')) {
            $this->setWhere($column .' >= ?');
            $return[] = $date1;
        }

        //　開始～終了
        if (($from_year != '') && ($from_month != '') && ($from_day != '')
            && ($to_year != '') && ($to_month != '') && ($to_day != '')
        ) {
            $this->setWhere($column . ' >= ? AND ' . $column . ' < date(?)');
            $return[] = $date1;
            $return[] = $date2;
        }

        // 終了期間だけ指定の場合
        if (($from_year == '') && ($from_month == '') && ($from_day == '') && ($to_year != '') && ($to_month != '') && ($to_day != '')) {
            $this->setWhere($column . ' < date(?)');
            $return[] = $date2;
        }

        return $return;
    }

    /**
     * checkboxなどで同一カラム内で単一、もしくは複数選択肢が有る場合
     *
     * 例: AND ( sex = xxx OR sex = xxx OR sex = xxx) AND ...
     * @param array $arr
     * @param string $ItemStr
     * @return array
     */
    public function setItemTerm($arr, $ItemStr)
    {
        $return = array();
        foreach ($arr as $data) {
            if (count($arr) > 1) {
                if (!is_null($data)) {
                    $item .= $ItemStr . ' = ? OR ';
                }
            } else {
                if (!is_null($data)) {
                    $item = $ItemStr . ' = ?';
                }
            }
            $return[] = $data;
        }

        if (count($arr) > 1) {
            // FIXME 多分この rtrim の使い方は不適切(偶然動作しそうだが)
            $item = '(' . rtrim($item, ' OR ') . ')';
        }
        $this->setWhere($item);

        return $return;
    }

    /**
     * NULL値が必要な場合
     *
     * @param array $arr
     * @param string $ItemStr
     * @return array
     */
    public function setItemTermWithNull($arr, $ItemStr)
    {
        $return = array();
        $item = " {$ItemStr} IS NULL ";

        if ($arr) {
            foreach ($arr as $data) {
                if ($data != '不明') {
                    $item .= " OR {$ItemStr} = ?";
                    $return[] = $data;
                }
            }
        }

        $item = "({$item}) ";
        $this->setWhere($item);

        return $return;
    }

    /**
     * NULLもしくは''で検索する場合
     * @deprecated 本体で使用していないため非推奨
     */
    public function setItemTermWithNullAndSpace($arr, $ItemStr)
    {
        $return = array();
        $count = count($arr);
        $item = " {$ItemStr} IS NULL OR {$ItemStr} = '' ";
        $i = 1;
        if ($arr) {
            foreach ($arr as $data) {
                if ($i == $count) break;
                $item .= " OR {$ItemStr} = ?";
                $return[] = $data;
                $i ++;
            }
        }
        $item = "({$item}) ";
        $this->setWhere($item);

        return $return;
    }

    /**
     * 複数のカラムでORで優先検索する場合　例：　AND ( item_flag1 = xxx OR item_flag2 = xxx OR item_flag3 = xxx) AND ...
     *
     * 配列の構造例　
     * if ($_POST['show_site1']) $arrShowsite_1 = array('column' => 'show_site1',
     * 'value'  => $_POST['show_site1']);
     *
     * @deprecated 本体で使用していないため非推奨
     */
    public function setWhereByOR($arrWhere)
    {
        $count = count($arrWhere);

        for ($i = 0; $i < $count; $i++) {
            if (isset($arrWhere[$i]['value'])) {
                $statement .= $arrWhere[$i]['column'] .' = ' . SC_Utils_Ex::sfQuoteSmart($arrWhere[$i]['value']) .' OR ';
            }
        }

        $statement = '(' . rtrim($statement, ' OR ') . ')';

        if ($this->where) {
            $this->where .= ' AND ' . $statement;
        } else {
            $this->where = 'WHERE ' . $statement;
        }
    }

    /**
     * WHERE を取得する。
     *
     * ヘンテコセッター互換仕様。
     * @param $with_where boolean 必要に応じて WHERE を前置するか
     * @return string WHERE
     */
    public function getWhere($with_where = false)
    {
        $where = $this->where;

        if (!$with_where) {
            $where = preg_replace('/^\s*WHERE\s+/', '', $where);
        }

        return $where;
    }

    /**
     * @param string $where
     */
    public function setWhere($where)
    {
        if ($where != '') {
            if ($this->where) {
                $this->where .= ' AND ' . $where;
            } else {
                $this->where = 'WHERE ' . $where;
            }
        }
    }

    /**
     * @param string $order
     */
    public function setOrder($order)
    {
        $this->order =  'ORDER BY ' . $order;
    }

    /**
     * @deprecated 本体で使用していないため非推奨
     */
    public function setGroup($group)
    {
        $this->group =  'GROUP BY ' . $group;
    }

    /**
     * @deprecated 本体で使用していないため非推奨
     */
    public function clearSql()
    {
        $this->select = '';
        $this->where = '';
        $this->group = '';
        $this->order = '';
    }

    /**
     * @deprecated 本体で使用していないため非推奨
     */
    public function setSelect($sql)
    {
        $this->select = $sql;
    }
}
