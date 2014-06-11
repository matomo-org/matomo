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
use Piwik\Plugin\Segment;

class EventName extends ActionDimension
{    
    protected $fieldName = 'idaction_name';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('eventName');
        $segment->setName('Events_EventName');
        $segment->setSqlFilter('\Piwik\Tracker\TableLogAction::getIdActionFromSegment');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Events_EventName');
    }
}