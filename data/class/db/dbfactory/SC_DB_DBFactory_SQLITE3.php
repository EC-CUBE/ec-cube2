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
 * SQLite3 固有の処理をするクラス.
 *
 * このクラスを直接インスタンス化しないこと.
 * 必ず SC_DB_DBFactory クラスを経由してインスタンス化する.
 * また, SC_DB_DBFactory クラスの関数を必ずオーバーライドしている必要がある.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class SC_DB_DBFactory_SQLITE3 extends SC_DB_DBFactory
{
    /** SC_Query インスタンス */
    public $objQuery;

    /**
     * DBのバージョンを取得する.
     *
     * @param  string $dsn データソース名
     *
     * @return string データベースのバージョン
     */
    public function sfGetDBVersion($dsn = '')
    {
        $objQuery = SC_Query_Ex::getSingletonInstance($dsn);
        $val = $objQuery->getOne('select sqlite_version()');

        return 'SQLite3 '.$val;
    }

    /**
     * SQLite3 用の SQL 文に変更する.
     *
     * @param  string $sql SQL 文
     *
     * @return string SQLite3 用に置換した SQL 文
     */
    public function sfChangeMySQL($sql)
    {
        // 改行、タブを1スペースに変換
        $sql = preg_replace("/[\r\n\t]/", ' ', $sql);

        // SERIAL PRIMARY KEY を INTEGER PRIMARY KEY AUTOINCREMENT に変換
        $sql = preg_replace('/\bSERIAL\s+PRIMARY\s+KEY\b/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);

        // RANDOM()はそのまま使える（SQLite3でサポート済み）

        // CURRENT_TIMESTAMP をローカルタイムに変換する
        // SQLite3の CURRENT_TIMESTAMP は UTC を返すため、ローカルタイムに変換する
        $sql = $this->sfChangeCurrentTimestamp($sql);

        // EXTRACT(field FROM column) を strftime に変換する
        $sql = $this->sfChangeExtract($sql);

        // ILIKE検索をLIKE検索に変換する（SQLite3はデフォルトで大文字小文字区別しない）
        $sql = $this->sfChangeILIKE($sql);

        // TRUNCをTRUNCATEに変換する
        $sql = $this->sfChangeTrunc($sql);

        // ARRAY_TO_STRINGをGROUP_CONCATに変換する
        $sql = $this->sfChangeArrayToString($sql);

        return $sql;
    }

    /**
     * ILIKE を LIKE に変換する.
     *
     * @param  string $sql SQL文
     *
     * @return string 変換後の SQL 文
     */
    public function sfChangeILIKE($sql)
    {
        $changesql = preg_replace('/(^|[^\w])ILIKE([^\w]|$)/i', '$1LIKE$2', $sql);

        return $changesql;
    }

    /**
     * RANDOM() はSQLite3でサポートされているのでそのまま返す
     *
     * @param  string $sql SQL文
     *
     * @return string 変換後の SQL 文
     */
    public function sfChangeRANDOM($sql)
    {
        // SQLite3ではRANDOM()がネイティブサポートされている
        return $sql;
    }

    /**
     * TRUNC() を TRUNCATE() に変換する.
     *
     * @param  string $sql SQL文
     *
     * @return string 変換後の SQL 文
     */
    public function sfChangeTrunc($sql)
    {
        $changesql = preg_replace('/(^|[^\w])TRUNC([^\w]|$)/i', '$1TRUNCATE$2', $sql);

        return $changesql;
    }

    /**
     * ARRAY_TO_STRING(ARRAY(A),B) を GROUP_CONCAT() に変換する.
     *
     * @param  string $sql SQL文
     *
     * @return string 変換後の SQL 文
     */
    public function sfChangeArrayToString($sql)
    {
        if (str_contains(strtoupper($sql), 'ARRAY_TO_STRING')) {
            preg_match_all('/ARRAY_TO_STRING.*?\(.*?ARRAY\(.*?SELECT (.+?) FROM (.+?) WHERE (.+?)\).*?\,.*?\'(.+?)\'.*?\)/is', $sql, $match, PREG_SET_ORDER);

            foreach ($match as $item) {
                // SQLite3でも GROUP_CONCAT を使用
                $replace = 'GROUP_CONCAT('.$item[1].' , \''.$item[4].'\') FROM '.$item[2].' WHERE '.$item[3];
                $sql = str_replace($item[0], $replace, $sql);
            }
        }

        return $sql;
    }

    /**
     * CURRENT_TIMESTAMP を datetime('now', 'localtime') に変換する.
     *
     * SQLite3 の CURRENT_TIMESTAMP は UTC を返すため、
     * ローカルタイムゾーンの日時を使用するように変換する。
     *
     * @param  string $sql SQL文
     *
     * @return string 変換後の SQL 文
     */
    public function sfChangeCurrentTimestamp($sql)
    {
        // Now() を datetime('now','localtime') に変換
        $sql = preg_replace('/\bNow\s*\(\s*\)/i', "datetime('now','localtime')", $sql);
        // CURRENT_TIMESTAMP を datetime('now','localtime') に変換
        $sql = preg_replace('/\bcurrent_timestamp\b/i', "datetime('now','localtime')", $sql);

        return $sql;
    }

    /**
     * EXTRACT(field FROM column) を SQLite3 の strftime に変換する.
     *
     * @param  string $sql SQL文
     *
     * @return string 変換後の SQL 文
     */
    public function sfChangeExtract($sql)
    {
        $formatMap = [
            'year' => '%Y',
            'month' => '%m',
            'day' => '%d',
            'hour' => '%H',
            'minute' => '%M',
            'second' => '%S',
        ];

        $sql = preg_replace_callback(
            '/\bEXTRACT\s*\(\s*(\w+)\s+FROM\s+([^)]+)\)/i',
            function ($matches) use ($formatMap) {
                $field = strtolower($matches[1]);
                $column = trim($matches[2]);
                if (isset($formatMap[$field])) {
                    return "CAST(strftime('".$formatMap[$field]."', ".$column.') AS INTEGER)';
                }

                return $matches[0];
            },
            $sql
        );

        return $sql;
    }

    /**
     * 擬似表を表すSQL文(FROM 句)を取得する
     *
     * SQLite3では不要なのでnullを返す
     *
     * @return string
     */
    public function getDummyFromClauseSql()
    {
        return '';
    }

    /**
     * テーブルのカラム一覧を取得する.
     *
     * @param  string $table_name テーブル名
     *
     * @return array  カラム一覧の配列
     */
    public function sfGetColumnList($table_name)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $sql = "PRAGMA table_info($table_name)";
        $arrRet = $objQuery->getAll($sql);
        $arrColList = [];
        foreach ($arrRet as $arrCol) {
            $arrColList[] = $arrCol['name'];
        }

        return $arrColList;
    }

    /**
     * LIMIT, OFFSET を SQL に付与する.
     *
     * SQLite3 では OFFSET を使う場合は必ず LIMIT が必要なため、
     * OFFSET のみ指定された場合は LIMIT -1 を自動付与する。
     *
     * @param  string           $sql    SQL文
     * @param  int|string|null  $limit  LIMIT 句に付与する値
     * @param  int|string|null  $offset OFFSET 句に付与する値
     *
     * @return string LIMIT, OFFSET 句を付与した SQL
     */
    public function addLimitOffset($sql, $limit = null, $offset = null)
    {
        // SQLite3では OFFSET を使う場合は LIMIT が必須
        if (is_numeric($offset) && !is_numeric($limit)) {
            $limit = -1; // -1 は無制限を意味する
        }

        if (is_numeric($limit)) {
            $sql .= ' LIMIT '.(int) $limit;
        }
        if (is_numeric($offset)) {
            $sql .= ' OFFSET '.(int) $offset;
        }

        return $sql;
    }

    /**
     * SQLite3 では特に何もしない
     *
     * @param SC_Query $objQuery SC_Query インスタンス
     * @param string   $table    テーブル名
     *
     * @return void
     */
    public function updateSeqNextVal($objQuery, $table)
    {
        // SQLite3はAUTOINCREMENTを使用するため、シーケンス更新は不要
        return;
    }

    /**
     * 昨日の売上高・売上件数を算出する SQL を返す.
     *
     * @param  string $method SUM または COUNT
     *
     * @return string 昨日の売上高・売上件数を算出する SQL
     */
    public function getOrderYesterdaySql($method)
    {
        return 'SELECT '.$method.'(total) FROM dtb_order '
               .'WHERE del_flg = 0 '
               ."AND date(create_date) = date('now', 'localtime', '-1 day') "
               .'AND status <> '.ORDER_CANCEL;
    }

    /**
     * 当月の売上高・売上件数を算出する SQL を返す.
     *
     * @param  string $method SUM または COUNT
     *
     * @return string 当月の売上高・売上件数を算出する SQL
     */
    public function getOrderMonthSql($method)
    {
        return 'SELECT '.$method.'(total) FROM dtb_order '
               .'WHERE del_flg = 0 '
               ."AND strftime('%Y/%m', create_date) = ? "
               ."AND date(create_date) <> date('now', 'localtime') "
               .'AND status <> '.ORDER_CANCEL;
    }

    /**
     * 昨日のレビュー書き込み件数を算出する SQL を返す.
     *
     * @return string 昨日のレビュー書き込み件数を算出する SQL
     */
    public function getReviewYesterdaySql()
    {
        return 'SELECT COUNT(*) FROM dtb_review AS A '
               .'LEFT JOIN dtb_products AS B '
               .'ON A.product_id = B.product_id '
               .'WHERE A.del_flg = 0 '
               .'AND B.del_flg = 0 '
               ."AND date(A.create_date) = date('now', 'localtime', '-1 day') "
               ."AND date(A.create_date) != date('now', 'localtime')";
    }

    /**
     * メール送信履歴の start_date の検索条件の SQL を返す.
     *
     * @return string 検索条件の SQL
     */
    public function getSendHistoryWhereStartdateSql()
    {
        return "start_date BETWEEN datetime('now', 'localtime', '-5 minutes') AND datetime('now', 'localtime', '+5 minutes')";
    }

    /**
     * ダウンロード販売の検索条件の SQL を返す.
     *
     * @param  string $dtb_order_alias
     *
     * @return string 検索条件の SQL
     */
    public function getDownloadableDaysWhereSql($dtb_order_alias = 'dtb_order')
    {
        $baseinfo = SC_Helper_DB_Ex::sfGetBasisData();
        $downloadable_days = $baseinfo['downloadable_days'];
        if ($downloadable_days == null || $downloadable_days == '') {
            $downloadable_days = 0;
        }
        $sql = <<< __EOS__
            (
                SELECT
                    CASE
                        WHEN (SELECT d1.downloadable_days_unlimited FROM dtb_baseinfo d1) = 1 AND $dtb_order_alias.payment_date IS NOT NULL THEN 1
                        WHEN date('now', 'localtime') <= date($dtb_order_alias.payment_date, '+$downloadable_days days') THEN 1
                        ELSE 0
                    END
            )
            __EOS__;

        return $sql;
    }

    /**
     * 文字列連結を行う.
     *
     * @param  string[]  $columns 連結を行うカラム名
     *
     * @return string 連結後の SQL 文
     */
    public function concatColumn($columns)
    {
        $sql = '';
        $i = 0;
        $total = count($columns);
        foreach ($columns as $column) {
            $sql .= $column;
            if ($i < $total - 1) {
                $sql .= ' || ';
            }
            $i++;
        }

        return $sql;
    }

    /**
     * データ型の互換性チェック
     *
     * @param  string $type データ型
     *
     * @return string 変換後のデータ型
     */
    public function convertDataType($type)
    {
        $type = strtoupper($type);

        // 数値型の変換
        if (preg_match('/^(TINY|SMALL|MEDIUM|BIG)?INT/i', $type)) {
            return 'INTEGER';
        }
        if (preg_match('/^(NUMERIC|DECIMAL|REAL|DOUBLE|FLOAT)/i', $type)) {
            return 'REAL';
        }

        // 文字列型の変換
        if (preg_match('/^(VAR)?CHAR/i', $type)) {
            return 'TEXT';
        }
        if (preg_match('/^(TINY|MEDIUM|LONG)?TEXT/i', $type)) {
            return 'TEXT';
        }

        // 日時型の変換
        if (preg_match('/^(DATE|TIME|DATETIME|TIMESTAMP)/i', $type)) {
            return 'TEXT';
        }

        return $type;
    }
}
