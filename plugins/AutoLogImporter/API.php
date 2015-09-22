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

use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Piwik;

/**
 * API for plugin AutoLogImporter
 *
 * @method static \Piwik\Plugins\AutoLogImporter\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Get all imported files no matter if the import was successful or not.
     *
     * Latest imported files are listed first.
     * @return array
     */
    public function getAllImportedFiles()
    {
        Piwik::checkUserHasSuperUserAccess();

        $dao = new Dao();
        $files = $dao->getAllImportedFiles();

        usort($files, function ($a, $b){
            return $a['idlogimport'] > $b['idlogimport'] ? -1 : 1;
        });

        return $files;
    }

    /**
     * Get all files that are currently in process importing logs.
     *
     * Latest importing files are listed first.
     * @return array
     */
    public function getAllImportingFiles()
    {
        Piwik::checkUserHasSuperUserAccess();

        $dao = new Dao();
        $files = $dao->getAllImportingFiles();

        usort($files, function ($a, $b){
            return $a['idlogimport'] > $b['idlogimport'] ? -1 : 1;
        });

        return $files;
    }

    /**
     * Get a list of files that will be imported as soon as the task runs the next time.
     *
     * @return string[]
     */
    public function getFilesThatCanBeImported()
    {
        Piwik::checkUserHasSuperUserAccess();

        $importer = StaticContainer::get('Piwik\Plugins\AutoLogImporter\LogImporter');
        return $importer->getFilesThatCanBeImported();
    }


    /**
     * Get a list of files that have a hash file but the verify hash doesn't match. This usually indicates a not
     * sucessfully copied log file.
     *
     * @return array
     */
    public function getFilesHavingInvalidHash()
    {
        Piwik::checkUserHasSuperUserAccess();

        $logFileList = StaticContainer::get('Piwik\Plugins\AutoLogImporter\LogImporter\LogFileList');
        return $logFileList->findFilesHavingWrongHashFile();
    }

}
