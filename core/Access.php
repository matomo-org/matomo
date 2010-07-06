<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Class to handle User Access:
 * - loads user access from the Piwik_Auth_Result object 
 * - provides easy to use API to check the permissions for the current (check* methods)
 * 
 * In Piwik there are mainly 4 access levels
 * - no access
 * - VIEW access
 * - ADMIN access
 * - Super admin access
 *
 * An access level is on a per website basis.
 * A given user has a given access level for a given website.
 * For example:
 * User Noemie has
 * 	- VIEW access on the website 1,
 *  - ADMIN on the website 2 and 4, and
 *  - NO access on the website 3 and 5
 *
 * There is only one Super User. He has ADMIN access to all the websites 
 * and he only can change the main configuration settings.
 *
 * @package Piwik
 * @subpackage Piwik_Access
 */
class Piwik_Access
{	
	/**
	 * Array of idsites available to the current user, indexed by permission level
	 * @see getSitesIdWith*()
	 *
	 * @var array
	 */
	protected $idsitesByAccess = null;
	
	/**
	 * Login of the current user
	 *
	 * @var string
	 */
	protected $login = null;
	
	/**
	 * token_auth of the current user
	 *
	 * @var string
	 */
	protected $token_auth = null;
	
	/**
	 * Defines if the current user is the super user
	 * @see isSuperUser()
	 * 
	 * @var bool
	 */
	protected $isSuperUser = false;

	/**
	 * List of available permissions in Piwik
	 *
	 * @var array
	 */
	static private $availableAccess = array('noaccess', 'view', 'admin', 'superuser');

	/**
	 * Authentification object (see Piwik_Auth)
	 *
	 * @var Piwik_Auth
	 */
	private $auth = null;
	
	/**
	 * Returns the list of the existing Access level.
	 * Useful when a given API method requests a given acccess Level.
	 * We first check that the required access level exists.
	 */
	static public function getListAccess()
	{
		return self::$availableAccess;
	}

	function __construct() 
	{
		$this->idsitesByAccess = array( 
							'view' => array(), 
							'admin'  => array(), 
							'superuser'  => array()
		);
	}
	
	/**
	 * Loads the access levels for the current user.
	 *
	 * Calls the authentication method to try to log the user in the system.
	 * If the user credentials are not correct we don't load anything.
	 * If the login/password is correct the user is either the SuperUser or a normal user.
	 * We load the access levels for this user for all the websites.
	 * 
	 * @return true on success, false if reloading access failed (when auth object wasn't specified and user is not enforced to be Super User)
	 */
	public function reloadAccess(Piwik_Auth $auth = null)
	{
		if(!is_null($auth)) 
		{
			$this->auth = $auth;
		}
		
		// if the Piwik_Auth wasn't set, we may be in the special case of setSuperUser(), otherwise we fail
		if(is_null($this->auth)) 
		{
			if($this->isSuperUser())
			{
				return $this->reloadAccessSuperUser();
			}
			return false;
		}
		
		// access = array ( idsite => accessIdSite, idsite2 => accessIdSite2)
		$result = $this->auth->authenticate();

		if(!$result->isValid())
		{
			return false;
		}
		$this->login = $result->getIdentity();
		$this->token_auth = $result->getTokenAuth();
		
		// case the superUser is logged in
		if($result->getCode() == Piwik_Auth_Result::SUCCESS_SUPERUSER_AUTH_CODE)
		{
			return $this->reloadAccessSuperUser();
		}
		// case valid authentification (normal user logged in)
		
		// we join with site in case there are rows in access for an idsite that doesn't exist anymore
		// (backward compatibility ; before we deleted the site without deleting rows in _access table)
		$accessRaw = Piwik_FetchAll(self::getSqlAccessSite("access, t2.idsite"), $this->login);
		foreach($accessRaw as $access)
		{
			$this->idsitesByAccess[$access['access']][] = $access['idsite'];
		}
		return true;
	}

	/**
	 * Returns the SQL query joining sites and access table for a given login
	 * 
	 * @param $select eg. "MIN(ts_created)"
	 * @return string SQL query
	 */
	static public function getSqlAccessSite($select)
	{
		return "SELECT ". $select ."
						  FROM ".Piwik_Common::prefixTable('access'). " as t1 
							JOIN ".Piwik_Common::prefixTable('site')." as t2 USING (idsite) ".
						" WHERE login = ?";
	}
	
	/**
	 * Reload super user access
	 *
	 * @return bool
	 */	
	protected function reloadAccessSuperUser()
	{
		$this->isSuperUser = true;
		$this->idsitesByAccess['superuser'] = Piwik_SitesManager_API::getInstance()->getAllSitesId();
		return true;
	}
	
	/**
	 * We bypass the normal auth method and give the current user Super User rights.
	 * This should be very carefully used.
	 */
	public function setSuperUser($bool = true)
	{
		if($bool) 
		{
			$this->reloadAccessSuperUser();
		}
		else
		{
			$this->isSuperUser = false;
			$this->idsitesByAccess['superuser'] = array();
		}
	}
	
	/**
	 * Returns true if the current user is logged in as the super user
	 *
	 * @return bool
	 */
	public function isSuperUser()
	{
		return $this->isSuperUser;
	}
	
	/**
	 * Returns the current user login
	 *
	 * @return string|null
	 */
	public function getLogin()
	{
		return $this->login;
	}

	/**
	 * Returns the token_auth used to authenticate this user in the API
	 *
	 * @return string|null
	 */
	public function getTokenAuth()
	{
		return $this->token_auth;
	}
	
	/**
	 * Returns an array of ID sites for which the user has at least a VIEW access.
	 * Which means VIEW or ADMIN or SUPERUSER.
	 *
	 * @return array Example if the user is ADMIN for 4
	 *              and has VIEW access for 1 and 7, it returns array(1, 4, 7);
	 */
	public function getSitesIdWithAtLeastViewAccess()
	{
		return array_unique(array_merge(
			$this->idsitesByAccess['view'],
			$this->idsitesByAccess['admin'],
			$this->idsitesByAccess['superuser'])
		);
	}
	
	/**
	 * Returns an array of ID sites for which the user has an ADMIN access.
	 *
	 * @return array Example if the user is ADMIN for 4 and 8
	 *              and has VIEW access for 1 and 7, it returns array(4, 8);
	 */
	public function getSitesIdWithAdminAccess()
	{
		return array_unique(array_merge(
			$this->idsitesByAccess['admin'],
			$this->idsitesByAccess['superuser'])
		);
	}


	/**
	 * Returns an array of ID sites for which the user has a VIEW access only.
	 *
	 * @return array Example if the user is ADMIN for 4
	 *              and has VIEW access for 1 and 7, it returns array(1, 7);
	 * @see getSitesIdWithAtLeastViewAccess()
	 */
	public function getSitesIdWithViewAccess()
	{
		return $this->idsitesByAccess['view'];
	}

	/**
	 * Throws an exception if the user is not the SuperUser
	 * 
	 * @throws Exception
	 */
	public function checkUserIsSuperUser()
	{
		if(!$this->isSuperUser())
		{
			throw new Piwik_Access_NoAccessException(Piwik_TranslateException('General_ExceptionPrivilege', array("'superuser'")));
		}
	}

	/**
	 * If the user doesn't have an ADMIN access for at least one website, throws an exception
	 *
	 * @throws Exception
	 */
	public function checkUserHasSomeAdminAccess()
	{
		if($this->isSuperUser())
		{
			return;
		}
		$idSitesAccessible = $this->getSitesIdWithAdminAccess();
		if(count($idSitesAccessible) == 0)
		{
			throw new Piwik_Access_NoAccessException(Piwik_TranslateException('General_ExceptionPrivilegeAtLeastOneWebsite', array('admin')));
		}
	}
	
	/**
	 * If the user doesn't have any view permission, throw exception
	 *
	 * @throws Exception
	 */
	public function checkUserHasSomeViewAccess()
	{
		if($this->isSuperUser())
		{
			return;
		}
		$idSitesAccessible = $this->getSitesIdWithAtLeastViewAccess();
		if(count($idSitesAccessible) == 0)
		{
			throw new Piwik_Access_NoAccessException(Piwik_TranslateException('General_ExceptionPrivilegeAtLeastOneWebsite', array('view')));
		}
	}

	/**
	 * This method checks that the user has ADMIN access for the given list of websites.
	 * If the user doesn't have ADMIN access for at least one website of the list, we throw an exception.
	 * 
	 * @param int|arrayOfIntegers List of ID sites to check
	 * @throws Exception If for any of the websites the user doesn't have an ADMIN access
	 */
	public function checkUserHasAdminAccess( $idSites )
	{
		if($this->isSuperUser())
		{
			return;
		}
		
		if($idSites === 'all')
		{
			$idSites = $this->getSitesIdWithAtLeastViewAccess();
		}
		if(!is_array($idSites))
		{
			$idSites = Piwik_Site::getIdSitesFromIdSitesString($idSites);
		}
		$idSitesAccessible = $this->getSitesIdWithAdminAccess();
		foreach($idSites as $idsite)
		{
			if(!in_array($idsite, $idSitesAccessible))
			{
				throw new Piwik_Access_NoAccessException(Piwik_TranslateException('General_ExceptionPrivilegeAccessWebsite', array("'admin'", $idsite)));
			}
		}
	}

	/**
	 * This method checks that the user has VIEW or ADMIN access for the given list of websites.
	 * If the user doesn't have VIEW or ADMIN access for at least one website of the list, we throw an exception.
	 * 
	 * @param int|arrayOfIntegers|string List of ID sites to check (integer, array of integers, string comma separated list of integers)
	 * @throws Exception If for any of the websites the user doesn't have an VIEW or ADMIN access
	 */
	public function checkUserHasViewAccess( $idSites )
	{
		if($this->isSuperUser())
		{
			return;
		}
		if($idSites === 'all')
		{
			$idSites = $this->getSitesIdWithAtLeastViewAccess();
		}
		
		if(!is_array($idSites))
		{
			$idSites = Piwik_Site::getIdSitesFromIdSitesString($idSites);
		}
		$idSitesAccessible = $this->getSitesIdWithAtLeastViewAccess();

		foreach($idSites as $idsite)
		{
			if(!in_array($idsite, $idSitesAccessible))
			{
				throw new Piwik_Access_NoAccessException(Piwik_TranslateException('General_ExceptionPrivilegeAccessWebsite', array("'view'", $idsite)));
			}
		}
	}
}

/**
 *
 * Exception thrown when a user doesn't  have sufficient access.
 * 
 * @package Piwik
 * @subpackage Piwik_Access
 */
class Piwik_Access_NoAccessException extends Exception
{}
