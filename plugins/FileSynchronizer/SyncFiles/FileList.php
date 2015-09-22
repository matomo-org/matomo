<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\FileSynchronizer\SyncFiles;

use Piwik\Db;
use Piwik\Filesystem;
use Piwik\Plugins\FileSynchronizer\Settings;

class FileList
{
    /**
     * @var Settings
     */
    private $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return string[]
     */
    public function findFilesToSync()
    {
        $path    = $this->settings->sourceDirectory->getValue();
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
}
