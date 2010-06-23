<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_0_2_34 implements Piwik_iUpdate
{
	static function update()
	{
		// force regeneration of cache files following #648
		Piwik::setUserIsSuperUser();
		$allSiteIds = Piwik_SitesManager_API::getAllSitesId();
		Piwik_Common::regenerateCacheWebsiteAttributes($allSiteIds);
	}
}
