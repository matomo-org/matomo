<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Common.php 168 2008-01-14 05:26:43Z matt $
 * 
 * @package Piwik
 */

/**
 * This file is executed before anything else. It checks the minimum Php version required to run Piwik.
 * This is done here because on PHP4 piwik would output an error directly.
 * Let's try to be user friendly :)
 * 
 * @package Piwik
 */

// we prefix the global variables
$piwik_minimumPhpVersion = '5.1.3';
$piwik_currentVersion = phpversion();

if( version_compare($piwik_minimumPhpVersion , $piwik_currentVersion ) >= 0 )
{
	$piwik_errorMessage = "<p><b>To run Piwik you need at least PHP version $piwik_minimumPhpVersion </b></p> 
				<p>Unfortunately it seems your webserver is using PHP version $piwik_currentVersion. </p>
				<p>Please try to update your PHP version, Piwik is really worth it! Nowadays most web hosts 
				support PHP $piwik_minimumPhpVersion. </p>";
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
						<li><a target="_blank" href="misc/redirectToUrl.php?url=http://piwik.org/demo">Piwik demo</a></li>
						</ul>';
	}
	$html = '<html>
				<head>
					<title>Piwik &rsaquo; Error</title>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<style>				
				html { background: #eee; }
				#content {
					background: #fff;
					color: #000;
					font-family: Georgia, "Times New Roman", Times, serif;
					margin-left: 20%;
					margin-top: 50px;
					margin-right: 20%;
					padding: 1em 2em;
					-moz-border-radius: 12px;
					-khtml-border-radius: 12px;
					-webkit-border-radius: 12px;
				}
				#h1 {
					color: #006;
					font-size: 45px;
					font-weight: lighter;
				}				
				#subh1 {
					color: #879DBD;
					font-size: 25px;
					font-weight: lighter;
				}
				p, li, dt {
					line-height: 140%;
					padding-bottom: 2px;
				}
				a { color: #006; }
				ul, ol { padding: 5px 5px 5px 20px; }
				#logo { margin-bottom: 2em; }
				code { margin-left: 40px; }
				.submit {
					font-size:18pt;
					padding: 5px 7px 7px;
					border: 1px solid #a3a3a3;
					-moz-border-radius: 3px;
					-khtml-border-radius: 3px;
					-webkit-border-radius: 3px;
					border-radius: 3px;
					color: #246;
					background: #e5e5e5;
				}
				.submit:hover {
					color: #d54e21;
					border-color: #535353;
				}
				</style>
				</head>
				<body>
				<div id="content">
					<span id="h1">Piwik </span><span id="subh1"> # open source web analytics</span>
					<p>'.$message.'</p>
					'. $optionalTrace .'
					'. $optionalLinks .'
				</div>
				</body>
				</html>';
	echo $html;
	exit;
}

if(isset($piwik_errorMessage))
{
	Piwik_ExitWithMessage($piwik_errorMessage, false, true);
}

// we now include the upgradephp package to define some functions used in piwik 
// that may not be defined in the current php version
require_once "libs/upgradephp/upgrade.php";