<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @package Piwik
 */

// Close the request if SAPI = php-fpm or mod_php
if (PHP_SAPI === 'fpm-fcgi') {
    header($_SERVER["SERVER_PROTOCOL"].' 204 No Response');
    header('Content-type: ');
    fastcgi_finish_request();
    
} elseif (PHP_SAPI === 'apache2handler') {
    header($_SERVER["SERVER_PROTOCOL"].' 204 No Response');
    header('Content-type: ');
    header('Content-Length: ');
    flush();
}

// and continue executing the php
require __DIR__.'/piwik.php';
