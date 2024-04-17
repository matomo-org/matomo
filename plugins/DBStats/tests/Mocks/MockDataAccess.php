<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DBStats\tests\Mocks;

use Piwik\Common;
use Piwik\Plugins\DBStats\MySQLMetadataDataAccess;

class MockDataAccess extends MySQLMetadataDataAccess
{
    public static $tableStatuses = array(
        'user' => array(5, 8192, 8192),
        'access' => array(9, 8192, 1024),
        'site' => array(10, 32000, 8192),
        'site_url' => array(8, 8192, 8192),
        'goal' => array(5, 16000, 8192),
        'logger_message' => array(100, 48000, 1024),
        'log_action' => array(3000, 64000, 32000),
        'log_visit' => array(1300, 64000, 16000),
        'log_conversion_item' => array(600, 64000, 16000),
        'log_conversion' => array(1000, 64000, 16000),
        'log_link_visit_action' => array(3000, 64000, 16000),
        'log_profiling' => array(0, 8192, 8192),
        'option' => array(200, 16000, 8192),
        'session' => array(0, 8192, 8192),
        'archive_numeric' => array(8000, 16000, 16000),
        'archive_blob' => array(8000, 128000, 1024),
        'segment' => array(3, 4048, 8192),
    );

    public static $numericRowCountsByArchiveName = array(
        array('label' => 'numericName1', 'row_count' => 93),
        array('label' => 'numericName2', 'row_count' => 203),
        array('label' => 'done', 'row_count' => 400),
        array('label' => 'done.plugin', 'row_count' => 150),
        array('label' => 'numericName3', 'row_count' => 340),
        array('label' => 'numericName4', 'row_count' => 240),
    );

    public static $blobRowCountsByArchiveName = array(
        array('label' => 'blobName1', 'row_count' => 123),
        array('label' => 'blobName2', 'row_count' => 145),
        array('label' => 'blobName3', 'row_count' => 83),
        array('label' => 'blobName4', 'row_count' => 45),
    );

    public function getDBStatus()
    {
        return array(
            'Uptime'                 => 10000,
            'Threads'                => 10,
            'Questions'              => 15,
            'Slow queries'           => 20,
            'Flush tables'           => 300,
            'Open tables'            => 2,
            'Opens'                  => 'unavailable',
            'Queries per second avg' => 'unavailable'
        );
    }

    public function getTableStatus($tableName)
    {
        list($rows, $rowLength, $indexRowLength) = self::$tableStatuses[$this->getTableNameKey($tableName)];

        return array(
            'Name' => $tableName,
            'Engine' => 'InnoDB',
            'Version' => 10,
            'Row_format' => 'Compact',
            'Rows' => $rows,
            'Avg_row_length' => $rowLength,
            'Data_length' => $rows * $rowLength,
            'Max_data_length' => 0,
            'Index_length' => $rows * $indexRowLength,
            'Data_free' => 236978176,
            'Auto_increment' => null,
            'Create_time' => '2014-01-01 23:54:56',
            'Update_time' => null,
            'Check_time' => null,
            'Collation' => 'utf8_general_ci',
            'Checksum' => null,
            'Create_options' => "",
            'Comment' => ""
        );
    }

    public function getAllTablesStatus()
    {
        $result = array();
        foreach (self::$tableStatuses as $tableName => $ignore) {
            if (
                $tableName == "archive_numeric"
                || $tableName == "archive_blob"
            ) {
                continue;
            }

            $unprefixed = Common::prefixTable($tableName);
            $result[] = $this->getTableStatus($unprefixed);
        }

        $result[] = $this->getTableStatus(Common::prefixTable('archive_numeric_2012_01'));
        $result[] = $this->getTableStatus(Common::prefixTable('archive_blob_2012_01'));

        $result[] = $this->getTableStatus(Common::prefixTable('archive_numeric_2012_02'));
        $result[] = $this->getTableStatus(Common::prefixTable('archive_blob_2012_02'));

        $result[] = $this->getTableStatus(Common::prefixTable('archive_numeric_2012_03'));
        $result[] = $this->getTableStatus(Common::prefixTable('archive_blob_2012_03'));

        $result[] = $this->getTableStatus(Common::prefixTable('archive_numeric_2012_04'));
        $result[] = $this->getTableStatus(Common::prefixTable('archive_blob_2012_04'));

        return $result;
    }

    public function getTableNameKey($tableName)
    {
        $result = Common::unprefixTable($tableName);
        if (strpos($tableName, "archive_numeric") !== false) {
            $result = "archive_numeric";
        } elseif (strpos($tableName, "archive_blob") !== false) {
            $result = "archive_blob";
        }
        return $result;
    }

    public function getRowCountsByArchiveName($tableName, $extraCols)
    {
        if (strpos($tableName, "achive_numeric") !== false) {
            return self::$numericRowCountsByArchiveName;
        } else {
            return self::$blobRowCountsByArchiveName;
        }
    }

    public function getColumnsFromTable($tableName)
    {
        return array(
            array('Field' => 'field1', 'Type' => 'datetime'),
            array('Field' => 'field2', 'Type' => 'int(11) unsigned'),
            array('Field' => 'field3', 'Type' => 'varchar(10)'),
            array('Field' => 'field4', 'Type' => 'varchar(24)'),
            array('Field' => 'field5', 'Type' => 'char(32)'),
            array('Field' => 'field6', 'Type' => 'timestamp'),
        );
    }
}
