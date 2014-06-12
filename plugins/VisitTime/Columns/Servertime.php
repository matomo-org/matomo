<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\Columns;

use Piwik\Piwik;
use Piwik\Plugin\VisitDimension;
use Piwik\Plugins\VisitTime\Segment;

class Servertime extends VisitDimension
{    
    protected $fieldName = 'visit_last_action_time';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('visitServerHour');
        $segment->setName('VisitTime_ColumnServerTime');
        $segment->setSqlSegment('HOUR(log_visit.visit_last_action_time)');
        $segment->setAcceptValues('0, 1, 2, 3, ..., 20, 21, 22, 23');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('VisitTime_ColumnServerTime');
    }
}