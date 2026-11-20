<?php

/**
 * SC_Helper_SmartyEscapeTest::testHelperIsOverridableByExClass 用の _Ex クラス.
 *
 * class_extends やプラグイン (loadClassFileChange) が SC_Helper_SmartyEscape_Ex を差し替えた状態を再現する.
 * composer の classmap の対象外 (tests/) に置き, テストが別プロセスで require する.
 */
class SC_Helper_SmartyEscape_Ex extends SC_Helper_SmartyEscape
{
    /** 規則を変えたので compile_id を分ける */
    public const VERSION = 'test';

    /** 許可リストを差し替える (tpl_onload は外れる) */
    public static $arrRawVariables = ['plg_raw_html'];
}
