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

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\FileSynchronizer\SyncFiles\Copy;
use Piwik\Plugins\FileSynchronizer\SyncFiles\File;
use Piwik\Plugins\FileSynchronizer\SyncFiles\FileList;

class SyncFiles
{
    /**
     * @var Dao
     */
    private $dao;

    /**
     * @var File
     */
    private $file;

    /**
     * @var string
     */
    private $copyCommandTemplate;

    /**
     * @var string[]
     */
    private $sourceFiles;

    /**
     * @var string
     */
    private $targetDirectory;

    /**
     * @var string
     */
    private $targetFilenameTemplate;

    public function __construct(Dao $dao, File $file, Settings $settings, FileList $fileList)
    {
        $this->dao = $dao;
        $this->file = $file;
        $this->copyCommandTemplate = $settings->copyCommandTemplate->getValue();
        $this->targetDirectory = $settings->targetDirectory->getValue();
        $this->targetFilenameTemplate = $settings->targetFilenameTemplate->getValue();
        $this->sourceFiles = $fileList->findFilesToSync();
    }

    public function getFilesThatCanBeSynced()
    {
        $files = array();

        foreach ($this->sourceFiles as $sourceFile) {
            $hash = $this->file->getHash($sourceFile);

            if (!$this->dao->isSynced($hash)) {
                $files[$hash] = $sourceFile;
            }
        }

        return $files;
    }

    public function sync()
    {
        $copy = StaticContainer::get('Piwik\Plugins\FileSynchronizer\SyncFiles\Copy');

        foreach ($this->sourceFiles as $sourceFile) {
            $hash = $this->file->getHash($sourceFile);

            if (!$this->dao->isSynced($hash)) {
                $this->copyFile($copy, $sourceFile, $hash);
            }

            if ($this->dao->isSynced($hash) && !$this->dao->isHashFileCreated($hash)) {
                $this->copyHashFile($copy, $sourceFile, $hash);
            }
        }
    }

    private function copyHashFile(Copy $copy, $sourceFile, $fileHash)
    {
        $targetFile = $this->file->buildTargetFileName($sourceFile, $this->targetFilenameTemplate);
        $hashFile   = $targetFile . '.hash';

        $result = $copy->copyContent($hashFile, $fileHash, $this->targetDirectory, $this->copyCommandTemplate);

        if ($result->getExitCode() == 0) {
            $this->dao->markHashFileCreated($fileHash);
        }
    }

    private function copyFile(Copy $copy, $sourceFile, $fileHash)
    {
        $fileSize   = $this->file->getSize($sourceFile);
        $targetFile = $this->file->buildTargetFilePath($sourceFile, $this->targetDirectory, $this->targetFilenameTemplate);

        $id = $this->dao->logFileSyncStart($sourceFile, $targetFile, $fileHash, $fileSize, Date::now()->getDatetime());

        $startTime = microtime(true);
        $result = $copy->copy($sourceFile, $targetFile, $this->copyCommandTemplate);

        $durationInMs = (microtime(true) - $startTime) * 1000;

        $this->dao->logFileSyncFinished($id, $result->getCommand(), $result->getOutput(), $result->getExitCode(),
                                        $durationInMs, Date::now()->getDatetime());
    }
}
