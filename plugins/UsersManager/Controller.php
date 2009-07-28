<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_UsersManager
 */


/**
 * 
 * @package Piwik_UsersManager
 */
class Piwik_UsersManager_Controller extends Piwik_Controller
{
	function index()
	{
		$view = new Piwik_View('UsersManager/templates/UsersManager.tpl');
		
		$IdSitesAdmin = Piwik_SitesManager_API::getSitesIdWithAdminAccess();
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
			$usersAccessByWebsite = Piwik_UsersManager_API::getUsersAccessFromSite( $idSiteSelected );
		}
	
		// requires super user access
		$usersLogin = Piwik_UsersManager_API::getUsersLogin();
		
		// we dont want to display the user currently logged so that the user can't change his settings from admin to view...
		$currentlyLogged = Piwik::getCurrentUserLogin();
	
		foreach($usersLogin as $login)
		{
			if( $login != $currentlyLogged
				&& !isset($usersAccessByWebsite[$login]))
			{
				$usersAccessByWebsite[$login] = 'noaccess';
			}
		}
		ksort($usersAccessByWebsite);
		
		$users = array();
		if(Zend_Registry::get('access')->isSuperUser())
		{
			$users = Piwik_UsersManager_API::getUsers();
		}
		
		$view->idSiteSelected = $idSiteSelected;
		$view->users = $users;
		$view->usersAccessByWebsite = $usersAccessByWebsite;
		$view->formUrl = Piwik_Url::getCurrentUrl();
		$view->websites = Piwik_SitesManager_API::getSitesWithAdminAccess();
		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetAdminMenu();
		echo $view->render();
	}
}
