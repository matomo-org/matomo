<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Filesystem;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API;
use Piwik\Tracker\Cache;
use Piwik\Updates;

/**
 */
class Updates_0_6_2 extends Updates
{
    static function update()
    {
        $obsoleteFiles = array(
            PIWIK_INCLUDE_PATH . '/core/Db/Mysqli.php',
        );
        foreach ($obsoleteFiles as $obsoleteFile) {
            if (file_exists($obsoleteFile)) {
                @unlink($obsoleteFile);
            }
        }

        $obsoleteDirectories = array(
            PIWIK_INCLUDE_PATH . '/core/Db/Pdo',
        );
        foreach ($obsoleteDirectories as $dir) {
            if (file_exists($dir)) {
                Filesystem::unlinkRecursive($dir, true);
            }
        }

        // force regeneration of cache files
        Piwik::setUserHasSuperUserAccess();
        $allSiteIds = API::getInstance()->getAllSitesId();
        Cache::regenerateCacheWebsiteAttributes($allSiteIds);
    }
}
