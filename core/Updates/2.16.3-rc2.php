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
 * Mark the upgrade as major as the field  visit_entry_idaction_url was updated in  https://github.com/piwik/piwik/pull/10510
 */
class Updates_2_16_3_rc2 extends PiwikUpdates
{

    public static function isMajorUpdate()
    {
        return true;
    }

}
