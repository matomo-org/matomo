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
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Events\Actions\ActionEvent;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class EventAction extends ActionDimension
{
    protected $columnName = 'idaction_event_action';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';
    protected $type = self::TYPE_TEXT;
    protected $segmentName = 'eventAction';
    protected $nameSingular = 'Events_EventAction';
    protected $namePlural = 'Events_EventActions';
    protected $suggestedValuesApi = 'Events.getAction';
    protected $category = 'Events_Events';
    protected $sqlFilter = '\Piwik\Tracker\TableLogAction::getIdActionFromSegment';

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
        return Action::TYPE_EVENT_ACTION;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        if (!($action instanceof ActionEvent)) {
            return false;
        }

        $eventAction = $action->getEventAction();
        $eventAction = trim($eventAction);

        if (strlen($eventAction) > 0) {
            return $eventAction;
        }

        throw new InvalidRequestParameterException('Param `e_a` must not be empty or filled with whitespaces');
    }
}