<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Columns;

use Piwik\Piwik;
use Piwik\Plugin\VisitDimension;
use Piwik\Plugin\Segment;

class City extends VisitDimension
{    
    protected $fieldName = 'location_city';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('city');
        $segment->setName('City');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('UserCountry_City');
    }
}