<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\AutoLogImporter;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;

class Dao
{
    /**
     * @var Db
     */
    private $db;

    private $tableName = 'auto_log_importer';
    private $tableNamePrefixed;

    public function __construct()
    {
        $this->db = Db::get();
        $this->tableNamePrefixed = Common::prefixTable($this->tableName);
    }

    public function getAllImportedFiles()
    {
        return Db::fetchAll('SELECT * FROM ' . $this->tableNamePrefixed . ' WHERE end_date is not null');
    }

    public function getAllImportingFiles()
    {
        return Db::fetchAll('SELECT * FROM ' . $this->tableNamePrefixed . ' WHERE end_date is null');
    }

    public function isAlreadyImported($fileHash)
    {
        return (bool) $this->db->fetchOne("SELECT 1 FROM " . $this->tableNamePrefixed . "
                                           WHERE `file_hash` = ? LIMIT 1", array($fileHash));
    }

    public function startLogImport($logFile, $fileHash, $fileSize, $numLogLines, $startDateTime)
    {
        $this->db->insert($this->tableNamePrefixed, array(
            'file' => $logFile,
            'file_hash' => $fileHash,
            'file_size' => $fileSize,
            'num_log_lines' => $numLogLines,
            'start_date' => $startDateTime,
        ));

        return (int) $this->db->lastInsertId();
    }

    public function setImportFinished($id, $command, $output, $exitCode, $duration, $endDateTime)
    {
        // we cannot store unlimited characters in TEXT field
        if ($posSummary = strpos($output, 'Logs import summary')) {
            // if it contains a summary, only store the actual summary
            $output = substr($output, strpos($output, 'Logs import summary'));
        } elseif (strlen($output) > 10000) {
            // only store last 10k characters
            $output = substr($output, strlen($output) - 10000);
        }

        $this->db->update($this->tableNamePrefixed, array(
            'output' => $output,
            'command' => $command,
            'exit_code' => $exitCode,
            'end_date' => $endDateTime,
            'duration' => $duration,
        ), 'idlogimport = ' . (int) $id);
    }

    public function install()
    {
        $table = "`idlogimport` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                  `start_date` datetime NOT NULL ,
                  `end_date` datetime NULL ,
                  `duration` MEDIUMINT UNSIGNED NULL ,
                  `file` TEXT NOT NULL ,
                  `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
                  `file_hash` VARCHAR(32) NOT NULL ,
                  `num_log_lines` INT UNSIGNED NOT NULL DEFAULT 0,
                  `command` TEXT NULL,
                  `output` TEXT NULL,
                  `exit_code` TINYINT UNSIGNED NULL,
                  UNIQUE KEY uniq_hash(file_hash)";

        DbHelper::createTable($this->tableName, $table);
    }

    public function uninstall()
    {
        Db::dropTables(array($this->tableNamePrefixed));
    }

}
