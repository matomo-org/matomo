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

class Localtime extends VisitDimension
{    
    protected $fieldName = 'HOUR(visitor_localtime)';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('visitLocalHour');
        $segment->setName('VisitTime_ColumnLocalTime');
        $segment->setSqlSegment('HOUR(log_visit.visitor_localtime)');
        $segment->setAcceptValues('0, 1, 2, 3, ..., 20, 21, 22, 23');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('VisitTime_ColumnLocalTime');
    }
}