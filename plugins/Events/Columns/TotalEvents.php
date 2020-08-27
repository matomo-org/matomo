<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Columns;

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class TotalEvents extends VisitDimension
{
    protected $columnName = 'visit_total_events';
    protected $columnType = 'INT(11) UNSIGNED NULL';
    protected $segmentName = 'events';
    protected $nameSingular = 'Events_Events';
    protected $acceptValues = 'To select all visits who triggered an Event, use: &segment=events>0';

    protected $type = self::TYPE_NUMBER;

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
