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
use Piwik\DataAccess\ArchiveInvalidator;
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

    /**
     * Used to invalidate archives. Only used if $shouldInvalidateArchives is true.
     *
     * @var ArchiveInvalidator
     */
    private $archiveInvalidator;

    /**
     * Constructor.
     *
     * @param ArchiveInvalidator|null $archiveInvalidator If null, archives are not invalidated.
     */
    public function __construct(ArchiveInvalidator $archiveInvalidator = null)
    {
        $this->logger = StaticContainer::get('Psr\Log\LoggerInterface');
        $this->archiveInvalidator = $archiveInvalidator;
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

        $this->logger->info("<info>Found {count} actions with duplicates.</info>", array(
            'count' => count($duplicateActions)
        ));

        $dupeCount = 0;

        $archivesAffected = array();
        if (!empty($duplicateActions)) {
            foreach ($duplicateActions as $index => $dupeInfo) {
                $name = $dupeInfo['name'];
                $idactions = $dupeInfo['idactions'];

                $this->logger->info("<info>[$index / $dupeCount]</info> Fixing duplicates for '{name}'", array(
                    'name' => $name
                ));
                $this->logger->debug("  idactions = [ {idactions} ]", array('idactions' => $idactions));

                $idactions = explode(',', $idactions);

                $dupeCount += count($idactions) - 1; // -1, because the first idaction is the one that isn't removed

                $this->fixDuplicateActions($idactions, $archivesAffected);
            }

            $this->deleteDuplicatesFromLogAction($duplicateActions);

            $archivesAffected = array_values(array_unique($archivesAffected, SORT_REGULAR));
            if (!empty($this->archiveInvalidator)) {
                $this->invalidateArchivesUsingActionDuplicates($archivesAffected);
            }
        }

        return array($dupeCount, $archivesAffected);
    }

    private function getDuplicateIdActions()
    {
        $sql = "SELECT name, hash, type, COUNT(*) AS count, GROUP_CONCAT(idaction ORDER BY idaction ASC SEPARATOR ',') as idactions
                  FROM " . Common::prefixTable('log_action') . "
              GROUP BY name, hash, type HAVING count > 1";
        return Db::fetchAll($sql);
    }

    private function fixDuplicateActions($idactions, &$archivesAffected)
    {
        $toIdAction = array_shift($idactions);
        $fromIdActions = $idactions;

        foreach (self::$tablesWithIdActionColumns as $table) {
            $this->fixDuplicateActionsInTable($table, $toIdAction, $fromIdActions, $archivesAffected);
        }
    }

    private function fixDuplicateActionsInTable($table, $toIdAction, $fromIdActions, &$archivesAffected)
    {
        $startTime = microtime(true);

        $sql = $this->getSitesAndDatesOfRowsUsingDuplicates($table, $fromIdActions);

        foreach (Db::fetchAll($sql) as $row) {
            $archivesAffected[] = $row;
        }

        $sql = $this->getSqlToFixDuplicates($table, $toIdAction, $fromIdActions);

        Db::query($sql);

        $endTime = microtime(true);
        $elapsed = $endTime - $startTime;

        $this->logger->info("\tFixed duplicates in {table} in <comment>{elapsed}s</comment>.", array(
            'table' => Common::prefixTable($table),
            'elapsed' => $elapsed
        ));
    }

    private function deleteDuplicatesFromLogAction($duplicateActions)
    {
        $table = Common::prefixTable('log_action');

        $this->logger->info("<info>Deleting duplicate actions from {table}...</info>", array(
            'table' => $table
        ));

        $sql = "DELETE FROM $table WHERE idaction IN (";
        foreach ($duplicateActions as $index => $dupeInfos) {
            if ($index != 0) {
                $sql .= ",";
            }

            $restIdActions = $dupeInfos['idactions'];

            $commaPos = strpos($restIdActions, ',');
            if ($commaPos !== false) {
                $restIdActions = substr($restIdActions, $commaPos + 1);
            }

            $sql .= $restIdActions;
        }
        $sql .= ")";

        Db::query($sql);
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

        $inFromIdsExpression = $this->getInFromIdsExpression($fromIdActions);
        $setExpression = "%1\$s = IF(($inFromIdsExpression), $toIdAction, %1\$s)";

        $sql = "UPDATE $table SET\n";
        foreach ($idactionColumns as $index => $column) {
            if ($index != 0) {
                $sql .= ",\n";
            }
            $sql .= sprintf($setExpression, $column);
        }
        $sql .= $this->getWhereToGetRowsUsingDuplicateActions($idactionColumns, $fromIdActions);

        return $sql;
    }

    private function getSitesAndDatesOfRowsUsingDuplicates($table, $fromIdActions)
    {
        $idactionColumns = array_values($this->idactionColumns[$table]);
        $table = Common::prefixTable($table);

        $sql = "SELECT idsite, DATE(server_time) as server_time FROM $table ";
        $sql .= $this->getWhereToGetRowsUsingDuplicateActions($idactionColumns, $fromIdActions);
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

    private function getWhereToGetRowsUsingDuplicateActions($idactionColumns, $fromIdActions)
    {
        $sql = "WHERE ";
        foreach ($idactionColumns as $index => $column) {
            if ($index != 0) {
                $sql .= "OR ";
            }

            $sql .= sprintf($this->getInFromIdsExpression($fromIdActions), $column) . " ";
        }
        return $sql;
    }

    private function getInFromIdsExpression($fromIdActions)
    {
        return "%1\$s IN (" . implode(',', $fromIdActions) . ")";
    }

    private function invalidateArchivesUsingActionDuplicates($archivesAffected)
    {
        $this->logger->info("Invalidating archives affected by duplicates fixed...");
        foreach ($archivesAffected as $archiveInfo) {
            $this->archiveInvalidator->markArchivesAsInvalidated(
                array($archiveInfo['idsite']), $archiveInfo['server_time'], $period = false);
        }
        $this->logger->info("...Done.");
    }
}