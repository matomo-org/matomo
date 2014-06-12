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

class Country extends VisitDimension
{    
    protected $fieldName = 'location_country';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('countryCode');
        $segment->setName('UserCountry_Country');
        $segment->setAcceptValues('de, us, fr, in, es, etc.');
        $this->addSegment($segment);

        $segment = new Segment();
        $segment->setSegment('continentCode');
        $segment->setName('UserCountry_Continent');
        $segment->setSqlFilter('Piwik\Plugins\UserCountry\UserCountry::getCountriesForContinent');
        $segment->setAcceptValues('eur, asi, amc, amn, ams, afr, ant, oce');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('UserCountry_Country');
    }
}