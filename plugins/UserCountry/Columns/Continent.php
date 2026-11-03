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
    protected $acceptValues = 'eur, asi, amc, amn, ams, afr, ant, oce, unk';

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        // Set on the segment only: the dimension itself still groups by country, see groupValue()
        $segment = new Segment();
        $segment->setSqlSegment($this->getContinentOfCountrySql());
        $segment->setSqlFilterValue([self::class, 'toContinentCode']);
        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));
    }

    /**
     * Lower-cases a segment value, as the continent codes are lower case whatever the collation of the database.
     *
     * @internal
     * @param string $value
     * @return string
     */
    public static function toContinentCode($value)
    {
        return strtolower((string) $value);
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
            // the codes are written into the SQL, so only plain codes are kept
            if (!preg_match('/^[a-z]{2}$/', (string) $countryCode) || !preg_match('/^[a-z]{3}$/', (string) $continentCode)) {
                continue;
            }
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
