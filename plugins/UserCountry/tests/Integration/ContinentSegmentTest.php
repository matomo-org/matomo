<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\UserCountry\tests\Integration;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UserCountry
 * @group Plugins
 */
class ContinentSegmentTest extends IntegrationTestCase
{
    /**
     * @var int
     */
    private $idSite;

    /**
     * @var string
     */
    private $date = '2015-01-02';

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser(true);
        $this->idSite = Fixture::createWebsite('2015-01-01 00:00:00');

        // two European, two South American, one North American, one Asian and one unknown country
        foreach (['fr', 'de', 'br', 'ar', 'us', 'jp', 'xx'] as $index => $country) {
            $tracker = Fixture::getTracker($this->idSite, $this->date . ' ' . (10 + $index) . ':00:00');
            $tracker->setCountry($country);
            $tracker->setIp('10.0.0.' . ($index + 1));
            Fixture::checkResponse($tracker->doTrackPageView('/page-' . $index));
        }
    }

    /**
     * @dataProvider getContinentSegments
     */
    public function testSegmentMatchesTheCountriesOfTheContinent(string $segment, array $expectedCountries): void
    {
        $this->assertSame($expectedCountries, $this->getCountriesInSegment($segment));
    }

    public function getContinentSegments(): iterable
    {
        yield 'is Europe' => ['continentCode==eur', ['de', 'fr']];
        yield 'is South America' => ['continentCode==ams', ['ar', 'br']];
        yield 'is not Europe' => ['continentCode!=eur', ['ar', 'br', 'jp', 'us', 'xx']];
        yield 'is not South America' => ['continentCode!=ams', ['de', 'fr', 'jp', 'us', 'xx']];
        yield 'is neither Europe nor South America' => ['continentCode!=eur;continentCode!=ams', ['jp', 'us', 'xx']];
        yield 'is Europe or not South America' => ['continentCode==eur,continentCode!=ams', ['de', 'fr', 'jp', 'us', 'xx']];
    }

    /**
     * @return string[]
     */
    private function getCountriesInSegment(string $segment): array
    {
        /** @var DataTable $table */
        $table = Request::processRequest('UserCountry.getCountry', [
            'idSite' => $this->idSite,
            'period' => 'day',
            'date' => $this->date,
            'segment' => $segment,
            'format' => 'original',
        ]);

        // the label holds the country name, the code is kept as metadata
        $countries = $table->getRowsMetadata('code');
        sort($countries);

        return $countries;
    }
}
