<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Columns;

use Piwik\Piwik;
use Piwik\Plugin\ActionDimension;
use Piwik\Plugins\Events\Segment;

class EventCategory extends ActionDimension
{    
    protected $fieldName = 'idaction_event_category';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('eventCategory');
        $segment->setName('Events_EventCategory');
        $segment->setCategory('Events_Events');
        $segment->setSqlFilter('\Piwik\Tracker\TableLogAction::getIdActionFromSegment');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Events_EventCategory');
    }
}