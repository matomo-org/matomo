<?php
class Piwik_Access
{
	private $acl = null;
	private $accesssByIdsite = null;
	private $idsitesByAccess = null;
	private $identity = null; //login
	private $isSuperUser = false;
	
	const SUCCESS_SUPERUSER_AUTH_CODE = 42;
	
	static private $availableAccess = array('noaccess', 'view', 'admin', 'superuser');
	
	public function __construct( $auth )
	{
		$this->auth = $auth;
		$this->loadAccess();
    }
	
	private function loadAccess()
	{
		$accessByIdsite = array();
		$idsitesByAccess = array( 'view', 'admin', 'superuser');
		
		// access = array ( idsite => accessIdSite, idsite2 => accessIdSite2)
        $result = $this->auth->authenticate();
		
		if($result->isValid())
		{
			$this->identity = $result->getIdentity();
			
			// case the superUser is logged in
			if($result->getCode() == Piwik_Access::SUCCESS_SUPERUSER_AUTH_CODE)
			{
				$this->isSuperUser = true;
				$sitesId = Piwik_SitesManager::getAllSitesId();
				foreach($sitesId as $idSite)
				{
					$accessByIdsite[$idSite] = 'superuser';
					$idsitesByAccess['superuser'][] = $idSite;
				}
			}
			// valid authentification (normal user logged in)
			else
			{				
				$db = Zend_Registry::get('db');
				$accessRaw = $db->fetchAll("SELECT access, idsite 
								  FROM ".Piwik::prefixTable('access').
								" WHERE login=?", $this->identity);
	
				foreach($accessRaw as $access)
				{
					$accessByIdsite[$access['idsite']] = $access['access'];
					$idsitesByAccess[$access['access']][] = $access['idsite'];
				}
			}
		}
		$this->accessByIdsite = $accessByIdsite;
		$this->idsitesByAccess = $idsitesByAccess;
	}
	
	static public function getIdentity()
	{
		return $this->identity;
	}
	static public function getListAccess()
	{
		return self::$availableAccess;
	}
	
	private function isAccessAllowed( $accessRequired, $idSite )
	{
		// if no access specified, the current access is noaccess
		$access = 'noaccess';
		
		if(isset($this->accessByIdsite[$idSite]))
		{
			$access = $this->accessByIdsite[$idSite];
		}
		
		switch($accessRequired)
		{
			case 'noaccess':
				return true;
			break;
			
			case 'view':
				return ($access == 'view' || $access == 'admin' || $access == 'superuser');
			break;
			
			case 'admin':
				return ($access == 'admin' || $access == 'superuser');
			break;
			
			case 'superuser':
				return ($access == 'superuser');
			break;
		}
	}
	
	public function getSitesIdWithAtLeastViewAccess()
	{
		return array_unique(array_merge(
					$this->idsitesByAccess['view'],
					$this->idsitesByAccess['admin'],
					$this->idsitesByAccess['superuser']));
	}
	
	public function getSitesIdWithAdminAccess()
	{
		return array_unique(array_merge(
					$this->idsitesByAccess['admin'],
					$this->idsitesByAccess['superuser']));
	}
	
	public function getSitesIdWithViewAccess()
	{
		return 	$this->idsitesByAccess['view'];
	}
	
	// is the current authentificated user allowed to access 
	// the method with the idsite given the minimumAccess
	// false means no IdSite provided to the method. null means apply the method to all the websites on which the user has
	// the access required.
	public function checkUserHasAccessToSites( $minimumAccess, $idSites = false )
	{
		// *use cases
		// view + 1/2/3 with 1/2 view and 3 noaccess => refused
		// view + 1/2/3 with 1/2 view and 3 admin => allowed
		// view + 1/2/3 with 1/2 noaccess and 3 admin => refused
		// view + null with 1/2 noaccess and 3 admin => allowed
		// admin + null with 1/2 view => refused
		// admin + 1 with 1 view => refused
		// admin + 1 with 1 admin => allowed
		// admin + null with 1 admin => allowed
		// superuser + 1 with 1 admin => refused
		if(is_null($idSites))
		{
			if(isset($this->idsitesByAccess[$minimumAccess]))
			{
				$idSites = $this->idsitesByAccess[$minimumAccess];				
			}
			else
			{
				$idSites = array();
			}
		}
		
		// when the method called doesn't accept an IdSite parameter, then we must be a superUser
		if($idSites === false)
		{
			self::checkUserIsSuperUser();
		}
		else
		{			
			if(!is_array($idSites))
			{
				$idSites = array($idSites);
			}
			
			// when the method called accepts an IdSite parameter, then we test that the user has a minimumAccess matching
			// for at least one website. For example, if the minimumAccess is "admin" then the user must have at least 
			// one "admin" access for a website to be allowed to execute the method. 
			// Then the method itself must take care of restricting its scope on the website with the "admin" right.
			elseif(count($idSites) > 0)
			{
				foreach($idSites as $idsite)
				{
					if(!$this->isAccessAllowed($minimumAccess, $idsite))
					{
						throw new Exception("Access to this resource requires a '$minimumAccess' access for the idsite = $idsite.");
					}
				}
			}
		}
		
		return true;
	}
	
	
	public function checkUserIsSuperUser()
	{
		if($this->isSuperUser === false)
		{
			throw new Exception("Access to this resource requires a 'superuser' access.");
		}
	}
	
	public function checkUserHasSomeAdminAccess()
	{
		//TODO implement
		return false;
	}
	public function checkUserHasAdminAccess( $idSites )
	{
		//TODO implement
		return false;
	}
	
	public function checkUserHasViewAccess( $idSites )
	{
		//TODO implement
		return false;
	}
}

?>