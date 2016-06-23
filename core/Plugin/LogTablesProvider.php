<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Container\StaticContainer;
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
        foreach ($this->getAllLogTables() as $table) {
            if ($table->getName() === $tableNameWithoutPrefix) {
                return $table;
            }
        }
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

            $this->tablesCache = array();
            foreach ($tables as $table) {
                $this->tablesCache[] = StaticContainer::get($table);
            }
        }

        return $this->tablesCache;
    }

}
