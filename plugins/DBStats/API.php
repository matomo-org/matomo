<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: API.php 482 2008-05-18 17:22:35Z matt $
 * 
 * @package Piwik_DBStats
 */

/**
 * 
 * @package Piwik_DBStats_API
 */
class Piwik_DBStats_API extends Piwik_Apiable
{
	static private $instance = null;
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}

 	static public function getDBStatus()
	{
		$configDb = Piwik_Config::getInstance()->database;
		
		// we decode the password. Password is html encoded because it's enclosed between " double quotes
		$configDb['password'] = htmlspecialchars_decode($configDb['password']);
		if(!isset($configDb['port']))
		{
			// before 0.2.4 there is no port specified in config file
			$configDb['port'] = '3306';  
		}

		$link   = mysql_connect($configDb['host'], $configDb['username'], $configDb['password']);
		$status = mysql_stat($link);
		mysql_close($link);
		return $status;
	}
	
	static function get_size($size) {
	$bytes = array('B','KB','MB','GB','TB');
	  foreach($bytes as $val) {
	   if($size > 1024){
	    $size = $size / 1024;
	   }else{
	    break;
	   }
	  }
	  return round($size, 1)." ".$val;
	}
	
	static public function getTableStatus($table, $field = '') 
	{
		$db = Zend_Registry::get('db');
		// http://dev.mysql.com/doc/refman/5.1/en/show-table-status.html
		$tables = $db->fetchAll("SHOW TABLE STATUS LIKE ?", $table);

		if ($field == '')
		{
			return $tables[0];
		}
		else
		{
			return $tables[0][$field];
		}
	}

	static public function getAllTablesStatus() 
	{
		$db = Zend_Registry::get('db');
		// http://dev.mysql.com/doc/refman/5.1/en/show-table-status.html
		$tablesPiwik =  Piwik::getTablesInstalled();
		foreach($tablesPiwik as $table) 
		{
			$t = self::getTableStatus($table);
			$t['Data_length'] = self::get_size($t['Data_length']);
			$t['Index_length'] = self::get_size($t['Index_length']);
			$t['Index_length'] = self::get_size($t['Index_length']);
			$t['Rows'] = self::get_size($t['Rows']);
			$tables[] = $t;
		}
		
		return $tables;
	}
}

