<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {include_php_ex} function plugin
 * include_php が非推奨なのでこちらで代用する
 *
 *
 * @param array $params
 * @param Smarty $smarty
 * @return void
 */
function smarty_function_include_php_ex($params, &$smarty)
{
    extract($params);

    if(! isset($file) ){
        return false ;
    }

    if(! file_exists($file) ){
        return false ;
    }

    if( isset($once) && $once === false){
        require $file ;
    } else {
        require_once $file ;
    }

    return ;
}
