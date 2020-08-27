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
use Piwik\Tracker\Action;

class EventUrl extends ActionDimension
{
    protected $columnName = 'idaction_url';
    protected $segmentName = 'eventUrl';
    protected $nameSingular = 'Events_EventUrl';
    protected $namePlural = 'Events_EventUrls';
    protected $type = self::TYPE_URL;
    protected $acceptValues = 'The URL must be URL encoded, for example: http%3A%2F%2Fexample.com%2Fpath%2Fpage%3Fquery';
    protected $category = 'Events_Events';
    protected $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', Action::TYPE_EVENT);
    }

}

