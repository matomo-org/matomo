<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Updates;

use Piwik\Date;
use Piwik\Plugins\ScheduledReports\API as ScheduledReportsAPI;
use Piwik\Plugins\ScheduledReports\Model as ScheduledReportsModel;
use Piwik\Site;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

/**
 * Update for version 2.16.5.
 *
 * Update existing scheduled reports to use UTC timezone for hour setting
 */
class Updates_2_16_5 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        $model      = new ScheduledReportsModel();
        $allReports = ScheduledReportsAPI::getInstance()->getReports();
        foreach ($allReports as $report) {
            $update = array('hour' => $this->adjustTimezoneBySite($report['hour'], $report['idsite']));
            $model->updateReport($report['idreport'], $update);
        }
    }

    protected function adjustTimezoneBySite($hour, $idSite)
    {
        $timezone           = Site::getTimezoneFor($idSite);
        $timeZoneDifference = -ceil(Date::getUtcOffset($timezone)/3600);
        return (24 + $hour + $timeZoneDifference) % 24;
    }
}
