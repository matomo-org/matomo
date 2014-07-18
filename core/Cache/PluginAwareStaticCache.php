<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Cache;

use Piwik\Plugin\Manager as PluginManager;
use Piwik\Translate;

/**
 * Caching class used for static caching which is plugin aware. It'll cache the given content depending on the plugins
 * that are installed. This prevents you from having to invalidate the cache during tests in case the loaded plugins
 * changes etc. The key is language aware as well.
 *
 * TODO convert this to a decorator... see {@link StaticCache}
 */
class PluginAwareStaticCache extends StaticCache
{
    protected function completeKey($cacheKey)
    {
        $pluginManager = PluginManager::getInstance();
        $pluginNames   = $pluginManager->getLoadedPluginsName();
        $cacheKey      = $cacheKey . md5(implode('', $pluginNames)) . Translate::getLanguageLoaded();

        return $cacheKey;
    }
}
