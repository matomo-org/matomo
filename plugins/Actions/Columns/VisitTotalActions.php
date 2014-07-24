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

class VisitTotalActions extends VisitDimension
{
    protected $columnName = 'visit_total_actions';
    protected $columnType = 'SMALLINT(5) UNSIGNED NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setType(Segment::TYPE_METRIC);
        $segment->setSegment('actions');
        $segment->setName('General_NbActions');
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
        $actionType = false;
        if ($action) {
            $actionType = $action->getActionType();
        }

        $actions = array(
            Action::TYPE_PAGE_URL,
            Action::TYPE_DOWNLOAD,
            Action::TYPE_OUTLINK,
            Action::TYPE_SITE_SEARCH,
            Action::TYPE_EVENT
        );

        // if visit starts with something else (e.g. ecommerce order), don't record as an action
        if (in_array($actionType, $actions)) {
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
        if (!$action) {
            return false;
        }

        $increment = 'visit_total_actions + 1';

        $idActionUrl = $action->getIdActionUrlForEntryAndExitIds();

        if ($idActionUrl !== false) {
            return $increment;
        }

        $actionType = $action->getActionType();

        if (in_array($actionType, array(Action::TYPE_SITE_SEARCH, Action::TYPE_EVENT))) {
            return $increment;
        }

        return false;
    }

}