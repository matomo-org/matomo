<?php
class Piwik_SitesManager_Controller extends Piwik_Controller
{
	function index()
	{
		$view = new Piwik_View('SitesManager/templates/SitesManager.tpl');
		
		$sites = Piwik_SitesManager_API::getSitesWithAdminAccess();
		foreach($sites as &$site)
		{
			$site['alias_urls'] = Piwik_SitesManager_API::getSiteUrlsFromId($site['idsite']);
		}
//		var_dump($sites);exit;
		$view->sites = $sites;
		echo $view->render();
	}
}
?>
