<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicePlugins;

use Piwik\Cache;
use Piwik\CacheId;
use Piwik\Columns\Dimension;
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
        $removedDimensions = Dimension::getRemovedDimensions();

        if (!$cache->contains($cacheId)) {
            $instances = [];

            foreach (self::getAllDevicePluginsColumnClasses() as $className) {
                if (!in_array($className, $removedDimensions)) {
                    $instances[] = new $className();
                }
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
