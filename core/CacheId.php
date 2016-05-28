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
}
