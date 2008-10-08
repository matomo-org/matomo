<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: APIable.php 482 2008-05-18 17:22:35Z matt $
 * 
 * @package Piwik_API
 */
require_once 'Archive.php';

/**
 * This class is the parent class of all the plugins that can be called using the API Proxy. 
 * For example a plugin "Provider" can publish its API by creating a file plugins/Provider/API.php
 * that is extending this Piwik_Apiable class.
 * All the Piwik_Apiable classes are read and loaded by the Piwik_API_Proxy class. 
 * The public methods of this class are published in the API and are then callable using the API module.
 * The parameters of the function are read directly from the GET request (they must have the same name).
 * 
 * For example
 *  public function helloWorld($text) { return "hello " . $text; } 
 * call be called using
 *  ?module=API&method=PluginName.helloWorld&text=world! 
 * 
 * See the documentation on http://dev.piwik.org > API
 * 
 * @package Piwik_API
 * @see Piwik_API_Proxy
 */

abstract class Piwik_Apiable 
{
	/**
	 * This array contains the name of the methods of the class we don't want to publish in the API.
	 * By default only public methods are published. Names of public methods in this array won't be published.
	 *
	 * @var array of strings
	 */
	static public $methodsNotToPublish = array();
	
	/**
	 * @see self::$methodsNotToPublish
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
