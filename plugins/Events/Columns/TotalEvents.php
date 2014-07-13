<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Columns;

use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class TotalEvents extends VisitDimension
{
    protected $columnName = 'visit_total_events';
    protected $columnType = 'SMALLINT(5) UNSIGNED NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('events');
        $segment->setName('Events_TotalEvents');
        $segment->setAcceptedValues('To select all visits who triggered an Event, use: &segment=events>0');
        $segment->setCategory('General_Visit');
        $segment->setType(Segment::TYPE_METRIC);
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Events_EventName');
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        if ($this->isEventAction($action)) {
            return 1;
        }

        return 0;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        if ($this->isEventAction($action)) {
            return 'visit_total_events + 1';
        }

        return false;
    }

    /**
     * @param Action|null $action
     * @return bool
     */
    private function isEventAction($action)
    {
        return ($action && $action->getActionType() == Action::TYPE_EVENT);
    }
}