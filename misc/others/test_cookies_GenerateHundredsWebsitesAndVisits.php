<?php
// Script that creates 100 websites, then outputs a IMG that records a pageview in each website
// Used initially to test how to handle cookies for this use case (see https://github.com/piwik/piwik/issues/409)
use Piwik\Common;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API;

exit;

define('PIWIK_INCLUDE_PATH', '../..');
define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
define('PIWIK_ENABLE_SESSION_START', false);
require_once PIWIK_INCLUDE_PATH . "/index.php";
require_once PIWIK_INCLUDE_PATH . "/core/API/Request.php";
require_once PIWIK_INCLUDE_PATH . "/libs/PiwikTracker/PiwikTracker.php";

FrontController::getInstance()->init();
Piwik::setUserHasSuperUserAccess();
$count = 100;
for ($i = 0; $i <= $count; $i++) {
    $id = API::getInstance()->addSite(Common::getRandomString(), 'http://piwik.org');
    $t = new PiwikTracker($id, 'http://localhost/trunk/piwik.php');
    echo $id . " <img width=100 height=10 border=1 src='" . $t->getUrlTrackPageView('title') . "'><br/>";
}

