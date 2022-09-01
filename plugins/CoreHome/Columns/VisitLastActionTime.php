<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Common;
use Piwik\Date;
use Piwik\Period;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Site;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Metrics\Formatter;

require_once PIWIK_INCLUDE_PATH . '/plugins/VisitTime/functions.php';

/**
 * This dimension holds the best guess for a visit's end time. It is set the last action
 * time for each visit. `ping=1` requests can be sent to update the dimension value so
 * it can be a more accurate guess of the time the visitor spent on the site.
 *
 * Note: though it is named 'visit last action time' it actually refers to the visit's last action's
 * end time.
 */
class VisitLastActionTime extends VisitDimension
{
    protected $columnName = 'visit_last_action_time';
    protected $type = self::TYPE_DATETIME;
    protected $nameSingular = 'VisitTime_ColumnVisitEndSiteHour';
    protected $sqlSegment = 'HOUR(log_visit.visit_last_action_time)';
    protected $segmentName = 'visitServerHour';
    protected $acceptValues = '0, 1, 2, 3, ..., 20, 21, 22, 23';

    /**
     * Converts the hour to the hour depending on the configured site's timezone.
     * Only works correct if a date/period is present in the request. Otherwise the result may vary depending on the
     * day of the year as it can't know about daylight savings for example. Also if the currently selected date range
     * includes multiple days with daylight savings and some without, then it might not be 100% correct as we are
     * only looking at the start of the time.
     * @param int $hour
     * @param int $idSite
     * @return int
     * @throws \Exception
     */
    public static function convertHourToHourInSiteTimezone($hour, $idSite)
    {
        $date = Date::now();
        $dateString = Common::getRequestVar('date', '', 'string');
        $periodString = Common::getRequestVar('period', '', 'string');
        if (!empty($dateString) && !empty($periodString)) {
            try {
                $date = Period\Factory::build($periodString, $dateString)->getDateStart();
            } catch (\Exception $e) {
                // ignore any error eg if values are wrong...
            }
        }
        $timezone = Site::getTimezoneFor($idSite);
        $datetime = $date->toString() . ' ' . $hour . ':00:00';
        $hourInTz = (int)Date::factory($datetime, $timezone)->toString('H');
        return $hourInTz;
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        $hourInTz = self::convertHourToHourInSiteTimezone($value, $idSite);

        return \Piwik\Plugins\VisitTime\getTimeLabel($hourInTz);
    }

    // we do not install or define column definition here as we need to create this column when installing as there is
    // an index on it. Currently we do not define the index here... although we could overwrite the install() method
    // and add column 'visit_last_action_time' and add index. Problem is there is also an index
    // INDEX(idsite, config_id, visit_last_action_time) and we maybe not be sure whether idsite already exists at
    // installing point (we do not know whether idsite column will be added first).

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return Date::getDatetimeFromTimestamp($request->getCurrentTimestamp());
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        if ($request->getParam('ping') == 1) {
            return false;
        }

        $originalVisitLastActionTime = $visitor->getPreviousVisitColumn('visit_last_action_time');

        if (!empty($originalVisitLastActionTime)
            && Date::factory($originalVisitLastActionTime)->getTimestamp() > $request->getCurrentTimestamp()) {
            // make sure to not set visit_last_action_time to an earlier time eg if tracking requests aren't sent in order
            return $originalVisitLastActionTime;
        }

        return $this->onNewVisit($request, $visitor, $action);
    }
}