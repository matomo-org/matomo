<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitorInterest\Columns;

use Piwik\Common;
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class VisitorSecondsSinceLast extends VisitDimension
{
    const COLUMN_TYPE = 'INT(11) UNSIGNED NULL';

    protected $columnName = 'visitor_seconds_since_last';
    protected $columnType = self::COLUMN_TYPE;
    protected $type = self::TYPE_NUMBER;
    protected $segmentName = 'secondsSinceLastVisit';
    protected $nameSingular = 'General_SecondsSinceLastVisit';

    public function getName()
    {
        return Piwik::translate('General_SecondsSinceLastVisit'); // TODO why was this overridden?
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        if (!$visitor->isVisitorKnown()) {
            return 0;
        }

        $currentTimestamp = $request->getCurrentTimestamp();
        $previousVisitLastActionTime = strtotime($visitor->getPreviousVisitColumn('visit_first_action_time'));

        if (empty($previousVisitLastActionTime)) {
            Common::printDebug("Found empty visit_last_action_time for last visit of known visitor, this is unexpected.");
            return 0;
        }

        $secondsSinceLast = $currentTimestamp - $previousVisitLastActionTime;
        return $secondsSinceLast;
    }

    protected function configureSegments()
    {
        parent::configureSegments();

        // TODO: is this right?
        $segment = new Segment();
        $segment->setSegment('daysSinceLastVisit');
        $segment->setName('General_DaysSinceLastVisit');
        $segment->setCategory('General_Visitors');
        $segment->setSqlFilterValue(function ($value) {
            return $value * 86400;
        });
        $this->addSegment($segment);
    }
}