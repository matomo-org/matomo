<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Plugins\ScheduledReports\API as ScheduledReportsAPI;
use Piwik\Plugins\ScheduledReports\Model as ScheduledReportsModel;
use Piwik\Site;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

/**
 * Update for version 2.16.3-b1.
 *
 * Update existing scheduled reports to use UTC timezone for hour setting
 */
class Updates_2_16_3_b1 extends PiwikUpdates
{

    public function doUpdate(Updater $updater)
    {
        $model      = new ScheduledReportsModel();
        $allReports = ScheduledReportsAPI::getInstance()->getReports();

        foreach ($allReports as $report) {
            $timezone     = Site::getTimezoneFor($report['idsite']);
            $dateTimeZone = new \DateTimeZone($timezone);

            $timeZoneDifference = -ceil($dateTimeZone->getOffset(new \DateTime()) / 3600);
            $report['hour'] += $timeZoneDifference;

            $model->updateReport($report['idreport'], $report);
        }
    }
}
