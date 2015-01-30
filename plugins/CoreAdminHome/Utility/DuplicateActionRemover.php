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
 * TODO
 */
class DuplicateActionRemover
{
    /**
     * TODO
     */
    private static $tablesWithIdActionColumns = array(
        'log_link_visit_action',
        'log_conversion',
        'log_conversion_item'
    );

    /**
     * TODO
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TODO
     */
    private $idactionColumns;

    public function __construct()
    {
        $this->logger = StaticContainer::get('Psr\Log\LoggerInterface');
    }

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
     * TODO
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
     * TODO: docs
     * convert 2,3,4 => 5
     *         6,7,8 => 9
     *
     * UPDATE log_link_visit_action
     *    SET a = IF(a IN ..., 5, a)
     *    SET b = IF(b IN ..., 5, b)
     *    ...
     *  WHERE a IN (...) OR b IN ...
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

    /**
     * TODO
     */
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