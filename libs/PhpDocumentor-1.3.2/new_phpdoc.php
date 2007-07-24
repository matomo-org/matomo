<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
/**
 * Advanced Web Interface to phpDocumentor
 * @see phpdoc.php
 * @package  phpDocumentor
 * @deprecated in favor of docbuilder (see {@link docbuilder/config.php})
 * @filesource
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

if (!function_exists('version_compare'))
{
    print "phpDocumentor requires PHP version 4.1.0 or greater to function";
    exit;
}

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
      <?php
      if(!isset($_GET['submit']) || !empty($_REQUEST['altuserdir'])) {
?>
<script src="HTML_TreeMenu-1.1.2/TreeMenu.js" language="JavaScript" type="text/javascript"></script>
                       <?php
        include_once('HTML_TreeMenu-1.1.2/TreeMenu.php');
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
        
        function getDir($path,&$node)
        {
            global $pd;
            if (!$dir = opendir($path)) return;
            
            $node = new HTML_TreeNode(array('text' => basename(realpath($path)), 'link' => "", 'icon' => 'folder.gif'));
            while (($file = readdir($dir)) !== false)
            {
                if ($file != '.' && $file != '..')
                {
                    if (is_dir("$path$pd$file") && !is_link("$path$pd$file"))
                    {
                        $entry[] = "$path$pd$file";
                    }
                }
            }
            closedir($dir);
            for($i = 0; $i < count($entry); $i++)
            {
                $node->addItem(new HTML_TreeNode(array('text'=>basename(realpath($entry[$i])), 'link' => "javascript:setHelp('".addslashes(realpath($entry[$i]))."');", 'icon' => 'folder.gif')));
            }
        }
        
        function recurseDir($path, &$node) {
            global $pd;
            if (!$dir = opendir($path)) {
                return false;
            }
            $anode = new HTML_TreeNode(array('text' => basename($path), 'link' => "javascript:setHelpVal('".$path."');", 'icon' => 'folder.gif'));
            $result = addslashes(realpath(stripslashes($path).$pd.".."));
            if (!$node) $anode->addItem(new DirNode('..',"javascript:setHelp('".$result."');",'folder.gif'),'..');
            while (($file = readdir($dir)) !== false) {
                if ($file != '.' && $file != '..') {
                    if (is_dir("$path$pd$file")) {
                        recurseDir("$path$pd$file",$anode);
                    }
                }
            }
            rewinddir($dir);//
            while (false){//($file = readdir($dir)) !== false) {
                if ($file != '.' && $file != '..') {
                    if (is_file("$path$pd$file")) {
                        $anode->addItem(new DirNode($file,"javascript:setHelpVal('$path$pd$file');",'branchtop.gif',"$path$pd$file"));
                    }
                }
            }
            if (!$node) $node = $anode;
            else
            $node->addItem($anode);
            closedir($dir);
        }
        
        function switchDirTree($path, &$node)
        {
            global $pd;

            // initialize recursion simulation values
            // array format: path => &parent in $node itemlist
            $parent = array();
            $parent_indexes = array();
            $parenti = 1;
            
            $node = new DirNode(basename($path),"javascript:setHelpVal('".$path."');",'folder.gif',$path);
            $result = addslashes(realpath($path.$pd.".."));
            $node->addItem(new DirNode('..',"javascript:setHelp('".$result."');",'folder.gif','..'));
            $rnode = &$node;
            $parent[realpath($path)] = false;
            $recur = 0;
            do
            {
                if ($recur++ > 120) return;
                if (!$dir = @opendir($path)) {
                    // no child files or directories
//                    echo "$path no child files or directories return to ";
                    $rnode = &$parent[realpath($path)];
                    $path = $rnode->path;
                    if (isset($parent_indexes[realpath($path)])) $parenti = $parent_indexes[realpath($path)];
//                    echo "$path parenti $parenti<br>";
                }
//                fancy_debug($path,$parent_indexes);
//                vdump_par($parent);
                if (!isset($parent_indexes[realpath($path)]))
                {
                    $file = readdir($dir);
                    while ($file !== false) {
                        if ($file != '.' && $file != '..') {
                            if (@is_dir(realpath("$path$pd$file"))) {
                                if (!isset($parent_indexes[realpath($path)])) $parent_indexes[realpath($path)] = true;
                                $parent[realpath("$path$pd$file")] = &$rnode;
//                                echo "<br>adding new ".addslashes(realpath($path.$pd.$file))." to $path<br>";
                                $rnode->addItem(new DirNode(addslashes(realpath("$path$pd$file")),"javascript:setHelpVal('".addslashes(realpath($path.$pd.$file))."');",'folder.gif',addslashes(realpath($path.$pd.$file))));
                            }
                        }
                        $file = readdir($dir);
                    }
                }
                // go down the tree if possible
                if (isset($parent_indexes[realpath($path)]))
                {
                    if ($parenti + 1 > (count($rnode->items)))
                    {
                        // no more children, go back up to parent
//                        echo "$path no more children, go back up to parent ";
                        $rnode = &$parent[realpath($path)];
                        $path = $rnode->path;
                        if (isset($parent_indexes[realpath($path)])) $parenti = $parent_indexes[realpath($path)];
//                        echo $path." parenti $parenti<br>";
                    } else
                    {
                        // go to next child
//                        echo "$path go to next child ";
                        $parent_indexes[realpath($path)] = $parenti+1;
//                        debug("set parent ".$rnode->items[$parenti]->path." = ".$rnode->path.'<br>');
                        $parent[realpath($rnode->items[$parenti]->path)] = &$rnode;
                        $rnode = &$rnode->items[$parenti];
                        $path = $rnode->path;
//                        echo "$path<br>";
                        $parenti = 0;
                    }
                } else
                {
                    // no children, go back up the tree to the next child
//                    echo "$path no children, go back up to parent ";
                    $rnode = &$parent[realpath($path)];
                    $path = $rnode->path;
                    if (isset($parent_indexes[realpath($path)])) $parenti = $parent_indexes[realpath($path)];
//                    echo "$path parenti $parenti<br>";
                }
                @closedir($dir);
            } while ($path && (($parenti < (count($rnode->items))) || ($parent[realpath($path)] !== false)));
        }
        
        function vdump_par($tree)
        {
            foreach($tree as $key => $val)
            {
                if ($val === false)
                debug($key.' -> false<br>');
                else
                debug($key.' -> ' .$val->path.'<br>');
            }
            debug('<br>');
        }
        
        $menu  = new HTML_TreeMenu();
        $filename = '';
        if (isset($_GET) && isset($_GET['fileName'])) $filename = $_GET['fileName'];
        $filename = realpath($filename);
        $pd = (substr(PHP_OS, 0, 3) == 'WIN') ? '\\' : '/';
        $test = ($pd == '/') ? '/' : 'C:\\';
        if (empty($filename) || ($filename == $test))
        {
            $filename = ($pd == '/') ? '/' : 'C:\\';
            $node = false;
            getDir($filename,$node);
        } else
        {
            flush();
//            if ($pd != '/') $pd = $pd.$pd;
            $anode = false;
            switchDirTree($filename,$anode);
//            recurseDir($filename,$anode);
            $node = new HTML_TreeNode(array('text' => "Click to view $filename",'link' => "",'icon' => 'branchtop.gif'));
            $node->addItem($anode);
        };
        $menu->addItem($node);
        $DHTMLmenu = &new HTML_TreeMenu_DHTML($menu, array('images' => 'HTML_TreeMenu-1.1.2/images'));
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
 {
  $pathdelim="\\";
  $newLine="\r\n";
 } else 
 {
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
function setHelp($str)
{
  document.helpForm.fileName.value = $str;
  document.helpForm.helpdata.click();
}

/** Sets the contents of the help box only
*/
function setHelpVal($str)
{
  document.helpForm.fileName.value = $str;
}
/**Adds the contents of the help box as a directory
*/
function addDirectory($object) {
 $a = document.helpForm.fileName.value;
 $a = myReplace($a,'\\\\','\\');
 if ($a[$a.length - 1] == $pathdelim) $a = $a.substring(0, $a.length - 1);
 if ($a.lastIndexOf('.') > 0)
 {
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
 $a = document.helpForm.fileName.value;
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
   } else
   {
    ?>
   <body bgcolor="#ffffff" onload="javascript:initializate()">
      <h1>
         phpDocumentor v<?php print PHPDOCUMENTOR_VER; ?> Web Interface
      </h1>
      phpDocumentor written by Joshua Eichorn<br>
      Web Interface written by Juan Pablo Morales and enhanced by Greg Beaver<br>
      <img src="poweredbyphpdoc.gif" alt="" width="88" height="31" border="0">
      <table cellpadding="1" cellspacing="1" border="0" width="60%" bgcolor=
         "#000000">
            <tr>
                 <td bgcolor="#ffff66">
                    <b>Help</b> 
                 </td>
                 <td bgcolor="#ffff99">
                    use this to find directories and files which can be used below
                    <form name="helpForm" action="<?php print $_SERVER['PHP_SELF']; ?>" method="get"
                    enctype="multipart/form-data">
                       <input size="80" type="text" name="fileName" value="<?php print $filename;?>">
                    <input type="submit" name="helpdata" value="browse tree">
                    </form>
<div id='menuLayer'></div>
<?php
        $DHTMLmenu->printMenu();
?>
                 </td>
              </tr>
         </table>

      <form name="dataForm" action="<?php print $_SERVER['PHP_SELF']; ?>" method="GET" onsubmit=
      "return validate()">
            <table cellpadding="3" cellspacing="3" border="0" width="80%"
  bgcolor="#000000">
               <tr>
                    <td bgcolor="#3399ff">
                        <b>Use a pre-created config file for form values.</b> 
                    </td>
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
               </tr>
                    <td bgcolor="#66cc66">
                       <b>Target</b> 
                    </td>
                    <td bgcolor="#eeeeee">
                       Target is the directory where
                       the output produced by phpDocumentor will reside<br>
                       <input type="TEXT" name="setting[target]" size=
                       "80"><br>
                       <a href=
                       "javascript:addDirectory(document.dataForm.elements[3])">
                       Add the directory in the help box</a> 
                    </td>
               </tr>
               <tr>
                    <td bgcolor="#3399ff">
                      <b>Files to parse</b> 
                   </td>
                      <td bgcolor="#CCCCCC">
                      This is a group of comma-separated names of php files
                      or tutorials that will be processed by phpDocumentor.<br>
<textarea rows="6" cols="80" name=
"setting[filename]"></textarea> <br>
                      <a href=
                      "javascript:addFile(document.dataForm.elements[4])">
                      Add the file in the help box</a> 
                   </td>
                </tr>
                <tr>
                    <td bgcolor="#66cc66">
                      <b>Directory to parse</b> 
                   </td>
                    <td bgcolor="#eeeeee">
                      This is a group of comma-separated directories where php files
                      or tutorials are found that will be processed by phpDocumentor.
                      phpDocumentor automatically parses subdirectories<br>
<textarea rows="6" cols="80" name="setting[directory]"></textarea> <br>
                      <a href=
                      "javascript:addDirectory(document.dataForm.elements[5])">
                      Add the directory in the help box</a> 
                   </td>
                </tr>
               <tr>
                    <td bgcolor="#66cc66">
                         <b>Files to ignore</b> 
                      </td>
                    <td bgcolor="#eeeeee">
                          A list of files (full path or filename), and patterns
                          to ignore.  Patterns may use wildcards * and ?. To
                          ignore all subdirectories named "test" for example,
                          using "test/"  To ignore all files and directories
                          with test in their name use "*test*"
<textarea rows="6" cols="80" name="setting[ignore]"></textarea> <br>
                         <a href=
                         "javascript:addDirectory(document.dataForm.elements[6])">
                         Add the directory in the help box</a> 
                      </td>
                   </tr>
               <tr>
                    <td bgcolor="#66cc66">
                       <b>Generated Documentation Title</b> 
                    </td>
                    <td bgcolor="#eeeeee">
                       Choose a title for the generated documentation<br>
                       <input type="TEXT" name="setting[title]" size=
                       "80" value="Generated Documentation"><br>
                    </td>
               </tr>
               <tr>
                    <td bgcolor="#3399ff">
                       <b>Default Package Name</b> 
                    </td>
                    <td bgcolor="#cccccc">
                       Choose a name for the default package<br>
                       <input type="TEXT" name="setting[defaultpackagename]" size=
                       "80" value="default"><br>
                    </td>
               <tr>
               <tr>
                    <td bgcolor="#3399ff">
                      <b>Custom Tags</b> 
                   </td>
                      <td bgcolor="#CCCCCC">
                      Custom Tags is a comma-separated list of tags
 you want phpDocumentor to include as valid tags
 in this parse.  An example would be "value, size"
 to allow @value and @size tags.
                      <input type="TEXT" name="setting[customtags]" size=
                      "80"><br>
                   </td>
               </tr>
               <tr>
                    <td bgcolor="#66cc66">
                     <b>Packages to parse</b> 
                  </td>
                    <td bgcolor="#eeeeee">
                      The parameter packages is a group of comma
                      separated names of abstract packages that will
                      be processed by phpDocumentor. All package names must be
                      separated by commas.<br>
<textarea rows="3" cols="80" name=
"setting[packageoutput]"></textarea> <br>
                  </td>
               </tr>
                <tr>
                    <td bgcolor="#3399ff">
                     <b>Output Information</b> 
                  </td>
                      <td bgcolor="#CCCCCC">
                     <br>
                     Outputformat
                     may be HTML, XML, PDF, or CHM (case-sensitive) in version 1.2.  There is only one Converter
                     for both CHM and PDF, <b>default</b>.  There are 2 HTML Converters,
                     <b>frames</b> and <b>Smarty</b>. <b>frames templates</b> may be any of:<br><br>
                     <b>default, l0l33t, phpdoc.de, phphtmllib, phpedit, DOM/default, DOM/l0l33t, or DOM/phpdoc.de</b>.
                                          <b>Smarty templates</b> may be any of:<br><br>
                     <b>default or PHP</b>.<br>
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
<option value="PDF:default:default">PDF:default:default</option>
<option value="CHM:default:default">CHM:default:default</option>
<option value="XML:DocBook/peardoc2:default">XML:DocBook/peardoc2:default</option>
</select><br>
<a href=
                     "javascript:addConverter(document.dataForm.elements[11])">
                     Add the converter in the help box</a> <br>
<a href=
                     "javascript:replaceConverter(document.dataForm.elements[11])">
                     Use ONLY the converter in the help box</a> 
                  </td>
               </tr>
                   <tr>
                    <td bgcolor="#3399ff">
                         <b>Parse @access private</b> 
                      </td>
                      <td bgcolor="#CCCCCC">
                         The parameter Parse @access private tells phpDocumentor
          whether to parse elements with an "@access private" tag in their docblock<br>
          <input type="checkbox" name="setting[parseprivate]" value="on">Parse private <br>
                      </td>
                   </tr>
                   <tr>
                    <td bgcolor="#66cc66">
                         <b>JavaDoc-compliant Description parsing.</b> 
                      </td>
                    <td bgcolor="#eeeeee">
                        Normally, phpDocumentor uses several rules to determine the short description.  This switch
                        asks phpDocumentor to simply search for the first period (.) and use it to delineate the short
                        description.  In addition, the short description will not be separated from the long description<br>
          <input type="checkbox" name="setting[javadocdesc]" value="on">JavaDoc-compliant Description <br>
                      </td>
                   </tr>
            </table>
            <input type="HIDDEN" name="interface" value="web"> <input type=
            "HIDDEN" name="submit" value="true"> <input type="SUBMIT" value=
            "Create Documentation" name="submitButton">
      </form>
      <br>
      <br>
      <br>
      <p>
         Joshua Eichorn <a href="mailto:jeichorn@phpdoc.org">jeichorn@phpdoc.org</a><br>
         Juan Pablo Morales <a href=
         "mailto:ju-moral@uniandes.edu.co">ju-moral@uniandes.edu.co</a><br>
         Gregory Beaver <a href=
         "mailto:cellog@users.sourceforge.net">cellog@users.sourceforge.net</a>
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

