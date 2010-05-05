<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_UsersManager
 */

/**
 * 
 * @package Piwik_UsersManager
 */
class Piwik_UsersManager_API 
{
	static private $instance = null;
	
	/**
	 * @return Piwik_UsersManager_API
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	const PREFERENCE_DEFAULT_REPORT = 'defaultReport';
	const PREFERENCE_DEFAULT_REPORT_DATE = 'defaultReportDate';
	
	/**
	 * Sets a user preference
	 * @param $userLogin
	 * @param $preferenceName
	 * @param $preferenceValue
	 * @return void
	 */
	public function setUserPreference($userLogin, $preferenceName, $preferenceValue)
	{
		Piwik::checkUserIsSuperUserOrTheUser($userLogin);
		Piwik_SetOption($this->getPreferenceId($userLogin, $preferenceName), $preferenceValue);
	}
	
	/**
	 * Gets a user preference
	 * @param $userLogin
	 * @param $preferenceName
	 * @param $preferenceValue
	 * @return void
	 */
	public function getUserPreference($userLogin, $preferenceName)
	{
		Piwik::checkUserIsSuperUserOrTheUser($userLogin);
		return Piwik_GetOption($this->getPreferenceId($userLogin, $preferenceName));
	}
	
	private function getPreferenceId($login, $preference)
	{
		return $login . '_' . $preference;
	}
	
	/**
	 * Returns the list of all the users
	 * 
	 * @return array the list of all the users
	 */
	public function getUsers()
	{
		Piwik::checkUserIsSuperUser();
		
		$db = Zend_Registry::get('db');
		$users = $db->fetchAll("SELECT * FROM ".Piwik::prefixTable("user")." ORDER BY login ASC");
		return $users;
	}
	
	/**
	 * Returns the list of all the users login
	 * 
	 * @return array the list of all the users login
	 */
	public function getUsersLogin()
	{
		Piwik::checkUserHasSomeAdminAccess();
		
		$db = Zend_Registry::get('db');
		$users = $db->fetchAll("SELECT login FROM ".Piwik::prefixTable("user")." ORDER BY login ASC");
		$return = array();
		foreach($users as $login)
		{
			$return[] = $login['login'];
		}
		return $return;
	}
	
	/**
	 * For each user, returns the list of website IDs where the user has the supplied $access level.
	 * If a user doesn't have the given $access to any website IDs, 
	 * the user will not be in the returned array.
	 * 
	 * @param string Access can have the following values : 'view' or 'admin'
	 * 
	 * @return array 	The returned array has the format 
	 * 					array( 
	 * 						login1 => array ( idsite1,idsite2), 
	 * 						login2 => array(idsite2), 
	 * 						...
	 * 					)
	 * 
	 */
	public function getUsersSitesFromAccess( $access )
	{
		Piwik::checkUserIsSuperUser();
		
		$this->checkAccessType($access);
		
		$db = Zend_Registry::get('db');
		$users = $db->fetchAll("SELECT login,idsite 
								FROM ".Piwik::prefixTable("access")
								." WHERE access = ?", $access);
		$return = array();
		foreach($users as $user)
		{
			$return[$user['login']][] = $user['idsite'];
		}
		return $return;
		
	}
	
	/**
	 * For each user, returns his access level for the given $idSite.
	 * If a user doesn't have any access to the $idSite ('noaccess'), 
	 * the user will not be in the returned array.
	 * 
	 * @param string website ID
	 * 
	 * @return array 	The returned array has the format 
	 * 					array( 
	 * 						login1 => 'view', 
	 * 						login2 => 'admin',
	 * 						login3 => 'view', 
	 * 						...
	 * 					)
	 */
	public function getUsersAccessFromSite( $idSite )
	{
		Piwik::checkUserHasAdminAccess( $idSite );
		
		$db = Zend_Registry::get('db');
		$users = $db->fetchAll("SELECT login,access 
								FROM ".Piwik::prefixTable("access")
								." WHERE idsite = ?", $idSite);
		$return = array();
		foreach($users as $user)
		{
			$return[$user['login']] = $user['access'];
		}
		return $return;
		
	}

	/**
	 * For each website ID, returns the access level of the given $userLogin.
	 * If the user doesn't have any access to a website ('noaccess'), 
	 * this website will not be in the returned array.
	 * If the user doesn't have any access, the returned array will be an empty array.
	 * 
	 * @param string User that has to be valid
	 * 
	 * @return array 	The returned array has the format 
	 * 					array( 
	 * 						idsite1 => 'view', 
	 * 						idsite2 => 'admin',
	 * 						idsite3 => 'view', 
	 * 						...
	 * 					)
	 */
	public function getSitesAccessFromUser( $userLogin )
	{
		Piwik::checkUserIsSuperUser();
		$this->checkUserExists($userLogin);
		$this->checkUserIsNotSuperUser($userLogin);
		
		$db = Zend_Registry::get('db');
		$users = $db->fetchAll("SELECT idsite,access 
								FROM ".Piwik::prefixTable("access")
								." WHERE login = ?", $userLogin);
		$return = array();
		foreach($users as $user)
		{
			$return[] = array(
				'site' => $user['idsite'],
				'access' => $user['access'],
			);
		}
		return $return;
	}

	/**
	 * Returns the user information (login, password md5, alias, email, date_registered, etc.)
	 * 
	 * @param string the user login
	 * 
	 * @return array the user information
	 */
	public function getUser( $userLogin )
	{
		Piwik::checkUserIsSuperUserOrTheUser($userLogin);
		$this->checkUserExists($userLogin);
		$this->checkUserIsNotSuperUser($userLogin);
		
		$db = Zend_Registry::get('db');
		$user = $db->fetchRow("SELECT * 
								FROM ".Piwik::prefixTable("user")
								." WHERE login = ?", $userLogin);
		return $user;
	}
	
	/**
	 * Returns the user information (login, password md5, alias, email, date_registered, etc.)
	 * 
	 * @param string the user email
	 * 
	 * @return array the user information
	 */
	public function getUserByEmail( $userEmail )
	{
		Piwik::checkUserIsSuperUser();
		$this->checkUserEmailExists($userEmail);
		
		$db = Zend_Registry::get('db');
		$user = $db->fetchRow("SELECT * 
								FROM ".Piwik::prefixTable("user")
								." WHERE email = ?", $userEmail);
		return $user;
	}
	
	private function checkLogin($userLogin)
	{
		if($this->userExists($userLogin))
		{
			throw new Exception(Piwik_TranslateException('UsersManager_ExceptionLoginExists', $userLogin));
		}
		
		Piwik::checkValidLoginString($userLogin);
	}
		
	private function checkPassword($password)
	{
		if(!$this->isValidPasswordString($password))
		{
			throw new Exception(Piwik_TranslateException('UsersManager_ExceptionInvalidPassword'));
		}
	}
	
	private function checkEmail($email)
	{
		if($this->userEmailExists($email))
		{
			throw new Exception(Piwik_TranslateException('UsersManager_ExceptionEmailExists', $email));
		}
		
		if(!Piwik::isValidEmailString($email))
		{
			throw new Exception(Piwik_TranslateException('UsersManager_ExceptionInvalidEmail'));
		}
	}
		
	private function getCleanAlias($alias,$userLogin)
	{
		if(empty($alias))
		{
			$alias = $userLogin;
		}
		return $alias;
	}
	
	private function getCleanPassword($password)
	{
		// if change here, should also edit the installation process 
		// to change how the root pwd is saved in the config file
		return md5($password);
	}
		
	/**
	 * Add a user in the database.
	 * A user is defined by 
	 * - a login that has to be unique and valid 
	 * - a password that has to be valid 
	 * - an alias 
	 * - an email that has to be in a correct format
	 * 
	 * @see userExists()
	 * @see isValidLoginString()
	 * @see isValidPasswordString()
	 * @see isValidEmailString()
	 * 
	 * @exception in case of an invalid parameter
	 */
	public function addUser( $userLogin, $password, $email, $alias = false )
	{
		Piwik::checkUserIsSuperUser();
		
		$this->checkLogin($userLogin);
		$this->checkUserIsNotSuperUser($userLogin);
		$this->checkPassword($password);
		$this->checkEmail($email);

		$alias = $this->getCleanAlias($alias,$userLogin);
		$passwordTransformed = $this->getCleanPassword($password);
		
		$token_auth = $this->getTokenAuth($userLogin, $passwordTransformed);
		
		$db = Zend_Registry::get('db');
		
		$db->insert( Piwik::prefixTable("user"), array(
									'login' => $userLogin,
									'password' => $passwordTransformed,
									'alias' => $alias,
									'email' => $email,
									'token_auth' => $token_auth,
									'date_registered' => Piwik_Date::now()->getDatetime()
							)
		);
		
		// we reload the access list which doesn't yet take in consideration this new user
		Zend_Registry::get('access')->reloadAccess();
		
	}
	
	/**
	 * Updates a user in the database. 
	 * Only login and password are required (case when we update the password).
	 * When the password changes, the key token for this user will change, which could break
	 * its API calls.
	 * 
	 * @see addUser() for all the parameters
	 */
	public function updateUser(  $userLogin, $password = false, $email = false, $alias = false )
	{
		Piwik::checkUserIsSuperUserOrTheUser($userLogin);
		$this->checkUserIsNotAnonymous( $userLogin );
		$this->checkUserIsNotSuperUser($userLogin);
		$userInfo = $this->getUser($userLogin);
				
		if(empty($password))
		{
			$password = $userInfo['password'];
		}
		else
		{
			$this->checkPassword($password);
			$password = $this->getCleanPassword($password);
		}

		if(empty($alias))
		{
			$alias = $userInfo['alias'];
		}

		if(empty($email))
		{
			$email = $userInfo['email'];
		}

		if($email != $userInfo['email'])
		{
			$this->checkEmail($email);
		}
		
		$alias = $this->getCleanAlias($alias,$userLogin);
		$token_auth = $this->getTokenAuth($userLogin,$password);
		
		$db = Zend_Registry::get('db');
											
		$db->update( Piwik::prefixTable("user"), 
					array(
						'password' => $password,
						'alias' => $alias,
						'email' => $email,
						'token_auth' => $token_auth,
						),
					"login = '$userLogin'"
			);		
	}
	
	/**
	 * Delete a user and all its access, given its login.
	 * 
	 * @param string the user login.
	 * 
	 * @exception if the user doesn't exist
	 * 
	 * @return bool true on success
	 */
	public function deleteUser( $userLogin )
	{
		Piwik::checkUserIsSuperUser();
		$this->checkUserIsNotAnonymous( $userLogin );
		$this->checkUserIsNotSuperUser($userLogin);
		if(!$this->userExists($userLogin))
		{
			throw new Exception(Piwik_TranslateException("UsersManager_ExceptionDeleteDoesNotExist", $userLogin));
		}
		
		$this->deleteUserOnly( $userLogin );
		$this->deleteUserAccess( $userLogin );
	}
	
	/**
	 * Returns true if the given userLogin is known in the database
	 * 
	 * @return bool true if the user is known
	 */
	public function userExists( $userLogin )
	{
		$count = Piwik_FetchOne("SELECT count(*) 
													FROM ".Piwik::prefixTable("user"). " 
													WHERE login = ?", $userLogin);
		return $count != 0;
	}
	
	/**
	 * Returns true if user with given email (userEmail) is known in the database
	 *
	 * @return bool true if the user is known
	 */
	public function userEmailExists( $userEmail )
	{
		Piwik::checkUserHasSomeAdminAccess();
		$count = Piwik_FetchOne("SELECT count(*) 
													FROM ".Piwik::prefixTable("user"). " 
													WHERE email = ?", $userEmail);
		return $count != 0;	
	}
	
	/**
	 * Set an access level to a given user for a list of websites ID.
	 * 
	 * If access = 'noaccess' the current access (if any) will be deleted.
	 * If access = 'view' or 'admin' the current access level is deleted and updated with the new value.
	 *  
	 * @param string Access to grant. Must have one of the following value : noaccess, view, admin
	 * @param string The user login 
	 * @param int|array The array of idSites on which to apply the access level for the user. 
	 *       If the value is "all" then we apply the access level to all the websites ID for which the current authentificated user has an 'admin' access.
	 * 
	 * @exception if the user doesn't exist
	 * @exception if the access parameter doesn't have a correct value
	 * @exception if any of the given website ID doesn't exist
	 * 
	 * @return bool true on success
	 */
	public function setUserAccess( $userLogin, $access, $idSites)
	{
		$this->checkAccessType( $access );
		$this->checkUserExists( $userLogin);
		$this->checkUserIsNotSuperUser($userLogin);
		
		if($userLogin == 'anonymous'
			&& $access == 'admin')
		{
			throw new Exception(Piwik_TranslateException("UsersManager_ExceptionAdminAnonymous"));
		}
		
		// in case idSites is null we grant access to all the websites on which the current connected user
		// has an 'admin' access
		if($idSites === 'all')
		{
			$idSites = Piwik_SitesManager_API::getInstance()->getSitesIdWithAdminAccess();
		}
		// in case the idSites is an integer we build an array		
		elseif(!is_array($idSites))
		{
			$idSites = Piwik_Site::getIdSitesFromIdSitesString($idSites);
		}
		
		// it is possible to set user access on websites only for the websites admin
		// basically an admin can give the view or the admin access to any user for the websites he manages
		Piwik::checkUserHasAdminAccess( $idSites );
		
		$this->deleteUserAccess( $userLogin, $idSites);
		
		// delete UserAccess
		$db = Zend_Registry::get('db');
		
		// if the access is noaccess then we don't save it as this is the default value
		// when no access are specified
		if($access != 'noaccess')
		{
			foreach($idSites as $idsite)
			{
				$db->insert(	Piwik::prefixTable("access"),
								array(	"idsite" => $idsite, 
										"login" => $userLogin,
										"access" => $access)
						);
			}
		}
		
		// we reload the access list which doesn't yet take in consideration this new user access
		Zend_Registry::get('access')->reloadAccess();
	}
	
	/**
	 * Throws an exception is the user login doesn't exist
	 * 
	 * @param string user login
	 * @exception if the user doesn't exist
	 */
	private function checkUserExists( $userLogin )
	{
		if(!$this->userExists($userLogin))
		{
			throw new Exception(Piwik_TranslateException("UsersManager_ExceptionUserDoesNotExist", $userLogin));
		}
	}
	
	/**
	 * Throws an exception is the user email cannot be found
	 * 
	 * @param string user email
	 * @exception if the user doesn't exist
	 */
	private function checkUserEmailExists( $userEmail )
	{
		if(!$this->userEmailExists($userEmail))
		{
			throw new Exception(Piwik_TranslateException("UsersManager_ExceptionUserDoesNotExist", $userEmail));
		}
	}
	
	private function checkUserIsNotAnonymous( $userLogin )
	{
		if($userLogin == 'anonymous')
		{
			throw new Exception(Piwik_TranslateException("UsersManager_ExceptionEditAnonymous"));
		}
	}
	private function checkUserIsNotSuperUser( $userLogin )
	{
		if($userLogin == Zend_Registry::get('config')->superuser->login)
		{
			throw new Exception(Piwik_TranslateException("UsersManager_ExceptionSuperUser"));
		}
	}
	
	private function checkAccessType($access)
	{
		$accessList = Piwik_Access::getListAccess();
		
		// do not allow to set the superUser access
		unset($accessList[array_search("superuser", $accessList)]);
		
		if(!in_array($access,$accessList))
		{
			throw new Exception(Piwik_TranslateException("UsersManager_ExceptionAccessValues", implode(", ", $accessList)));
		}
	}
	
	/**
	 * Delete a user given its login.
	 * The user's access are not deleted.
	 * 
	 * @param string the user login.
	 *  
	 */
	private function deleteUserOnly( $userLogin )
	{
		$db = Zend_Registry::get('db');
		$db->query("DELETE FROM ".Piwik::prefixTable("user")." WHERE login = ?", $userLogin);

		Piwik_PostEvent('UsersManager.deleteUser', $userLogin);
	}
	
	
	/**
	 * Delete the user access for the given websites.
	 * The array of idsite must be either null OR the values must have been checked before for their validity!
	 * 
	 * @param string the user login
	 * @param array array of idsites on which to delete the access. If null then delete all the access for this user.
	 *  
	 * @return bool true on success
	 */
	private function deleteUserAccess( $userLogin, $idSites = null )
	{
		$db = Zend_Registry::get('db');
		
		if(is_null($idSites))
		{
			$db->query(	"DELETE FROM ".Piwik::prefixTable("access").
						" WHERE login = ?",
					array( $userLogin) );
		}
		else
		{
			foreach($idSites as $idsite)
			{
				$db->query(	"DELETE FROM ".Piwik::prefixTable("access").
							" WHERE idsite = ? AND login = ?",
						array($idsite, $userLogin)
				);
			}
		}
	}
	
	/**
	 * Generates a unique MD5 for the given login & password
	 * 
	 * @param string Login
	 * @param string MD5ied string of the password
	 */
	public function getTokenAuth($userLogin, $md5Password)
	{
		if(strlen($md5Password) != 32) 
		{
			throw new Exception("UsersManager.getTokenAuth is expecting a MD5-hashed password (32 chars long string). 
								Please call the md5() function on the password before calling this method.");
		}
		return md5($userLogin . $md5Password );
	}
	
	/**
	 * Returns true if the password is complex enough (at least 6 characters and max 26 characters)
	 * 
	 * @param string email
	 * @return bool
	 */
	private function isValidPasswordString( $input )
	{		
		$l = strlen($input);
		return $l >= 6 && $l <= 26;
	}
}
