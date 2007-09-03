<?php
/**
 * This class is the parent class of all the modules that can be called using the 
 * API Proxy.
 * 
 * @package Piwik_API
 */
require_once "Archive.php";

class Piwik_Apiable 
{
	static public $methodsNotToPublish = array();
	
	protected function __construct()
	{
	}

	/**
	 * Register a public method as "not to be published in the API".
	 * Sometimes methods have to be marked as public to be used by other classes but
	 * we don't want these methods to be called from outside the application.
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
