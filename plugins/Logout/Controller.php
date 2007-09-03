<?php
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
?>
