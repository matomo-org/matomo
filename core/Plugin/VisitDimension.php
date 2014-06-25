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
use Piwik\Common;
use Piwik\Db;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;
use Piwik\Translate;

/**
 * @api
 * @since 2.5.0
 */
abstract class VisitDimension extends Dimension
{
    private $tableName = 'log_visit';

    public function install($visitColumns, $conversionColumns)
    {
        if (!$this->columnType) {
            return array();
        }

        $changes = array();

        $hasVisitColumn = array_key_exists($this->columnName, $visitColumns);

        if (!$hasVisitColumn) {
            $tableVisit           = Common::prefixTable($this->tableName);
            $changes[$tableVisit] = array("ADD COLUMN `$this->columnName` $this->columnType");
        }

        $handlingLogConversion = $this->isHandlingLogConversion();
        $hasConversionColumn   = array_key_exists($this->columnName, $conversionColumns);
        $tableConversion       = Common::prefixTable("log_conversion");

        if (!$hasConversionColumn && $handlingLogConversion) {
            $changes[$tableConversion] = array("ADD COLUMN `$this->columnName` $this->columnType");
        } elseif ($hasConversionColumn && !$handlingLogConversion) {
            $changes[$tableConversion] = array("DROP COLUMN `$this->columnName`");
        }

        return $changes;
    }

    private function isHandlingLogVisit()
    {
        if (empty($this->columnName) || empty($this->columnType)) {
            return false;
        }

        return $this->hasImplementedEvent('onNewVisit')
            || $this->hasImplementedEvent('onExistingVisit')
            || $this->hasImplementedEvent('onConvertedVisit');
    }

    private function isHandlingLogConversion()
    {
        if (empty($this->columnName) || empty($this->columnType)) {
            return false;
        }

        return $this->hasImplementedEvent('onRecordGoal');
    }

    public function uninstall($visitColumns, $conversionColumns)
    {
        if (empty($this->columnName) || empty($this->columnType)) {
            return array();
        }

        $columnsToDrop = array();

        if (array_key_exists($this->columnName, $visitColumns) && $this->isHandlingLogVisit()) {
            $tableVisit                 = Common::prefixTable($this->tableName);
            $columnsToDrop[$tableVisit] = array("DROP COLUMN `$this->columnName`");
        }

        if (array_key_exists($this->columnName, $conversionColumns) && $this->isHandlingLogConversion()) {
            $tableConversion                 = Common::prefixTable("log_conversion");
            $columnsToDrop[$tableConversion] = array("DROP COLUMN `$this->columnName`");
        }

        return $columnsToDrop;
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

    public function onRecordGoal(Request $request, Visitor $visitor, $action)
    {
        return false;
    }

    /** @return \Piwik\Plugin\VisitDimension[] */
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

        if (!empty($fields) && in_array($b->getColumnName(), $fields)) {
            return 1;
        }

        return 0;
    }

    /** @return \Piwik\Plugin\VisitDimension[] */
    public static function getDimensions(\Piwik\Plugin $plugin)
    {
        $dimensions = $plugin->findMultipleComponents('Columns', '\\Piwik\\Plugin\\VisitDimension');
        $instances  = array();

        foreach ($dimensions as $dimension) {
            $instances[] = new $dimension();
        }

        return $instances;
    }

}
