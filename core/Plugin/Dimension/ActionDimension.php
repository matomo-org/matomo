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
use Piwik\Plugin;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Exception;

/**
 * Defines a new action dimension that records any information during tracking for each action.
 *
 * You can record any action information by implementing one of the following events: {@link onLookupAction()} and
 * {@link getActionId()} or {@link onNewAction()}. By defining a {@link $columnName} and {@link $columnType} a new
 * column will be created in the database (table `log_link_visit_action`) automatically and the values you return in
 * the previous mentioned events will be saved in this column.
 *
 * You can create a new dimension using the console command `./console generate:dimension`.
 *
 * @api
 * @since 2.5.0
 */
abstract class ActionDimension extends Dimension
{
    const INSTALLER_PREFIX = 'log_link_visit_action.';

    protected $dbTableName = 'log_link_visit_action';
    protected $category = 'General_Actions';

    /**
     * If the value you want to save for your dimension is something like a page title or page url, you usually do not
     * want to save the raw value over and over again to save bytes in the database. Instead you want to save each value
     * once in the log_action table and refer to this value by its ID in the log_link_visit_action table. You can do
     * this by returning an action id in "getActionId()" and by returning a value here. If a value should be ignored
     * or not persisted just return boolean false. Please note if you return a value here and you implement the event
     * "onNewAction" the value will be probably overwritten by the other event. So make sure to implement only one of
     * those.
     *
     * @param Request $request
     * @param Action $action
     *
     * @return false|mixed
     * @api
     */
    public function onLookupAction(Request $request, Action $action)
    {
        return false;
    }

    /**
     * An action id. The value returned by the lookup action will be associated with this id in the log_action table.
     * @return int
     * @throws Exception in case not implemented
     */
    public function getActionId()
    {
        throw new Exception('You need to overwrite the getActionId method in case you implement the onLookupAction method in class: ' . get_class($this));
    }

    /**
     * This event is triggered before a new action is logged to the `log_link_visit_action` table. It overwrites any
     * looked up action so it makes usually no sense to implement both methods but it sometimes does. You can assign
     * any value to the column or return boolan false in case you do not want to save any value.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action $action
     *
     * @return mixed|false
     * @api
     */
    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        return false;
    }

    /**
     * Get all action dimensions that are defined by all activated plugins.
     * @return ActionDimension[]
     * @ignore
     */
    public static function getAllDimensions()
    {
        $cacheId = CacheId::pluginAware('ActionDimensions');
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
     * Get all action dimensions that are defined by the given plugin.
     * @param Plugin $plugin
     * @return ActionDimension[]
     * @ignore
     */
    public static function getDimensions(Plugin $plugin)
    {
        $dimensions = $plugin->findMultipleComponents('Columns', '\\Piwik\\Plugin\\Dimension\\ActionDimension');
        $instances  = array();

        foreach ($dimensions as $dimension) {
            $instances[] = new $dimension();
        }

        return $instances;
    }
}
