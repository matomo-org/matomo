<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Columns;

use Piwik\Columns\Discriminator;
use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Events\Actions\ActionEvent;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class EventName extends ActionDimension
{
    protected $columnName = 'idaction_name';
    protected $type = self::TYPE_TEXT;
    protected $category = 'Events_Events';
    protected $sqlFilter = '\Piwik\Tracker\TableLogAction::getIdActionFromSegment';
    protected $segmentName = 'eventName';
    protected $suggestedValuesApi = 'Events.getName';

    protected $nameSingular = 'Events_EventName';
    protected $namePlural = 'Events_EventNames';

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', $this->getActionId());
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

        $eventName = $action->getEventName();
        $eventName = trim($eventName);

        if (strlen($eventName) > 0) {
            return $eventName;
        }

        return false;
    }
}