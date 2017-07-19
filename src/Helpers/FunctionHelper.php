<?php

namespace DDPro\Admin\Helpers;

/**
 * Class FunctionHelper
 *
 * Determines whether callbacks can be called, and calls them.
 *
 * ### Example
 *
 * <code>
 * if (FunctionHelper::canCall($function)) {
 *     FunctionHelper::doCall($function, $param1, $param2, ...);
 * }
 * </code>
 *
 * @link https://www.exakat.io/the-art-of-php-callback/
 * @link http://php.net/manual/en/language.types.callable.php
 * @link http://php.net/manual/en/function.is-callable.php
 */
class FunctionHelper
{
    /**
     * Determine if a callback can actually be called.
     *
     * Returns true if it can be called, otherwise false.
     *
     * @param mixed $callback
     * @return bool
     */
    public static function canCall($callback = null)
    {
        if (empty($callback)) {
            return false;
        }
        if (is_callable($callback)) {
            return true;
        }
        if (is_string($callback)) {
            if (function_exists($callback)) {
                return true;
            }
        }
    }

    /**
     * Make a call to a callback.
     *
     * Takes a variable number of parameters, the first one of which is the function to be called.
     *
     * @return mixed
     */
    public static function doCall()
    {
        // I think there is no need to do anything other than this, e.g. hooky stuff like
        // $callback($arg_list); etc.  call_user_func_array appears to work for all types
        // of callback.
        $arg_list = func_get_args();
        $callback = array_shift($arg_list);
        return call_user_func_array($callback, $arg_list);
    }
}
