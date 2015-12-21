<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Config;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CoreHome\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class VisitTotalTime extends VisitDimension
{
    protected $columnName = 'visit_total_time';
    protected $columnType = 'SMALLINT(5) UNSIGNED NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('visitDuration');
        $segment->setName('General_ColumnVisitDuration');
        $segment->setType(Segment::TYPE_METRIC);
        $this->addSegment($segment);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $totalTime = Config::getInstance()->Tracker['default_time_one_page_visit'];
        $totalTime = $this->cleanupVisitTotalTime($totalTime);

        return $totalTime;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        $firstActionTime = $visitor->getVisitorColumn('visit_first_action_time');

        $totalTime = 1 + $request->getCurrentTimestamp() - $firstActionTime;
        $totalTime = $this->cleanupVisitTotalTime($totalTime);

        return $totalTime;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onConvertedVisit(Request $request, Visitor $visitor, $action)
    {
        if (!$visitor->isVisitorKnown()) {
            return false;
        }

        $totalTime = $visitor->getVisitorColumn('visit_total_time');

        // If a pageview and goal conversion in the same second, with previously a goal conversion recorded
        // the request would not "update" the row since all values are the same as previous
        // therefore the request below throws exception, instead we make sure the UPDATE will affect the row
        $totalTime = $totalTime + $request->getParam('idgoal');
        // +2 to offset idgoal=-1 and idgoal=0
        $totalTime = $totalTime + 2;

        return $this->cleanupVisitTotalTime($totalTime);
    }

    public function getRequiredVisitFields()
    {
        return array('visit_first_action_time');
    }

    private function cleanupVisitTotalTime($t)
    {
        $t = (int)$t;

        if ($t < 0) {
            $t = 0;
        }

        $smallintMysqlLimit = 65534;

        if ($t > $smallintMysqlLimit) {
            $t = $smallintMysqlLimit;
        }

        return $t;
    }

}