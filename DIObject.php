<?php

namespace DI;

/**
 * This file aims to circumvent problems when updating to Matomo 4.
 * Matomo 4 includes a newer version of PHP-DI, which does not include \DI\object() any longer
 * To not run into any problems with plugins still using that we forward this method to \DI\autowire
 */

if (!function_exists("\DI\object")) {

    function object()
    {
        return call_user_func_array("\DI\autowire", func_get_args());
    }

}

if (!function_exists("\DI\link")) {

    function link()
    {
        return call_user_func_array("\DI\get", func_get_args());
    }

}