<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Cache\PluginAwareStaticCache;
use Piwik\Columns\Dimension;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Common;
use Piwik\Db;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Translate;

/**
 * @api
 * @since 2.5.0
 */
abstract class ActionDimension extends Dimension
{
    public function install($actionColumns)
    {
        if (empty($this->columnName)) {
            return array();
        }

        $columnExists = in_array($this->columnName, $actionColumns);

        if (!empty($this->columnType) && !$columnExists) {
            return array(
                Common::prefixTable("log_link_visit_action") => array("ADD COLUMN `$this->columnName` $this->columnType")
            );
        }

        return array();
    }

    public function uninstall($actionColumns)
    {
        if (!empty($this->columnName)
            && !empty($this->columnType)
            && in_array($this->getColumnName(), $actionColumns)) {

            return array(
                Common::prefixTable("log_link_visit_action") => array("DROP COLUMN `$this->columnName`")
            );
        }

        return array();
    }

    /**
     * @return string|int
     * @throws \Exception in case not implemented
     */
    public function getActionId()
    {
        throw new \Exception('You need to overwrite the getActionId method in case you implement the onLookupAction method in class: ' . get_class($this));
    }

    protected function addSegment(Segment $segment)
    {
        $sqlSegment = $segment->getSqlSegment();
        if (!empty($this->columnName) && empty($sqlSegment)) {
            $segment->setSqlSegment('log_link_visit_action.' . $this->columnName);
        }

        parent::addSegment($segment);
    }

    /** @return \Piwik\Plugin\ActionDimension[] */
    public static function getAllDimensions()
    {
        $cache = new PluginAwareStaticCache('ActionDimensions');

        if (!$cache->has()) {

            $plugins   = PluginManager::getInstance()->getLoadedPlugins();
            $instances = array();

            foreach ($plugins as $plugin) {
                foreach (self::getDimensions($plugin) as $instance) {
                    $instances[] = $instance;
                }
            }

            $cache->set($instances);
        }

        return $cache->get();
    }

    /** @return \Piwik\Plugin\ActionDimension[] */
    public static function getDimensions(\Piwik\Plugin $plugin)
    {
        $dimensions = $plugin->findMultipleComponents('Columns', '\\Piwik\\Plugin\\ActionDimension');
        $instances  = array();

        foreach ($dimensions as $dimension) {
            $instances[] = new $dimension();
        }

        return $instances;
    }

    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        return false;
    }

}
