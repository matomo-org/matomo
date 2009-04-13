<?php

// force regeneration of cache files following #648
Piwik::setUserIsSuperUser();
$allSiteIds = Piwik_SitesManager_API::getAllSitesId();
Piwik_Common::regenerateCacheWebsiteAttributes($allSiteIds);

