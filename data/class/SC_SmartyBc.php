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
 * Smarty Backward Compatibility Wrapper Class
 *
 * @see https://github.com/smarty-php/smarty/blob/v3.1.48/libs/SmartyBC.class.php
 */
class SC_SmartyBc extends \Smarty\Smarty
{
    /**
     * Smarty 2 BC
     *
     * @var string
     */
    public $_version = self::SMARTY_VERSION;

    /**
     * This is an array of directories where trusted php scripts reside.
     *
     * @var array
     */
    public $trusted_dir = [];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * wrapper for assign_by_ref
     *
     * @param string $tpl_var the template variable name
     * @param mixed  &$value  the referenced value to assign
     */
    public function assign_by_ref($tpl_var, &$value)
    {
        trigger_error('assign_by_ref is obsolete, use assignByRef instead', E_USER_WARNING);
        $this->assignByRef($tpl_var, $value);
    }

    /**
     * wrapper for append_by_ref
     *
     * @param string  $tpl_var the template variable name
     * @param mixed   &$value  the referenced value to append
     * @param bool $merge   flag if array elements shall be merged
     */
    public function append_by_ref($tpl_var, &$value, $merge = false)
    {
        trigger_error('append_by_ref is obsolete, use appendByRef instead', E_USER_WARNING);
        $this->appendByRef($tpl_var, $value, $merge);
    }

    /**
     * clear the given assigned template variable.
     *
     * @param string $tpl_var the template variable to clear
     */
    public function clear_assign($tpl_var)
    {
        trigger_error('clear_assign is obsolete, use clearAssign instead', E_USER_WARNING);
        $this->clearAssign($tpl_var);
    }

    /**
     * Registers custom function to be used in templates
     *
     * @param string $function      the name of the template function
     * @param string $function_impl the name of the PHP function to register
     * @param bool   $cacheable
     * @param mixed  $cache_attrs
     *
     * @throws \SmartyException
     */
    public function register_function($function, $function_impl, $cacheable = true, $cache_attrs = null)
    {
        trigger_error('register_function is obsolete, use registerPlugin instead', E_USER_WARNING);
        $this->registerPlugin('function', $function, $function_impl, $cacheable, $cache_attrs);
    }

    /**
     * Unregister custom function
     *
     * @param string $function name of template function
     */
    public function unregister_function($function)
    {
        trigger_error('unregister_function is obsolete, use unregisterPlugin instead', E_USER_WARNING);
        $this->unregisterPlugin('function', $function);
    }

    /**
     * Registers object to be used in templates
     *
     * @param string  $object        name of template object
     * @param object  $object_impl   the referenced PHP object to register
     * @param array   $allowed       list of allowed methods (empty = all)
     * @param bool $smarty_args   smarty argument format, else traditional
     * @param array   $block_methods list of methods that are block format
     *
     * @throws   SmartyException
     *
     * @internal param array $block_functs list of methods that are block format
     */
    public function register_object(
        $object,
        $object_impl,
        $allowed = [],
        $smarty_args = true,
        $block_methods = []
    ) {
        trigger_error('register_object is obsolete, use registerObject instead', E_USER_WARNING);
        $allowed = (array) $allowed;
        $smarty_args = (bool) $smarty_args;
        $this->registerObject($object, $object_impl, $allowed, $smarty_args, $block_methods);
    }

    /**
     * Unregister object
     *
     * @param string $object name of template object
     */
    public function unregister_object($object)
    {
        trigger_error('unregister_object is obsolete, use unregisterObject instead', E_USER_WARNING);
        $this->unregisterObject($object);
    }

    /**
     * Registers block function to be used in templates
     *
     * @param string $block      name of template block
     * @param string $block_impl PHP function to register
     * @param bool   $cacheable
     * @param mixed  $cache_attrs
     *
     * @throws \SmartyException
     */
    public function register_block($block, $block_impl, $cacheable = true, $cache_attrs = null)
    {
        trigger_error('register_block is obsolete, use registerPlugin instead', E_USER_WARNING);
        $this->registerPlugin('block', $block, $block_impl, $cacheable, $cache_attrs);
    }

    /**
     * Unregister block function
     *
     * @param string $block name of template function
     */
    public function unregister_block($block)
    {
        trigger_error('unregister_block is obsolete, use unregisterPlugin instead', E_USER_WARNING);
        $this->unregisterPlugin('block', $block);
    }

    /**
     * Registers compiler function
     *
     * @param string $function      name of template function
     * @param string $function_impl name of PHP function to register
     * @param bool   $cacheable
     *
     * @throws \SmartyException
     */
    public function register_compiler_function($function, $function_impl, $cacheable = true)
    {
        trigger_error('register_compiler_function is obsolete, use registerPlugin instead', E_USER_WARNING);
        $this->registerPlugin('compiler', $function, $function_impl, $cacheable);
    }

    /**
     * Unregister compiler function
     *
     * @param string $function name of template function
     */
    public function unregister_compiler_function($function)
    {
        trigger_error('unregister_compiler_function is obsolete, use unregisterPlugin instead', E_USER_WARNING);
        $this->unregisterPlugin('compiler', $function);
    }

    /**
     * Registers modifier to be used in templates
     *
     * @param string $modifier      name of template modifier
     * @param string $modifier_impl name of PHP function to register
     *
     * @throws \SmartyException
     */
    public function register_modifier($modifier, $modifier_impl)
    {
        trigger_error('register_modifier is obsolete, use registerPlugin instead', E_USER_WARNING);
        $this->registerPlugin('modifier', $modifier, $modifier_impl);
    }

    /**
     * Unregister modifier
     *
     * @param string $modifier name of template modifier
     */
    public function unregister_modifier($modifier)
    {
        trigger_error('unregister_modifier is obsolete, use unregisterPlugin instead', E_USER_WARNING);
        $this->unregisterPlugin('modifier', $modifier);
    }

    /**
     * Registers a resource to fetch a template
     *
     * @param string $type      name of resource
     * @param array  $functions array of functions to handle resource
     */
    public function register_resource($type, $functions)
    {
        trigger_error('register_resource is obsolete, use registerResource instead', E_USER_WARNING);
        $this->registerResource($type, $functions);
    }

    /**
     * Unregister a resource
     *
     * @param string $type name of resource
     */
    public function unregister_resource($type)
    {
        trigger_error('unregister_resource is obsolete, use unregisterResource instead', E_USER_WARNING);
        $this->unregisterResource($type);
    }

    /**
     * Registers a prefilter function to apply
     * to a template before compiling
     *
     * @param callable $function
     *
     * @throws \SmartyException
     */
    public function register_prefilter($function)
    {
        trigger_error('register_prefilter is obsolete, use registerFilter instead', E_USER_WARNING);
        $this->registerFilter('pre', $function);
    }

    /**
     * Unregister a prefilter function
     *
     * @param callable $function
     */
    public function unregister_prefilter($function)
    {
        trigger_error('unregister_prefilter is obsolete, use unregisterFilter instead', E_USER_WARNING);
        $this->unregisterFilter('pre', $function);
    }

    /**
     * Registers a postfilter function to apply
     * to a compiled template after compilation
     *
     * @param callable $function
     *
     * @throws \SmartyException
     */
    public function register_postfilter($function)
    {
        trigger_error('register_postfilter is obsolete, use registerFilter instead', E_USER_WARNING);
        $this->registerFilter('post', $function);
    }

    /**
     * Unregister a postfilter function
     *
     * @param callable $function
     */
    public function unregister_postfilter($function)
    {
        trigger_error('unregister_postfilter is obsolete, use unregisterFilter instead', E_USER_WARNING);
        $this->unregisterFilter('post', $function);
    }

    /**
     * Registers an output filter function to apply
     * to a template output
     *
     * @param callable $function
     *
     * @throws \SmartyException
     */
    public function register_outputfilter($function)
    {
        trigger_error('register_outputfilter is obsolete, use registerFilter instead', E_USER_WARNING);
        $this->registerFilter('output', $function);
    }

    /**
     * Unregister an outputfilter function
     *
     * @param callable $function
     */
    public function unregister_outputfilter($function)
    {
        trigger_error('unregister_outputfilter is obsolete, use unregisterFilter instead', E_USER_WARNING);
        $this->unregisterFilter('output', $function);
    }

    /**
     * load a filter of specified type and name
     *
     * @param string $type filter type
     * @param string $name filter name
     *
     * @throws \SmartyException
     */
    public function load_filter($type, $name)
    {
        trigger_error('load_filter is obsolete, use loadFilter instead', E_USER_WARNING);
        $this->loadFilter($type, $name);
    }

    /**
     * clear cached content for the given template and cache id
     *
     * @param string $tpl_file   name of template file
     * @param string $cache_id   name of cache_id
     * @param string $compile_id name of compile_id
     * @param string $exp_time   expiration time
     *
     * @return bool
     */
    public function clear_cache($tpl_file = null, $cache_id = null, $compile_id = null, $exp_time = null)
    {
        trigger_error('clear_cache is obsolete, use clearCache instead', E_USER_WARNING);

        return $this->clearCache($tpl_file, $cache_id, $compile_id, $exp_time);
    }

    /**
     * clear the entire contents of cache (all templates)
     *
     * @param string $exp_time expire time
     *
     * @return bool
     */
    public function clear_all_cache($exp_time = null)
    {
        trigger_error('clear_all_cache is obsolete, use clearCache instead', E_USER_WARNING);

        return $this->clearCache(null, null, null, $exp_time);
    }

    /**
     * test to see if valid cache exists for this template
     *
     * @param string $tpl_file name of template file
     * @param string $cache_id
     * @param string $compile_id
     *
     * @return bool
     *
     * @throws \Exception
     * @throws \SmartyException
     */
    public function is_cached($tpl_file, $cache_id = null, $compile_id = null)
    {
        trigger_error('is_cached is obsolete, use isCached instead', E_USER_WARNING);

        return $this->isCached($tpl_file, $cache_id, $compile_id);
    }

    /**
     * clear all the assigned template variables.
     */
    public function clear_all_assign()
    {
        trigger_error('clear_all_assign is obsolete, use clearAllAssign instead', E_USER_WARNING);
        $this->clearAllAssign();
    }

    /**
     * clears compiled version of specified template resource,
     * or all compiled template files if one is not specified.
     * This function is for advanced use only, not normally needed.
     *
     * @param string $tpl_file
     * @param string $compile_id
     * @param string $exp_time
     *
     * @return bool results of {@link smarty_core_rm_auto()}
     */
    public function clear_compiled_tpl($tpl_file = null, $compile_id = null, $exp_time = null)
    {
        trigger_error('clear_compiled_tpl is obsolete, use clearCompiledTemplate instead', E_USER_WARNING);

        return $this->clearCompiledTemplate($tpl_file, $compile_id, $exp_time);
    }

    /**
     * Checks whether requested template exists.
     *
     * @param string $tpl_file
     *
     * @return bool
     *
     * @throws \SmartyException
     */
    public function template_exists($tpl_file)
    {
        trigger_error('template_exists is obsolete, use templateExists instead', E_USER_WARNING);

        return $this->templateExists($tpl_file);
    }

    /**
     * Returns an array containing template variables
     *
     * @param string $name
     *
     * @return array
     */
    public function get_template_vars($name = null)
    {
        trigger_error('get_template_vars is obsolete, use getTemplateVars instead', E_USER_WARNING);

        return $this->getTemplateVars($name);
    }

    /**
     * Returns an array containing config variables
     *
     * @param string $name
     *
     * @return array
     */
    public function get_config_vars($name = null)
    {
        trigger_error('get_config_vars is obsolete, use getConfigVars instead', E_USER_WARNING);

        return $this->getConfigVars($name);
    }

    /**
     * load configuration values
     *
     * @param string $file
     * @param string $section
     * @param string $scope
     */
    public function config_load($file, $section = null, $scope = 'global')
    {
        trigger_error('config_load is obsolete, use ConfigLoad instead', E_USER_WARNING);
        $this->ConfigLoad($file, $section, $scope);
    }

    /**
     * return a reference to a registered object
     *
     * @param string $name
     *
     * @return object
     */
    public function get_registered_object($name)
    {
        trigger_error('get_registered_object is obsolete, use getRegisteredObject instead', E_USER_WARNING);

        return $this->getRegisteredObject($name);
    }

    /**
     * clear configuration values
     *
     * @param string $var
     */
    public function clear_config($var = null)
    {
        trigger_error('clear_config is obsolete, use clearConfig instead', E_USER_WARNING);
        $this->clearConfig($var);
    }

    /**
     * trigger Smarty error
     *
     * @param string  $error_msg
     * @param int $error_type
     */
    public function trigger_error($error_msg, $error_type = E_USER_WARNING)
    {
        trigger_error("Smarty error: $error_msg", $error_type);
    }
}
