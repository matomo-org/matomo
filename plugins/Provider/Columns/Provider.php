<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Provider\Columns;

use Piwik\Piwik;
use Piwik\Plugin\VisitDimension;
use Piwik\Plugin\Segment;

class Provider extends VisitDimension
{    
    protected $fieldName = 'location_provider';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('provider');
        $segment->setCategory('Visit Location');
        $segment->setName('Provider_ColumnProvider');
        $segment->setAcceptValues('comcast.net, proxad.net, etc.');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Provider_ColumnProvider');
    }
}