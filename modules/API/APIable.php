<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */


/**
 * This class is the parent class of all the modules that can be called using the API Proxy. 
 * For example a plugin "Provider" can publish its API by creating a file plugins/Provider/API.php
 * that is extending this Piwik_Apiable class.
 * 
 * All the Piwik_Apiable classes are read and loaded by the Piwik_API_Proxy class. 
 * 
 * @package Piwik_API
 * @see Piwik_API_Proxy
 */
require_once "Archive.php";

abstract class Piwik_Apiable 
{
	static public $methodsNotToPublish = array();
	
	protected function __construct()
	{
	}

	/**
	 * Register a public method as "not to be published in the API".
	 * Sometimes methods have to be marked as public to be used by other classes but
	 * we don't want these methods to be called from outside the application using the API.
	 * 
	 * @param string Method name not to be published
	 */
	protected function doNotPublishMethod( $methodName )
	{
		if(!method_exists($this, $methodName))
		{
			throw new Exception("The method $methodName doesn't exist so it can't be added to the list of the methods not to be published in the API.");
		}
		$this->methodsNotToPublish[] = $methodName;
	}
}
