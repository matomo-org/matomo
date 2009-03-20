<?php
require_once "SitesManager/API.php";
Piwik::setUserIsSuperUser();
$allSiteIds = Piwik_SitesManager_API::getAllSitesId();
Piwik_Common::regenerateCacheWebsiteAttributes($allSiteIds);
