<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Columns;

use Piwik\Columns\Dimension;
use Piwik\Metrics\Formatter;
use function Piwik\Plugins\UserCountry\continentTranslate;

class Continent extends Dimension
{
    protected $dbTableName = 'log_visit';
    protected $columnName = 'location_country';
    protected $type = self::TYPE_TEXT;
    protected $category = 'UserCountry_VisitLocation';
    protected $nameSingular = 'UserCountry_Continent';
    protected $segmentName = 'continentCode';
    protected $acceptValues = 'eur, asi, amc, amn, ams, afr, ant, oce';
    protected $sqlFilter = 'Piwik\Plugins\UserCountry\UserCountry::getCountriesForContinent';

    public function formatValue($value, Formatter $formatter)
    {
        return continentTranslate($value);
    }

}