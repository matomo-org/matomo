<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\AutoLogImporter\LogImporter;

use Piwik\Db;
use Piwik\Filesystem;
use Piwik\Plugins\AutoLogImporter\Settings;

class LogFileList
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var File
     */
    private $file;

    public function __construct(Settings $settings, File $file)
    {
        $this->settings = $settings;
        $this->file = $file;
    }

    /**
     * @return string[]
     */
    public function findLogFiles()
    {
        $path = $this->settings->logFilesPath->getValue();
        $pattern = $this->settings->filePattern->getValue();

        if (!$this->settings->enabled->getValue()) {
            return array();
        }

        if (empty($path) || !file_exists($path) || !is_readable($path) || !is_dir($path)) {
            // case when plugin not configured yet
            return array();
        }

        if (empty($pattern)) {
            $pattern = '*';
        }

        return Filesystem::globr($path, $pattern);
    }

    public function findFilesHavingWrongHashFile()
    {
        $files = $this->findLogFiles();

        $wrong = array();
        foreach ($files as $file) {
            $hash = $this->file->getHash($file);

            if ($this->file->hasVerifyHash($file)
                && !$this->file->wasFileSuccessfullyCopiedViaFileSynchronizerPlugin($file, $hash)) {
                $wrong[] = array(
                    'file' => $file,
                    'hash' => $hash,
                    'verify_file' => $this->file->getPathToVerifyHashFile($file),
                    'verify_hash' => $this->file->getVerifyHash($file)
                );
            }
        }

        return $wrong;
    }
}
