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
use Piwik\Plugins\UserCountry\Segment;

class Longitude extends VisitDimension
{
    protected $fieldName = 'location_longitude';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('longitude');
        $segment->setName('UserCountry_Longitude');
        $segment->setAcceptValues('-70.664, 14.326, etc.');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('UserCountry_Latitude');
    }
}