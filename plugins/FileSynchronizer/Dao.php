<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\FileSynchronizer;

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

    private $tableName = 'file_synchronizer';
    private $tableNamePrefixed;

    public function __construct()
    {
        $this->db = Db::get();
        $this->tableNamePrefixed = Common::prefixTable($this->tableName);
    }

    public function getAllSyncedFiles()
    {
        return Db::fetchAll('SELECT * FROM ' . $this->tableNamePrefixed . ' WHERE end_date is not null');
    }

    public function getAllSyncingFiles()
    {
        return Db::fetchAll('SELECT * FROM ' . $this->tableNamePrefixed . ' WHERE end_date is null');
    }

    public function isSynced($fileHash)
    {
        // "end_date is null" => probably in process by another server
        // "exit_code = 0" => file was already imported successfully
        return (bool) $this->db->fetchOne("SELECT 1 FROM " . $this->tableNamePrefixed . "
                                           WHERE `file_hash` = ? and (end_date is null or exit_code = 0) LIMIT 1", array($fileHash));
    }

    public function isHashFileCreated($fileHash)
    {
        return (bool) $this->db->fetchOne("SELECT 1 FROM " . $this->tableNamePrefixed . "
                                           WHERE `file_hash` = ? and hash_file_created = 1 LIMIT 1", array($fileHash));
    }

    public function markHashFileCreated($fileHash)
    {
        $this->db->update($this->tableNamePrefixed, array(
            'hash_file_created' => 1,
        ), '`file_hash` = "' . $fileHash . '"');
    }

    public function logFileSyncStart($source, $target, $fileHash, $fileSize, $startDate)
    {
        $this->db->insert($this->tableNamePrefixed, array(
            'source' => $source,
            'target' => $target,
            'file_hash' => $fileHash,
            'file_size' => $fileSize,
            'start_date' => $startDate,
        ));

        return (int) $this->db->lastInsertId();
    }

    public function logFileSyncFinished($id, $command, $output, $exitCode, $duration, $endDate)
    {
        $this->db->update($this->tableNamePrefixed, array(
            'output' => $output,
            'command' => $command,
            'exit_code' => $exitCode,
            'end_date' => $endDate,
            'duration_in_ms' => $duration,
        ), 'idfilesync = ' . (int) $id);
    }

    public function install()
    {
        $table = "`idfilesync` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                  `start_date` datetime NOT NULL ,
                  `end_date` datetime NULL ,
                  `duration_in_ms` INT UNSIGNED NULL ,
                  `source` TEXT NOT NULL ,
                  `target` TEXT NOT NULL ,
                  `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
                  `file_hash` VARCHAR(32) NOT NULL ,
                  `hash_file_created` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                  `command` TEXT NULL,
                  `output` TEXT NULL ,
                  `exit_code` TINYINT UNSIGNED NULL";

        DbHelper::createTable($this->tableName, $table);
    }

    public function uninstall()
    {
        Db::dropTables(array($this->tableNamePrefixed));
    }

}
