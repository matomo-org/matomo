<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Updates
 */
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API;
use Piwik\Tracker\Cache;
use Piwik\Updates;

/**
 * @package Updates
 */
class Piwik_Updates_0_2_34 extends Updates
{
    static function update($schema = 'Myisam')
    {
        // force regeneration of cache files following #648
        Piwik::setUserIsSuperUser();
        $allSiteIds = API::getInstance()->getAllSitesId();
        Cache::regenerateCacheWebsiteAttributes($allSiteIds);
    }
}
