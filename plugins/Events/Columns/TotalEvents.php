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
use Piwik\Plugin\VisitDimension;
use Piwik\Plugins\Events\Segment;

class TotalEvents extends VisitDimension
{    
    protected $fieldName = 'visit_total_events';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('events');
        $segment->setName('Events_TotalEvents');
        $segment->setAcceptValues('To select all visits who triggered an Event, use: &segment=events>0');
        $segment->setCategory(Piwik::translate('General_Visit'));
        $segment->setType(Segment::TYPE_METRIC);
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Events_EventName');
    }
}