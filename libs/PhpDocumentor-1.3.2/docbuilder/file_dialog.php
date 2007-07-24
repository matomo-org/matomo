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
 * @version    CVS: $Id: file_dialog.php,v 1.6 2007/03/14 12:48:37 ashnazg Exp $
 * @filesource
 * @see phpdoc.php
 */
if (!function_exists( 'version_compare' )) {
    print "phpDocumentor requires PHP version 4.1.0 or greater to function";
    exit;
}

if ('@WEB-DIR@' != '@'.'WEB-DIR@')
{
    /**
    * common file information
    */
    require_once 'PhpDocumentor/phpDocumentor/common.inc.php';
    require_once 'PhpDocumentor/HTML_TreeMenu-1.1.2/TreeMenu.php';
    require_once( '@WEB-DIR@' . PATH_DELIMITER . 'PhpDocumentor/docbuilder/includes/utilities.php' );

    define(PHPDOC_WEBROOT_DIR, 'PhpDocumentor');

    // set up include path so we can find all files, no matter what
    $GLOBALS['_phpDocumentor_install_dir'] = PHPDOC_WEBROOT_DIR;

    // find the .ini directory by parsing phpDocumentor.ini and extracting _phpDocumentor_options[userdir]
    $ini = phpDocumentor_parse_ini_file('@DATA-DIR@' . PATH_DELIMITER . 'PhpDocumentor/phpDocumentor.ini', true);
    if (isset($ini['_phpDocumentor_options']['userdir'])) {
        $configdir = $ini['_phpDocumentor_options']['userdir'];
    } else {
        $configdir = '@DATA-DIR@' . PATH_DELIMITER . 'PhpDocumentor/user';
    }
} else {

    define(PHPDOC_WEBROOT_DIR, dirname(dirname(__FILE__)));
    /**
    * common file information
    */
    include_once(PHPDOC_WEBROOT_DIR . "/phpDocumentor/common.inc.php");
	include_once(PHPDOC_WEBROOT_DIR . "/HTML_TreeMenu-1.1.2/TreeMenu.php");
	include_once(PHPDOC_WEBROOT_DIR . "/docbuilder/includes/utilities.php" );

    // set up include path so we can find all files, no matter what
    $GLOBALS['_phpDocumentor_install_dir'] = dirname(dirname( realpath( __FILE__ ) ));
    // add my directory to the include path, and make it first, should fix any errors
    if (substr(PHP_OS, 0, 3) == 'WIN') {
    	ini_set('include_path',$GLOBALS['_phpDocumentor_install_dir'].';'.ini_get('include_path'));
    } else {
    	ini_set('include_path',$GLOBALS['_phpDocumentor_install_dir'].':'.ini_get('include_path'));
    }

    // find the .ini directory by parsing phpDocumentor.ini and extracting _phpDocumentor_options[userdir]
    $ini = phpDocumentor_parse_ini_file($_phpDocumentor_install_dir . PATH_DELIMITER . 'phpDocumentor.ini', true);
    if (isset($ini['_phpDocumentor_options']['userdir'])) {
        $configdir = $ini['_phpDocumentor_options']['userdir'];
    } else {
        $configdir = $_phpDocumentor_install_dir . '/user';
    }
}

// allow the user to change this at runtime
if (!empty( $_REQUEST['altuserdir'] )) {
	$configdir = $_REQUEST['altuserdir'];
}
?>
<html>
<head>
	<title>
		File browser
	</title>
	<style type="text/css">
		body, td, th, select, input {
			font-family: verdana,sans-serif;
			font-size: 9pt;
		}
		.text {
			font-family: verdana,sans-serif;
			font-size: 9pt;
			border: solid 1px #000000;
		}
		.button {
			border: solid 1px #000000;
		}
		.small {
			font-size: 7pt;
		}
	</style>

	<script src="../HTML_TreeMenu-1.1.2/TreeMenu.js" language="JavaScript" type="text/javascript"></script>

<?php
	set_time_limit(0);    // six minute timeout
	ini_set("memory_limit","256M");

	/**
	 * Directory Node
	 * @package HTML_TreeMenu
	 */
	class DirNode extends HTML_TreeNode
	{
		/**
		* full path to this node
		* @var string
		*/
		var $path;
		
		function DirNode($text = false, $link = false, $icon = false, $path, $events = array())
		{
			$this->path = $path;
			$options = array();
			if ($text) $options['text'] = $text;
			if ($link) $options['link'] = $link;
			if ($icon) $options['icon'] = $icon;
			HTML_TreeNode::HTML_TreeNode($options,$events);
		}
	}


	$menu  = new HTML_TreeMenu();
	$filename = '';
	if (isset($_GET) && isset($_GET['fileName'])) {
		$filename = $_GET['fileName'];
	}
	$filename = realpath($filename);
	$pd = (substr(PHP_OS, 0, 3) == 'WIN') ? '\\' : '/';
	$test = ($pd == '/') ? '/' : 'C:\\';
	if (empty($filename) || ($filename == $test)) {
		$filename = ($pd == '/') ? '/' : 'C:\\';
		$node = false;
        getDir($filename,$node);
    } else {
        flush();
//            if ($pd != '/') $pd = $pd.$pd;
        $anode = false;
        switchDirTree($filename,$anode);
//            recurseDir($filename,$anode);
        $node = new HTML_TreeNode(array('text' => "Click to view ".addslashes($filename),'link' => "",'icon' => 'branchtop.gif'));
        $node->addItem($anode);
    };
    $menu->addItem($node);
    if ('@WEB-DIR@' != '@'.'WEB-DIR@')
    {
    	$DHTMLmenu = &new HTML_TreeMenu_DHTML($menu,
                        array('images' => '../HTML_TreeMenu-1.1.2/images'));
    } else {
       $DHTMLmenu = &new HTML_TreeMenu_DHTML($menu,
                        array('images' => str_replace('/docbuilder/file_dialog.php','',$_SERVER['PHP_SELF']) .
                              '/HTML_TreeMenu-1.1.2/images'));
    }
?>
<script type="text/javascript" language="Javascript">
/**
   Creates some global variables
*/
function initializate() {
//
//The "platform independent" newLine
//
//Taken from http://developer.netscape.com/docs/manuals/communicator/jsref/brow1.htm#1010426
	if (navigator.appVersion.lastIndexOf( 'Win' ) != -1) {
		$pathdelim="\\";
		$newLine="\r\n";
	} else {
		$newLine="\n";
		$pathdelim="/";
	}
	/* for($a=0;$a<document.dataForm.elements.length;$a++) {
	 alert("The name is '"+document.dataForm.elements[$a].name+"' "+$a);
	 }
	*/
}
/** Sets the contents of the help box, and submits the form
*/
function setHelp( $str ) {
	document.helpForm.fileName.value = $str;
	document.helpForm.submit();
}

/** Sets the contents of the help box only
*/
function setHelpVal( $str ) {
	document.helpForm.fileName.value = $str;
}
/**Takes a given string and leaves it ready to add a new string
   That is, puts the comma and the new line if needed
*/
function prepareString($myString) {
 //First verify that a comma is not at the end
 if($myString.lastIndexOf(",") >= $myString.length-2) {
  //We have a comma at the end
  return $myString;
 }
 if($myString.length > 0) {
  $myString+=","+$newLine;
 }
 return $myString;
}


 function myReplace($string,$text,$by) {
 // Replaces text with by in string
     var $strLength = $string.length, $txtLength = $text.length;
     if (($strLength == 0) || ($txtLength == 0)) return $string;

     var $i = $string.indexOf($text);
     if ((!$i) && ($text != $string.substring(0,$txtLength))) return $string;
     if ($i == -1) return $string;

     var $newstr = $string.substring(0,$i) + $by;

     if ($i+$txtLength < $strLength)
         $newstr += myReplace($string.substring($i+$txtLength,$strLength),$text,$by);

     return $newstr;
 }
</script>

</head>

<body bgcolor="#ffffff" onload="javascript:initializate()">
<strong>Directory Browser</strong>

<table cellpadding="1" cellspacing="1" border="0" width="100%">

<form name="helpForm" action="<?php print $_SERVER['PHP_SELF']; ?>" method="get" enctype="multipart/form-data">
<tr>
	<td colspan="2" width="100%">
		Use this to find directories and files which can be used below:
	</td>
</tr>
<tr>
	<td align="right">
		<a href="javascript:document.helpForm.submit();" title="browse tree">
<?php
	echo showImage( 'images/rc-gui-install-24.png', '24', '24' );
?>
		</a>
	</td>
	<td>
		<input size="60" type="text" name="fileName" value="<?php print $filename;?>" class="text" />
	</td>
</tr>
<tr>
	<td>
		<input type="submit" name="helpdata" value="close" class="button" onclick="window.close();" />
	</td>
	<td align="right">
		<input type="submit" name="helpdata" value="accept" class="button" onclick="opener.setFile(document.helpForm.fileName.value);window.close();" />
	</td>
</tr>
<tr>
	<td colspan="2">
		<div id='menuLayer'></div>
		<?php $DHTMLmenu->printMenu(); ?>
	</td>
</tr>
</form>

</table>

</body>
</html>
