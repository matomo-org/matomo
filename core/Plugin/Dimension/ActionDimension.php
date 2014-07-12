<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin\Dimension;

use Piwik\Cache\PluginAwareStaticCache;
use Piwik\Columns\Dimension;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugin\Segment;
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
    private $tableName = 'log_link_visit_action';

    public function install()
    {
        if (empty($this->columnName) || empty($this->columnType)) {
            return array();
        }

        return array(
            $this->tableName => array("ADD COLUMN `$this->columnName` $this->columnType")
        );
    }

    public function update()
    {
        if (empty($this->columnName) || empty($this->columnType)) {
            return array();
        }

        return array(
            $this->tableName => array("MODIFY COLUMN `$this->columnName` $this->columnType")
        );
    }

    public function uninstall()
    {
        if (empty($this->columnName) || empty($this->columnType)) {
            return;
        }

        try {
            $sql = "ALTER TABLE `" . Common::prefixTable($this->tableName) . "` DROP COLUMN `$this->columnName`";
            Db::exec($sql);
        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, '1091')) {
                throw $e;
            }
        }
    }

    public function getVersion()
    {
        return $this->columnType;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        return false;
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
            $segment->setSqlSegment($this->tableName . '.' . $this->columnName);
        }

        parent::addSegment($segment);
    }

    /** @return \Piwik\Plugin\Dimension\ActionDimension[] */
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

    /** @return \Piwik\Plugin\Dimension\ActionDimension[] */
    public static function getDimensions(\Piwik\Plugin $plugin)
    {
        $dimensions = $plugin->findMultipleComponents('Columns', '\\Piwik\\Plugin\\Dimension\\ActionDimension');
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
