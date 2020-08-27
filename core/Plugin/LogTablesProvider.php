<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Container\StaticContainer;
use Piwik\DataAccess\LogTableTemporary;
use Piwik\Piwik;
use Piwik\Tracker\LogTable;

class LogTablesProvider {

    /**
     * @var Manager
     */
    private $pluginManager;

    /**
     * @var LogTable[]
     */
    private $tablesCache;

    /**
     * @var LogTableTemporary
     */
    private $tempTable;

    public function __construct(Manager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * Get an instance of a specific log table if such a log table exists.
     *
     * @param string $tableNameWithoutPrefix  eg "log_visit"
     * @return LogTable|null
     */
    public function getLogTable($tableNameWithoutPrefix)
    {
        if ($this->tempTable && $this->tempTable->getName() === $tableNameWithoutPrefix) {
            return $this->tempTable;
        }
        foreach ($this->getAllLogTables() as $table) {
            if ($table->getName() === $tableNameWithoutPrefix) {
                return $table;
            }
        }
    }

    /**
     * @param LogTableTemporary|null $table
     */
    public function setTempTable($table)
    {
        $this->tempTable = $table;
    }

    public function clearCache()
    {
        $this->tablesCache = null;
    }

    /**
     * Needed for log query builder
     * @return LogTable[]
     */
    public function getAllLogTablesWithTemporary()
    {
        $tables = $this->getAllLogTables();
        if ($this->tempTable) {
            $tables[] = $this->tempTable;
        }
        return $tables;
    }

    /**
     * Get all log table instances defined by any activated and loaded plugin. The returned tables are not sorted in
     * any order.
     * @return LogTable[]
     */
    public function getAllLogTables()
    {
        if (!isset($this->tablesCache)) {
            $tables = $this->pluginManager->findMultipleComponents('Tracker', 'Piwik\\Tracker\\LogTable');

            $logTables = array();

            /**
             * Only used for tests. Triggered to add custom log tables which are not automatically picked up.
             * In case you need to define a log table, please put them inside the "Tracker" directory of your plugin.
             * Please note custom log tables are currently not an official API.
             *
             * **Example**
             *
             *     public function addLogTable(&$logTables)
             *     {
             *         $logTables[] = new LogTable();
             *     }
             *
             * @param array &$logTables An array containing a list of log entries.
             *
             * @internal Only used for tests
             * @ignore
             */
            Piwik::postEvent('LogTables.addLogTables', array(&$logTables));

            foreach ($tables as $table) {
                $logTables[] = StaticContainer::get($table);
            }

            $this->tablesCache = $logTables;
        }

        return $this->tablesCache;
    }

}
