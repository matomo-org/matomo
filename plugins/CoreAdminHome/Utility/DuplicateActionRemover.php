<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreAdminHome\Utility;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Psr\Log\LoggerInterface;

/**
 * Finds duplicate actions rows in log_action and removes them. Fixes references to duplicate
 * actions in the log_link_visit_action table, log_conversion table, and log_conversion_item
 * table.
 *
 * Prior to version 2.11, there was a race condition in the tracker where it was possible for
 * two or more actions with the same name and type to be inserted simultaneously. This resulted
 * in inaccurate data. A Piwik database with this problem can be fixed using this class.
 *
 * With version 2.11 and above, it is still possible for duplicate actions to be inserted, but
 * ONLY if the tracker's PHP process fails suddenly right after inserting an action. This is
 * very rare, and even if it does happen, report data will not be affected, but the extra
 * actions can be deleted w/ this class.
 */
class DuplicateActionRemover
{
    /**
     * The tables that contain idaction reference columns.
     *
     * @var string[]
     */
    private static $tablesWithIdActionColumns = array(
        'log_link_visit_action',
        'log_conversion',
        'log_conversion_item'
    );

    /**
     * The logger. Used to log status updates.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * List of idaction columns in each table in $tablesWithIdActionColumns. idaction
     * columns are table columns with the string `"idaction"` in them.
     *
     * @var string[]
     */
    private $idactionColumns;

    public function __construct()
    {
        $this->logger = StaticContainer::get('Psr\Log\LoggerInterface');
    }

    /**
     * Performs the duplicate row removal & reference fixing.
     *
     * @return int The number of duplicates removed.
     */
    public function removeDuplicateActionsFromDb()
    {
        $this->getIdActionTableColumnsFromMetadata();

        $duplicateActions = $this->getDuplicateIdActions();
        $dupeCount = count($duplicateActions);

        $this->logger->info("<info>Found {duplicates} duplicate actions.</info>", array(
            'duplicates' => $dupeCount
        ));

        if ($dupeCount != 0) {
            foreach ($duplicateActions as $index => $dupeInfo) {
                $name = $dupeInfo['name'];
                $idactions = $dupeInfo['idactions'];

                $this->logger->info("<info>[$index / $dupeCount]</info> Fixing duplicates for '{name}'", array(
                    'name' => $name
                ));
                $this->logger->debug("  idactions = [ {idactions} ]", array('idactions' => $idactions));

                $this->fixDuplicateActions($idactions);
            }

            $this->deleteDuplicatesFromLogAction($duplicateActions);
        }

        return $dupeCount;
    }

    /**
     * Returns a list of SQL statements that can be used to remove duplicate actions. Executing
     * the statements in order will have the same effect as calling removeDuplicateActionsFromDb().
     *
     * Note: this method is used by the Update class that removes duplicate log actions.
     *
     * @return string[]
     */
    public function getSqlToRemoveDuplicateActions()
    {
        $this->getIdActionTableColumnsFromMetadata();

        $duplicateActions = $this->getDuplicateIdActions();

        $sqlQueries = array();
        if (!empty($duplicateActions)) {
            foreach ($duplicateActions as $index => $dupeInfo) {
                $idactions = $dupeInfo['idactions'];

                list($toIdAction, $fromIdActions) = $this->getIdActionToRenameToAndFrom($idactions);

                foreach (self::$tablesWithIdActionColumns as $table) {
                    $sql = $this->getSqlToFixDuplicates($table, $toIdAction, $fromIdActions);
                    $sqlQueries[$sql] = false;
                }
            }

            $deleteActionsSql = $this->getSqlToDeleteDuplicateLogActionRows($duplicateActions);
            $sqlQueries[$deleteActionsSql] = false;
        }
        return $sqlQueries;
    }

    private function getDuplicateIdActions()
    {
        $sql = "SELECT name, hash, type, COUNT(*) AS count, GROUP_CONCAT(idaction ORDER BY idaction ASC SEPARATOR ',') as idactions
                  FROM " . Common::prefixTable('log_action') . "
              GROUP BY name, hash, type HAVING count > 1";
        return Db::fetchAll($sql);
    }

    private function fixDuplicateActions($idactions)
    {
        list($toIdAction, $fromIdActions) = $this->getIdActionToRenameToAndFrom($idactions);

        foreach (self::$tablesWithIdActionColumns as $table) {
            $this->fixDuplicateActionsInTable($table, $toIdAction, $fromIdActions);
        }
    }

    private function getIdActionToRenameToAndFrom($idactions)
    {
        $idactions = explode(',', $idactions);

        $toIdAction = array_shift($idactions);
        $fromIdActions = $idactions;

        return array($toIdAction, $fromIdActions);
    }

    private function fixDuplicateActionsInTable($table, $toIdAction, $fromIdActions)
    {
        $sql = $this->getSqlToFixDuplicates($table, $toIdAction, $fromIdActions);

        $startTime = microtime(true);

        Db::query($sql);

        $endTime = microtime(true);
        $elapsed = $endTime - $startTime;

        $this->logTableFixFinished($table, $elapsed);
    }

    private function deleteDuplicatesFromLogAction($duplicateActions)
    {
        $table = Common::prefixTable('log_action');

        $this->logger->info("<info>Deleting duplicate actions from {table}...</info>", array(
            'table' => $table
        ));

        $sql = $this->getSqlToDeleteDuplicateLogActionRows($duplicateActions);
        Db::query($sql);
    }

    private function getSqlToDeleteDuplicateLogActionRows($duplicateActions)
    {
        $table = Common::prefixTable('log_action');

        $sql = "DELETE FROM $table WHERE idaction IN (";
        foreach ($duplicateActions as $index => $dupeInfos) {
            if ($index != 0) {
                $sql .= ",";
            }

            $restIdActions = $this->getDuplicateIdActionsFromAll($dupeInfos['idactions']);

            $sql .= $restIdActions;
        }
        $sql .= ")";

        return $sql;
    }

    private function logTableFixFinished($table, $elapsed)
    {
        $this->logger->info("\tFixed duplicates in {table} in <comment>{elapsed}s</comment>.", array(
            'table' => Common::prefixTable($table),
            'elapsed' => $elapsed
        ));
    }

    private function getDuplicateIdActionsFromAll($idactions)
    {
        $commaPos = strpos($idactions, ',');
        if ($commaPos !== false) {
            $idactions = substr($idactions, $commaPos + 1);
        }
        return $idactions;
    }

    /**
     * Creates SQL that sets multiple columns in a table to a single value, if those
     * columns are set to certain values.
     *
     * The SQL will look like:
     *
     *     UPDATE $table SET
     *         col1 = IF((col1 IN ($fromIdActions)), $toIdAction, col1),
     *         col2 = IF((col2 IN ($fromIdActions)), $toIdAction, col2),
     *         ...
     *      WHERE col1 IN ($fromIdActions) OR col2 IN ($fromIdActions) OR ...
     */
    private function getSqlToFixDuplicates($table, $toIdAction, $fromIdActions)
    {
        $idactionColumns = array_values($this->idactionColumns[$table]);
        $table = Common::prefixTable($table);

        $inFromIdsExpression = "%1\$s IN (" . implode(',', $fromIdActions) . ")";
        $setExpression = "%1\$s = IF(($inFromIdsExpression), $toIdAction, %1\$s)";

        $sql = "UPDATE $table SET\n";
        foreach ($idactionColumns as $index => $column) {
            if ($index != 0) {
                $sql .= ",\n";
            }
            $sql .= sprintf($setExpression, $column);
        }
        $sql .= "WHERE ";
        foreach ($idactionColumns as $index => $column) {
            if ($index != 0) {
                $sql .= "OR ";
            }

            $sql .= sprintf($inFromIdsExpression, $column) . " ";
        }

        return $sql;
    }

    private function getIdActionTableColumnsFromMetadata()
    {
        foreach (self::$tablesWithIdActionColumns as $table) {
            $columns = Db::fetchAll("SHOW COLUMNS FROM " . Common::prefixTable($table));

            $columns = array_map(function ($row) { return $row['Field']; }, $columns);

            $columns = array_filter($columns, function ($columnName) {
                return strpos($columnName, 'idaction') !== false;
            });

            $this->logger->debug("Found following idactions in {table}: {columns}", array(
                'table' => $table,
                'columns' => implode(',', $columns)
            ));

            $this->idactionColumns[$table] = $columns;
        }
    }
}