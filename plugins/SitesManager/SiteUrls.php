<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SitesManager;

use Piwik\Cache;

class SiteUrls
{
    private static $cacheId = 'allSiteUrlsPerSite';

    public static function clearSitesCache()
    {
        self::getCache()->delete(self::$cacheId);
    }

    public function getAllCachedSiteUrls()
    {
        $cache    = $this->getCache();
        $siteUrls = $cache->fetch(self::$cacheId);

        if (empty($siteUrls)) {
            $siteUrls = $this->getAllSiteUrls();
            $cache->save(self::$cacheId, $siteUrls, 1800);
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
        return Cache::getLazyCache();
    }
}
