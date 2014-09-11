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
use Piwik\Plugin;
use Exception;

/**
 * Defines a new visit dimension that records any visit related information during tracking.
 *
 * You can record any visit information by implementing one of the following events: {@link onNewVisit()},
 * {@link onExistingVisit()}, {@link onConvertedVisit()} or {@link onAnyGoalConversion()}. By defining a
 * {@link $columnName} and {@link $columnType} a new column will be created in the database (table `log_visit`)
 * automatically and the values you return in the previous mentioned events will be saved in this column.
 *
 * You can create a new dimension using the console command `./console generate:dimension`.
 *
 * @api
 * @since 2.5.0
 */
abstract class VisitDimension extends Dimension
{
    private $tableName = 'log_visit';

    /**
     * Installs the visit dimension in case it is not installed yet. The installation is already implemented based on
     * the {@link $columnName} and {@link $columnType}. If you want to perform additional actions beside adding the
     * column to the database - for instance adding an index - you can overwrite this method. We recommend to call
     * this parent method to get the minimum required actions and then add further custom actions since this makes sure
     * the column will be installed correctly. We also recommend to change the default install behavior only if really
     * needed. FYI: We do not directly execute those alter table statements here as we group them together with several
     * other alter table statements do execute those changes in one step which results in a faster installation. The
     * column will be added to the `log_visit` MySQL table.
     *
     * Example:
     * ```
    public function install()
    {
        $changes = parent::install();
        $changes['log_visit'][] = "ADD INDEX index_idsite_servertime ( idsite, server_time )";

        return $changes;
    }
    ```
     *
     * @return array An array containing the table name as key and an array of MySQL alter table statements that should
     *               be executed on the given table. Example:
     * ```
    array(
        'log_visit' => array("ADD COLUMN `$this->columnName` $this->columnType", "ADD INDEX ...")
    );
    ```
     * @api
     */
    public function install()
    {
        if (empty($this->columnType) || empty($this->columnName)) {
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

    /**
     * @see ActionDimension::update()
     * @param array $conversionColumns An array of currently installed columns in the conversion table.
     * @return array
     * @ignore
     */
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

    /**
     * @see ActionDimension::getVersion()
     * @return string
     * @ignore
     */
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

    /**
     * Uninstalls the dimension if a {@link $columnName} and {@link columnType} is set. In case you perform any custom
     * actions during {@link install()} - for instance adding an index - you should make sure to undo those actions by
     * overwriting this method. Make sure to call this parent method to make sure the uninstallation of the column
     * will be done.
     * @throws Exception
     * @api
     */
    public function uninstall()
    {
        if (empty($this->columnName) || empty($this->columnType)) {
            return;
        }

        try {
            $sql = "ALTER TABLE `" . Common::prefixTable($this->tableName) . "` DROP COLUMN `$this->columnName`";
            Db::exec($sql);
        } catch (Exception $e) {
            if (!Db::get()->isErrNo($e, '1091')) {
                throw $e;
            }
        }

        try {
            $sql = "ALTER TABLE `" . Common::prefixTable('log_conversion') . "` DROP COLUMN `$this->columnName`";
            Db::exec($sql);
        } catch (Exception $e) {
            if (!Db::get()->isErrNo($e, '1091')) {
                throw $e;
            }
        }
    }

    /**
     * Adds a new segment. It automatically sets the SQL segment depending on the column name in case none is set
     * already.
     * @see \Piwik\Columns\Dimension::addSegment()
     * @param Segment $segment
     * @api
     */
    protected function addSegment(Segment $segment)
    {
        $sqlSegment = $segment->getSqlSegment();
        if (!empty($this->columnName) && empty($sqlSegment)) {
            $segment->setSqlSegment('log_visit.' . $this->columnName);
        }

        parent::addSegment($segment);
    }

    /**
     * Sometimes you may want to make sure another dimension is executed before your dimension so you can persist
     * this dimensions' value depending on the value of other dimensions. You can do this by defining an array of
     * dimension names. If you access any value of any other column within your events, you should require them here.
     * Otherwise those values may not be available.
     * @return array
     * @api
     */
    public function getRequiredVisitFields()
    {
        return array();
    }

    /**
     * The `onNewVisit` method is triggered when a new visitor is detected. This means you can define an initial
     * value for this user here. By returning boolean `false` no value will be saved. Once the user makes another action
     * the event "onExistingVisit" is executed. Meaning for each visitor this method is executed once.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed|false
     * @api
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return false;
    }

    /**
     * The `onExistingVisit` method is triggered when a visitor was recognized meaning it is not a new visitor.
     * You can overwrite any previous value set by the event `onNewVisit` by implemting this event. By returning boolean
     * `false` no value will be updated.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed|false
     * @api
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        return false;
    }

    /**
     * This event is executed shortly after `onNewVisit` or `onExistingVisit` in case the visitor converted a goal.
     * Usually this event is not needed and you can simply remove this method therefore. An example would be for
     * instance to persist the last converted action url. Return boolean `false` if you do not want to change the
     * current value.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed|false
     * @api
     */
    public function onConvertedVisit(Request $request, Visitor $visitor, $action)
    {
        return false;
    }

    /**
     * By implementing this event you can persist a value to the `log_conversion` table in case a conversion happens.
     * The persisted value will be logged along the conversion and will not be changed afterwards. This allows you to
     * generate reports that shows for instance which url was called how often for a specific conversion. Once you
     * implement this event and a $columnType is defined a column in the `log_conversion` MySQL table will be
     * created automatically.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed|false
     * @api
     */
    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        return false;
    }

    /**
     * Get all visit dimensions that are defined by all activated plugins.
     * @return VisitDimension[]
     */
    public static function getAllDimensions()
    {
        $cache = new PluginAwareStaticCache('VisitDimensions');

        if (!$cache->has()) {

            $plugins   = PluginManager::getInstance()->getPluginsLoadedAndActivated();
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

    /**
     * @ignore
     */
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

    /**
     * Get all visit dimensions that are defined by the given plugin.
     * @param Plugin $plugin
     * @return VisitDimension[]
     * @ignore
     */
    public static function getDimensions(Plugin $plugin)
    {
        $dimensions = $plugin->findMultipleComponents('Columns', '\\Piwik\\Plugin\\Dimension\\VisitDimension');
        $instances  = array();

        foreach ($dimensions as $dimension) {
            $instances[] = new $dimension();
        }

        return $instances;
    }

}
