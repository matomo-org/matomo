<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Plugin\Manager;

class CacheId
{
    public static function languageAware($cacheId)
    {
        return $cacheId . '-' . Translate::getLanguageLoaded();
    }

    public static function pluginAware($cacheId)
    {
        $pluginManager = Manager::getInstance();
        $pluginNames   = $pluginManager->getLoadedPluginsName();
        $cacheId       = $cacheId . '-' . md5(implode('', $pluginNames));
        $cacheId       = self::languageAware($cacheId);

        return $cacheId;
    }

    public static function siteAware($cacheId, array $idSites = null)
    {
        if ($idSites === null) {
            $idSites = self::getIdSiteList('idSite');
            $cacheId .= self::idSiteListCacheKey($idSites);

            $idSites = self::getIdSiteList('idSites');
            $cacheId .= self::idSiteListCacheKey($idSites);
        } else {
            $cacheId .= self::idSiteListCacheKey($idSites);
        }

        return $cacheId;
    }

    private static function getIdSiteList($queryParamName)
    {
        $idSiteParam = Common::getRequestVar($queryParamName, false);
        if ($idSiteParam === false) {
            return [];
        }

        $idSiteParam = explode(',', $idSiteParam);
        $idSiteParam = array_map('intval', $idSiteParam);
        $idSiteParam = array_unique($idSiteParam);
        sort($idSiteParam);
        return $idSiteParam;
    }

    private static function idSiteListCacheKey($idSites)
    {
        if (empty($idSites)) {
            return '';
        }

        if (count($idSites) <= 5) {
            return '-' . implode('_', $idSites); // we keep the cache key readable when possible
        } else {
            return '-' . md5(implode('_', $idSites)); // we need to shorten it
        }
    }
}
