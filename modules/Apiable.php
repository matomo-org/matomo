<?php
class Piwik_Apiable 
{
	protected function __construct()
	{
	}

	public function checkAccessSpecified()
	{
		 
	}
	public function getMinimumRoleRequired( $methodName )
	{
		if(isset($this->minimumAccessRequired[$methodName]))
		{
			$minimumRole = $this->minimumAccessRequired[$methodName];
		}
		else
		{
			$minimumRole = 'superuser';
		}
		return $minimumRole;
	}	
}
?>