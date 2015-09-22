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

use Piwik\Date;
use Piwik\Plugins\AutoLogImporter\LogImporter\Import;
use Piwik\Plugins\AutoLogImporter\LogImporter\LogFileList;
use Piwik\Plugins\AutoLogImporter\LogImporter\File;

class LogImporter
{
    /**
     * @var Dao
     */
    private $dao;

    /**
     * @var LogFileList
     */
    private $logFileList;

    /**
     * @var File
     */
    private $file;

    /**
     * @var Import
     */
    private $import;

    public function __construct(Dao $dao, LogFileList $logFileList, File $file, Import $import)
    {
        $this->dao = $dao;
        $this->logFileList = $logFileList;
        $this->file = $file;
        $this->import = $import;
    }

    public function getFilesThatCanBeImported()
    {
        $files = array();

        foreach ($this->logFileList->findLogFiles() as $logFile) {
            $hash = $this->file->getHash($logFile);

            if (!$this->dao->isAlreadyImported($hash)
                && $this->file->wasFileSuccessfullyCopiedViaFileSynchronizerPlugin($logFile, $hash)) {
                $files[$hash] = $logFile;
            }
        }

        return $files;
    }

    public function importFiles()
    {
        $files = $this->getFilesThatCanBeImported();

        foreach ($files as $hash => $logFile) {
            $this->importFile($logFile, $hash);
        }
    }

    private function importFile($logFile, $hash)
    {
        $size  = $this->file->getSize($logFile);
        $lines = $this->file->getNumberOfLines($logFile);
        $id    = $this->dao->startLogImport($logFile, $hash, $size, $lines, $this->getCurrentDateTime());

        $start    = time();
        $result   = $this->import->import($logFile);
        $duration = time() - $start;

        $this->dao->setImportFinished($id, $result->getCommand(), $result->getOutput(), $result->getExitCode(), $duration, $this->getCurrentDateTime());
    }

    private function getCurrentDateTime()
    {
        return Date::now()->getDatetime();
    }
}
