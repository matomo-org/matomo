<?php
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
		
		$login = $this->_identity;
		$token = $this->_credential;
		$rootLogin = Zend_Registry::get('config')->superuser->login;
		$rootPassword = Zend_Registry::get('config')->superuser->password;
		$rootToken = Piwik_UsersManager_API::getTokenAuth($rootLogin,$rootPassword);
		
		if($login == $rootLogin 
			&& $token == $rootToken)
		{
			return new Piwik_Auth_Result(Piwik_Auth::SUCCESS_SUPERUSER_AUTH_CODE, 
										$login, 
										array() // message empty
									);
		}
	
		// if not then we return the result of the database authentification provided by zend
		return parent::authenticate();
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
