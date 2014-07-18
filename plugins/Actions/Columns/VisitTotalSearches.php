<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CoreHome\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker;
use Piwik\Tracker\Visitor;

class VisitTotalSearches extends VisitDimension
{
    protected $columnName = 'visit_total_searches';
    protected $columnType = 'SMALLINT(5) UNSIGNED NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setType(Segment::TYPE_METRIC);
        $segment->setSegment('searches');
        $segment->setName('General_NbSearches');
        $segment->setAcceptedValues('To select all visits who used internal Site Search, use: &segment=searches>0');
        $this->addSegment($segment);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        if ($this->isSiteSearchAction($action)) {
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
        if ($this->isSiteSearchAction($action)) {
            return 'visit_total_searches + 1';
        }

        return false;
    }

    /**
     * @param Action|null $action
     * @return bool
     */
    private function isSiteSearchAction($action)
    {
        return ($action && $action->getActionType() == Action::TYPE_SITE_SEARCH);
    }

}