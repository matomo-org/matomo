<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\ActionDimension;

class InteractionPosition extends ActionDimension
{
    protected $columnName = 'interaction_position';
    protected $columnType = 'SMALLINT UNSIGNED DEFAULT NULL';

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action $action
     *
     * @return mixed|false
     */
    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        $shouldCount = VisitTotalInteractions::shouldCountInteraction($action);

        if ($shouldCount && $visitor->isNewVisit()) {
            return 1;
        } else if ($shouldCount) {
            return VisitTotalInteractions::getCurrentInteractionPosition($request);
        }

        return false;
    }

    public function getName()
    {
        return Piwik::translate('Actions_ColumnInteractionPosition');
    }

}