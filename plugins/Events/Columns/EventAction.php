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
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Events\Segment;
use Piwik\Plugins\Events\Actions\ActionEvent;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class EventAction extends ActionDimension
{
    protected $columnName = 'idaction_event_action';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('eventAction');
        $segment->setName('Events_EventAction');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Events_EventAction');
    }

    public function getActionId()
    {
        return Action::TYPE_EVENT_ACTION;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        if (!($action instanceof ActionEvent)) {
            return false;
        }

        $eventAction = $request->getParam('e_a');
        $eventAction = trim($eventAction);

        if (strlen($eventAction) > 0) {
            return $eventAction;
        }

        return false;
    }
}