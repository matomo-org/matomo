<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicePlugins;

use Piwik\Cache;
use Piwik\CacheId;
use Piwik\Piwik;
use Piwik\Plugin;

/**
 *
 */
class DevicePlugins extends \Piwik\Plugin
{
    /**
     * @see Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Metrics.getDefaultMetricTranslations' => 'addMetricTranslations',
        );
    }

    public function addMetricTranslations(&$translations)
    {
        $metrics = array(
            'nb_visits_percentage' => Piwik::translate('General_ColumnPercentageVisits')
        );

        $translations = array_merge($translations, $metrics);
    }


    /**
     * Returns all available DevicePlugins Columns
     *
     * @return Columns\DevicePluginColumn[]
     * @throws \Exception
     */
    public static function getAllPluginColumns()
    {
        $cacheId = CacheId::pluginAware('DevicePluginColumns');
        $cache   = Cache::getTransientCache();

        if (!$cache->contains($cacheId)) {
            $instances = [];

            foreach (self::getAllDevicePluginsColumnClasses() as $className) {
                $instance = new $className();
                $instances[] = $instance;
            }
            $cache->save($cacheId, $instances);
        }

        return $cache->fetch($cacheId);
    }

    /**
     * Returns class names of all DevicePlugins Column classes.
     *
     * @return string[]
     * @api
     */
    protected static function getAllDevicePluginsColumnClasses()
    {
        return Plugin\Manager::getInstance()->findMultipleComponents('Columns', 'Piwik\Plugins\DevicePlugins\Columns\DevicePluginColumn');
    }
}
