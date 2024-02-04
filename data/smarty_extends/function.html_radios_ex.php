<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

require_once DATA_REALDIR . 'vendor/smarty/smarty/libs/plugins/function.html_radios.php';

/**
 * @deprecated 2.18 2.17で残置していた {html_radios_ex} の互換用途。
 * @see https://github.com/EC-CUBE/ec-cube2/issues/815
 */
function smarty_function_html_radios_ex($params, &$smarty)
{
    return smarty_function_html_radios($params, $smarty);
}
