<?php
class Piwik_Access
{
	private $acl = null;
	private $rolesByIdsite = null;
	private $idsitesByRole = null;
	private $identity = null; //login
	private $isSuperUser = false;
	
	const SUCCESS_SUPERUSER_AUTH_CODE = 42;
	
	static private $availableRoles = array('noaccess', 'view', 'admin', 'superuser');
	
	public function __construct( $auth )
	{
		$this->auth = $auth;
		$this->loadRoles();
    }
	
	private function loadRoles()
	{
		$rolesByIdsite = array();
		$idsitesByRole = array( 'view', 'admin', 'superuser');
		
		// roles = array ( idsite => roleIdSite, idsite2 => roleIdSite2)
        $result = $this->auth->authenticate();
		
		// case the superUser is logged in
		if($result->getCode() == Piwik_Access::SUCCESS_SUPERUSER_AUTH_CODE)
		{
			$this->isSuperUser = true;
			$sitesId = Piwik_SitesManager::getAllSitesId();
			foreach($sitesId as $idSite)
			{
				$rolesByIdsite[$idSite] = 'superuser';
				$idsitesByRole['superuser'][] = $idSite;
			}
		}
		// valid authentification (normal user logged in)
		elseif($result->isValid())
		{
			$this->identity = $result->getIdentity();
			
			$db = Zend_Registry::get('db');
			$rolesRaw = $db->fetchAll("SELECT role, idsite 
							  FROM ".Piwik::prefixTable('role').
							" WHERE login=?", $this->identity);

			foreach($rolesRaw as $role)
			{
				$rolesByIdsite[$role['idsite']] = $role['role'];
				$idsitesByRole[$role['role']][] = $role['idsite'];
			}
		}
		
		$this->rolesByIdsite = $rolesByIdsite;
		$this->idsitesByRole = $idsitesByRole;
	}
	    
	static public function getListRoles()
	{
		return self::$availableRoles;
	}
	
	private function isRoleAllowed( $roleRequired, $idSite )
	{
		// if no role specified, the current access is noaccess
		$role = 'noaccess';
		
		if(isset($this->rolesByIdsite[$idSite]))
		{
			$role = $this->rolesByIdsite[$idSite];
		}
		
		switch($roleRequired)
		{
			case 'noaccess':
				return true;
			break;
			
			case 'view':
				return ($role == 'view' || $role == 'admin' || $role == 'superuser');
			break;
			
			case 'admin':
				return ($role == 'admin' || $role == 'superuser');
			break;
			
			case 'superuser':
				return ($role == 'superuser');
			break;
		}
	}
	
	public function getSitesIdWithAtLeastViewAccess()
	{
		return array_unique(array_merge(
					$this->idsitesByRole['view'],
					$this->idsitesByRole['admin'],
					$this->idsitesByRole['superuser']));
	}
	
	public function getSitesIdWithAdminAccess()
	{
		return array_unique(array_merge(
					$this->idsitesByRole['admin'],
					$this->idsitesByRole['superuser']));
	}
	
	public function getSitesIdWithViewAccess()
	{
		return 	$this->idsitesByRole['view'];
	}
	
	// is the current authentificated user allowed to access 
	// the method with the idsite given the minimumRole
	// false means no IdSite provided to the method. null means apply the method to all the websites on which the user has
	// the access required.
	public function isAllowed( $minimumRole, $idSites = false )
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
			if(isset($this->idsitesByRole[$minimumRole]))
			{
				$idSites = $this->idsitesByRole[$minimumRole];				
			}
			else
			{
				$idSites = array();
			}
		}
		
		// when the method called doesn't accept an IdSite parameter, then we must be a superUser
		if($idSites === false)
		{
			if(!$this->isSuperUser)
			{
				throw new Exception("Access to this resource requires a 'superuser' role.");
			}
		}
		else
		{			
			if(!is_array($idSites))
			{
				$idSites = array($idSites);
			}
			
			// when the method called accepts an IdSite parameter, then we test that the user has a minimumRole matching
			// for at least one website. For example, if the minimumRole is "admin" then the user must have at least 
			// one "admin" role for a website to be allowed to execute the method. 
			// Then the method itself must take care of restricting its scope on the website with the "admin" right.
			elseif(count($idSites) > 0)
			{
				foreach($idSites as $idsite)
				{
					if(!$this->isRoleAllowed($minimumRole, $idsite))
					{
						throw new Exception("Access to this resource requires a '$minimumRole' role for the idsite = $idsite.");
					}
				}
			}
		}
		
		return true;
	}
}

?>
