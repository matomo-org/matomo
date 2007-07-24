<?php
//	
//	getSiteFromId( id )
//	getSiteFromUrl( mainUrl )
//	getSites( accessType )
//	getNumberOfSites()
//	getNumberOfSitesWithAdminAccess()
Zend_Loader::loadClass("Piwik_Access");


class Piwik_UsersManager extends Piwik_APIable
{
//	
//	getUsersExtended()
//	
//	getUserFromLogin( login )
//	getUserFromEmail( email )
//
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
	
	/**
	 * Returns the list of all the users login.
	 * @return array the list of all the login 
	 */
	static public function getUsers()
	{
		$db = Zend_Registry::get('db');
		$prefix = Zend_Registry::get("tablesPrefix");
		$users = $db->fetchCol("SELECT login FROM ".Piwik::prefixTable("user"));
		return $users;
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
	 * @exception in case of an invalid parameter
	 * @return bool true on success
	 */
	static public function addUser( $userLogin, $password, $alias, $email )
	{
		if(self::userExists($userLogin))
		{
			throw new Exception("Login $login already exists.");
		}
		if(!self::isValidLoginString($userLogin))
		{
			throw new Exception("The login must contain only letters, numbers, or the characters '_' or '-' or '.'.");
		}
		if(!self::isValidPasswordString($password))
		{
			throw new Exception("The password must contain at least 6 characters including at least one number.");
		}
		if(!self::isValidEmailString($email))
		{
			throw new Exception("The email doesn't have a valid format.");
		}
		
		$db = Zend_Registry::get('db');
		
		$db->insert( Piwik::prefixTable("user"), array(
									'login' => $userLogin,
									'password' => md5($password),
									'alias' => $alias,
									'email' => $email,
									'token_auth' => self::getTokenAuth($userLogin,$password)
									)
		);
		return true;
		
	}
	
	/**
	 * Delete a user given its login
	 * 
	 * @param string the user login.
	 * 
	 * @exception if the user doesn't exist
	 * 
	 * @return bool true on success
	 */
	static public function deleteUser( $userLogin )
	{
		if(!self::userExists($userLogin))
		{
			throw new Exception("User $userLogin doesn't exist therefore it can't be deleted.");
		}
		
		$db = Zend_Registry::get('db');
		$db->query("DELETE FROM ".Piwik::prefixTable("user")." WHERE login = ?", $userLogin);
		
	}
	
	static public function userExists( $userLogin )
	{
		$aLogins = self::getUsers();
		return in_array($userLogin, $aLogins);
	}
	
	// role = noaccess / view / admin / superuser
	static public function setUserRole( $role, $userLogin, $idSites = null)
	{
		$roles = Piwik_Access::getListRoles();
		// do not allow to set the superUser role
		unset($roles[array_search("superuser", $roles)]);
		
		if(!in_array($role,$roles))
		{
			throw new Exception("The parameter role must have one of the following values : [ ". implode(", ", $roles)." ]");
		}
		if(!self::userExists($userLogin))
		{
			throw new Exception("User '$userLogin' doesn't exist.");
		}
		
		if(is_null($idSites))
		{
			$idSites = Piwik_SitesManager::getSitesIdWithAdminAccess();
		}
		elseif(!is_array($idSites))
		{
			$idSites = array($idSites);
		}
		
		foreach($idSites as $idsite)
		{		
			if(	!is_null($idsite)
				&& !Piwik_SitesManager::siteExists($idsite))
			{
				throw new Exception("Site id = $idsite doesn't exist.");
			}
		}
		
		// delete UserRole
		$db = Zend_Registry::get('db');
		
		foreach($idSites as $idsite)
		{
			$db->query(	"DELETE FROM ".Piwik::prefixTable("role").
							" WHERE idsite = ? AND login = ?",
						array($idsite, $userLogin)
					);
		}
		
		// if the role is noaccess then we don't save it as this is the default value
		// when no role are specified
		if($role != 'noaccess')
		{
			foreach($idSites as $idsite)
			{
				$db->insert(	Piwik::prefixTable("role"),
								array(	"idsite" => $idsite, 
										"login" => $userLogin,
										"role" => $role)
						);
			}
		}
		
	}
	
	static private function getTokenAuth($userLogin, $password)
	{
		return md5($userLogin . $password . time());
		
	}
    static private function isValidEmailString( $email ) 
    {
		return (preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]{2,4}$/', $email) > 0);
    }
	
	static private function isValidLoginString( $input )
	{
		return preg_match('/^[A-Za-z0-9\_\.-]*$/', $input) > 0;
	}
	
	static private function isValidPasswordString( $input )
	{
		$isNumeric = false;
		
		$l = strlen($input);
		if( $l < 6)
		{
			return false;
		}
		
		for($i = 0; $i < $l ; $i++)
		{
			if(is_numeric($input[$i]))
			{
				$isNumeric=true;
			}
		}
		return $isNumeric;
	}

}