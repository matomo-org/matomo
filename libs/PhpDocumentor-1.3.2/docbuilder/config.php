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
 * @version    CVS: $Id$
 * @filesource
 * @see phpdoc.php
 */

if (!function_exists( 'version_compare' )) {
	print "phpDocumentor requires PHP version 4.1.0 or greater to function";
	exit;
}

if ('@DATA-DIR@' != '@'.'DATA-DIR@')
{
    $root_dir = 'PhpDocumentor';
    $path = '@WEB-DIR@/PhpDocumentor/docbuilder/images/';

    /**
    * common file information
    */
    include_once("PhpDocumentor/phpDocumentor/common.inc.php");
    include_once("@WEB-DIR@/PhpDocumentor/docbuilder/includes/utilities.php" );

    // find the .ini directory by parsing phpDocumentor.ini and extracting _phpDocumentor_options[userdir]
    $ini = phpDocumentor_parse_ini_file('@DATA-DIR@/PhpDocumentor/phpDocumentor.ini', true);
    if (isset($ini['_phpDocumentor_options']['userdir'])) {
    	$configdir = $ini['_phpDocumentor_options']['userdir'];
    } else {
    	$configdir =  '@DATA-DIR@/PhpDocumentor/user';
    }
} else {
    $root_dir = dirname(dirname(__FILE__));
    $path = 'images/';

    // set up include path so we can find all files, no matter what
    $GLOBALS['_phpDocumentor_install_dir'] = dirname(dirname( realpath( __FILE__ ) ));
    // add my directory to the include path, and make it first, should fix any errors
    if (substr(PHP_OS, 0, 3) == 'WIN') {
    	ini_set('include_path',$GLOBALS['_phpDocumentor_install_dir'].';'.ini_get('include_path'));
    } else {
    	ini_set('include_path',$GLOBALS['_phpDocumentor_install_dir'].':'.ini_get('include_path'));
    }

    /**
    * common file information
    */
    include_once("$root_dir/phpDocumentor/common.inc.php");
    include_once("$root_dir/docbuilder/includes/utilities.php" );

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

// assign the available converters
$converters = array(
	"HTML:frames:default"			=>	'HTML:frames:default',
	"HTML:frames:earthli"			=>	'HTML:frames:earthli',
	"HTML:frames:l0l33t"			=>	'HTML:frames:l0l33t',
	"HTML:frames:phpdoc.de"			=>	'HTML:frames:phpdoc.de',
	"HTML:frames:phphtmllib"		=>	'HTML:frames:phphtmllib',
	"HTML:frames:phpedit"			=>	'HTML:frames:phpedit',
	"HTML:frames:DOM/default"		=>	'HTML:frames:DOM/default',
	"HTML:frames:DOM/earthli"	    =>	'HTML:frames:DOM/earthli',
	"HTML:frames:DOM/l0l33t"		=>	'HTML:frames:DOM/l0l33t',
	"HTML:frames:DOM/phpdoc.de"		=>	'HTML:frames:DOM/phpdoc.de',
	"HTML:frames:DOM/phphtmllib"	=>	'HTML:frames:DOM/phphtmllib',
	"HTML:Smarty:default"			=>	'HTML:Smarty:default',
	"HTML:Smarty:HandS"				=>	'HTML:Smarty:HandS',
	"HTML:Smarty:PHP"   			=>	'HTML:Smarty:PHP',
	"PDF:default:default"			=>	'PDF:default:default',
	"CHM:default:default"			=>	'CHM:default:default',
	"XML:DocBook/peardoc2:default"	=>	'XML:DocBook/peardoc2:default'
);

// compile a list of available screen shots
$convScreenShots = array();

if ($dir = opendir($path)) {
	while (($file = readdir( $dir )) !== false) { 
		if ($file != '.' && $file != '..') {
			if (!is_dir( $path . $file )) {
				if (strpos( $file, 'ss_' ) === 0) {
					$key = substr( $file, 3 );
					$key = str_replace( '_', ':', $key );
					$key = str_replace( '-', '/', $key );
					$key = str_replace( '.png', '', $key );
					$convScreenShots[$key] = $file;
				}
			}
		}
	}
}

?>
<html>
<head>
	<title>
		Form to submit to phpDocumentor v<?php print PHPDOCUMENTOR_VER; ?>
	</title>
	<style type="text/css">
		body, td, th {
			font-family: verdana,sans-serif;
			font-size: 9pt;
		}
		.text {
			font-family: verdana,sans-serif;
			font-size: 9pt;
			border: solid 1px #000000;
		}
		.small {
			font-size: 7pt;
		}
	</style>

	<script src="includes/tabpane.js" language="JavaScript" type="text/javascript"></script>
	<link id="webfx-tab-style-sheet" type="text/css" rel="stylesheet" href="includes/tab.webfx.css" />

<script type="text/javascript" language="Javascript">
/**
   Creates some global variables
*/
function initializate() {
//
//The "platform independent" newLine
//
//Taken from http://developer.netscape.com/docs/manuals/communicator/jsref/brow1.htm#1010426
	if (navigator.appVersion.lastIndexOf('Win') != -1) {
	  $pathdelim="\\";
	  $newLine="\r\n";
	} else {
	  $newLine="\n";
	  $pathdelim="/";
	}
}

/**Adds the contents of the help box as a directory
*/
function addDirectory($object) {
	//$a = document.helpForm.fileName.value;
	$a = parent.ActionFrame.document.actionFrm.fileName.value;
	$a = myReplace( $a, '\\\\', '\\' );
	if ($a[$a.length - 1] == $pathdelim) {
		$a = $a.substring(0, $a.length - 1);
	}
	if ($a.lastIndexOf('.') > 0) {
		$a = $a.substring(0,$a.lastIndexOf($pathdelim));
	}
	$object.value = prepareString($object.value)+$a;
}
/**Adds the contents of the converter box to the converters list
*/
function addConverter($object) {
 $object.value = prepareString($object.value)+document.dataForm.ConverterSetting.value;
}
/**Replaces the converters list with the contents of the converter box
*/
function replaceConverter($object) {
 $object.value = document.dataForm.ConverterSetting.value;
}
/**Adds the contents of the help box as a file to the given control
*/
function addFile($object) {
	//$a = document.helpForm.fileName.value;
	$a = parent.ActionFrame.document.actionFrm.fileName.value;
	$a = myReplace($a,'\\\\','\\');
	$object.value = prepareString($object.value)+$a;
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
/**Do the validation needed before sending the from and return a truth value indicating if the form can be sent
*/
 function validate() {
  //Take out all newLines and change them by nothing
  //This could be done by using javascript function's replacebut that was implemented only until Navigator 4.0 and so it is better to use more backward compatible functions like substr
  document.dataForm.elements[0].value= stripNewLines(document.dataForm.elements[0].value);
  document.dataForm.elements[1].value= stripNewLines(document.dataForm.elements[1].value);
  document.dataForm.elements[2].value= stripNewLines(document.dataForm.elements[2].value);
  document.dataForm.elements[3].value= stripNewLines(document.dataForm.elements[3].value);
  document.dataForm.elements[4].value= stripNewLines(document.dataForm.elements[4].value);
  document.dataForm.elements[5].value= stripNewLines(document.dataForm.elements[5].value);
  document.dataForm.elements[6].value= stripNewLines(document.dataForm.elements[6].value);
  document.dataForm.elements[7].value= stripNewLines(document.dataForm.elements[7].value);
  //By returning true we are allowing the form to be submitted
  return true;
 }
/**Takes a string and removes all the ocurrences of new lines
Could have been implemented a lot easier with replace but it's not very backwards compatible
*/
function stripNewLines( $myString ) {
	return myReplace($myString,$newLine,'');
}

function myReplace($string,$text,$by) {
	// Replaces text with by in string
	var $strLength = $string.length, $txtLength = $text.length;
	if (($strLength == 0) || ($txtLength == 0)) {
		return $string;
	}
	var $i = $string.indexOf($text);
	if ((!$i) && ($text != $string.substring(0,$txtLength))) {
		return $string;
	}
	if ($i == -1) {
		return $string;
	}
	var $newstr = $string.substring(0,$i) + $by;
	if ($i+$txtLength < $strLength) {
		$newstr += myReplace($string.substring($i+$txtLength,$strLength),$text,$by);
	}
	return $newstr;
}

var screenShots = new Array();
<?php
	$temp = array();
	foreach ($converters as $k=>$v) {
		if (array_key_exists( $k, $convScreenShots )) {
			echo "\nscreenShots['$k'] = '{$convScreenShots[$k]}'";
		} else {
			echo "\nscreenShots['$k'] = ''";
		}
	}
?>


/** Swaps the converted screen shot image
*/
	function swapImage( key ) {
		document.screenshot.src = 'images/' + screenShots[key];
	}

</script>

</head>

<body bgcolor="#ffffff" onload="javascript:initializate()" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">

<!-- onsubmit="return validate()"  -->

<form name="dataForm" action="builder.php" method="get" target="OutputFrame">

<div class="tab-pane" id="tabPane1">
<script type="text/javascript">
	var tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ));
</script>
	<div class="tab-page" id="tab_intro">
		<h2 class="tab">Introduction</h2>
		Welcome to <b>docBuilder</b>.
		<p>This is the new web-interface for running, in our opinion, the best in-code documentation compiler there is: <b>phpDocumentor</b>.</p>
		<p>What's new in this release?  Heaps of things, but here are the headlines:</p>
		<ul>
            <li>Much greater support for PEAR on both windows and linux</li>
			<li>CHM, PDF and XML:DocBook/peardoc2 converters are all stable!</li>
			<li>New tokenizer-based parser is literally twice as fast as the old parser (requires PHP 4.3.0+)</li>
			<li>New external user-level manual parsing and generation allows cross-linking between API docs and DocBook-format tutorials/manuals!</li>
			<li>Color syntax source highlighting and cross-referencing with documentation of source code in HTML, CHM and PDF with customizable templating</li>
			<li>New Configuration files simplify repetitive and complex documentation tasks</li>
			<li>Brand new extensive manual - which can be generated directly from the source using makedocs.ini!</li>
			<li>Many improvements to in-code API documentation including new tags, and better handling of in-code html tags</li>
			<li>New XML:DocBook/peardoc converter makes generating PEAR manual formats easy for PEAR developers along with the --pear command-line switch</li>
			<li>Many new HTML templates, all of them beautiful thanks to Marco von Ballmoos</li>
			<li>A brand new web interface thanks to Andrew Eddie!</li>
		</ul>
	</div>

	<div class="tab-page" id="tab_config">
		<h2 class="tab">Config</h2>
		<table cellspacing="0" cellpadding="3" border="0">
		<tr>
			<td colspan="2"><b>Use a pre-created config file for form values.</b></td>
		</tr>
		<tr>
			<td nowrap="nowrap">
				<b>change config directory:</b>
			</td>
			<td width="100%">
				<input size="20" type="text" name="altuserdir" value="" /><input type="SUBMIT" value="Change" name="submitButton" onclick="document.dataForm.target='DataFrame'; document.dataForm.action = 'config.php';document.dataForm.submit();">
		<?php
			if (!empty($_REQUEST['altuserdir'])) {
				print '<br><i>changed to <b>"'.$_REQUEST['altuserdir'].'"</b></i>';
			}
		?>
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap">
				<b>Choose a config:</b>
			</td>
			<td>

				<select name="setting_useconfig">
					  <option value="" <?php if (empty($_REQUEST['altuserdir'])) print 'selected'; ?>>don't use config file</option>
					  <?php
					  $dirs = array();
					  $dirs = phpDocumentor_ConfigFileList($configdir);
					  $path = '';
					  $sel = ' selected';
					  if (!empty($_REQUEST['altuserdir'])) $path = $configdir . PATH_DELIMITER;
					  else $sel = '';
					  foreach($dirs as $configfile)
					  {
						  print '<option value="'.$path.$configfile.'"'.$sel.'>'.$configfile.".ini</option>\n";
						  $sel = '';
					  }
					  ?>
				</select>
				<input type="SUBMIT" value="Go" name="submitButton">
			</td>
		</tr>
		<tr>
			<td colspan="2">
				Normally, phpDocumentor uses the form values from this form to set up parsing.  In version 1.2, phpDocumentor allows you to "save" form values in configuration files so that you can replicate common complicated documentation tasks with only one time.  Just choose a config file below or create a new one and refresh this page to choose it.
			</td>
		</tr>
		</table>
	</div>

	<div class="tab-page" id="tab_files">
		<h2 class="tab">Files</h2>
		<table cellspacing="0" cellpadding="3" border="0">
		<tr>
			<td align="right" valign="top" nowrap="nowrap">
				<b>Files<br />to parse</b>
				<br />
				<a href="javascript:addFile(document.dataForm.setting_filename)" title="Add the file in the help box">
<?php
	echo showImage( 'images/rc-gui-install-24.png', '24', '24' );
?></a>
			</td>
			<td valign="top">
				<textarea rows="5" cols="60" name="setting_filename" class="text"></textarea>
			</td>
			<td valign="top" class="small">
				This is a group of comma-separated names of php files or tutorials that will be processed by phpDocumentor.
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" nowrap="nowrap">
				<b>Directory<br />to parse</b>
				<br />
				<a href="javascript:addFile(document.dataForm.setting_directory)" title="Add the file in the help box">
<?php
	echo showImage( 'images/rc-gui-install-24.png', '24', '24' );
?></a>
			</td>
			<td valign="top">
				<textarea rows="5" cols="60" name="setting_directory" class="text" title=""></textarea>
			</td>
			<td valign="top" class="small">
				This is a group of comma-separated directories where php files or tutorials are found that will be processed by phpDocumentor. phpDocumentor automatically parses subdirectories
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" nowrap="nowrap">
				<b>Files<br />to ignore</b>
				<br />
				<a href="javascript:addFile(document.dataForm.setting_ignore)" title="Add the file in the help box">
<?php
	echo showImage( 'images/rc-gui-install-24.png', '24', '24' );
?></a>
			</td>
			<td valign="top">
				<textarea rows="5" cols="60" class="text" name="setting_ignore"></textarea>
			</td>
			<td valign="top" class="small">
				A list of files (full path or filename), and patterns to ignore.  Patterns may use wildcards * and ?.  To ignore all subdirectories named "test" for example, using "test/"  To ignore all files and directories with test in their name use "*test*"
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" nowrap="nowrap">
				<b>Packages<br />to parse</b>
			</td>
			<td valign="top">
				<textarea rows="4" cols="60" class="text" name="setting_packageoutput"></textarea>
			</td>
			<td valign="top" class="small">
				The parameter packages is a group of comma separated names of abstract packages that will be processed by phpDocumentor. All package names must be separated by commas.
			</td>
		</tr>
		</table>
	</div>


	<div class="tab-page" id="tab_output">
		<h2 class="tab">Output</h2>
		<table cellspacing="0" cellpadding="3" border="0">
		<tr>
			<td align="right" valign="top" nowrap="nowrap">
				<b>Target</b>
				<br />
				<a href="javascript:addFile(document.dataForm.setting_target)" title="Add the file in the help box">
<?php
	echo showImage( 'images/rc-gui-install-24.png', '24', '24' );
?></a>
			</td>
			<td valign="top">
				<input type="text" name="setting_target" size="60" class="text" />
			</td>
			<td valign="top" class="small">
				Target is the directory where the output produced by phpDocumentor will reside.
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" nowrap="nowrap">
				<b>Output<br />Format</b>
			</td>
			<td valign="top">
				<textarea cols="60" rows="3" name="setting_output" class="text">HTML:Smarty:default</textarea>
				<br />
				Output type:Converter name:template name
				<br />
<?php
	echo htmlArraySelect( $converters, 'ConverterSetting', 'size="1" class="text" onchange="swapImage(this.options[this.options.selectedIndex].value);"', 'HTML:Smarty:default' );
?>
				<br />
				<a href="javascript:addConverter(document.dataForm.setting_output)">
					Add the converter in the help box
				</a>
				<br />
				<br />
				<img name="screenshot" src="images/ss_HTML_Smarty_default.png" width="200" height="200" border="2" alt="Screen Shot">
			</td>
			<td valign="top" class="small">
				Outputformat may be HTML, XML, PDF, or CHM (case-sensitive) in version 1.2.
				<br />There is only one Converter for both CHM and PDF:<br /><i>default</i>.
				<br />There are 2 HTML Converters:<br /><i>frames</i> or <i>Smarty</i>.
				<br /><b>frames templates</b> may be any of:
				<br />
				<i>default, earthli, l0l33t, phpdoc.de, phphtmllib, phpedit, DOM/default, DOM/earthli, DOM/l0l33t, DOM/phphtmllib, or DOM/phpdoc.de</i>.
				<br />
				<b>Smarty templates</b> may be any of:
				<br />
				<i>default, HandS, or PHP</i>
				<br />
				<strong>XML:DocBook/peardoc2:default</strong> is the only choice for XML in 1.2.2
			</td>
		</tr>
		</table>
	</div>

	<div class="tab-page" id="tab_options">
		<h2 class="tab">Options</h2>
		<table cellspacing="0" cellpadding="3" border="0">
		<tr>
			<td align="right" nowrap="nowrap">
				<b>Generated Documentation Title</b>
			</td>
			<td>
				<input type="text" name="setting_title" size="40" value="Generated Documentation" class="text">
			</td>
			<td class="small">
				Choose a title for the generated documentation
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap">
				<b>Default Package Name</b>
			</td>
			<td>
				<input type="TEXT" name="setting_defaultpackagename" size="40" value="default" class="text" />
			</td>
			<td class="small">
				Choose a name for the default package
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap">
				<b>Default Category Name</b>
			</td>
			<td>
				<input type="TEXT" name="setting_defaultcategoryname" size="40" value="default" class="text" />
			</td>
			<td class="small">
				Choose a name for the default category.  This is only used by the peardoc2 converter
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap">
				<b>Custom Tags</b>
			</td>
			<td>
				<input type="text" name="setting_customtags" size="40" class="text" />
			</td>
			<td class="small">
				Custom Tags is a comma-separated list of tags you want phpDocumentor to include as valid tags in this parse.  An example would be 'value, size' to allow @value and @size tags.
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap">
				<b>Parse @access private and @internal/{@internal}}</b>
			</td>
			<td nowrap="nowrap">
				<input type="checkbox" name="setting_parseprivate" value="on" />
			</td>
			<td class="small">
				The parameter Parse @access private tells phpDocumentor whether to parse elements with an '@access private' tag in their docblock.  In addition, it will turn on parsing of @internal tags and inline {@internal}} sections
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap">
				<b>Generate Highlighted Source Code</b>
			</td>
			<td nowrap="nowrap">
				<input type="checkbox" name="setting_sourcecode" value="on" />
			</td>
			<td class="small">
				The parameter Generate Highlighted Source Code tells phpDocumentor whether to generate highlighted XRef source code similar to PHP-XRef output.
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap">
				<b>JavaDoc-compliant<br />Description parsing.</b>
			</td>
			<td>
				<input type="checkbox" name="setting_javadocdesc" value="on" />
			</td>
			<td class="small">
				Normally, phpDocumentor uses several rules to determine the short description.  This switch asks phpDocumentor to simply search for the first period (.) and use it to delineate the short description.  In addition, the short description will not be separated from the long description.
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap">
				<b>PEAR package repository parsing</b>
			</td>
			<td>
				<input type="checkbox" name="setting_pear" value="on" />
			</td>
			<td class="small">
				PEAR package repositories have specific requirements:
                <ol>
                    <li>Every package is in a directory with the same name.</li>
                    <li>All private data members and methods begin with an underscore (function _privfunction()).</li>
                    <li>_Classname() is a destructor</li>
                </ol>
                This option recognizes these facts and uses them to make assumptions about packaging and access levels.  Note that with PHP 5, the destructor option will be obsolete.
			</td>
		</tr>
		</table>
	</div>

	<div class="tab-page" id="tab_credits">
		<h2 class="tab">Credits</h2>
		phpDocumentor written by Joshua Eichorn
		<br />Web Interface originally written by Juan Pablo Morales, enhanced by Greg Beaver and super-charged by Andrew Eddie
		<p>
		Joshua Eichorn <a href="mailto:jeichorn@phpdoc.org">jeichorn@phpdoc.org</a>
		<br>Juan Pablo Morales <a href=
		"mailto:ju-moral@uniandes.edu.co">ju-moral@uniandes.edu.co</a>
		<br>Gregory Beaver <a href=
		"mailto:cellog@users.sourceforge.net">cellog@users.sourceforge.net</a>
		<br>Andrew Eddie <a href=
		"mailto:eddieajau@users.sourceforge.net">eddieajau@users.sourceforge.net</a>
		</p>
		<p>
		If you have any problems with phpDocumentor, please visit the website: <a href='http://phpdocu.sourceforge.net'>phpdocu.sourceforge.net</a> and submit a bug
		</p>
		<!-- Created: Tue Jun 26 18:52:40 MEST 2001 -->
		<!-- hhmts start -->
		<pre>
		Last modified: $Date: 2006/04/30 22:18:13 $
		Revision: $Revision: 1.4 $
		</pre>
	</div>
	<div class="tab-page" id="tab_links">
		<h2 class="tab">Links</h2>
		<ul>
			<li><a target="_top" href="http://www.phpdoc.org/manual.php">phpDocumentor manual</a> - Learn how to use phpDocumentor to document your PHP source code</li>
			<li><a target="_top" href="http://phpdocu.sourceforge.net/">phpDocumentor homepage</a> on SourceForge</li>
			<li><a target="_top" href="http://freshmeat.net/projects/phpdocu">Freshmeat record</a> - subscribe here</li>
		</ul>
	</div>
</div>
<input type="hidden" name="interface" value="web">
<input type="hidden" name="dataform" value="true">

</form>

<script type="text/javascript">

	tp1.addTabPage( document.getElementById( "tab_intro" ) );
	tp1.addTabPage( document.getElementById( "tab_config" ) );
	tp1.addTabPage( document.getElementById( "tab_files" ) );
	tp1.addTabPage( document.getElementById( "tab_output" ) );
	tp1.addTabPage( document.getElementById( "tab_options" ) );
	tp1.addTabPage( document.getElementById( "tab_credits" ) );
	tp1.addTabPage( document.getElementById( "tab_links" ) );
	setupAllTabs();
</script>

</body>
</html>
