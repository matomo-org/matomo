<?php

if (!function_exists('dump')) {
    /**
     * `symfony/var-dumper` works only in development environment
     * adds `dump` for production
     * should be included after autoloading
     * @see https://github.com/matomo-org/matomo/issues/6890
     */
    function dump()
    {
        //
    }
}
