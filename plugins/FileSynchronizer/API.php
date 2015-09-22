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
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Piwik;

/**
 * API for plugin FileSynchronizer
 *
 * @method static \Piwik\Plugins\FileSynchronizer\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Get all synced files whether they were successful or not.
     *
     * Latest synced files are listed first.
     * @return array
     */
    public function getAllSyncedFiles()
    {
        Piwik::checkUserHasSuperUserAccess();

        $dao   = new Dao();
        $files = $dao->getAllSyncedFiles();

        usort($files, function ($a, $b){
            return $a['idfilesync'] > $b['idfilesync'] ? -1 : 1;
        });

        return $files;
    }

    /**
     * Get all files that are currently syncing.
     *
     * @return array
     */
    public function getAllSyncingFiles()
    {
        Piwik::checkUserHasSuperUserAccess();

        $dao   = new Dao();
        $files = $dao->getAllSyncingFiles();

        return $files;
    }

    /**
     * Get a list of all files that can be synced and will be synced as soon as the next tasks run.
     *
     * @return array
     */
    public function getFilesThatCanBeSynced()
    {
        Piwik::checkUserHasSuperUserAccess();

        $syncFiles = StaticContainer::get('Piwik\Plugins\FileSynchronizer\SyncFiles');
        return $syncFiles->getFilesThatCanBeSynced();
    }

}
