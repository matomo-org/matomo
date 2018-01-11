<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Columns;

use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\Metrics\Formatter;

class Continent extends Dimension
{
    protected $dbTableName = 'log_visit';
    protected $columnName = 'location_country';
    protected $type = self::TYPE_TEXT;
    protected $category = 'UserCountry_VisitLocation';
    protected $nameSingular = 'UserCountry_Continent';
    protected $namePlural = 'UserCountry_Continents';
    protected $segmentName = 'continentCode';
    protected $acceptValues = 'eur, asi, amc, amn, ams, afr, ant, oce';
    protected $sqlFilter = 'Piwik\Plugins\UserCountry\UserCountry::getCountriesForContinent';

    public function groupValue($value, $idSite)
    {
        return Common::getContinent($value);
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\UserCountry\continentTranslate($value);
    }

}