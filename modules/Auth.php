<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

/**
 * 
 * @package Piwik
 */
class Piwik_Auth extends Zend_Auth_Adapter_DbTable
{
	const SUCCESS_SUPERUSER_AUTH_CODE = 42;
	
	public function __construct()
	{
		$db = Zend_Registry::get('db');
		parent::__construct($db);
	}
	
	public function authenticate()
	{
		// we first try if the user is the super user
		$rootLogin = Zend_Registry::get('config')->superuser->login;
		$rootPassword = Zend_Registry::get('config')->superuser->password;
		$rootToken = Piwik_UsersManager_API::getTokenAuth($rootLogin,$rootPassword);
		
//		echo $rootToken;
//		echo "<br>". $this->_credential;exit;
		if($this->_identity == $rootLogin 
			&& $this->_credential == $rootToken)
		{
			return new Piwik_Auth_Result(Piwik_Auth::SUCCESS_SUPERUSER_AUTH_CODE, 
										$this->_identity, 
										array() // message empty
									);
		}
	
		// we then look if the user is API authenticated
		// API authentication works without login name, but only with the token
		if(is_null($this->_identity))
		{
			$authenticated = false;
			
			if($this->_credential === $rootToken)
			{
				return new Piwik_Auth_Result(Piwik_Auth::SUCCESS_SUPERUSER_AUTH_CODE, 
										$rootLogin, 
										array() // message empty
									);
			}
			
			$login = Zend_Registry::get('db')->fetchOne(
						'SELECT login FROM '.Piwik::prefixTable('user').' WHERE token_auth = ?',
						array($this->_credential)
						);
			if($login !== false)
			{
				return new Piwik_Auth_Result(Zend_Auth_Result::SUCCESS, 
										$login, 
										array() // message empty
									);
			}
			else
			{
				return new Piwik_Auth_Result( Zend_Auth_Result::FAILURE,
            							$this->_identity,
            							array()
						            );
			}
		}
	
		// if not then we return the result of the database authentification provided by zend
		return parent::authenticate();
	}
	
	public function getTokenAuth()
	{
		return $this->_credential;
	}
}



/**
 * 
 * @package Piwik
 */
class Piwik_Auth_Result extends Zend_Auth_Result
{
	public function __construct($code, $identity, array $messages = array())
    {
        $this->_code     = (int)$code;
        $this->_identity = $identity;
        $this->_messages = $messages;
    }
}
