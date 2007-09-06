<?php
/**
 * PHP Configuration init
 */
error_reporting(E_ALL|E_NOTICE);
date_default_timezone_set('Europe/London');
define('PIWIK_INCLUDE_PATH', '.');
define('PIWIK_PLUGINS_PATH', PIWIK_INCLUDE_PATH . '/plugins');
define('PIWIK_DATAFILES_INCLUDE_PATH', PIWIK_INCLUDE_PATH . "/modules/DataFiles");

set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/modules/'
					. PATH_SEPARATOR . get_include_path());

assert_options(ASSERT_ACTIVE, 	1);
assert_options(ASSERT_WARNING, 	1);
assert_options(ASSERT_BAIL, 	1);

if(!defined('E_STRICT'))            define('E_STRICT', 2048);
if(!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096);
if(!defined('E_EXCEPTION')) 		define('E_EXCEPTION', 8192);

/**
 * Error / exception handling functions
 */
require_once PIWIK_INCLUDE_PATH . "/modules/ErrorHandler.php";
require_once PIWIK_INCLUDE_PATH . "/modules/ExceptionHandler.php";
set_error_handler('Piwik_ErrorHandler');
set_exception_handler('Piwik_ExceptionHandler');

require_once "FrontController.php";


$controller = new Piwik_FrontController;
$controller->init();
$controller->dispatch();
$controller->end();

exit;

//
//main();

function dump($var)
{
	print("<pre>");
	var_export($var);
	print("</pre>");
}

?>

<br>
<br>
<a href="piwik.php?idsite=1&download=http://php.net/get&name=test download/ the file">test download </a>
<br>
<a href="piwik.php?idsite=1&download=http://php.net/get">test download - without name var</a>
<br>
<a href="piwik.php?idsite=1&link=http://php.net/&name=php.net website">test link php</a>
<br>
<a href="piwik.php?idsite=1&link=http://php.net/">test link php - without name var</a>
<br>
<!-- Piwik -->
<a href="http://piwik.org" title="Web analytics" onclick="window.open(this.href);return(false);">
<script language="javascript" src="piwik.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
	piwik_action_name = '';
	piwik_idsite = 1;
	piwik_url = "http://localhost/dev/piwiktrunk/piwik.php";
	piwik_log(piwik_action_name, piwik_idsite, piwik_url);
//-->
</script><object>
<noscript><p>Web analytics<img src="http://localhost/dev/piwiktrunk/piwik.php" style="border:0" /></p>
</noscript></object></a>
<!-- /Piwik --> 