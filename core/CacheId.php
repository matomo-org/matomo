<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager;

class CacheId
{
    public static function languageAware($cacheId)
    {
        return $cacheId . '-' . StaticContainer::get('Piwik\Translation\Translator')->getCurrentLanguage();
    }

    public static function pluginAware($cacheId)
    {
        $pluginManager = Manager::getInstance();
        $pluginNames   = $pluginManager->getLoadedPluginsName();
        $cacheId       = $cacheId . '-' . md5(implode('', $pluginNames));
        $cacheId       = self::languageAware($cacheId);

        return $cacheId;
    }

    public static function siteAware($cacheId, ?array $idSites = null)
    {
        if ($idSites === null) {
            $idSites = self::getIdSiteList('idSite');
            $cacheId .= self::idSiteListCacheKey($idSites);

            $idSites = self::getIdSiteList('idSites');
            $cacheId .= self::idSiteListCacheKey($idSites);

            $idSites = self::getIdSiteList('idsite'); // tracker param
            $cacheId .= self::idSiteListCacheKey($idSites);
        } else {
            $cacheId .= self::idSiteListCacheKey($idSites);
        }

        return $cacheId;
    }

    private static function getIdSiteList($queryParamName)
    {
        if (
            empty($_GET[$queryParamName])
            && empty($_POST[$queryParamName])
        ) {
            return [];
        }

        $idSiteGetParam = [];
        if (!empty($_GET[$queryParamName])) {
            $value = $_GET[$queryParamName];
            $idSiteGetParam = is_array($value) ? $value : explode(',', $value);
        }

        $idSitePostParam = [];
        if (!empty($_POST[$queryParamName])) {
            $value = $_POST[$queryParamName];
            $idSitePostParam = is_array($value) ? $value : explode(',', $value);
        }

        $idSiteList = array_merge($idSiteGetParam, $idSitePostParam);
        $idSiteList = array_map('intval', $idSiteList);
        $idSiteList = array_unique($idSiteList);
        sort($idSiteList);
        return $idSiteList;
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
