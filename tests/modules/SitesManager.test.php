<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '../..');
}
require_once PATH_TEST_TO_ROOT ."/tests/config_test.php";

require_once('Database.test.php');

class Test_Piwik_SitesManager extends Test_Database
{
    function __construct() 
    {
        parent::__construct('Log class test');
    }
    
    function testToAdd()
    {
    	
    }
}
?>
