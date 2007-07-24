<?php
/** @package tests */
/**
* does not parse correctly because of function in definition
*/
define('SMART_PATH_DELIMITER', (substr(PHP_OS, 0, 3) == 'WIN') ? '\\' : '/' ); // set the correct path delimiter
?>