<?php
require_once "SitesManager/API.php";

// alter table to set the utf8 collation
$tablesToAlter = Piwik::getTablesInstalled(true);
foreach($tablesToAlter as $table) {
	Piwik_Query("ALTER TABLE `". $table . "` 
				 CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci ");
}
// force regeneration of cache files as we add 'hosts' entry in it
Piwik::setUserIsSuperUser();
$allSiteIds = Piwik_SitesManager_API::getAllSitesId();
Piwik_Common::regenerateCacheWebsiteAttributes($allSiteIds);

