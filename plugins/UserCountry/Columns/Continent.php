<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\Columns;

use Piwik\Columns\Dimension;
use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Intl\Data\Provider\RegionDataProvider;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\Segment;
use Piwik\Segment\SegmentsList;

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

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        // Set on the segment only: the dimension itself still groups by country, see groupValue()
        $segment = new Segment();
        $segment->setSqlSegment($this->getContinentOfCountrySql());
        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));
    }

    /**
     * Maps the visit country to its continent code the way {@see Common::getContinent()} does for the
     * Continent report, so every segment operator applies to the continent code itself.
     */
    private function getContinentOfCountrySql(): string
    {
        /** @var RegionDataProvider $regionDataProvider */
        $regionDataProvider = StaticContainer::get(RegionDataProvider::class);

        $countriesByContinent = [];
        foreach ($regionDataProvider->getCountryList() as $countryCode => $continentCode) {
            $countriesByContinent[$continentCode][] = $countryCode;
        }
        // Common::getContinent() counts the legacy Tibet code as China
        $countriesByContinent[Common::getContinent('ti')][] = 'ti';

        $column = $this->dbTableName . '.' . $this->columnName;
        $sql = 'CASE';
        foreach ($countriesByContinent as $continentCode => $countryCodes) {
            $sql .= sprintf(" WHEN %s IN ('%s') THEN '%s'", $column, implode("', '", $countryCodes), $continentCode);
        }

        return $sql . " ELSE 'unk' END";
    }

    public function groupValue($value, $idSite)
    {
        return Common::getContinent($value);
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\UserCountry\continentTranslate($value);
    }
}
