<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Events\Columns;

use Piwik\Columns\Discriminator;
use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Events\Actions\ActionEvent;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\TableLogAction;

class EventCategory extends ActionDimension
{
    protected $columnName = 'idaction_event_category';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';
    protected $type = self::TYPE_TEXT;
    protected $category = 'Events_Events';
    protected $segmentName = 'eventCategory';
    protected $suggestedValuesApi = 'Events.getCategory';
    protected $nameSingular = 'Events_EventCategory';
    protected $namePlural = 'Events_EventCategories';
    protected $sqlFilter = [TableLogAction::class, 'getOptimizedIdActionSqlMatch'];

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
        return Action::TYPE_EVENT_CATEGORY;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        if (!($action instanceof ActionEvent)) {
            return false;
        }

        $eventCategory = $action->getEventCategory();
        $eventCategory = trim($eventCategory);

        if (strlen($eventCategory) > 0) {
            return $eventCategory;
        }

        throw new InvalidRequestParameterException('Param `e_c` must not be empty or filled with whitespaces');
    }
}
