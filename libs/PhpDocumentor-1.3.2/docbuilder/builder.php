<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
/**
 * phpDocumentor :: docBuilder Web Interface
 * 
 * Advanced Web Interface to phpDocumentor
 *
 * PHP versions 4 and 5
 *
 * Copyright (c) 2003-2006 Andrew Eddie, Greg Beaver
 * 
 * LICENSE:
 * 
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package    phpDocumentor
 * @author     Andrew Eddie
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2003-2006 Andrew Eddie, Greg Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: builder.php,v 1.4 2006/04/30 22:18:13 cellog Exp $
 * @filesource
 * @see phpdoc.php
 */

if (!function_exists('version_compare'))
{
    print "phpDocumentor requires PHP version 4.1.0 or greater to function";
    exit;
}


if ('@DATA-DIR@' != '@'.'DATA-DIR@') {
    // set up include path so we can find all files, no matter what
    $root_dir = 'PhpDocumentor';
    /**
    * common file information
    */
    include_once("$root_dir/phpDocumentor/common.inc.php");
    $GLOBALS['_phpDocumentor_install_dir'] = 'PhpDocumentor';
    // find the .ini directory by parsing phpDocumentor.ini and extracting _phpDocumentor_options[userdir]
    $ini = phpDocumentor_parse_ini_file('@DATA-DIR@/PhpDocumentor/phpDocumentor.ini', true);
    if (isset($ini['_phpDocumentor_options']['userdir']))
    {
        $configdir = $ini['_phpDocumentor_options']['userdir'];
    } else {
        $configdir = '@DATA-DIR@/user';
    }
} else {
    // set up include path so we can find all files, no matter what
    $GLOBALS['_phpDocumentor_install_dir'] = dirname(dirname(realpath(__FILE__)));
    $root_dir = dirname(dirname(__FILE__));
    /**
    * common file information
    */
    include_once("$root_dir/phpDocumentor/common.inc.php");
    // add my directory to the include path, and make it first, should fix any errors
    if (substr(PHP_OS, 0, 3) == 'WIN')
    {
        ini_set('include_path',$GLOBALS['_phpDocumentor_install_dir'].';'.ini_get('include_path'));
    } else {
        ini_set('include_path',$GLOBALS['_phpDocumentor_install_dir'].':'.ini_get('include_path'));
    }
    // find the .ini directory by parsing phpDocumentor.ini and extracting _phpDocumentor_options[userdir]
    $ini = phpDocumentor_parse_ini_file($_phpDocumentor_install_dir . PATH_DELIMITER . 'phpDocumentor.ini', true);
    if (isset($ini['_phpDocumentor_options']['userdir']))
    {
        $configdir = $ini['_phpDocumentor_options']['userdir'];
    } else {
        $configdir = $_phpDocumentor_install_dir . '/user';
    }
}



// allow the user to change this at runtime
if (!empty($_REQUEST['altuserdir'])) $configdir = $_REQUEST['altuserdir'];
?>
<html>
<head>
	<title>
		output: docbuilder - phpDocumentor v<?php print PHPDOCUMENTOR_VER; ?> doc generation information
	</title>
	<style type="text/css">
		body, td, th {
			font-family: verdana,sans-serif;
			font-size: 8pt;
		}
	</style>

</head>
<body bgcolor="#e0e0e0" text="#000000" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">

<?php
// Find out if we are submitting and if we are, send it
// This code originally by Joshua Eichorn on phpdoc.php
//
if (isset($_GET['dataform']) && empty($_REQUEST['altuserdir'])) {
	foreach ($_GET as $k=>$v) {
		if (strpos( $k, 'setting_' ) === 0) {
			$_GET['setting'][substr( $k, 8 )] = $v;
		}
	}

	echo "<strong>Parsing Files ...</strong>";
	flush();
	echo "<pre>\n";
	/** phpdoc.inc */
	include("$root_dir/phpDocumentor/phpdoc.inc");
	echo "</pre>\n";
	echo "<h1>Operation Completed!!</h1>";
} else {
	echo "whoops!";
}
?>