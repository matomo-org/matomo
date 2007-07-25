<?php
class Piwik_Apiable 
{
	protected function __construct()
	{
	}

	public function checkAccessSpecified()
	{
		 
	}
	public function getMinimumAccessRequired( $methodName )
	{
		if(isset($this->minimumAccessRequired[$methodName]))
		{
			$minimumAccess = $this->minimumAccessRequired[$methodName];
		}
		else
		{
			$minimumAccess = 'superuser';
		}
		return $minimumAccess;
	}	
}
?>