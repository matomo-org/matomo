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
use Piwik\Common;
use Piwik\Db;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugin\Segment;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;
use Piwik\Tracker;
use Piwik\Translate;

/**
 * @api
 * @since 2.5.0
 */
abstract class VisitDimension extends Dimension
{
    private $tableName = 'log_visit';

    public function install()
    {
        if (!$this->columnType) {
            return array();
        }

        $changes = array(
            $this->tableName => array("ADD COLUMN `$this->columnName` $this->columnType")
        );

        if ($this->isHandlingLogConversion()) {
            $changes['log_conversion'] = array("ADD COLUMN `$this->columnName` $this->columnType");
        }

        return $changes;
    }

    public function update($conversionColumns)
    {
        if (!$this->columnType) {
            return array();
        }

        $changes = array();

        $changes[$this->tableName] = array("MODIFY COLUMN `$this->columnName` $this->columnType");

        $handlingConversion  = $this->isHandlingLogConversion();
        $hasConversionColumn = array_key_exists($this->columnName, $conversionColumns);

        if ($hasConversionColumn && $handlingConversion) {
            $changes['log_conversion'] = array("MODIFY COLUMN `$this->columnName` $this->columnType");
        } elseif (!$hasConversionColumn && $handlingConversion) {
            $changes['log_conversion'] = array("ADD COLUMN `$this->columnName` $this->columnType");
        } elseif ($hasConversionColumn && !$handlingConversion) {
            $changes['log_conversion'] = array("DROP COLUMN `$this->columnName`");
        }

        return $changes;
    }

    public function getVersion()
    {
        return $this->columnType . $this->isHandlingLogConversion();
    }

    private function isHandlingLogConversion()
    {
        if (empty($this->columnName) || empty($this->columnType)) {
            return false;
        }

        return $this->hasImplementedEvent('onAnyGoalConversion');
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

        try {
            $sql = "ALTER TABLE `" . Common::prefixTable('log_conversion') . "` DROP COLUMN `$this->columnName`";
            Db::exec($sql);
        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, '1091')) {
                throw $e;
            }
        }
    }

    protected function addSegment(Segment $segment)
    {
        $sqlSegment = $segment->getSqlSegment();
        if (!empty($this->columnName) && empty($sqlSegment)) {
            $segment->setSqlSegment('log_visit.' . $this->columnName);
        }

        parent::addSegment($segment);
    }

    public function getRequiredVisitFields()
    {
        return array();
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return false;
    }

    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        return false;
    }

    public function onConvertedVisit(Request $request, Visitor $visitor, $action)
    {
        return false;
    }

    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        return false;
    }

    /** @return \Piwik\Plugin\Dimension\VisitDimension[] */
    public static function getAllDimensions()
    {
        $cache = new PluginAwareStaticCache('VisitDimensions');

        if (!$cache->has()) {

            $plugins   = PluginManager::getInstance()->getLoadedPlugins();
            $instances = array();
            
            foreach ($plugins as $plugin) {
                foreach (self::getDimensions($plugin) as $instance) {
                    $instances[] = $instance;
                }
            }

            usort($instances, array('self', 'sortByRequiredFields'));

            $cache->set($instances);
        }

        return $cache->get();
    }

    public static function sortByRequiredFields($a, $b)
    {
        $fields = $a->getRequiredVisitFields();

        if (empty($fields)) {
            return -1;
        }

        if (in_array($b->columnName, $fields)) {
            return 1;
        }

        return 0;
    }

    /** @return \Piwik\Plugin\Dimension\VisitDimension[] */
    public static function getDimensions(\Piwik\Plugin $plugin)
    {
        $dimensions = $plugin->findMultipleComponents('Columns', '\\Piwik\\Plugin\\Dimension\\VisitDimension');
        $instances  = array();

        foreach ($dimensions as $dimension) {
            $instances[] = new $dimension();
        }

        return $instances;
    }

}
