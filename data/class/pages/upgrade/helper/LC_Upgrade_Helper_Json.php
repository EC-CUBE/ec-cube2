<?php
/**
 * Enter description here...
 *
 */
class LC_Upgrade_Helper_Json
{
    /** */
    public $arrData = array(
        'status'  => null,
        'errcode' => null,
        'msg'     => null,
        'data'    => array()
    );

    /**
     * Enter description here...
     *
     * @return SC_Upgrade_Helper_Json
     */
    public function __construct()
    {
    }

    /**
     * Enter description here...
     *
     */
    public function isError()
    {
        return $this->isSuccess() ? false : true;
    }

    public function isSuccess()
    {
        if ($this->arrData['status'] === OSTORE_STATUS_SUCCESS) {
            return true;
        }

        return false;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $errCode
     */
    public function setError($errCode)
    {
        $masterData = new SC_DB_MasterData_Ex();
        $arrOStoreErrMsg = $masterData->getMasterData('mtb_ownersstore_err');

        $this->arrData['status']  = OSTORE_STATUS_ERROR;
        $this->arrData['errcode'] = $errCode;
        $this->arrData['msg']  = isset($arrOStoreErrMsg[$errCode])
            ? $arrOStoreErrMsg[$errCode]
            : $arrOStoreErrMsg[OSTORE_E_UNKNOWN];
    }

    /**
     * Enter description here...
     *
     * @param mixed $data
     */
    public function setSuccess($data = array(), $msg = '')
    {
        $this->arrData['status'] = OSTORE_STATUS_SUCCESS;
        $this->arrData['data']   = $data;
        $this->arrData['msg']    = $msg;
    }

    /**
     * Enter description here...
     *
     */
    public function display()
    {
        header('Content-Type: text/javascript; charset=UTF-8');
        echo json_encode($this->arrData);
    }

    /**
     * JSONデータをデコードする.
     *
     * php5.2.0からpreg_match関数に渡せるデータ長に制限がある(?)ため,
     * Services_JSONが正常に動作しなくなる.
     * そのため5.2.0以上の場合は組み込み関数のjson_decode()を使用する.
     *
     * @param  string   $str
     * @return StdClass
     * @see SC_Utils_Ex::jsonDecode
     */
    public function decode($str)
    {
        return json_decode($str);
    }
}
