<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Openads
 */


/**
 * 
 * @package Piwik_Openads
 */
class Piwik_Openads_Controller extends Piwik_Installation_Controller
{
	function __construct()
	{
		parent::__construct();
	}
	function openadsIntegration()
	{
		
		$view = new Piwik_Install_View(
						'Openads/templates/openadsIntegration.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$view->showNextStep = true;
	
		$superUserInfos = array(
			'login' 		=> 'openads_root',
			'password' 		=> 'openads_pwd',
			'email' 		=> 'openads_email',
		);
		
		$_SESSION['superuser_infos'] = $superUserInfos;
		
		// we load the DB/config/etc. in order to call the API
		$this->initObjectsToCallAPI();
		
		// we load the websites from opeands and add them to piwik
		$sitesToAdd = array();		
		foreach($sitesToAdd as $site)
		{
			$request = new Piwik_API_Request("
							method=SitesManager.addSite
							&name=$name
							&urls=$url
							&format=original
						");
		}
		
		
		// we load the users from opeands and add them to piwik
		$usersToAdd = array();		
		foreach($usersToAdd as $user)
		{
			$userLogin = $user['login'];
			$userLogin = $user['login'];
			$password = 'fake password because it is not necessary the auth will not be used.';
			
			$email = '';
			$alias = '';
			//addUser( $userLogin, $password, $email, $alias = false )
			$request = new Piwik_API_Request("
							method=UsersManager.addUser
							&userLogin=$userLogin
							&password=$password
							&email=$email
							&alias=$alias
							&format=original
						");
		}
		
		
		$_SESSION['currentStepDone'] = __FUNCTION__;		
		echo $view->render();
	}
}