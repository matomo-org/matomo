<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: ExamplePlugin.php 169 2008-01-14 05:41:15Z matt $
 * 
 * @package Piwik_ExamplePlugin
 */

class Piwik_Dashboard extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			// name must be the className prefix!
			'name' => 'Dashboard',
			'description' => '',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}

	public function install()
	{
		// we catch the exception
		try{
			$sql = "CREATE TABLE ". Piwik::prefixTable('user_dashboard')." (
					login VARCHAR( 20 ) NOT NULL ,
					iddashboard INT NOT NULL ,
					layout TEXT NOT NULL,
					PRIMARY KEY ( login , iddashboard )
					) " ;
			Piwik_Query($sql);
		} catch(Zend_Db_Statement_Exception $e){
			// mysql code error 1050:table already exists
			// see bug #153 http://dev.piwik.org/trac/ticket/153
			if(ereg('1050',$e->getMessage()))
			{
				return;
			}
			else
			{
				throw $e;
			}
		}
	}
	
	public function uninstall()
	{
		$sql = "DROP TABLE ". Piwik::prefixTable('user_dashboard') ;
		Piwik_Query($sql);		
	}
	
}

Piwik_AddMenu('Dashboard', '', array('module' => 'Dashboard', 'action' => 'embeddedIndex'));
