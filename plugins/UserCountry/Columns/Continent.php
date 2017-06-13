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

class Continent extends Dimension
{
    protected $columnName = 'location_country';
    protected $dbTableName = 'log_visit';
    protected $category = 'UserCountry_VisitLocation';
    protected $nameSingular = 'UserCountry_Continent';
    protected $segmentName = 'continentCode';
    protected $acceptValues = 'eur, asi, amc, amn, ams, afr, ant, oce';
    protected $sqlFilter = 'Piwik\Plugins\UserCountry\UserCountry::getCountriesForContinent';

}