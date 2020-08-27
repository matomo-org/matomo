<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreAdminHome\Model;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\TableMetadata;
use Piwik\Db;
use Psr\Log\LoggerInterface;

/**
 * Provides methods to find duplicate actions and fix duplicate action references in tables
 * that reference log_action rows.
 */
class DuplicateActionRemover
{
    /**
     * The tables that contain idaction reference columns.
     *
     * @var string[]
     */
    public static $tablesWithIdActionColumns = array(
        'log_link_visit_action',
        'log_conversion',
        'log_conversion_item'
    );

    /**
     * DAO used to get idaction column names in tables that reference log_action rows.
     *
     * @var TableMetadata
     */
    private $tableMetadataAccess;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * List of idaction columns in each table in $tablesWithIdActionColumns. idaction
     * columns are table columns with the string `"idaction"` in them.
     *
     * @var string[]
     */
    private $idactionColumns = null;

    /**
     * Constructor.
     *
     * @param TableMetadata $tableMetadataAccess
     * @param LoggerInterface $logger
     */
    public function __construct(TableMetadata $tableMetadataAccess = null, LoggerInterface $logger = null)
    {
        $this->tableMetadataAccess = $tableMetadataAccess ?: new TableMetadata();
        $this->logger = $logger ?: StaticContainer::get('Psr\Log\LoggerInterface');
    }

    /**
     * Returns list of all duplicate actions in the log_action table by name and the lowest action ID.
     * The duplicate actions are returned with each action.
     *
     * @return array Contains the following elements:
     *
     *               * **name**: The action's name.
     *               * **idaction**: The action's ID.
     *               * **duplicateIdActions**: An array of duplicate action IDs.
     */
    public function getDuplicateIdActions()
    {
        $sql = "SELECT name, COUNT(*) AS count, GROUP_CONCAT(idaction ORDER BY idaction ASC SEPARATOR ',') as idactions
                  FROM " . Common::prefixTable('log_action') . "
              GROUP BY name, hash, type HAVING count > 1";

        $result = array();
        foreach (Db::fetchAll($sql) as $row) {
            $dupeInfo = array('name' => $row['name']);

            $idActions = explode(",", $row['idactions']);
            $dupeInfo['idaction'] = array_shift($idActions);
            $dupeInfo['duplicateIdActions'] = $idActions;

            $result[] = $dupeInfo;
        }
        return $result;
    }

    /**
     * Executes one SQL statement that sets all idaction columns in a table to a single value, if the
     * values of those columns are in the specified set (`$duplicateIdActions`).
     *
     * Notes:
     *
     * The SQL will look like:
     *
     *     UPDATE $table SET
     *         col1 = IF((col1 IN ($duplicateIdActions)), $realIdAction, col1),
     *         col2 = IF((col2 IN ($duplicateIdActions)), $realIdAction, col2),
     *         ...
     *      WHERE col1 IN ($duplicateIdActions) OR col2 IN ($duplicateIdActions) OR ...
     *
     * @param string $table
     * @param int $realIdAction The idaction to set column values to.
     * @param int[] $duplicateIdActions The idaction values that should be changed.
     */
    public function fixDuplicateActionsInTable($table, $realIdAction, $duplicateIdActions)
    {
        $idactionColumns = $this->getIdActionTableColumnsFromMetadata();
        $idactionColumns = array_values($idactionColumns[$table]);
        $table = Common::prefixTable($table);

        $inFromIdsExpression = $this->getInFromIdsExpression($duplicateIdActions);
        $setExpression = "%1\$s = IF(($inFromIdsExpression), $realIdAction, %1\$s)";

        $sql = "UPDATE $table SET\n";
        foreach ($idactionColumns as $index => $column) {
            if ($index != 0) {
                $sql .= ",\n";
            }
            $sql .= sprintf($setExpression, $column);
        }
        $sql .= $this->getWhereToGetRowsUsingDuplicateActions($idactionColumns, $duplicateIdActions);

        Db::query($sql);
    }

    /**
     * Returns the server time and idsite of rows in a log table that reference at least one action
     * in a set.
     *
     * @param string $table
     * @param int[] $duplicateIdActions
     * @return array with two elements **idsite** and **server_time**. idsite is the site ID and server_time
     *               is the date of the log.
     */
    public function getSitesAndDatesOfRowsUsingDuplicates($table, $duplicateIdActions)
    {
        $idactionColumns = $this->getIdActionTableColumnsFromMetadata();
        $idactionColumns = array_values($idactionColumns[$table]);
        $table = Common::prefixTable($table);

        $sql = "SELECT idsite, DATE(server_time) as server_time FROM $table ";
        $sql .= $this->getWhereToGetRowsUsingDuplicateActions($idactionColumns, $duplicateIdActions);
        return Db::fetchAll($sql);
    }

    private function getIdActionTableColumnsFromMetadata()
    {
        if ($this->idactionColumns === null) {
            $this->idactionColumns = array();
            foreach (self::$tablesWithIdActionColumns as $table) {
                $columns = $this->tableMetadataAccess->getIdActionColumnNames(Common::prefixTable($table));

                $this->logger->debug("Found following idactions in {table}: {columns}", array(
                    'table' => $table,
                    'columns' => implode(',', $columns)
                ));

                $this->idactionColumns[$table] = $columns;
            }
        }
        return $this->idactionColumns;
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
}