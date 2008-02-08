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
	$message = "<p><b>To run Piwik you need at least PHP version $piwik_minimumPhpVersion </b></p> 
				<p>Unfortunately it seems your webserver is using PHP version $piwik_currentVersion. </p>
				<p>Please try to update your PHP version, Piwik is really worth it! Nowadays most web hosts 
				support PHP $piwik_minimumPhpVersion. </p>
				
				<ul><li><a href='http://piwik.org'>Piwik homepage</a></li>
					<li><a href='http://piwik.org/demo'>Piwik demo</a></li></ul>
					 ";
	$html = '<html>
				<head>
					<title>Piwik &rsaquo; Error</title>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<style>				
				html { background: #eee; }				
				body {
					background: #fff;
					color: #000;
					font-family: Georgia, "Times New Roman", Times, serif;
					margin-left: 20%;
					margin-top: 25px;
					margin-right: 20%;
					padding: .2em 2em;
				}
				a { color: #006; }
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
				ul, ol { padding: 5px 5px 5px 20px; }
				</style>
				</head>
				<body>
					<span id="h1">Piwik </span><span id="subh1"> # open source web analytics</span>
					<p>'.$message.'</p>
				</body>
				</html>';
	echo $html;
	exit;
}

// we now include the upgradephp package to define some functions used in piwik 
// that may not be defined in the current php version
require_once PIWIK_INCLUDE_PATH . "/libs/upgradephp/upgrade.php";