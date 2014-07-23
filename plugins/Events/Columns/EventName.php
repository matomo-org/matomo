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

class EventName extends ActionDimension
{
    protected $columnName = 'idaction_name';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('eventName');
        $segment->setName('Events_EventName');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Events_EventName');
    }

    public function getActionId()
    {
        return Action::TYPE_EVENT_NAME;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        if (!($action instanceof ActionEvent)) {
            return false;
        }

        $eventName = $request->getParam('e_n');
        $eventName = trim($eventName);

        if (strlen($eventName) > 0) {
            return $eventName;
        }

        return false;
    }
}