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
// ./phpdoc -d /home/jeichorn/phpdoc -dn phpDocumentor -ti "phpDocumentor generated docs" -td templates/DOM/l0l33t -t /tmp/phpdoc_DOM_l0l33t
/**
* This file creates example documentation output of all templates.
* @package phpDocumentor
*/

/**
* Directory the output should go to.
* Change this variable to an output directory on your computer
* @global	string	$output_directory
*/
$output_directory = "/tmp";
/**
* default package name, used to change all non-included files to this package
* @global	string	$base_package
*/
$base_package = "phpDocumentor";
/**
* Title of the generated documentation
* @global	string	$title
*/
$title = "phpDocumentor Generated Documentation";
/**
* location of the files to parse.  Change to a location on your computer.
* Example:
* <code>
* $parse_directory = "/home/jeichorn/phpdoc";
* </code>
* @global	string	$parse_directory
*/
$parse_directory = "/you-MUST/change-me/to-fit/your-environment";

/**
* directories to output examples into.
* @global	array	$output
*/
$output = array(
	$output_directory.'/docs/phpdoc_default'		=> 'HTML:default:default',
	$output_directory.'/docs/phpdoc_l0l33t'			=> 'HTML:default:l0l33t',
	$output_directory.'/docs/phpdoc_phpdoc_de'		=> 'HTML:default:phpdoc.de',
	$output_directory.'/docs/phpdoc_DOM_default'		=> 'HTML:default:DOM/default',
	$output_directory.'/docs/phpdoc_DOM_l0l33t'		=> 'HTML:default:DOM/l0l33t',
	$output_directory.'/docs/phpdoc_DOM_phpdoc_de' 		=> 'HTML:default:DOM/phpdoc.de',
	$output_directory.'/docs/phpdoc_smarty_default' 	=> 'HTML:Smarty:default',
	$output_directory.'/docs/phpdoc_pdf_default' 		=> 'PDF:default:default',
	$output_directory.'/docs/phpdoc_chm_default' 		=> 'CHM:default:default',
	);

foreach($output as $output => $template)
{
	passthru("./phpdoc -d /home/jeichorn/phpdoc -dn $base_package -ti \"$title\" -td $template -t $output");
}
