<?php
/**
 * Smarty plugin
 *
 * Type:     modifier<br>
 * Name:     format_name<br>
 * Purpose:  名前をフォーマットする<br>
 * Example:  {$arrCustomer|format_name}
 *
 * @author   Seasoft 塚田将久
 *
 * @return string
 */
function smarty_modifier_format_name()
{
    return call_user_func_array([SC_Utils_Ex::class, 'formatName'], func_get_args());
}
