<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_CoreHome
 * 
 */

/**
 * @package Piwik_Dashboard
 */
class Piwik_LanguagesManager_Controller extends Piwik_Controller
{
	/**
	 * anonymous = in the session
	 * authenticated user = in the session and in DB
	 */
	public function saveLanguage()
	{
		$language = Piwik_Common::getRequestVar('language');
		$currentUser = Piwik::getCurrentUserLogin();
		$session = new Zend_Session_Namespace("LanguagesManager");
		$session->language = $language;
		if($currentUser !== 'anonymous')
		{
			Piwik_LanguagesManager_API::setLanguageForUser($currentUser, $language);
		}
		Piwik_Url::redirectToReferer();
	}
	
}
