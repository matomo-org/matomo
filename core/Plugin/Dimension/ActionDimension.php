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
use Piwik\Plugin;
use Piwik\Db;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Translate;
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
    private $tableName = 'log_link_visit_action';

    /**
     * Installs the action dimension in case it is not installed yet. The installation is already implemented based on
     * the {@link $columnName} and {@link $columnType}. If you want to perform additional actions beside adding the
     * column to the database - for instance adding an index - you can overwrite this method. We recommend to call
     * this parent method to get the minimum required actions and then add further custom actions since this makes sure
     * the column will be installed correctly. We also recommend to change the default install behavior only if really
     * needed. FYI: We do not directly execute those alter table statements here as we group them together with several
     * other alter table statements do execute those changes in one step which results in a faster installation. The
     * column will be added to the `log_link_visit_action` MySQL table.
     *
     * Example:
     * ```
    public function install()
    {
        $changes = parent::install();
        $changes['log_link_visit_action'][] = "ADD INDEX index_idsite_servertime ( idsite, server_time )";

        return $changes;
    }
    ```
     *
     * @return array An array containing the table name as key and an array of MySQL alter table statements that should
     *               be executed on the given table. Example:
     * ```
    array(
        'log_link_visit_action' => array("ADD COLUMN `$this->columnName` $this->columnType", "ADD INDEX ...")
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
     * Updates the action dimension in case the {@link $columnType} has changed. The update is already implemented based
     * on the {@link $columnName} and {@link $columnType}. This method is intended not to overwritten by plugin
     * developers as it is only supposed to make sure the column has the correct type. Adding additional custom "alter
     * table" actions would not really work since they would be executed with every {@link $columnType} change. So
     * adding an index here would be executed whenever the columnType changes resulting in an error if the index already
     * exists. If an index needs to be added after the first version is released a plugin update class should be
     * created since this makes sure it is only executed once.
     *
     * @return array An array containing the table name as key and an array of MySQL alter table statements that should
     *               be executed on the given table. Example:
     * ```
    array(
    'log_link_visit_action' => array("MODIFY COLUMN `$this->columnName` $this->columnType", "DROP COLUMN ...")
    );
    ```
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
     * Get the version of the dimension which is used for update checks.
     * @return string
     * @ignore
     */
    public function getVersion()
    {
        return $this->columnType;
    }

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
            $segment->setSqlSegment($this->tableName . '.' . $this->columnName);
        }

        parent::addSegment($segment);
    }

    /**
     * Get all action dimensions that are defined by all activated plugins.
     * @ignore
     */
    public static function getAllDimensions()
    {
        $cache = new PluginAwareStaticCache('ActionDimensions');

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
