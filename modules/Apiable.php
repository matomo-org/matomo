<?php
class Piwik_Apiable 
{
	protected function __construct()
	{
	}

	public function getMinimumRoleRequired( $methodName )
	{
		if(isset($this->roles[$methodName]))
		{
			$minimumRole = $this->roles[$methodName];
		}
		else
		{
			$minimumRole = 'superuser';
		}
		return $minimumRole;
	}	
}
?>