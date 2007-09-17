<?php

class Piwik_Openads_Plugin
{
	/**
	 * Returns an array associating the hook name to the method to call
	 * 
	 * @return array array("hookToListenTo" => "methodNameToTrigger")
	 */
	function getListHooksRegistered()
	{
		return array(
			// publishers
			"openAds.insertPublisher" => "insertWebsite",
			"openAds.deletePublisher" => "deleteWebsite",
			"openAds.updatePublisher" => "updateWebsite",
			
			// users
			"openAds.insertPublisher" => "insertUser",
			"openAds.insertPublisher" => "deleteUser",
			"openAds.updatePublisher" => "updateUser",

		);
	}
	
	/**
	 * Methods triggered on the Openads DLL events
	 */
//	function insertWebsite( $parameters )
//	function deleteWebsite( $parameters )
//	function updateWebsite( $parameters )
//	function insertUser( $parameters )
//	function deleteUser( $parameters )
//	function updateUser( $parameters )	
	
	/**
	 * Function called statically by the Openads-Piwik delivery engine plugin
	 * Returns the javascript to print in the HEAD 
	 * 
	 * @param int The publisher ID in openads
	 * @return string A javascript code or the empty string
	 */
	function getJavascriptTag( $openadsPublisherId )
	{
		// reads the $piwikUrl from the Piwik Config file
		// if not available, returns empty string as it means piwik is not installed
		
		// The mapping is applied as follows
		// Piwik_SiteId from the openads PublisherId
		
	}
	
	
	/**
	 * Function called statically within Piwik at the end of the installation process
	 * 
	 * Returns a value from the Openads config file.
	 * This function is called either from openads or it can be called from piwik directly, 
	 * without the Openads initialization process.
	 * So this function must load the config file if it is not already loaded.
	 * 
	 * @param string Name of the config variable to return
	 * @param string Optional category name containing the config variable to return
	 * 
	 * @return string
	 * 
	 * -------------------------
	 * Example:
	 *  
	 * #config file
	 * [adminPlugins]
	 * piwik = piwik/example.php
	 * 
	 * Piwik_Openads_Plugin::getOpenadsConfigurationValue('piwik', 'adminPlugins') returns 'piwik/example.php'
	 * 
	 */
	function getOpenadsConfigurationValue($valueName, $valueCategory = null)
	{
		
	}
	
	
	/**
	 * 
	 * Function called statically within Piwik at the end of the installation process
	 * 
	 * Set a value from the Openads config file.
	 * This function is called either from openads or it can be called from piwik directly, 
	 * without the Openads initialization process.
	 * So this function must load the config file if it is not already loaded.
	 * 
	 * @param string Name of the value to set
	 * @param string Optional category name containing the config var to return
	 * 
	 * @return string
	 * 
	 * -------------------------
	 * Example:
	 * Piwik_Openads_Plugin::setOpenadsConfigurationValue('adminPlugins', 'piwik', 'piwik/example.php') 
	 * add the following lines in the configuration file (or modify the existing value for this variable)
	 *  
	 * [adminPlugins]
	 * piwik = piwik/example.php
	 * 
	 * 
	 */
	function setOpenadsConfigurationValue($variableCategory, $variableName, $value)
	{
		
	}
	
	/**
	 * Function called statically within Piwik at the end of the installation process
	 * 
	 * Returns the openads database information
	 * - host
	 * - login
	 * - password
	 * - Database name
	 * 
	 * @return array The array of values array( 
	 * 				'host' => "localhost",
	 *  			'login' => "mysqlUser",
	 * 				'password' => "passwordUser",
	 * 				'database_name' => "openads_db",
	 * );
	 */
	function getOpenadsDatabaseInformation()
	{
		
	}
	
	/**
	 * Function called statically within Piwik at the end of the installation process
	 * Returns the login of the OpenAds super user
	 * 
	 * @return string
	 */
	function getOpenadsSuperUserInformation()
	{
		
	}
	
	/**
	 * Function called statically within Piwik at the end of the installation process.
	 * 
	 * Returns an array containing all the Openads users login & piwik access information
	 * 
	 * This method does the mapping between the openads permission system and piwik permission system
	 * For a given user, we return an array containing, for the list of publishersId, the permission
	 * 
	 * The permission is one of the following value
	 * - view
	 * - admin
	 * 
	 * The mapping between openads and piwik is as follows
	 * - maps to "view" in piwik
	 * - maps to "admin" in piwik
	 * - maps to No Access in piwik (no access set)
	 * 
	 * Example:
	 * The user 'radek' in Openads is a publisher for the publisherId = 4. 
	 * He is also X for the publisherId = 12
	 * 
	 * "radek" => array( 4 => "view", 12 => "admin") 
	 * 
	 * @return array Returns array( 
	 * 		"loginUser1" => array ( openadsPubliserId_1 => piwikAccessForThisPublisher 
	 * 						),
	 * 		"loginUser2" => array ( openadsPubliserId_34 => piwikAccessForThisPublisher,
	 * 								openadsPubliserId_27 => piwikAccessForThisPublisher ) 
	 *						),
	 * 		)
	 */
	function getAllOpenadsUsersInformation()
	{
		
	}
	
	
	/**
	 * Function called statically within Piwik at the end of the installation process
	 * 
	 * Returns an array containing all the Openads websites ID & main Url
	 * 
	 * @return array Returns array( 
	 * 		array('id' => 1, 'url'  => 'http://www.example.com')
	 * 		array('id' => 65, 'url' => 'http://www.example2.com') 
	 * )
	 */
	function getAllOpenadsWebsitesInformation()
	{
		
	}
	
	
	/**
	 * Function called statically within Piwik when trying to authenticate the user
	 * 
	 * Returns the current logged OpenAds user
	 * 
	 * @return string User login of the currently logged user. 
	 * 				  Returns empty string if no user is authenticated.
	 * 
	 */
	function getCurrentlyAuthenticatedUserLogin()
	{
		
	}
	 
	/**
	 * Redirect the current page to the Piwik/index.php if we find the files
	 * - Piwik/index.php
	 * - Piwik/plugins/OpenadsIntegration.php
	 * 
	 * If we don't find the files we display a nice message explaining what to do to the users
	 * - We tell them where to download the package with the openads plugin
	 * - Once they have uploaded the package on this server, 
	 *   they give us the path to the package.
	 * - We save this path in the openads configuration file 
	 * - We redirect to this same page so it checks if now the files are available
	 */
	function redirectToPiwikIfAvailable()
	{
		print("redirecting...");
	}
	
}

// if we call this script directly then we call the conditional redirect method
if( basename($_SERVER['PHP_SELF']) == basename(__FILE__))
{
	Piwik_Openads_Plugin::redirectToPiwikIfAvailable();
}

