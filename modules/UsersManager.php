<?php
Zend_Loader::loadClass("Piwik_Access");

class Piwik_UsersManager extends Piwik_APIable
{
	static private $instance = null;
	protected function __construct()
	{
		parent::__construct();
	}
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	static public $methodsNotToPublish = array();
	
	/**
	 * Returns the list of all the users login.
	 * 
	 * @return array the list of all the login 
	 */
	static public function getUsers()
	{
		Piwik::checkUserIsSuperUser();
		
		$db = Zend_Registry::get('db');
		$users = $db->fetchAll("SELECT login FROM ".Piwik::prefixTable("user"));
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
	static public function getUsersSitesFromAccess( $access )
	{
		Piwik::checkUserIsSuperUser();
		
		self::checkAccessType($access);
		
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
	static public function getUsersAccessFromSite( $idSite )
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

//  id1 => view, id2 =>admin
//	getSiteAccessFromUser( $userLogin )

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
	static public function getSitesAccessFromUser( $userLogin )
	{
		Piwik::checkUserIsSuperUser();
		
		self::checkUserExists($userLogin);
		
		$db = Zend_Registry::get('db');
		$users = $db->fetchAll("SELECT idsite,access 
								FROM ".Piwik::prefixTable("access")
								." WHERE login = ?", $userLogin);
		$return = array();
		foreach($users as $user)
		{
			$return[$user['idsite']] = $user['access'];
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
	static public function getUser( $login )
	{
		Piwik::checkUserIsSuperUser();
		self::checkUserExists($login);
		
		$db = Zend_Registry::get('db');
		$user = $db->fetchRow("SELECT * 
								FROM ".Piwik::prefixTable("user")
								." WHERE login = ?", $login);
		return $user;
	}
	
	static private function checkLogin($userLogin)
	{
		if(self::userExists($userLogin))
		{
			throw new Exception("Login $userLogin already exists.");
		}
		
		if(!self::isValidLoginString($userLogin))
		{
			throw new Exception("The login must contain only letters, numbers, or the characters '_' or '-' or '.'.");
		}
	}
		
	static private function checkPassword($password)
	{
		if(!self::isValidPasswordString($password))
		{
			throw new Exception("The password must contain at least 6 characters including at least one number.");
		}
	}
	
	static private function checkEmail($email)
	{
		if(!self::isValidEmailString($email))
		{
			throw new Exception("The email doesn't have a valid format.");
		}
	}
		
	static private function getCleanAlias($alias,$userLogin)
	{
		if(is_null($alias)
			|| empty($alias))
		{
			$alias = $userLogin;
		}
		return $alias;
	}
	static private function getCleanPassword($password)
	{
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
	static public function addUser( $userLogin, $password, $email, $alias = null )
	{
		Piwik::checkUserIsSuperUser();
		
		self::checkLogin($userLogin);
		self::checkPassword($password);
		self::checkEmail($email);
		
		$alias = self::getCleanAlias($alias,$userLogin);
		$token_auth = self::getTokenAuth($userLogin,$password);
		$passwordTransformed = self::getCleanPassword($password);
		
		$db = Zend_Registry::get('db');
		
		$db->insert( Piwik::prefixTable("user"), array(
									'login' => $userLogin,
									'password' => $passwordTransformed,
									'alias' => $alias,
									'email' => $email,
									'token_auth' => $token_auth,
									)
		);
		
	}
	
	/**
	 * Updates a user in the database. 
	 * Only login and password are required (case when we update the password).
	 * When the password changes, the key token for this user will change, which could break
	 * its API calls.
	 * 
	 * @see addUser() for all the parameters
	 */
	static public function updateUser(  $userLogin, $password, $email = null, $alias = null )
	{
		Piwik::checkUserIsSuperUserOrTheUser($userLogin);
		
		$userInfo = self::getUser($userLogin);
				
		if(is_null($alias))
		{
			$alias = $userInfo['alias'];
		}
		if(is_null($email))
		{
			$email = $userInfo['email'];
		}
		
		self::checkPassword($password);
		self::checkEmail($email);
		
		$alias = self::getCleanAlias($alias,$userLogin);
		$token_auth = self::getTokenAuth($userLogin,$password);
		$passwordTransformed = self::getCleanPassword($password);
		
		$db = Zend_Registry::get('db');
											
		$db->update( Piwik::prefixTable("user"), 
					array(
						'password' => $passwordTransformed,
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
	static public function deleteUser( $userLogin )
	{
		Piwik::checkUserIsSuperUser();
		
		if(!self::userExists($userLogin))
		{
			throw new Exception("User $userLogin doesn't exist therefore it can't be deleted.");
		}
		self::deleteUserOnly( $userLogin );
		self::deleteUserAccess( $userLogin );
	}
	
	/**
	 * Returns true if the given userLogin is known in the database
	 * 
	 * @return bool true if the user is known
	 */
	static public function userExists( $userLogin )
	{
		Piwik::checkUserHasSomeAdminAccess();	
		$count = Zend_Registry::get('db')->fetchOne("SELECT count(*) 
													FROM ".Piwik::prefixTable("user"). " 
													WHERE login = ?", $userLogin);
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
	 * @param int|array The array of idSites on which to apply the access level for the user. If the parameter is null then we apply the access level to all the websites ID for which the current authentificated user has an 'admin' access.
	 * 
	 * @exception if the user doesn't exist
	 * @exception if the access parameter doesn't have a correct value
	 * @exception if any of the given website ID doesn't exist
	 * 
	 * @return bool true on success
	 */
	static public function setUserAccess( $userLogin, $access, $idSites = null)
	{
		self::checkAccessType( $access );
		self::checkUserExists( $userLogin);
		
		// in case idSites is null we grant access to all the websites on which the current connected user
		// has an 'admin' access
		if(is_null($idSites))
		{
			$idSites = Piwik_SitesManager::getSitesIdWithAdminAccess();
		}
		// in case the idSites is an integer we build an array		
		elseif(!is_array($idSites))
		{
			$idSites = array($idSites);
		}
		
		// it is possible to set user access on websites only for the websites admin
		// basically an admin can give the view or the admin access to any user for the websites he manages
		Piwik::checkUserHasAdminAccess( $idSites );
		
		self::deleteUserAccess( $userLogin, $idSites);
		
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
	}
	
	/**
	 * Throws an exception is the user login doesn't exist
	 * 
	 * @param string user login
	 * @exception if the user doesn't exist
	 */
	static private function checkUserExists( $userLogin )
	{
		if(!self::userExists($userLogin))
		{
			throw new Exception("User '$userLogin' doesn't exist.");
		}
	}
	
	
	static private function checkAccessType($access)
	{
		$accessList = Piwik_Access::getListAccess();
		
		// do not allow to set the superUser access
		unset($accessList[array_search("superuser", $accessList)]);
		
		if(!in_array($access,$accessList))
		{
			throw new Exception("The parameter access must have one of the following values : [ ". implode(", ", $accessList)." ]");
		}
	}
	
	/**
	 * Delete a user given its login.
	 * The user's access are not deleted.
	 * 
	 * @param string the user login.
	 *  
	 */
	static private function deleteUserOnly( $userLogin )
	{
		$db = Zend_Registry::get('db');
		$db->query("DELETE FROM ".Piwik::prefixTable("user")." WHERE login = ?", $userLogin);
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
	static private function deleteUserAccess( $userLogin, $idSites = null )
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
	 * @param string login
	 * @param string password
	 */
	static private function getTokenAuth($userLogin, $password)
	{
		return md5($userLogin . $password );
		
	}
	
	/**
	 * Returns true if the email is a valid email
	 * 
	 * @param string email
	 * @return bool
	 */
    static private function isValidEmailString( $email ) 
    {
		return (preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]{2,4}$/', $email) > 0);
    }
	
	/**
	 * Returns true if the login has a valid format : 
	 * - only A-Z a-z and the characters _ . and -
	 * - length between 3 and 26
	 * 
	 * @param string login
	 * @return bool
	 */
	static private function isValidLoginString( $input )
	{
		$l = strlen($input);
		return $l >= 3 
				&& $l <= 26 
				&& (preg_match('/^[A-Za-z0-9\_\.-]*$/', $input) > 0);
	}
	
	/**
	 * Returns true if the password is complex enough (at least 6 characters and max 26 characters)
	 * 
	 * @param string email
	 * @return bool
	 */
	static private function isValidPasswordString( $input )
	{		
		$l = strlen($input);
		return $l >= 6 && $l <= 26;
	}

}