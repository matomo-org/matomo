<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

class Piwik_Logout_Controller extends Piwik_Controller
{
	function index()
	{		
		$authCookieName = 'piwik-auth';
		$cookie = new Piwik_Cookie($authCookieName);
		$cookie->delete();
		
		$baseUrl = Piwik_Url::getCurrentUrlWithoutQueryString();
	
		Piwik_Url::redirectToUrl($baseUrl);
	}
}
