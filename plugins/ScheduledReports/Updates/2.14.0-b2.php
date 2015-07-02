<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ScheduledReports;

use Piwik\Common;
use Piwik\Db;
use Piwik\Updater;
use Piwik\Updates;

/**
 * Update for version 2.14.1.
 */
class Updates_2_14_1 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        $reportTable = Common::prefixTable('report');
        $updateSingleReportDescSql = "UPDATE " . $reportTable . " SET description = \"%s\" WHERE idreport = %s";

        $reports = Db::fetchAll("SELECT idreport, description FROM " . $reportTable);
        foreach ($reports as $report) {
            $newDescription = addslashes(Common::unsanitizeInputValue($report['description']));
            $sql = sprintf($updateSingleReportDescSql, $newDescription, $report['idreport']);

            $updater->executeSingleMigrationQuery($sql, false, __FILE__);
        }
    }
}
