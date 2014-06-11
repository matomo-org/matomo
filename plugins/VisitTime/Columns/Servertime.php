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
use Piwik\Plugin\Segment;

class Servertime extends VisitDimension
{    
    protected $fieldName = 'HOUR(visit_last_action_time)';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('visitServerHour');
        $segment->setName('Server time');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('VisitTime_ColumnServerTime');
    }
}