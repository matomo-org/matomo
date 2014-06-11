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

class Continent extends VisitDimension
{    
    protected $fieldName = 'location_country';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('continentCode');
        $segment->setName('Continent');
        $segment->setSqlFilter('Piwik\Plugins\UserCountry\UserCountry::getCountriesForContinent');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('UserCountry_Continent');
    }
}