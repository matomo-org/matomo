<?php
// Script that creates 100 websites, then outputs a IMG that records a pageview in each website
// Used initially to test how to handle cookies for this use case (see http://dev.piwik.org/trac/ticket/409)
exit;

define('PIWIK_INCLUDE_PATH', '..');
define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
define('PIWIK_ENABLE_SESSION_START', false);
require_once PIWIK_INCLUDE_PATH . "/index.php";
require_once PIWIK_INCLUDE_PATH . "/core/API/Request.php";
require_once PIWIK_INCLUDE_PATH . "/libs/PiwikTracker/PiwikTracker.php";

Piwik_FrontController::getInstance()->init();
Piwik::setUserIsSuperUser();
$count = 100;
for($i = 0; $i <= $count; $i++)
{
	$id = Piwik_SitesManager_API::getInstance()->addSite(Piwik_Common::getRandomString(), 'http://piwik.org');
    $t = new PiwikTracker($id, 'http://localhost/trunk/piwik.php');
    echo $id . " <img width=100 height=10 border=1 src='".$t->getUrlTrackPageView('title') ."'><br/>";
}

