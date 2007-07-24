<?php
/**
* This page returns a class with name "%s"'."\n", and shouldn't find class at all
* @package tests
*/
/**
* warning triggered when inheritance could be from more than one class
*/
define("PDERROR_MULTIPLE_PARENT",1);

//********************************************************


$GLOBALS['phpDocumentor_error_descrip'][PDERROR_PACKAGEOUTPUT_DELETES_PARENT_FILE] = '-po (packageoutput) option deletes parent file "%s" containing class "%s."'."\n".'  Try using --defaultpackagename (-dn) %s to include the parent file in the same package as the class';

/**
* @package tests
*/
class RecordWarning
{
}
?>