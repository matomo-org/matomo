<?php

class Piwik_ExampleFeedburner extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			// name must be the className prefix!
			'name' => 'ExampleFeedburner',
			'description' => 'Example Plugin: How to display your Feedburner subscriber in a Widget in the Dashboard?',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}

	function install()
	{
		try{
			Piwik_Query('ALTER TABLE '.Piwik::prefixTable('site'). " ADD `feedburnerName` VARCHAR( 100 ) DEFAULT NULL");
		} catch(Zend_Db_Statement_Exception $e){
			// mysql code error 1060: column already exists
			// if there is another error we throw the exception, otherwise it is OK as we are simply reinstalling the plugin
			if(!ereg('1060',$e->getMessage()))
			{
				throw $e;
			}
		}
	}
	
	function uninstall()
	{
		Piwik_Query('ALTER TABLE '.Piwik::prefixTable('site'). " DROP `feedburnerName`");
	}
}

Piwik_AddWidget('ExampleFeedburner', 'feedburner', 'Feedburner statistics');

class Piwik_ExampleFeedburner_Controller extends Piwik_Controller
{

	/**
	 * Simple feedburner statistics output
	 *
	 */
	function feedburner()
	{
		$view = new Piwik_View('ExampleFeedburner/feedburner.tpl');
		$feedburnerFeedName = Piwik_FetchOne('SELECT feedburnerName FROM '.Piwik::prefixTable('site').
								' WHERE idsite = ?', Piwik_Common::getRequestVar('idSite',1,'int') );
		if(empty($feedburnerFeedName))
		{
			$feedburnerFeedName = 'Piwik';
		}
		$view->feedburnerFeedName = $feedburnerFeedName;
		echo $view->render();
	}
	
	/**
	 * Function called to save the Feedburner ID entered in the form
	 *
	 */
	function saveFeedburnerName()
	{
		// we save the value in the DB for an authenticated user
		if(Piwik::getCurrentUserLogin() != 'anonymous')
		{
			Piwik_Query('UPDATE '.Piwik::prefixTable('site').' SET feedburnerName = ? WHERE idsite = ?', 
				array(Piwik_Common::getRequestVar('name','','string'), Piwik_Common::getRequestVar('idSite',1,'int'))
				);
		}
	}
}