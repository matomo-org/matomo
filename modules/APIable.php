<?php
class Piwik_Apiable 
{
	static public $methodsNotToPublish = array();
	
	protected function __construct()
	{
	}

	protected function doNotPublishMethod( $methodName )
	{
		if(!method_exists($this, $methodName))
		{
			throw new Exception("The method $methodName doesn't exist so it can't be added to the list of the methods not to be published in the API.");
		}
		$this->methodsNotToPublish[] = $methodName;
	}
}
?>