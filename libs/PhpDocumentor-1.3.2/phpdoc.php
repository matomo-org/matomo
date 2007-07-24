<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
/**
 * Web Interface to phpDocumentor
 * @see new_phpdoc.php
 * @filesource
 * @deprecated in favor of docbuilder (see {@link docbuilder/config.php})
 * @package  phpDocumentor
 */
// 
// 
// An HTML interface for Joshua Eichorn's phpDocumentor
// Author: Juan Pablo Morales  <ju-moral@uniandes.edu.co>
//    Joshua Eichorn <jeichorn@phpdoc.org>
//    Gregory Beaver <cellog@users.sourceforge.net>
//
// phpDocumentor, a program for creating javadoc style documentation from php code
// Copyright (C) 2000-2002 Joshua Eichorn
// 
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
//
// Copyleft 2001 Juan Pablo Morales

// set up include path so we can find all files, no matter what
$GLOBALS['_phpDocumentor_install_dir'] = dirname(realpath(__FILE__));
// add my directory to the include path, and make it first, should fix any errors
if (substr(PHP_OS, 0, 3) == 'WIN')
ini_set('include_path',$GLOBALS['_phpDocumentor_install_dir'].';'.ini_get('include_path'));
else
ini_set('include_path',$GLOBALS['_phpDocumentor_install_dir'].':'.ini_get('include_path'));

/**
* common file information
*/
include_once("phpDocumentor/common.inc.php");
if (!function_exists('version_compare'))
{
    print "phpDocumentor requires PHP version 4.1.0 or greater to function";
    exit;
}

// find the .ini directory by parsing phpDocumentor.ini and extracting _phpDocumentor_options[userdir]
$ini = phpDocumentor_parse_ini_file($_phpDocumentor_install_dir . PATH_DELIMITER . 'phpDocumentor.ini', true);
if (isset($ini['_phpDocumentor_options']['userdir']))
    $configdir = $ini['_phpDocumentor_options']['userdir'];
else
    $configdir = $_phpDocumentor_install_dir . '/user';

// allow the user to change this at runtime
if (!empty($_REQUEST['altuserdir'])) $configdir = $_REQUEST['altuserdir'];
?>
<html>
   <head>
      <title>
         Form to submit to phpDocumentor v<?php print PHPDOCUMENTOR_VER; ?>
      </title>
      <?php   if(!isset($_GET['submit']) || !empty($_REQUEST['altuserdir'])) {
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
if (navigator.appVersion.lastIndexOf('Win') != -1)
  $newLine="\r\n";
 else 
  $newLine="\n";
/* for($a=0;$a<document.dataForm.elements.length;$a++) {
 alert("The name is '"+document.dataForm.elements[$a].name+"' "+$a);
 }
*/
}
/**Adds the contents of the help box as a directory
*/
function addDirectory($object) {
 $object.value = prepareString($object.value)+document.helpForm.fileName.value;
}
/**Adds the contents of the converter box to the converters list
*/
function addConverter($object) {
 $object.value = prepareString($object.value)+document.dataForm.ConverterSetting.value;
}
/**Adds the contents of the help box as a file to the given control
*/
function addFile($object) {
 $object.value = prepareString($object.value)+document.helpForm.fileName.value;
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
 function stripNewLines($myString) {
  return myReplace($myString,$newLine,'');
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
</script><?php } ?>
   </head>
   <?php
   //Find out if we are submitting and if we are, send it
   // This code originally by Joshua Eichorn on phpdoc.php
   //
   if(isset($_GET['submit']) && empty($_REQUEST['altuserdir'])) {
    echo "<body bgcolor=\"#ffffff\">";
    echo "<h1>Parsing Files ...</h1>";
    flush();
    echo "<pre>\n";
    /** phpdoc.inc */
    include("phpDocumentor/phpdoc.inc");
    echo "</pre>\n";
    echo "<h1>Operation Completed!!</h1>";
   } else {
    ?>
   <body bgcolor="#ffffff" onload="javascript:initializate()">
      <h1>
         Form to submit to phpDocumentor v<?php print PHPDOCUMENTOR_VER; ?>
      </h1>
      <form name="dataForm" action="phpdoc.php" method="GET" onsubmit=
      "return validate()">
         <div align="center">
            <table cellpadding="0" cellspacing="0" border="0" width="80%"
            bgcolor="#000000">
               <tr>
                  <td>
                     <table cellpadding="0" cellspacing="1" border="0" width=
                     "100%">
                        <tr>
                           <td bgcolor="#ff6633">
                              <b>Use a pre-created config file for form values.</b> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#CCCCCC">
                              Normally, phpDocumentor uses the form values from this form to set up parsing.  In version 1.2,
                              phpDocumentor allows you to "save" form values in configuration files so that you can replicate
                              common complicated documentation tasks with only one time.  Just choose a config file below or create a
                              new one and refresh this page to choose it.<hr /><b>change config directory:</b><input size="20" type="text" name="altuserdir" value=""><?php if (!empty($_REQUEST['altuserdir'])) print '<br><i>changed to <b>"'.$_REQUEST['altuserdir'].'"</b></i>'; ?>
              <b>Choose a config:</b> <select name="setting[useconfig]">
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
                              </select><input type="SUBMIT" value=
            "Go" name="submitButton"><br>
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#ffdddd">
                              <b>Generated Documentation Title</b> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#eeeeee">
                              Choose a title for the generated documentation<br>
                              <input type="TEXT" name="setting[title]" size=
                              "80" value="Generated Documentation"><br>
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#ff6633">
                              <b>Default Package Name</b> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#cccccc">
                              Choose a name for the default package<br>
                              <input type="TEXT" name="setting[defaultpackagename]" size=
                              "80" value="default"><br>
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#ffdddd">
                              <b>Target</b> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#eeeeee">
                              Target is the directory where
                              the output produced by phpDocumentor will reside<br>
                              <input type="TEXT" name="setting[target]" size=
                              "80"><br>
                              <a href=
                              "javascript:addDirectory(document.dataForm.elements[5])">
                              Add the directory in the help box</a> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#ffdddd">
                              <b>Custom Tags</b> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#eeeeee">
                              Custom Tags is a comma-separated list of tags
                              you want phpDocumentor to include as valid tags
                              in this parse.  An example would be "value, size"
                              to allow @value and @size tags.
                              <input type="TEXT" name="setting[customtags]" size=
                              "80"><br>
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#ff6633">
                              <b>Packages to parse</b> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#CCCCCC">
                              The parameter packages is a group of comma
                              separated names of abstract packages that will
                              be processed by phpDocumentor. All package names must be
                              separated by commas.<br>
<textarea rows="3" cols="80" name=
"setting[packageoutput]"></textarea> <br>
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#ffdddd">
                              <b>Files to parse</b> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#eeeeee">
                              This is a group of comma-separated names of php files
                              or tutorials that will be processed by phpDocumentor.<br>
<textarea rows="6" cols="80" name=
"setting[filename]"></textarea> <br>
                              <a href=
                              "javascript:addFile(document.dataForm.elements[8])">
                              Add the file in the help box</a> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#ff6633">
                              <b>Directory to parse</b> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#CCCCCC">
                              This is a group of comma-separated directories where php files
                              or tutorials are found that will be processed by phpDocumentor.
                              phpDocumentor automatically parses subdirectories<br>
<textarea rows="6" cols="80" name="setting[directory]"></textarea> <br>
                              <a href=
                              "javascript:addDirectory(document.dataForm.elements[9])">
                              Add the directory in the help box</a> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#ffdddd">
                              <b>Output Information</b> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#eeeeee">
                              <br>
                              Output Information is a comma-separated list of <b>Outputformat:Converter:Templates</b>
                              to apply to the output.  The Converters must be classes descended from Converter
                              defined in the phpDocumentor files, or parsing will not work.  Outputformat
                              may be HTML, XML, PDF, or CHM (case-sensitive) in version 1.2.  There is only one Converter
                              for both CHM and PDF, <b>default</b>.  There are 2 HTML Converters,
                              <b>frames</b> and <b>Smarty</b>. <b>frames templates</b> may be any of:<br><br>
                              <b>default, l0l33t, phpdoc.de, phphtmllib, phpedit, DOM/default, DOM/l0l33t, or DOM/phpdoc.de</b>.
                              <b>Smarty templates</b> may be any of:<br><br>
                              <b>default or PHP</b>.
                              <br>
                              There is only 1 template for all other Converters, <b>default</b>
                              <br>Output type:Converter name:template name <input type=
                              "TEXT" name="setting[output]" value=
                              "HTML:Smarty:default" size="80"><br>
                              <select name="ConverterSetting">
    <option value="HTML:frames:default">HTML:frames:default</option>
    <option value="HTML:frames:l0l33t">HTML:frames:l0l33t</option>
    <option value="HTML:frames:phpdoc.de">HTML:frames:phpdoc.de</option>
    <option value="HTML:frames:phphtmllib">HTML:frames:phphtmllib</option>
    <option value="HTML:frames:phpedit">HTML:frames:phpedit</option>
    <option value="HTML:frames:DOM/default">HTML:frames:DOM/default</option>
    <option value="HTML:frames:DOM/l0l33t">HTML:frames:DOM/l0l33t</option>
    <option value="HTML:frames:DOM/phpdoc.de">HTML:frames:DOM/phpdoc.de</option>
    <option value="HTML:Smarty:default" SELECTED>HTML:Smarty:default</option>
    <option value="HTML:Smarty:PHP">HTML:Smarty:PHP</option>
    <option value="PDF:default:default">PDF:default:default</option>
    <option value="CHM:default:default">CHM:default:default</option>
    <option value="XML:DocBook/peardoc2:default">XML:DocBook/peardoc2:default</option>
</select><br>
                            <a href=
                              "javascript:addConverter(document.dataForm.elements[10])">
                              Add the converter in the help box</a> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#ff6633">
                              <b>Files to ignore</b> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#CCCCCC">
                              A list of files (full path or filename), and patterns
                              to ignore.  Patterns may use wildcards * and ?. To
                              ignore all subdirectories named "test" for example,
                              using "test/"  To ignore all files and directories
                              with test in their name use "*test*"
<textarea rows="6" cols="80" name="setting[ignore]"></textarea> <br>
                              <a href=
                              "javascript:addDirectory(document.dataForm.elements[12])">
                              Add the directory in the help box</a> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#ff6633">
                              <b>Parse @access private</b> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#CCCCCC">
                              The parameter Parse @access private tells phpDocumentor
                              whether to parse elements with an "@access private" tag in their docblock<br>
                              <input type="checkbox" name="setting[parseprivate]" value="on">Parse private <br>
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#ff6633">
                              <b>JavaDoc-compliant Description parsing.</b> 
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#CCCCCC">
                              Normally, phpDocumentor uses several rules to determine the short description.  This switch
                              asks phpDocumentor to simply search for the first period (.) and use it to delineate the short
                              description.  In addition, the short description will not be separated from the long description<br>
                              <input type="checkbox" name="setting[javadocdesc]" value="on">JavaDoc-compliant Description <br>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
            </table>
            <input type="HIDDEN" name="interface" value="web"> <input type=
            "HIDDEN" name="submit" value="true"> <input type="SUBMIT" value=
            "Send Form" name="submitButton">
         </div>
      </form>
      <br>
      <br>
      <div align="center">
         <table cellpadding="0" cellspacing="0" border="0" width="80%" bgcolor=
         "#000000">
            <tr>
               <td>
                  <table cellpadding="0" cellspacing="1" border="0" width=
                  "100%">
                     <tr>
                        <td bgcolor="#ffdddd">
                           <b>A little help</b> 
                        </td>
                     </tr>
                     <tr>
                        <td bgcolor="#eeeeee">
                           Since remember long path is not that easy here is a
                           little file control to view names of files that can
                           the be aggregated to the different properties 
                           <form name="helpForm" action="" method="get"
                           enctype="multipart/form-data">
                              <input size="80" type="file" name="fileName">
                           </form>
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
         </table>
      </div>
      <br>
      <b>Web-Interface Author:</b>
      <p>
         Juan Pablo Morales<br>
         <a href=
         "mailto:ju-moral@uniandes.edu.co">ju-moral@uniandes.edu.co</a><br>
         Gregory Beaver<br>
         <a href=
         "mailto:cellog@users.sourceforge.net">cellog@users.sourceforge.net</a>, all post-0.3.0 modifications
      </p>
      <p>
         If you have any problems with phpDocumentor, please visit the website: <a
         href='http://phpdocu.sourceforge.net'>phpdocu.sourceforge.net</a> and
         submit a bug
      </p>
      <!-- Created: Tue Jun 26 18:52:40 MEST 2001 -->
      <!-- hhmts start -->
<pre>
Last modified: $Date: 2005/10/17 18:15:16 $
Revision: $Revision: 1.1 $
</pre>
      <!-- hhmts end -->
      <?php } //End the else that prints all code
      ?>
   </body>
</html>

