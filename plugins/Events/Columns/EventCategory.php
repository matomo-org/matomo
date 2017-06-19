<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Columns;

use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Events\Actions\ActionEvent;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class EventCategory extends ActionDimension
{
    protected $columnName = 'idaction_event_category';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';
    protected $type = self::TYPE_JOIN_ID;
    protected $category = 'Events_Events';
    protected $sqlFilter = '\Piwik\Tracker\TableLogAction::getIdActionFromSegment';
    protected $segmentName = 'eventCategory';
    protected $nameSingular = 'Events_EventCategory';

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function getActionId()
    {
        return Action::TYPE_EVENT_CATEGORY;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        if (!($action instanceof ActionEvent)) {
            return false;
        }

        $eventCategory = $request->getParam('e_c');
        $eventCategory = trim($eventCategory);

        if (strlen($eventCategory) > 0) {
            return $eventCategory;
        }

        return false;
    }
}