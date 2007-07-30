<?php
function Piwik_ErrorHandler($errno, $errstr, $errfile, $errline)
{
    ob_start();
    debug_print_backtrace();
    $backtrace = ob_get_contents();
    ob_end_clean();
    Zend_Registry::get('logger_error')->log($errno, $errstr, $errfile, $errline, $backtrace);
}
?>
