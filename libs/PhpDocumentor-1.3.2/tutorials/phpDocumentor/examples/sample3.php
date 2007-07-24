<?php
/**
 * Sample File 3, phpDocumentor Quickstart
 * 
 * This file demonstrates the use of the @name tag
 * @author Greg Beaver <cellog@php.net>
 * @version 1.0
 * @package sample
 */
/**
 * Special global variable declaration DocBlock
 * @global integer $GLOBALS['_myvar']
 */ 
$GLOBALS['_myvar'] = 6;
/**
 * Notice that the @name tag does not validate what you give it!
 * @global string $GLOBALS['turkey']
 * @name $turkify
 */ 
$GLOBALS['turkey'] = 'tester';
/**
 * Sample function @global usage
 * 
 * Note that the $turkey variable is not linked to its documentation at
 * {@link $turkify} because of the improper @name tag
 * @global integer
 * @global string this one has the optional description
 */
function testit()
{
    global $_myvar, $turkey;
}
?>