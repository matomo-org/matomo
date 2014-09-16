<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SitesManager;

use Piwik\CacheFile;

class SiteUrls
{
    private static $allUrlsCacheKey = 'allSiteUrlsPerSite';

    public static function clearSitesCache()
    {
        self::getCache()->delete(self::$allUrlsCacheKey);
    }

    public function getAllCachedSiteUrls()
    {
        $cache    = $this->getCache();
        $siteUrls = $cache->get(self::$allUrlsCacheKey);

        if (empty($siteUrls)) {
            $siteUrls = $this->getAllSiteUrls();
            $cache->set(self::$allUrlsCacheKey, $siteUrls);
        }

        return $siteUrls;
    }

    public function getAllSiteUrls()
    {
        $model    = new Model();
        $siteIds  = $model->getSitesId();
        $siteUrls = array();

        if (empty($siteIds)) {
            return array();
        }

        foreach ($siteIds as $siteId) {
            $siteId = (int) $siteId;
            $siteUrls[$siteId] = $model->getSiteUrlsFromId($siteId);
        }

        return $siteUrls;
    }

    private static function getCache()
    {
        return new CacheFile('tracker', 1800);
    }
}
