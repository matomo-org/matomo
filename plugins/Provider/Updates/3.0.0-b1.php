<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Provider\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

/**
 * Update for version 3.0.0-b1.
 */
class Updates_3_0_0_b1 extends PiwikUpdates
{

    public function getMigrationQueries(Updater $updater)
    {
        $errorCodesToIgnore = array(1060);
        $tableName = Common::prefixTable('log_visit');
        $updateSql = "ALTER TABLE `" . $tableName . "` CHANGE `location_provider` `location_provider` VARCHAR(200) NULL";

        return array(
             $updateSql => $errorCodesToIgnore
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
