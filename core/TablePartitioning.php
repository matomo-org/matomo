<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 *
 * NB: When a new table is partitionned using this class, we have to update the method
 *     Piwik::getTablesInstalled() to add the new table to the list of tablename_* to fetch
 *
 * @package Piwik
 * @subpackage Piwik_TablePartitioning
 */
abstract class Piwik_TablePartitioning
{
    protected $tableName = null;
    protected $generatedTableName = null;
    protected $timestamp = null;

    static public $tablesAlreadyInstalled = null;

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    abstract protected function generateTableName();

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        $this->generatedTableName = null;
        $this->getTableName();
    }

    public function getTableName()
    {
        // table name already processed
        if (!is_null($this->generatedTableName)) {
            return $this->generatedTableName;
        }

        if (is_null($this->timestamp)) {
            throw new Exception("You have to specify a timestamp for a Table Partitioning by date.");
        }

        // generate table name
        $this->generatedTableName = $this->generateTableName();

        // we make sure the table already exists
        $this->checkTableExists();
    }

    protected function checkTableExists()
    {
        if (is_null(self::$tablesAlreadyInstalled)) {
            self::$tablesAlreadyInstalled = Piwik::getTablesInstalled($forceReload = false);
        }

        if (!in_array($this->generatedTableName, self::$tablesAlreadyInstalled)) {
            $db = Zend_Registry::get('db');
            $sql = Piwik::getTableCreateSql($this->tableName);

            $config = Piwik_Config::getInstance();
            $prefixTables = $config->database['tables_prefix'];
            $sql = str_replace($prefixTables . $this->tableName, $this->generatedTableName, $sql);
            try {
                $db->query($sql);
            } catch (Exception $e) {
                // mysql error 1050: table already exists
                if (!$db->isErrNo($e, '1050')) {
                    // failed for some other reason
                    throw $e;
                }
            }

            self::$tablesAlreadyInstalled[] = $this->generatedTableName;
        }
    }

    public function __toString()
    {
        return $this->getTableName();
    }
}

/**
 *
 * @package Piwik
 * @subpackage Piwik_TablePartitioning
 */
class Piwik_TablePartitioning_Monthly extends Piwik_TablePartitioning
{
    public function __construct($tableName)
    {
        parent::__construct($tableName);
    }

    protected function generateTableName()
    {
        $config = Piwik_Config::getInstance();
        return $config->database['tables_prefix'] . $this->tableName . "_" . date("Y_m", $this->timestamp);
    }

}

/**
 *
 * @package Piwik
 * @subpackage Piwik_TablePartitioning
 */
class Piwik_TablePartitioning_Daily extends Piwik_TablePartitioning
{
    public function __construct($tableName)
    {
        parent::__construct($tableName);
    }

    protected function generateTableName()
    {
        $config = Piwik_Config::getInstance();
        return $config->database['tables_prefix'] . $this->tableName . "_" . date("Y_m_d", $this->timestamp);
    }
}
