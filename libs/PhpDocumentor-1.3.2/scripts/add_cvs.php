<?php
//
// +------------------------------------------------------------------------+
// | phpDocumentor                                                          |
// +------------------------------------------------------------------------+
// | Copyright (c) 2000-2003 Joshua Eichorn, Gregory Beaver                 |
// | Email         jeichorn@phpdoc.org, cellog@phpdoc.org                   |
// | Web           http://www.phpdoc.org                                    |
// | Mirror        http://phpdocu.sourceforge.net/                          |
// | PEAR          http://pear.php.net/package-info.php?pacid=137           |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
/**
 * CVS file adding iterator
 *
 * This file iterates over a directory, and adds everything to CVS that is
 * found, ignoring any error messages, until all files in each directory
 * and subdirectory have been added to cvs.  It then commits the files to cvs
 * @package phpDocumentor
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @copyright Copyright 2003, Greg Beaver
 * @version 1.0
 */
/**#@+
 * phpDocumentor include files.  If you don't have phpDocumentor, go get it!
 * Your php life will be changed forever
 */
$dir = realpath(dirname(__FILE__).'/..');
require_once("$dir/phpDocumentor/common.inc.php");
require_once("$dir/phpDocumentor/Io.inc");
/**#@-*/

/**
* Physical location on this computer of the package to parse
* @global string $cvsadd_directory
*/
$cvsadd_directory = realpath('.');
/**
* Comma-separated list of files and directories to ignore
*
* This uses wildcards * and ? to remove extra files/directories that are
* not part of the package or release
* @global string $ignore
*/
$ignore = array('CVS/');

/******************************************************************************
*       Don't change anything below here unless you're adventuresome          *
*******************************************************************************/

/**
 * @global Io $files
 */
$files = new Io;

$allfiles = $files->dirList($cvsadd_directory);
/**#@+
 * Sorting functions for the file list
 * @param string
 * @param string
 */
function sortfiles($a, $b)
{
	return strnatcasecmp($a['file'],$b['file']);
}

function mystrucsort($a, $b)
{
	if (is_numeric($a) && is_string($b)) return 1;
	if (is_numeric($b) && is_string($a)) return -1;
	if (is_numeric($a) && is_numeric($b))
	{
		if ($a > $b) return 1;
		if ($a < $b) return -1;
		if ($a == $b) return 0;
	}
	return strnatcasecmp($a,$b);
}
/**#@-*/

$struc = array();
foreach($allfiles as $file)
{
	if ($files->checkIgnore(basename($file),dirname($file),$ignore, false))
    {
//        print 'Ignoring '.$file."<br>\n";
        continue;
    }
	$path = substr(dirname($file),strlen(str_replace('\\','/',realpath($cvsadd_directory)))+1);
	if (!$path) $path = '/';
	$file = basename($file);
	$ext = array_pop(explode('.',$file));
	if (strlen($ext) == strlen($file)) $ext = '';
	$struc[$path][] = array('file' => $file,'ext' => $ext);
}
uksort($struc,'strnatcasecmp');
foreach($struc as $key => $ind)
{
	usort($ind,'sortfiles');
	$struc[$key] = $ind;
}
$tempstruc = $struc;
$struc = array('/' => $tempstruc['/']);
$bv = 0;
foreach($tempstruc as $key => $ind)
{
	$save = $key;
	if ($key != '/')
	{
        $struc['/'] = setup_dirs($struc['/'], explode('/',$key), $tempstruc[$key]);
	}
}
uksort($struc['/'],'mystrucsort');
/**
 * Recursively add files to cvs
 * @param array the sorted directory structure
 */
function addToCVS($struc)
{
	foreach($struc as $dir => $files)
	{
		if ($dir === '/')
		{
            print 'processing '.$dir . "\n";
			addToCVS($struc[$dir]);
			return;
		} else
		{
			if (!isset($files['file']))
			{
                print 'adding '.$dir . "\n";
                system('cvs add '.$dir);
                chdir($dir);
				addToCVS($files);
                chdir('..');
			} else
			{
                print 'adding '.$files['file'] . "\n";
                system('cvs add '.$files['file']);
                system('cvs commit -m "" '.$files['file']);
			}
		}
	}
}
addToCVS($struc);
print "\n".'done';
?>