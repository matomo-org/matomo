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
use Piwik\Tracker\Visitor;

class VisitTotalInteractions extends VisitDimension
{
    protected $columnName = 'visit_total_interactions';
    protected $columnType = 'SMALLINT UNSIGNED DEFAULT 0';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setType(Segment::TYPE_METRIC);
        $segment->setSegment('interactions');
        $segment->setName('General_NbInteractions');
        $segment->setAcceptedValues('Any positive integer');
        $segment->setSuggestedValuesCallback(function ($idSite, $maxValuesToReturn) {
            $positions = range(1,50);

            return array_slice($positions, 0, $maxValuesToReturn);
        });
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
        if (self::shouldCountInteraction($action)) {
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
        $request->setMetadata('Actions', $this->columnName, $visitor->getVisitorColumn($this->columnName));

        if (self::shouldCountInteraction($action)) {
            return $this->columnName . ' + 1';
        }

        return false;
    }

    /**
     * @param Request $request
     * @return int
     */
    public static function getCurrentInteractionPosition($request)
    {
        $position = $request->getMetadata('Actions', 'visit_total_interactions');

        $position = $position + 1;

        // Remove this in Piwik 4
        return min($position, 32765);
    }

    /**
     * @param Action|null $action
     * @return bool
     */
    public static function shouldCountInteraction($action)
    {
        if (empty($action)) {
            return false;
        }

        $idActionUrl = $action->getIdActionUrlForEntryAndExitIds();

        if ($idActionUrl !== false) {
            return true;
        }

        $actionType = $action->getActionType();
        $types = array(Action::TYPE_SITE_SEARCH);

        if (in_array($actionType, $types)) {
            return true;
        }

        return false;
    }

}