<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

// no direct access
defined('PIWIK_INCLUDE_PATH') or die;

/**
 * This file is executed before anything else. 
 * It checks the minimum PHP version required to run Piwik.
 * This file must be compatible PHP4.
 */

$piwik_minimumPHPVersion = '5.1.3';
$piwik_currentPHPVersion = phpversion();
if( version_compare($piwik_minimumPHPVersion , $piwik_currentPHPVersion ) >= 0 )
{
	$piwik_errorMessage = "<p><b>To run Piwik you need at least PHP version $piwik_minimumPHPVersion </b></p> 
				<p>Unfortunately it seems your webserver is using PHP version $piwik_currentPHPVersion. </p>
				<p>Please try to update your PHP version, Piwik is really worth it! Nowadays most web hosts 
				support PHP $piwik_minimumPHPVersion.</p>";
}					

$piwik_zend_compatibility_mode = ini_get("zend.ze1_compatibility_mode");
if($piwik_zend_compatibility_mode == 1)
{
	$piwik_errorMessage = "<p><b>Piwik is not compatible with the directive <code>zend.ze1_compatibility_mode = On</code></b></p> 
				<p>It seems your php.ini file has <pre>zend.ze1_compatibility_mode = On</pre>It makes PHP5 behave like PHP4.
				If you want to use Piwik you need to set <pre>zend.ze1_compatibility_mode = Off</pre> in your php.ini configuration file. You may have to ask your system administrator.</p>";
}
      
/**
 * Displays info/warning/error message in a friendly UI and exits.
 *
 * @param string $message Main message
 * @param string|false $optionalTrace Backtrace; will be displayed in lighter color
 * @param bool $optionalLinks If true, will show links to the Piwik website for help
 */
function Piwik_ExitWithMessage($message, $optionalTrace = false, $optionalLinks = false)
{
	if($optionalTrace)
	{
		$optionalTrace = '<font color="#888888">Backtrace:<br/><pre>'.$optionalTrace.'</pre></font>';
	}
	if($optionalLinks)
	{
		$optionalLinks = '<ul>
						<li><a target="_blank" href="misc/redirectToUrl.php?url=http://piwik.org">Piwik homepage</a></li>
						<li><a target="_blank" href="misc/redirectToUrl.php?url=http://piwik.org/faq/">Piwik Frequently Asked Questions</a></li>
						<li><a target="_blank" href="misc/redirectToUrl.php?url=http://piwik.org/docs/">Piwik Documentation</a></li>
						<li><a target="_blank" href="misc/redirectToUrl.php?url=http://forum.piwik.org/">Piwik Forums</a></li>
						<li><a target="_blank" href="misc/redirectToUrl.php?url=http://piwik.org/demo">Piwik Online Demo</a></li>
						</ul>';
	}
	$headerPage = file_get_contents(PIWIK_INCLUDE_PATH . '/themes/default/simple_structure_header.tpl');
	$footerPage = file_get_contents(PIWIK_INCLUDE_PATH . '/themes/default/simple_structure_footer.tpl');
	$headerPage = str_replace('{$HTML_TITLE}', 'Piwik &rsaquo; Error', $headerPage);
	$content = '<p>'.$message.'</p>'. $optionalTrace .' '. $optionalLinks;
	
	echo $headerPage . $content . $footerPage;
	exit;
}

if (!function_exists('file_get_contents'))
{
	function file_get_contents($filename)
	{
		$fhandle = fopen($filename, "r");
		$fcontents = fread($fhandle, filesize($filename));
		fclose($fhandle);
		return $fcontents;
	}
}

if(isset($piwik_errorMessage))
{
	Piwik_ExitWithMessage($piwik_errorMessage, false, true);
}

// we now include the upgradephp package to define some functions used in piwik 
// that may not be defined in the current php version
require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';
