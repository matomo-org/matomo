<?php
/**
 * @package Piwik
 */
class Piwik_Login_Auth implements Piwik_Auth
{
	protected $login = null;
	protected $token_auth = null;
	
	public function authenticate()
	{
		$rootLogin = Zend_Registry::get('config')->superuser->login;
		$rootPassword = Zend_Registry::get('config')->superuser->password;
		$rootToken = Piwik_UsersManager_API::getTokenAuth($rootLogin, $rootPassword);

		if($this->login == $rootLogin
			&& $this->token_auth == $rootToken)
		{
			return new Piwik_Auth_Result(Piwik_Auth_Result::SUCCESS_SUPERUSER_AUTH_CODE, $this->login, $this->token_auth );
		}

		if($this->token_auth === $rootToken)
		{
			return new Piwik_Auth_Result(Piwik_Auth_Result::SUCCESS_SUPERUSER_AUTH_CODE, $rootLogin, $rootToken );
		}

		$login = Zend_Registry::get('db')->fetchOne(
					'SELECT login FROM '.Piwik::prefixTable('user').' WHERE token_auth = ?',
					array($this->token_auth)
		);
		if($login !== false)
		{
			if(is_null($this->login)
				|| $this->login == $login)
			{
				return new Piwik_Auth_Result(Piwik_Auth_Result::SUCCESS, $login, $this->token_auth );
			}
		}
		return new Piwik_Auth_Result( Piwik_Auth_Result::FAILURE, $this->login, $this->token_auth );
	}

	public function setLogin($login)
	{
		$this->login = $login;
	}
	
	public function setTokenAuth($token_auth)
	{
		$this->token_auth = $token_auth;
	}
}
