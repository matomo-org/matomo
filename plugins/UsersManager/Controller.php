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
class Piwik_UsersManager_Controller extends Piwik_Controller
{
	/**
	 * The "Manage Users and Permissions" Admin UI screen
	 */
	function index()
	{
		$view = Piwik_View::factory('UsersManager');
		
		$IdSitesAdmin = Piwik_SitesManager_API::getInstance()->getSitesIdWithAdminAccess();
		$idSiteSelected = 1;
		
		if(count($IdSitesAdmin) > 0)
		{
			$defaultWebsiteId = $IdSitesAdmin[0];
			$idSiteSelected = Piwik_Common::getRequestVar('idsite', $defaultWebsiteId);
		}
		
		if($idSiteSelected==='all')
		{
			$usersAccessByWebsite = array();
		}
		else
		{
			$usersAccessByWebsite = Piwik_UsersManager_API::getInstance()->getUsersAccessFromSite( $idSiteSelected );
		}
	
		// requires super user access
		$usersLogin = Piwik_UsersManager_API::getInstance()->getUsersLogin();
		
		// we dont want to display the user currently logged so that the user can't change his settings from admin to view...
		$currentlyLogged = Piwik::getCurrentUserLogin();
		foreach($usersLogin as $login)
		{
			if(!isset($usersAccessByWebsite[$login]))
			{
				$usersAccessByWebsite[$login] = 'noaccess';
			}
		}
		unset($usersAccessByWebsite[$currentlyLogged]);

		ksort($usersAccessByWebsite);
		
		$users = array();
		if(Zend_Registry::get('access')->isSuperUser())
		{
			$users = Piwik_UsersManager_API::getInstance()->getUsers();
		}
		
		$view->idSiteSelected = $idSiteSelected;
		$view->users = $users;
		$view->usersAccessByWebsite = $usersAccessByWebsite;
		$view->formUrl = Piwik_Url::getCurrentUrl();
		$view->websites = Piwik_SitesManager_API::getInstance()->getSitesWithAdminAccess();
		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetAdminMenu();
		echo $view->render();
	}
	
	const DEFAULT_DATE = 'today';
	
	/**
	 * The "User Settings" admin UI screen view
	 */
	public function userSettings()
	{
		$view = Piwik_View::factory('userSettings');
		
		$userLogin = Piwik::getCurrentUserLogin();
		if(Piwik::isUserIsSuperUser())
		{
			$view->userAlias = $userLogin;
			$view->userEmail = Zend_Registry::get('config')->superuser->email;
			if(!Zend_Registry::get('config')->isFileWritable())
			{
				$view->configFileNotWritable = true;
			}
		}
		else
		{
    		$user = Piwik_UsersManager_API::getInstance()->getUser($userLogin);
    		$view->userAlias = $user['alias'];
    		$view->userEmail = $user['email'];
		}
		
		$defaultReport = Piwik_UsersManager_API::getInstance()->getUserPreference($userLogin, Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT);
		if($defaultReport === false)
		{
    		$defaultReport = $this->getDefaultWebsiteId();
		}
		$view->defaultReport = $defaultReport;

		$defaultDate = Piwik_UsersManager_API::getInstance()->getUserPreference($userLogin, Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT_DATE);
		if($defaultDate === false)
		{
			$defaultDate = self::DEFAULT_DATE;
		}
		$view->defaultDate = $defaultDate;
		$view->availableDefaultDates = array(
			'today' => Piwik_Translate('General_Today'),
			'yesterday' => Piwik_Translate('General_Yesterday'),
			'week' => Piwik_Translate('General_CurrentWeek'),
			'month' => Piwik_Translate('General_CurrentMonth'),
			'year' => Piwik_Translate('General_CurrentYear'),
		);
		
		$view->ignoreCookieSet = $this->isIgnoreCookieFound();
		$this->initViewAnonymousUserSettings($view);
		
		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetAdminMenu();
		echo $view->render();
	}
	
	public function setIgnoreCookie()
	{
		Piwik::checkUserHasSomeViewAccess();
		$this->checkTokenInUrl();
		$cookie = $this->getIgnoreCookie();
		if($cookie->isCookieFound())
		{
			$cookie->delete();
		}
		else
		{
			$cookie->save();
		}
		Piwik::redirectToModule('UsersManager', 'userSettings');
	}

	protected function getIgnoreCookie()
	{
		return new Piwik_Cookie(Piwik_Tracker_Visit::COOKIE_IGNORE_VISITS);
	}
	
	protected function isIgnoreCookieFound()
	{
		$cookie = $this->getIgnoreCookie();
		return $cookie->isCookieFound();
	}
	
	/**
	 * The Super User can modify Anonymous user settings
	 * @param $view
	 */
	protected function initViewAnonymousUserSettings($view)
	{
		if(!Piwik::isUserIsSuperUser())
		{
			return;
		}
		$userLogin = 'anonymous';
		
		// Which websites are available to the anonymous users?
		$anonymousSitesAccess = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser($userLogin);
		$anonymousSites = array();
		foreach($anonymousSitesAccess as $info) 
		{
			$idSite = $info['site'];
			$anonymousSites[$idSite] = Piwik_SitesManager_API::getInstance()->getSiteFromId($idSite);
		}
		$view->anonymousSites = $anonymousSites;
		
		// Which report is displayed by default to the anonymous user?
		$anonymousDefaultReport = Piwik_UsersManager_API::getInstance()->getUserPreference($userLogin, Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT);
		if($anonymousDefaultReport === false)
		{
			if(empty($anonymousSites))
			{
				$anonymousDefaultReport = Piwik::getLoginPluginName();
			}
			else
			{
    			// we manually imitate what would happen, in case the anonymous user logs in 
    			// and is redirected to the first website available to him in the list
    			// @see getDefaultWebsiteId()
    			reset($anonymousSites);
    			$anonymousDefaultReport = key($anonymousSites);
			} 
		}
		$view->anonymousDefaultReport = $anonymousDefaultReport;
		
		$anonymousDefaultDate = Piwik_UsersManager_API::getInstance()->getUserPreference($userLogin, Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT_DATE);
		if($anonymousDefaultDate === false)
		{
			$anonymousDefaultDate = self::DEFAULT_DATE;
		}
		$view->anonymousDefaultDate = $anonymousDefaultDate;
	}

	/**
	 * Records settings for the anonymous users (default report, default date)
	 */
	public function recordAnonymousUserSettings()
	{
		$response = new Piwik_API_ResponseBuilder(Piwik_Common::getRequestVar('format'));
		try {
			Piwik::checkUserIsSuperUser();
    		$this->checkTokenInUrl();
    		$anonymousDefaultReport = Piwik_Common::getRequestVar('anonymousDefaultReport');
    		$anonymousDefaultDate = Piwik_Common::getRequestVar('anonymousDefaultDate');
    		$userLogin = 'anonymous';
    		Piwik_UsersManager_API::getInstance()->setUserPreference($userLogin, 
    															Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT, 
    															$anonymousDefaultReport);
    		Piwik_UsersManager_API::getInstance()->setUserPreference($userLogin, 
    															Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT_DATE, 
    															$anonymousDefaultDate);
			$toReturn = $response->getResponse();
		} catch(Exception $e ) {
			$toReturn = $response->getResponseException( $e );
		}
		echo $toReturn;
	}
	
	/**
	 * Records settings from the "User Settings" page
	 */
	public function recordUserSettings()
	{
		$response = new Piwik_API_ResponseBuilder(Piwik_Common::getRequestVar('format'));
		try {
    		$this->checkTokenInUrl();
    		$alias = Piwik_Common::getRequestVar('alias');
    		$email = Piwik_Common::getRequestVar('email');
    		$defaultReport = Piwik_Common::getRequestVar('defaultReport');
    		$defaultDate = Piwik_Common::getRequestVar('defaultDate');

    		$newPassword = false;
    		$password = Piwik_Common::getRequestvar('password', false);
    		$passwordBis = Piwik_Common::getRequestvar('passwordBis', false);
    		if(!empty($password)
    			|| !empty($passwordBis))
			{
				if($password != $passwordBis)
				{
					throw new Exception(Piwik_Translate('Login_PasswordsDoNotMatch'));
				}
				$newPassword = $password;
			}
			
    		$userLogin = Piwik::getCurrentUserLogin();
    		if(Piwik::isUserIsSuperUser())
    		{
    			$superUser = Zend_Registry::get('config')->superuser;
    			$updatedSuperUser = false;
    			if($newPassword !== false)
    			{
    				$md5PasswordSuperUser = md5($newPassword);
    				$superUser->password = $md5PasswordSuperUser;
    				$updatedSuperUser = true;
    			}
    			if($superUser->email != $email)
    			{
    				$superUser->email = $email;
    				$updatedSuperUser = true;
    			}
				if($updatedSuperUser)
				{
    				Zend_Registry::get('config')->superuser = $superUser->toArray();
    			}
    		}
    		else
    		{
    			Piwik_UsersManager_API::getInstance()->updateUser($userLogin, $newPassword, $email, $alias);
    		}
    		
			// logs the user in with the new password
    		if($newPassword !== false)
    		{
        		$info = array(	'login' => $userLogin, 
        						'md5Password' => md5($newPassword),
        		);
        		Piwik_PostEvent('Login.initSession', $info);
    		}
    		
    		Piwik_UsersManager_API::getInstance()->setUserPreference($userLogin, 
    															Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT, 
    															$defaultReport);
    		Piwik_UsersManager_API::getInstance()->setUserPreference($userLogin, 
    															Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT_DATE, 
    															$defaultDate);
    															
			$toReturn = $response->getResponse();
		} catch(Exception $e ) {
			$toReturn = $response->getResponseException( $e );
		}
		echo $toReturn;
	}
	
}
