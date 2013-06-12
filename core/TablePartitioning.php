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
 * FIXMEA: simplify/delete this code
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
    private static $blobArchiveTable = null;
    private static $numericArchiveTable = null;
    
    public function __construct($tableName)
    {
        parent::__construct($tableName);
    }

    protected function generateTableName()
    {
        $config = Piwik_Config::getInstance();
        return $config->database['tables_prefix'] . $this->tableName . "_" . date("Y_m", $this->timestamp);
    }
    
    /**
     * Creates archive_blob & archive_numeric tables for a period if they don't already exist.
     * 
     * @param Piwik_Date
     */
    public static function createArchiveTablesIfAbsent($dateInMonth)
    {
        $timestamp = $dateInMonth->getTimestamp();
        
        self::$blobArchiveTable->setTimestamp($timestamp);
        self::$blobArchiveTable->getTableName();
        
        self::$numericArchiveTable->setTimestamp($timestamp);
        self::$numericArchiveTable->getTableName();
    }
    
    public static function init()
    {
        self::$blobArchiveTable = new Piwik_TablePartitioning_Monthly('archive_blob');
        self::$numericArchiveTable = new Piwik_TablePartitioning_Monthly('archive_numeric');
    }
}

Piwik_TablePartitioning_Monthly::init();
