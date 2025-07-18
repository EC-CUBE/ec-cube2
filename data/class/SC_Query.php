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
 * SQLの構築・実行を行う
 *
 * TODO エラーハンドリング, ロギング方法を見直す
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class SC_Query
{
    public $option = '';
    public $where = '';
    public $arrWhereVal = [];
    /** @var MDB2_Driver_pgsql|MDB2_Driver_mysqli */
    public $conn;
    public $groupby = '';
    public $order = '';
    public $force_run = false;
    /** @var SC_DB_DBFactory */
    public $dbFactory;

    /**
     * LIMIT 句 の値
     *
     * string は、uint64 対策。
     *
     * @var int|string|null
     */
    public $limit;

    /**
     * OFFSET 句 の値
     *
     * string は、uint64 対策。
     *
     * @var int|string|null
     */
    public $offset;

    /** シングルトン動作のためのインスタンスプール配列。キーは DSN の識別情報。 */
    public static $arrPoolInstance = [];

    /**
     * コンストラクタ.
     *
     * @param string  $dsn       データソース名
     * @param bool $force_run エラーが発生しても処理を続行する場合 true
     * @param bool $new       新規に接続を行うかどうか
     */
    public function __construct($dsn = '', $force_run = false, $new = false)
    {
        if ($dsn == '') {
            $dsn = [
                'phptype' => DB_TYPE,
                'username' => DB_USER,
                'password' => DB_PASSWORD,
                'protocol' => 'tcp',
                'hostspec' => DB_SERVER,
                'port' => DB_PORT,
                'database' => DB_NAME,
            ];
        }

        // オプション
        $options = [
            // 持続的接続
            'persistent' => PEAR_DB_PERSISTENT,
            // Debugモード
            'debug' => PEAR_DB_DEBUG,
            // バッファリング true にするとメモリが解放されない。
            // 連続クエリ実行時に問題が生じる。
            'result_buffering' => false,
        ];

        //  fix for PHP7.2
        if (!array_key_exists('_MDB2_dsninfo_default', $GLOBALS)) {
            $GLOBALS['_MDB2_dsninfo_default'] = [];
        }
        if (!array_key_exists('_MDB2_databases', $GLOBALS)) {
            $GLOBALS['_MDB2_databases'] = [];
        }

        if ($new) {
            $this->conn = MDB2::connect($dsn, $options);
        } else {
            $this->conn = MDB2::singleton($dsn, $options);
        }
        if (!PEAR::isError($this->conn)) {
            $this->conn->setCharset('utf8');
            $this->conn->setFetchMode(MDB2_FETCHMODE_ASSOC);
        }

        // XXX 上書きインストール時にDBを変更するケースを想定し第1引数を与えている。
        $this->dbFactory = SC_DB_DBFactory_Ex::getInstance($this->conn->dsn['phptype']);
        $this->dbFactory->initObjQuery($this);

        $this->force_run = $force_run;
    }

    /**
     * シングルトンの SC_Query インスタンスを取得する.
     *
     * @param  string   $dsn       データソース名
     * @param  bool  $force_run エラーが発生しても処理を続行する場合 true
     * @param  bool  $new       新規に接続を行うかどうか
     *
     * @return SC_Query シングルトンの SC_Query インスタンス
     */
    public static function getSingletonInstance($dsn = '', $force_run = false, $new = false)
    {
        $objThis = SC_Query_Ex::getPoolInstance($dsn);
        if (is_null($objThis)) {
            $objThis = SC_Query_Ex::setPoolInstance(new SC_Query_Ex($dsn, $force_run, $new), $dsn);
        }
        /*
         * 歴史的な事情で、このメソッドの呼び出し元は参照で受け取る確率がある。
         * 退避しているインスタンスをそのまま返すと、退避している SC_Query の
         * プロパティを直接書き換えることになる。これを回避するため、クローンを返す。
         * 厳密な意味でのシングルトンではないが、パフォーマンス的に大差は無い。
         */

        return clone $objThis;
    }

    /**
     * エラー判定を行う.
     *
     * @deprecated PEAR::isError() を使用して下さい
     *
     * @return bool
     */
    public function isError()
    {
        if (PEAR::isError($this->conn)) {
            return true;
        }

        return false;
    }

    /**
     * COUNT文を実行する.
     *
     * @param  string  $table       テーブル名
     * @param  string  $where       where句
     * @param  array   $arrWhereVal プレースホルダ
     *
     * @return int 件数
     */
    public function count($table, $where = '', $arrWhereVal = [])
    {
        return $this->get('COUNT(*)', $table, $where, $arrWhereVal);
    }

    /**
     * EXISTS文を実行する.
     *
     * @param  string  $table       テーブル名
     * @param  string  $where       where句
     * @param  array   $arrWhereVal プレースホルダ
     *
     * @return bool 有無
     */
    public function exists($table, $where = '', $arrWhereVal = [])
    {
        $sql_inner = $this->getSql('*', $table, $where, $arrWhereVal);
        $sql = "SELECT CASE WHEN EXISTS($sql_inner) THEN 1 ELSE 0 END";
        $res = $this->getOne($sql, $arrWhereVal);

        return (bool) $res;
    }

    /**
     * SELECT文を実行する.
     *
     * @param  string     $cols        カラム名. 複数カラムの場合はカンマ区切りで書く
     * @param  string     $from        テーブル名
     * @param  string     $where       WHERE句
     * @param  array      $arrWhereVal プレースホルダ
     * @param  int    $fetchmode   使用するフェッチモード。デフォルトは MDB2_FETCHMODE_ASSOC。
     *
     * @return array|null
     */
    public function select($cols, $from = '', $where = '', $arrWhereVal = [], $fetchmode = MDB2_FETCHMODE_ASSOC)
    {
        $sqlse = $this->getSql($cols, $from, $where, $arrWhereVal);

        return $this->getAll($sqlse, $arrWhereVal, $fetchmode);
    }

    /**
     * 直前に実行されたSQL文を取得する.
     *
     * @param  bool $disp trueの場合、画面出力を行う.
     *
     * @return string  SQL文
     */
    public function getLastQuery($disp = true)
    {
        $sql = $this->conn->last_query;
        if ($disp) {
            echo $sql.";<br />\n";
        }

        return $sql;
    }

    /**
     * トランザクションをコミットする.
     *
     * @return MDB2_OK 成功した場合は MDB2_OK;
     *         失敗した場合は PEAR::Error オブジェクト
     */
    public function commit()
    {
        if ($this->inTransaction()) {
            return $this->conn->commit();
        } else {
            return false;
        }
    }

    /**
     * トランザクションを開始する.
     *
     * @return MDB2_OK 成功した場合は MDB2_OK;
     *         失敗した場合は PEAR::Error オブジェクト
     */
    public function begin()
    {
        return $this->conn->beginTransaction();
    }

    /**
     * トランザクションをロールバックする.
     *
     * @return MDB2_OK 成功した場合は MDB2_OK;
     *         失敗した場合は PEAR::Error オブジェクト
     */
    public function rollback()
    {
        if ($this->inTransaction()) {
            return $this->conn->rollback();
        } else {
            return false;
        }
    }

    /**
     * トランザクションが開始されているかチェックする.
     *
     * @return bool トランザクションが開始されている場合 true
     */
    public function inTransaction()
    {
        return $this->conn->inTransaction();
    }

    /**
     * 更新系の SQL を実行する.
     *
     * この関数は SC_Query::query() のエイリアスです.
     *
     * FIXME MDB2::exec() の実装であるべき
     */
    public function exec($str, $arrVal = [])
    {
        return $this->query($str, $arrVal);
    }

    /**
     * クエリを実行し、結果行毎にコールバック関数を適用する
     *
     * @param  callable $function  コールバック先
     * @param  string   $sql       SQL クエリ
     * @param  array    $arrVal    プリペアドステートメントの実行時に使用される配列。配列の要素数は、クエリ内のプレースホルダの数と同じでなければなりません。
     * @param  int  $fetchmode 使用するフェッチモード。デフォルトは DB_FETCHMODE_ASSOC。
     *
     * @return bool  結果
     */
    public function doCallbackAll($cbFunc, $sql, $arrVal = [], $fetchmode = MDB2_FETCHMODE_ASSOC)
    {
        $sql = $this->dbFactory->sfChangeMySQL($sql);

        $sth = $this->prepare($sql);
        if (PEAR::isError($sth) && $this->force_run) {
            return;
        }

        $affected = $this->execute($sth, $arrVal);
        if (PEAR::isError($affected) && $this->force_run) {
            return;
        }
        $result = null;
        while ($data = $affected->fetchRow($fetchmode)) {
            $result = call_user_func($cbFunc, $data);
            if ($result === false) {
                break;
            }
        }
        $sth->free();

        return $result;
    }

    /**
     * クエリを実行し、全ての行を返す
     *
     * @param  string  $sql       SQL クエリ
     * @param  array   $arrVal    プリペアドステートメントの実行時に使用される配列。配列の要素数は、クエリ内のプレースホルダの数と同じでなければなりません。
     * @param  int $fetchmode 使用するフェッチモード。デフォルトは DB_FETCHMODE_ASSOC。
     *
     * @return array   データを含む2次元配列。失敗した場合に 0 または DB_Error オブジェクトを返します。
     */
    public function getAll($sql, $arrVal = [], $fetchmode = MDB2_FETCHMODE_ASSOC)
    {
        $sql = $this->dbFactory->sfChangeMySQL($sql);

        $sth = $this->prepare($sql);
        if (PEAR::isError($sth) && $this->force_run) {
            return;
        }

        $affected = $this->execute($sth, $arrVal);
        if (PEAR::isError($affected) && $this->force_run) {
            return;
        }

        // MySQL での不具合対応のため、一旦変数に退避
        $arrRet = $affected->fetchAll($fetchmode);

        // PREPAREの解放
        $sth->free();

        return $arrRet;
    }

    /**
     * 構築した SELECT 文を取得する.
     *
     * クラス変数から WHERE 句を組み立てる場合、$arrWhereVal を経由してプレースホルダもクラス変数のもので上書きする。
     *
     * @param  string $cols        SELECT 文に含めるカラム名
     * @param  string $from        SELECT 文に含めるテーブル名
     * @param  string $where       SELECT 文に含める WHERE 句
     * @param  mixed  $arrWhereVal プレースホルダ(参照)
     * @param  bool   $preserve_additional_clauses 句に関わる設定を維持するか。(デフォルトは維持しない。リセットする。)
     *
     * @return string 構築済みの SELECT 文
     */
    public function getSql($cols, $from = '', $where = '', &$arrWhereVal = null, $preserve_additional_clauses = false)
    {
        $dbFactory = SC_DB_DBFactory_Ex::getInstance();

        $sqlse = "SELECT $cols";

        if (strlen($from) === 0) {
            $sqlse .= ' '.$dbFactory->getDummyFromClauseSql();
        } else {
            $sqlse .= " FROM $from";
        }

        // 引数の$whereを優先する。
        if (strlen($where) >= 1) {
            $sqlse .= " WHERE $where";
        } elseif (strlen($this->where) >= 1) {
            $sqlse .= ' WHERE '.$this->where;
            // 実行時と同じくキャストしてから評価する (空文字を要素1の配列と評価させる意図)
            $arrWhereValForEval = (array) $arrWhereVal;
            if (empty($arrWhereValForEval)) {
                $arrWhereVal = $this->arrWhereVal;
            }
        }

        $sqlse .= ' '.$this->groupby;
        $sqlse .= ' '.$this->order;
        $sqlse = $this->dbFactory->addLimitOffset($sqlse, $this->limit, $this->offset);
        $sqlse .= ' '.$this->option;

        if (!$preserve_additional_clauses) {
            $this->resetAdditionalClauses();
        }

        return $sqlse;
    }

    /**
     * SELECT 文の末尾に付与する SQL を設定する.
     *
     * この関数で設定した値は SC_Query::getSql() で使用されます.
     *
     * @param  string   $str 付与する SQL 文
     *
     * @return SC_Query 自分自身のインスタンス
     */
    public function setOption($str)
    {
        $this->option = $str;

        return $this;
    }

    /**
     * SELECT 文に付与する LIMIT, OFFSET 句を設定する.
     *
     * この関数で設定した値は SC_Query::getSql() で使用されます.
     *
     * @param  int|string|null  $limit  LIMIT 句に付与する値
     * @param  int|string|null  $offset OFFSET 句に付与する値
     *
     * @return SC_Query 自分自身のインスタンス
     */
    public function setLimitOffset($limit = null, $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    /**
     * SELECT 文に付与する GROUP BY 句を設定する.
     *
     * この関数で設定した値は SC_Query::getSql() で使用されます.
     *
     * @param  string   $str GROUP BY 句に付与する文字列
     *
     * @return SC_Query 自分自身のインスタンス
     */
    public function setGroupBy($str = '')
    {
        if (strlen($str) == 0) {
            $this->groupby = '';
        } else {
            $this->groupby = 'GROUP BY '.$str;
        }

        return $this;
    }

    /**
     * SELECT 文の WHERE 句に付与する AND 条件を設定する.
     *
     * この関数で設定した値は SC_Query::getSql() で使用されます.
     *
     * @param  string   $str WHERE 句に付与する AND 条件の文字列
     *
     * @return SC_Query 自分自身のインスタンス
     */
    public function andWhere($str)
    {
        if ($this->where != '') {
            $this->where .= ' AND '.$str;
        } else {
            $this->where = $str;
        }

        return $this;
    }

    /**
     * SELECT 文の WHERE 句に付与する OR 条件を設定する.
     *
     * この関数で設定した値は SC_Query::getSql() で使用されます.
     *
     * @param  string   $str WHERE 句に付与する OR 条件の文字列
     *
     * @return SC_Query 自分自身のインスタンス
     */
    public function orWhere($str)
    {
        if ($this->where != '') {
            $this->where .= ' OR '.$str;
        } else {
            $this->where = $str;
        }

        return $this;
    }

    /**
     * SELECT 文に付与する WHERE 句を設定する.
     *
     * この関数で設定した値は SC_Query::getSql() で使用されます.
     *
     * @param  string   $where       WHERE 句に付与する文字列
     * @param  mixed    $arrWhereVal プレースホルダ
     *
     * @return SC_Query 自分自身のインスタンス
     */
    public function setWhere($where = '', $arrWhereVal = [])
    {
        $this->where = $where;
        $this->arrWhereVal = $arrWhereVal;

        return $this;
    }

    /**
     * SELECT 文に付与する ORDER BY 句を設定する.
     *
     * この関数で設定した値は SC_Query::getSql() で使用されます.
     *
     * @param  string   $str ORDER BY 句に付与する文字列
     *
     * @return SC_Query 自分自身のインスタンス
     */
    public function setOrder($str = '')
    {
        if (strlen($str) == 0) {
            $this->order = '';
        } else {
            $this->order = 'ORDER BY '.$str;
        }

        return $this;
    }

    /**
     * SELECT 文に付与する LIMIT 句を設定する.
     *
     * この関数で設定した値は SC_Query::getSql() で使用されます.
     *
     * @param  int|string|null  $limit LIMIT 句に設定する値
     *
     * @return SC_Query 自分自身のインスタンス
     */
    public function setLimit($limit = null)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * SELECT 文に付与する OFFSET 句を設定する.
     *
     * この関数で設定した値は SC_Query::getSql() で使用されます.
     *
     * @param  int  $offset OFFSET 句に設定する値
     *
     * @return SC_Query 自分自身のインスタンス
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * INSERT文を実行する.
     *
     * @param  string                   $table      テーブル名
     * @param  array                    $arrVal     array('カラム名' => '値', ...)の連想配列
     * @param  array                    $arrSql     array('カラム名' => 'SQL文', ...)の連想配列
     * @param  array                    $arrSqlVal  SQL文の中で使用するプレースホルダ配列
     * @param  string                   $from       FROM 句・WHERE 句
     * @param  array                    $arrFromVal FROM 句・WHERE 句で使用するプレースホルダ配列
     *
     * @return int|DB_Error|bool 挿入件数またはエラー(DB_Error, false)
     */
    public function insert($table, $arrVal, $arrSql = [], $arrSqlVal = [], $from = '', $arrFromVal = [])
    {
        $strcol = '';
        $strval = '';
        $find = false;
        $arrValForQuery = [];

        foreach ($arrVal as $key => $val) {
            $strcol .= $key.',';
            if (strcasecmp('Now()', $val) === 0) {
                $strval .= 'Now(),';
            } elseif (strcasecmp('CURRENT_TIMESTAMP', $val) === 0) {
                $strval .= 'CURRENT_TIMESTAMP,';
            } else {
                $strval .= '?,';
                $arrValForQuery[] = $val;
            }
            $find = true;
        }

        foreach ($arrSql as $key => $val) {
            $strcol .= $key.',';
            $strval .= $val.',';
            $find = true;
        }

        $arrValForQuery = array_merge($arrValForQuery, $arrSqlVal);

        if (!$find) {
            return false;
        }
        // 文末の','を削除
        $strcol = rtrim($strcol, ',');
        $strval = rtrim($strval, ',');
        $sqlin = "INSERT INTO $table($strcol) SELECT $strval";

        if (strlen($from) >= 1) {
            $sqlin .= ' '.$from;
            $arrValForQuery = array_merge($arrValForQuery, $arrFromVal);
        }

        // INSERT文の実行
        $ret = $this->query($sqlin, $arrValForQuery, false, null, MDB2_PREPARE_MANIP);

        return $ret;
    }

    /**
     * UPDATE文を実行する.
     *
     * @param string $table        テーブル名
     * @param array  $arrVal       array('カラム名' => '値', ...)の連想配列
     * @param string $where        WHERE句
     * @param array  $arrWhereVal  WHERE句用のプレースホルダ配列 (従来は追加カラム用も兼ねていた)
     * @param array  $arrRawSql    追加カラム
     * @param array  $arrRawSqlVal 追加カラム用のプレースホルダ配列
     *
     * @return
     */
    public function update($table, $arrVal, $where = '', $arrWhereVal = [], $arrRawSql = [], $arrRawSqlVal = [])
    {
        $arrCol = [];
        $arrValForQuery = [];
        $find = false;

        foreach ($arrVal as $key => $val) {
            if (strcasecmp('Now()', $val) === 0) {
                $arrCol[] = $key.'= Now()';
            } elseif (strcasecmp('CURRENT_TIMESTAMP', $val) === 0) {
                $arrCol[] = $key.'= CURRENT_TIMESTAMP';
            } else {
                $arrCol[] = $key.'= ?';
                $arrValForQuery[] = $val;
            }
            $find = true;
        }

        if ($arrRawSql != '') {
            foreach ($arrRawSql as $key => $val) {
                $arrCol[] = "$key = $val";
            }
        }

        $arrValForQuery = array_merge($arrValForQuery, $arrRawSqlVal);

        if (empty($arrCol)) {
            return false;
        }

        // 文末の','を削除
        $strcol = implode(', ', $arrCol);

        if (is_array($arrWhereVal)) { // 旧版との互換用
            // プレースホルダー用に配列を追加
            $arrValForQuery = array_merge($arrValForQuery, $arrWhereVal);
        }

        $sqlup = "UPDATE $table SET $strcol";
        if (strlen($where) >= 1) {
            $sqlup .= " WHERE $where";
        }

        // UPDATE文の実行
        return $this->query($sqlup, $arrValForQuery, false, null, MDB2_PREPARE_MANIP);
    }

    /**
     * MAX文を実行する.
     *
     * @param  string  $table       テーブル名
     * @param  string  $col         カラム名
     * @param  string  $where       付与する WHERE 句
     * @param  array   $arrWhereVal プレースホルダに挿入する値
     *
     * @return int MAX文の実行結果
     */
    public function max($col, $table, $where = '', $arrWhereVal = [])
    {
        $ret = $this->get("MAX($col)", $table, $where, $arrWhereVal);

        return $ret;
    }

    /**
     * MIN文を実行する.
     *
     * @param  string  $table       テーブル名
     * @param  string  $col         カラム名
     * @param  string  $where       付与する WHERE 句
     * @param  array   $arrWhereVal プレースホルダに挿入する値
     *
     * @return int MIN文の実行結果
     */
    public function min($col, $table, $where = '', $arrWhereVal = [])
    {
        $ret = $this->get("MIN($col)", $table, $where, $arrWhereVal);

        return $ret;
    }

    /**
     * SQL を構築して, 特定のカラムの値を取得する.
     *
     * @param  string $table       テーブル名
     * @param  string $col         カラム名
     * @param  string $where       付与する WHERE 句
     * @param  array  $arrWhereVal プレースホルダに挿入する値
     *
     * @return mixed  SQL の実行結果
     */
    public function get($col, $table = '', $where = '', $arrWhereVal = [])
    {
        $sqlse = $this->getSql($col, $table, $where, $arrWhereVal);
        // SQL文の実行
        $ret = $this->getOne($sqlse, $arrWhereVal);

        return $ret;
    }

    /**
     * SQL を指定して, 特定のカラムの値を取得する.
     *
     * @param  string $sql    実行する SQL
     * @param  array  $arrVal プレースホルダに挿入する値
     *
     * @return mixed  SQL の実行結果
     */
    public function getOne($sql, $arrVal = [])
    {
        $sql = $this->dbFactory->sfChangeMySQL($sql);

        $sth = $this->prepare($sql);
        if (PEAR::isError($sth) && $this->force_run) {
            return;
        }

        $affected = $this->execute($sth, $arrVal);
        if (PEAR::isError($affected) && $this->force_run) {
            return;
        }

        // MySQL での不具合対応のため、一旦変数に退避
        $arrRet = $affected->fetchOne();

        // PREPAREの解放
        $sth->free();

        return $arrRet;
    }

    /**
     * 一行をカラム名をキーとした連想配列として取得
     *
     * @param  string  $table       テーブル名
     * @param  string  $col         カラム名
     * @param  string  $where       WHERE句
     * @param  array   $arrWhereVal プレースホルダ配列
     * @param  int $fetchmode   使用するフェッチモード。デフォルトは MDB2_FETCHMODE_ASSOC。
     *
     * @return array|null   array('カラム名' => '値', ...)の連想配列。一致なしは null。
     */
    public function getRow($col, $table = '', $where = '', $arrWhereVal = [], $fetchmode = MDB2_FETCHMODE_ASSOC)
    {
        $sql = $this->getSql($col, $table, $where, $arrWhereVal);
        $sql = $this->dbFactory->sfChangeMySQL($sql);

        $sth = $this->prepare($sql);
        if (PEAR::isError($sth) && $this->force_run) {
            return;
        }

        $affected = $this->execute($sth, $arrWhereVal);
        if (PEAR::isError($affected) && $this->force_run) {
            return;
        }

        // MySQL での不具合対応のため、一旦変数に退避
        $arrRet = $affected->fetchRow($fetchmode);

        // PREPAREの解放
        $sth->free();

        return $arrRet;
    }

    /**
     * SELECT 文の実行結果を 1列のみ取得する.
     *
     * @param  string $table       テーブル名
     * @param  string $col         カラム名
     * @param  string $where       付与する WHERE 句
     * @param  array  $arrWhereVal プレースホルダに挿入する値
     *
     * @return array  SQL の実行結果の配列
     */
    public function getCol($col, $table = '', $where = '', $arrWhereVal = [])
    {
        $sql = $this->getSql($col, $table, $where, $arrWhereVal);
        $sql = $this->dbFactory->sfChangeMySQL($sql);

        $sth = $this->prepare($sql);
        if (PEAR::isError($sth) && $this->force_run) {
            return;
        }

        $affected = $this->execute($sth, $arrWhereVal);
        if (PEAR::isError($affected) && $this->force_run) {
            return;
        }

        // MySQL での不具合対応のため、一旦変数に退避
        $arrRet = $affected->fetchCol();

        // PREPAREの解放
        $sth->free();

        return $arrRet;
    }

    /**
     * レコードの削除
     *
     * @param string $table       テーブル名
     * @param string $where       WHERE句
     * @param array  $arrWhereVal プレースホルダ
     *
     * @return int|MDB2_Error 削除件数
     */
    public function delete($table, $where = '', $arrWhereVal = [])
    {
        // デッドロックを生じ得る条件の場合、空 DELETE を避ける
        if (
            $this->dbFactory->isSkipDeleteIfNotExists()
            && $this->inTransaction()
            && !$this->exists($table, $where, $arrWhereVal)
        ) {
            return 0;
        }

        if (strlen($where) <= 0) {
            $sqlde = 'DELETE FROM '.$this->conn->quoteIdentifier($table);
        } else {
            $sqlde = 'DELETE FROM '.$this->conn->quoteIdentifier($table).' WHERE '.$where;
        }
        $ret = $this->query($sqlde, $arrWhereVal, false, null, MDB2_PREPARE_MANIP);

        return $ret;
    }

    /**
     * 次のシーケンス値を取得する.
     *
     * @param string $seq_name 取得するシーケンス名
     * @param int 次のシーケンス値
     */
    public function nextVal($seq_name)
    {
        return $this->conn->nextID($seq_name);
    }

    /**
     * 現在のシーケンス値を取得する.
     *
     * @param  string  $seq_name 取得するシーケンス名
     *
     * @return int 現在のシーケンス値
     */
    public function currVal($seq_name)
    {
        return $this->conn->currID($seq_name);
    }

    /**
     * シーケンス値を設定する.
     *
     * @param  string  $seq_name シーケンス名
     * @param  int $start    設定するシーケンス値
     *
     * @return MDB2_OK
     */
    public function setVal($seq_name, $start)
    {
        $objManager = $this->conn->loadModule('Manager');

        // XXX 値変更の役割のため、存在チェックは行なわない。存在しない場合、ここでエラーとなる。
        $ret = $objManager->dropSequence($seq_name);
        if (PEAR::isError($ret)) {
            $this->error("setVal -> dropSequence [$seq_name]");
        }

        $ret = $objManager->createSequence($seq_name, $start);
        if (PEAR::isError($ret)) {
            $this->error("setVal -> createSequence [$seq_name] [$start]");
        }

        return $ret;
    }

    /**
     * SQL を実行する.
     *
     * FIXME $ignore_errが無視されるようになっているが互換性として問題が無いか確認が必要
     *
     * @param  string  $n            実行する SQL 文
     * @param  array   $arr          プレースホルダに挿入する値
     * @param  bool $ignore_err   MDB2切替で無効化されている (エラーが発生しても処理を続行する場合 true)
     * @param  mixed   $types        プレースホルダの型指定 デフォルトnull = string
     * @param  mixed   $result_types 返値の型指定またはDML実行(MDB2_PREPARE_MANIP)
     *
     * @return MDB2_Result|int|MDB2_Error   SQL の実行結果
     */
    public function query($n, $arr = [], $ignore_err = false, $types = null, $result_types = MDB2_PREPARE_RESULT)
    {
        $n = $this->dbFactory->sfChangeMySQL($n);

        $sth = $this->prepare($n, $types, $result_types);
        if (PEAR::isError($sth) && $this->force_run) {
            return $sth;
        }

        $result = $this->execute($sth, $arr);
        if (PEAR::isError($result) && $this->force_run) {
            return $sth;
        }

        // PREPAREの解放
        $sth->free();

        return $result;
    }

    /**
     * シーケンスの一覧を取得する.
     *
     * @return array シーケンス名の配列
     */
    public function listSequences()
    {
        $objManager = $this->conn->loadModule('Manager');

        return $objManager->listSequences();
    }

    /**
     * テーブル一覧を取得する.
     *
     * @return array テーブル名の配列
     */
    public function listTables()
    {
        return $this->dbFactory->listTables($this);
    }

    /**
     * テーブルのカラム一覧を取得する.
     *
     * @param  string $table テーブル名
     *
     * @return array  指定のテーブルのカラム名の配列
     */
    public function listTableFields($table)
    {
        $objManager = $this->conn->loadModule('Manager');

        return $objManager->listTableFields($table);
    }

    /**
     * テーブルのインデックス一覧を取得する.
     *
     * @param  string $table テーブル名
     *
     * @return array  指定のテーブルのインデックス一覧
     */
    public function listTableIndexes($table)
    {
        $objManager = $this->conn->loadModule('Manager');

        return $objManager->listTableIndexes($table);
    }

    /**
     * テーブルにインデックスを付与する
     *
     * @param string $table      テーブル名
     * @param string $name       インデックス名
     * @param array  $definition フィールド名など　通常のフィールド指定時は、$definition=array('fields' => array('フィールド名' => array()));
     *               MySQLのtext型フィールドを指定する場合は $definition['length'] = 'text_field(NNN)' が必要
     */
    public function createIndex($table, $name, $definition)
    {
        $definition = $this->dbFactory->sfGetCreateIndexDefinition($table, $name, $definition);
        $objManager = $this->conn->loadModule('Manager');

        return $objManager->createIndex($table, $name, $definition);
    }

    /**
     * テーブルにインデックスを破棄する
     *
     * @param string $table テーブル名
     * @param string $name  インデックス名
     */
    public function dropIndex($table, $name)
    {
        $objManager = $this->conn->loadModule('Manager');

        return $objManager->dropIndex($table, $name);
    }

    /**
     * テーブルの詳細情報を取得する。
     *
     * @param  string $table テーブル名
     *
     * @return array  テーブル情報の配列
     */
    public function getTableInfo($table)
    {
        $objManager = $this->conn->loadModule('Reverse');

        return $objManager->tableInfo($table, null);
    }

    /**
     * 値を適切にクォートする.
     *
     * TODO MDB2 に対応するための暫定的な措置.
     *      プレースホルダが使用できない実装があるため.
     *      本来であれば, MDB2::prepare() を適切に使用するべき
     *
     * @see MDB2::quote()
     *
     * @param  string $val クォートを行う文字列
     *
     * @return string クォートされた文字列
     */
    public function quote($val)
    {
        return $this->conn->quote($val);
    }

    /**
     * パラメーターの連想配列から, テーブルに存在する列のみを取得する.
     *
     * @param string $table テーブル名
     * @param array プレースホルダの連想配列
     *
     * @return array テーブルに存在する列のみ抽出した連想配列
     */
    public function extractOnlyColsOf($table, $arrParams)
    {
        $arrCols = $this->listTableFields($table);
        $arrResults = [];
        foreach ($arrParams as $key => $val) {
            if (in_array($key, $arrCols)) {
                $arrResults[$key] = $val;
            }
        }

        return $arrResults;
    }

    /**
     * プリペアドステートメントを構築する.
     *
     * @param  string                $sql          プリペアドステートメントを構築する SQL
     * @param  mixed                 $types        プレースホルダの型指定 デフォルト null
     * @param  mixed                 $result_types 返値の型指定またはDML実行(MDB2_PREPARE_MANIP)、nullは指定無し
     *
     * @return MDB2_Statement_Common|MDB2_Error プリペアドステートメントインスタンス
     */
    public function prepare($sql, $types = null, $result_types = MDB2_PREPARE_RESULT)
    {
        $sth = $this->conn->prepare($sql, $types, $result_types);
        if (PEAR::isError($sth)) {
            $msg = $this->traceError($sth, $sql);
            $this->error($msg);
        }

        return $sth;
    }

    /**
     * プリペアドクエリを実行する.
     *
     * @param MDB2_Statement_Common プリペアドステートメントインスタンス
     * @param  array       $arrVal プレースホルダに挿入する配列
     *
     * @return MDB2_Result|int|MDB2_Error|MDB2_Result_pgsql|MDB2_Result_mysql|MDB2_Result_mysqli
     *     MDB2_Result or integer (affected rows).
     */
    public function execute(&$sth, $arrVal = [])
    {
        // #1658 (SC_Query の各種メソッドでプレースホルダの数に誤りがあるとメモリリークが発生する) 対応
        // TODO 現状は PEAR 内のバックトレースを抑制することで、メモリーリークの影響を小さくしている。
        //      根本的には、そのバックトレースが、どこに居座っているかを特定して、対策すべき。
        $pear_property = PEAR::getStaticProperty('PEAR_Error', 'skiptrace');
        $bak = $pear_property;
        $pear_property = true;

        $arrStartInfo = $this->lfStartDbTraceLog($sth, $arrVal);
        $affected = $sth->execute((array) $arrVal);
        $this->lfEndDbTraceLog($arrStartInfo, $sth, $arrVal);

        $pear_property = $bak;

        if (PEAR::isError($affected)) {
            $sql = $sth->query ?? '';
            $msg = $this->traceError($affected, $sql, $arrVal);
            $this->error($msg);
        }
        $this->conn->last_query = stripslashes($sth->query);

        return $affected;
    }

    /**
     * エラーの内容をトレースする.
     *
     * XXX trigger_error で処理する場合、1024文字以内に抑える必要がある。
     * XXX 重要な情報を先頭に置き、冗長になりすぎないように留意する。
     *
     * @param  PEAR::Error $error  PEAR::Error インスタンス
     * @param  string      $sql    エラーの発生した SQL 文
     * @param  array       $arrVal プレースホルダ
     *
     * @return string      トレースしたエラー文字列
     */
    public function traceError($error, $sql = '', $arrVal = false)
    {
        $err = "SQL: [$sql]\n";
        if ($arrVal !== false) {
            $err .= 'PlaceHolder: ['.var_export($arrVal, true)."]\n";
        }
        $err .= $error->getMessage()."\n";
        $err .= rtrim($error->getUserInfo())."\n";

        // PEAR::MDB2 内部のスタックトレースを出力する場合、下記のコメントを外す。
        // $err .= GC_Utils_Ex::toStringBacktrace($error->getBackTrace());
        return $err;
    }

    /**
     * エラー処理
     */
    public function error($msg)
    {
        $msg = "DB処理でエラーが発生しました。\n".$msg;
        if (!$this->force_run) {
            trigger_error($msg, E_USER_ERROR);
        } else {
            GC_Utils_Ex::gfPrintLog($msg, ERROR_LOG_REALFILE, true);
        }
    }

    /**
     * SQLクエリの結果セットのカラム名だけを取得する
     *
     * @param string $n   実行する SQL 文
     * @param array  $arr プレースホルダに挿入する値
     * @param bool エラーが発生しても処理を続行する場合 true
     * @param  mixed $types        プレースホルダの型指定 デフォルトnull = string
     * @param  mixed $result_types 返値の型指定またはDML実行(MDB2_PREPARE_MANIP)
     *
     * @return array 実行結果の配列
     */
    public function getQueryDefsFields($n, $arr = [], $ignore_err = false, $types = null, $result_types = MDB2_PREPARE_RESULT)
    {
        $n = $this->dbFactory->sfChangeMySQL($n);

        $sth = $this->prepare($n, $types, $result_types);
        if (PEAR::isError($sth) && ($this->force_run || $ignore_err)) {
            return;
        }

        $result = $this->execute($sth, $arr);
        if (PEAR::isError($result) && ($this->force_run || $ignore_err)) {
            return;
        }
        $arrRet = $result->getColumnNames();
        // PREPAREの解放
        $sth->free();

        return $arrRet;
    }

    /**
     * SQL の実行ログ (トレースログ) を書き出す
     *
     * @param string 実行するSQL文
     * @param  array $arrVal プレースホルダに挿入する配列
     *
     * @return array
     */
    private function lfStartDbTraceLog(&$objSth, &$arrVal)
    {
        if (!defined('SQL_QUERY_LOG_MODE') || SQL_QUERY_LOG_MODE === 0) {
            return;
        }
        if (!array_key_exists('_SC_Query_TraceLogInfo', $GLOBALS)) {
            $GLOBALS['_SC_Query_TraceLogInfo'] = [];
        }
        $arrInfo = &$GLOBALS['_SC_Query_TraceLogInfo'];
        if (!isset($arrInfo['http_request_id'])) {
            $arrInfo['http_request_id'] = uniqid();
        }
        if (!isset($arrInfo['count'])) {
            $arrInfo['count'] = 0;
        }
        $arrStartInfo = [
            'http_request_id' => $arrInfo['http_request_id'],
            'time_start' => microtime(true),
            'count' => ++$arrInfo['count'],
        ];

        // ログモード1の場合、開始はログに出力しない
        if (SQL_QUERY_LOG_MODE === 1) {
            return $arrStartInfo;
        }

        $msg = "[execute start {$arrStartInfo['http_request_id']}#{$arrStartInfo['count']}]\n"
            .'SQL: '.$objSth->query."\n"
            .'PlaceHolder: '.var_export($arrVal, true)."\n";
        GC_Utils_Ex::gfPrintLog($msg, DB_LOG_REALFILE);

        return $arrStartInfo;
    }

    /**
     * SQL の実行ログ (トレースログ) を書き出す
     *
     * @param string 実行するSQL文
     * @param  array $arrVal プレースホルダに挿入する配列
     *
     * @return void
     */
    private function lfEndDbTraceLog(&$arrStartInfo, &$objSth, &$arrVal)
    {
        if (!defined('SQL_QUERY_LOG_MODE') || SQL_QUERY_LOG_MODE === 0) {
            return;
        }
        $msg = "[execute end {$arrStartInfo['http_request_id']}#{$arrStartInfo['count']}]\n";

        $timeEnd = microtime(true);
        $timeExecTime = $timeEnd - $arrStartInfo['time_start'];

        // ログモード1の場合、
        if (SQL_QUERY_LOG_MODE === 1) {
            // 規定時間より速い場合、ログに出力しない
            if (!defined('SQL_QUERY_LOG_MIN_EXEC_TIME') || $timeExecTime < (float) SQL_QUERY_LOG_MIN_EXEC_TIME) {
                return;
            }
            // 開始時にログ出力していないため、ここで実行内容を出力する
            $msg .= 'SQL: '.$objSth->query."\n";
            $msg .= 'PlaceHolder: '.var_export($arrVal, true)."\n";
        }

        $msg .= 'execution time: '.sprintf('%.2f sec', $timeExecTime)."\n";
        GC_Utils_Ex::gfPrintLog($msg, DB_LOG_REALFILE);
    }

    /**
     * インスタンスをプールする
     *
     * @param  SC_Query $objThis プールするインスタンス
     * @param  string   $dsn     データソース名
     *
     * @return SC_Query プールしたインスタンス
     */
    public static function setPoolInstance(&$objThis, $dsn = '')
    {
        $key_str = serialize($dsn);

        return SC_Query_Ex::$arrPoolInstance[$key_str] = $objThis;
    }

    /**
     * プールしているインスタンスを取得する
     *
     * @param  string        $dsn データソース名
     *
     * @return SC_Query|null
     */
    public static function getPoolInstance($dsn = '')
    {
        $key_str = serialize($dsn);
        if (isset(SC_Query_Ex::$arrPoolInstance[$key_str])) {
            return SC_Query_Ex::$arrPoolInstance[$key_str];
        }
    }

    /**
     * 句に関わる設定をリセットする。
     *
     * TODO: WHERE 句に関しても扱うべきか検討する。
     *
     * @return void
     */
    public function resetAdditionalClauses()
    {
        $this->setGroupBy();
        $this->setOrder();
        $this->setLimitOffset();
    }
}
