<?php
require_once 'SitesManager.php';
class Piwik_Access
{
	private $acl = null;
	private $accesssByIdsite = null;
	private $idsitesByAccess = null;
	private $identity = null; //login
	private $isSuperUser = false;
	
	
	static private $availableAccess = array('noaccess', 'view', 'admin', 'superuser');
	
	static public function getListAccess()
	{
		return self::$availableAccess;
	}
	
	public function __construct( $auth )
	{
		$this->auth = $auth;
    }
	
	public function loadAccess()
	{
		$accessByIdsite = array();
		$idsitesByAccess = array( 'view' => array(), 'admin'  => array(), 'superuser'  => array());
		
		// access = array ( idsite => accessIdSite, idsite2 => accessIdSite2)
        $result = $this->auth->authenticate();
		
		if($result->isValid())
		{
			$this->identity = $result->getIdentity();
			
			// case the superUser is logged in
			if($result->getCode() == Piwik_Auth::SUCCESS_SUPERUSER_AUTH_CODE)
			{
				$this->isSuperUser = true;
				$sitesId = Piwik_SitesManager_API::getAllSitesId();
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
	
	public function getIdentity()
	{
		return $this->identity;
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
		
	public function checkUserIsSuperUser()
	{
		if($this->isSuperUser === false)
		{
			throw new Exception("You can't access this resource as it requires a 'superuser' access.");
		}
	}
	
	public function checkUserHasSomeAdminAccess()
	{
			$idSitesAccessible = $this->getSitesIdWithAdminAccess();
			if(count($idSitesAccessible) == 0)
			{
				throw new Exception("You can't access this resource as it requires an 'admin' access for at least one website.");
			}
	}
	public function checkUserHasAdminAccess( $idSites )
	{
		if(!is_array($idSites))
		{
			$idSites = array($idSites);
		}
			$idSitesAccessible = $this->getSitesIdWithAdminAccess();
			foreach($idSites as $idsite)
			{
				if(!in_array($idsite, $idSitesAccessible))
				{
					throw new Exception("You can't access this resource as it requires an 'admin' access for the website id = $idsite.");
				}
			}
	}
	
	public function checkUserHasViewAccess( $idSites )
	{
		if(!is_array($idSites))
		{
			$idSites = array($idSites);
		}
			$idSitesAccessible = $this->getSitesIdWithAtLeastViewAccess();
			foreach($idSites as $idsite)
			{
				if(!in_array($idsite, $idSitesAccessible))
				{
					throw new Exception("You can't access this resource as it requires a 'view' access for the website id = $idsite.");
				}
			}
	}
}

