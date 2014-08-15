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
use Piwik\Common;
use Piwik\Db;
use Piwik\Tracker\Action;
use Piwik\Tracker\GoalManager;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Translate;
use Piwik\Plugin\Segment;
use Piwik\Plugin;
use Exception;

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
    private $tableName = 'log_conversion';

    /**
     * Installs the conversion dimension in case it is not installed yet. The installation is already implemented based
     * on the {@link $columnName} and {@link $columnType}. If you want to perform additional actions beside adding the
     * column to the database - for instance adding an index - you can overwrite this method. We recommend to call
     * this parent method to get the minimum required actions and then add further custom actions since this makes sure
     * the column will be installed correctly. We also recommend to change the default install behavior only if really
     * needed. FYI: We do not directly execute those alter table statements here as we group them together with several
     * other alter table statements do execute those changes in one step which results in a faster installation. The
     * column will be added to the `log_conversion` MySQL table.
     *
     * Example:
     * ```
    public function install()
    {
    $changes = parent::install();
    $changes['log_conversion'][] = "ADD INDEX index_idsite_servertime ( idsite, server_time )";

    return $changes;
    }
    ```
     *
     * @return array An array containing the table name as key and an array of MySQL alter table statements that should
     *               be executed on the given table. Example:
     * ```
    array(
    'log_conversion' => array("ADD COLUMN `$this->columnName` $this->columnType", "ADD INDEX ...")
    );
    ```
     * @api
     */
    public function install()
    {
        if (empty($this->columnName) || empty($this->columnType)) {
            return array();
        }

        return array(
            $this->tableName => array("ADD COLUMN `$this->columnName` $this->columnType")
        );
    }

    /**
     * @see ActionDimension::update()
     * @return array
     * @ignore
     */
    public function update()
    {
        if (empty($this->columnName) || empty($this->columnType)) {
            return array();
        }

        return array(
            $this->tableName => array("MODIFY COLUMN `$this->columnName` $this->columnType")
        );
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
    }

    /**
     * @see ActionDimension::getVersion()
     * @return string
     * @ignore
     */
    public function getVersion()
    {
        return $this->columnType;
    }

    /**
     * Adds a new segment. It automatically sets the SQL segment depending on the column name in case none is set
     * already.
     *
     * @see \Piwik\Columns\Dimension::addSegment()
     * @param Segment $segment
     * @api
     */
    protected function addSegment(Segment $segment)
    {
        $sqlSegment = $segment->getSqlSegment();
        if (!empty($this->columnName) && empty($sqlSegment)) {
            $segment->setSqlSegment($this->tableName . '.' . $this->columnName);
        }

        parent::addSegment($segment);
    }

    /**
     * Get all conversion dimensions that are defined by all activated plugins.
     * @ignore
     */
    public static function getAllDimensions()
    {
        $cache = new PluginAwareStaticCache('ConversionDimensions');

        if (!$cache->has()) {

            $plugins   = PluginManager::getInstance()->getPluginsLoadedAndActivated();
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
