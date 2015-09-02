<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager;
use Piwik\Translation\Translator;

class CacheId
{
    public static function languageAware($cacheId)
    {
        /** @var Translator $translator */
        $translator = StaticContainer::get('Piwik\Translation\Translator');

        return $cacheId . '-' . $translator->getCurrentLanguage();
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
