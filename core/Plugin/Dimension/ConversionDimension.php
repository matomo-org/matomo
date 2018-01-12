<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin\Dimension;

use Piwik\CacheId;
use Piwik\Cache as PiwikCache;
use Piwik\Columns\Dimension;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Tracker\Action;
use Piwik\Tracker\GoalManager;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Plugin;

/**
 * Defines a new conversion dimension that records any visit related information during tracking.
 *
 * You can record any visit information by implementing one of the following events:
 * {@link onEcommerceOrderConversion()}, {@link onEcommerceCartUpdateConversion()} or {@link onGoalConversion()}.
 * By defining a {@link $columnName} and {@link $columnType} a new column will be created in the database
 * (table `log_conversion`) automatically and the values you return in the previous mentioned events will be saved in
 * this column.
 *
 * You can create a new dimension using the console command `./console generate:dimension`.
 *
 * @api
 * @since 2.5.0
 */
abstract class ConversionDimension extends Dimension
{
    const INSTALLER_PREFIX = 'log_conversion.';

    protected $dbTableName = 'log_conversion';
    protected $category = 'Goals_Conversion';

    /**
     * Get all conversion dimensions that are defined by all activated plugins.
     * @ignore
     */
    public static function getAllDimensions()
    {
        $cacheId = CacheId::pluginAware('ConversionDimensions');
        $cache   = PiwikCache::getTransientCache();

        if (!$cache->contains($cacheId)) {
            $plugins   = PluginManager::getInstance()->getPluginsLoadedAndActivated();
            $instances = array();

            foreach ($plugins as $plugin) {
                foreach (self::getDimensions($plugin) as $instance) {
                    $instances[] = $instance;
                }
            }

            $cache->save($cacheId, $instances);
        }

        return $cache->fetch($cacheId);
    }

    /**
     * Get all conversion dimensions that are defined by the given plugin.
     * @param Plugin $plugin
     * @return ConversionDimension[]
     * @ignore
     */
    public static function getDimensions(Plugin $plugin)
    {
        $dimensions = $plugin->findMultipleComponents('Columns', '\\Piwik\\Plugin\\Dimension\\ConversionDimension');
        $instances  = array();

        foreach ($dimensions as $dimension) {
            $instances[] = new $dimension();
        }

        return $instances;
    }

    /**
     * This event is triggered when an ecommerce order is converted. Any returned value will be persist in the database.
     * Return boolean `false` if you do not want to change the value in some cases.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @param GoalManager $goalManager
     *
     * @return mixed|false
     * @api
     */
    public function onEcommerceOrderConversion(Request $request, Visitor $visitor, $action, GoalManager $goalManager)
    {
        return false;
    }

    /**
     * This event is triggered when an ecommerce cart update is converted. Any returned value will be persist in the
     * database. Return boolean `false` if you do not want to change the value in some cases.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @param GoalManager $goalManager
     *
     * @return mixed|false
     * @api
     */
    public function onEcommerceCartUpdateConversion(Request $request, Visitor $visitor, $action, GoalManager $goalManager)
    {
        return false;
    }

    /**
     * This event is triggered when an any custom goal is converted. Any returned value will be persist in the
     * database. Return boolean `false` if you do not want to change the value in some cases.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @param GoalManager $goalManager
     *
     * @return mixed|false
     * @api
     */
    public function onGoalConversion(Request $request, Visitor $visitor, $action, GoalManager $goalManager)
    {
        return false;
    }
}
