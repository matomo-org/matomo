<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MultiSites\tests\Integration;

use Piwik\Access;
use Piwik\Date;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugins\MultiSites\API as APIMultiSites;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Class Plugins_MultiSitesTest
 *
 * @group Plugins
 */
class MultiSitesTest extends IntegrationTestCase
{
    protected $idSiteAccess;

    public function setUp(): void
    {
        parent::setUp();

        $access = Access::getInstance();
        $access->setSuperUserAccess(true);

        $this->idSiteAccess = APISitesManager::getInstance()->addSite("test", "http://test");

        \Piwik\Plugin\Manager::getInstance()->loadPlugins(['MultiSites', 'VisitsSummary', 'Actions']);
        \Piwik\Plugin\Manager::getInstance()->installLoadedPlugins();
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    /**
     * @dataProvider getTimezonesAndTheirPreviousDay
     */
    public function testGetOneComparesAgainstThePreviousDayOfTheSiteTimezone($timezone, $now, $expectedLastDate)
    {
        Date::$now = strtotime($now);

        $idSite = $this->addSiteInTimezone($timezone);

        $dataTable = APIMultiSites::getInstance()->getOne($idSite, 'day', 'today');

        $this->assertEquals($expectedLastDate, $dataTable->getMetadata('last_period_date')->toString());
    }

    public function testGetOneComparesTheVisitsOfThePreviousDayOfTheSiteTimezone()
    {
        Fixture::createSuperUser();
        $idSite = $this->addSiteInTimezone('UTC+12');
        // In the site's days: three visits on the 25th, one on the 24th and five on the 23rd,
        // UTC's previous day.
        $visits = [
            '2020-12-24 13:00:00', '2020-12-24 14:00:00', '2020-12-24 15:00:00',
            '2020-12-23 13:00:00',
            '2020-12-22 13:00:00', '2020-12-22 14:00:00', '2020-12-22 15:00:00', '2020-12-22 16:00:00', '2020-12-22 17:00:00',
        ];
        foreach ($visits as $when) {
            $tracker = Fixture::getTracker($idSite, $when);
            Fixture::checkResponse($tracker->doTrackPageView('/page'));
        }
        Date::$now = strtotime('2020-12-24 16:37:00');

        $dataTable = APIMultiSites::getInstance()->getOne($idSite, 'day', 'today');

        $this->assertEquals(3, $dataTable->getMetadata('total_nb_visits'));
        // The visits the evolution columns are calculated against.
        $this->assertEquals(1, $dataTable->getMetadata('previous_total_nb_visits'));
    }

    public function testGetAllComparesAgainstThePreviousDayOfTheSiteTimezoneWhenOnlyOneSiteMatches()
    {
        Date::$now = strtotime('2020-12-24 16:37:00');
        $this->addSiteInTimezone('UTC+12');

        $dataTable = APIMultiSites::getInstance()->getAll('day', 'today', null, null, false, 'timezone');

        $this->assertEquals('2020-12-24', $dataTable->getMetadata('last_period_date')->toString());
    }

    public function testGetAllWithGroupsComparesAgainstThePreviousDayOfTheSiteTimezoneForASingleSite()
    {
        Date::$now = strtotime('2020-12-24 16:37:00');
        $idSite = $this->addSiteInTimezone('UTC+12');
        // The pattern of getAllWithGroups() filters after archiving, so restrict the sites before.
        Piwik::addAction('MultiSites.filterSites', function (&$idSites) use ($idSite) {
            $idSites = [$idSite];
        });

        $response = APIMultiSites::getInstance()->getAllWithGroups('day', 'today');

        $this->assertEquals('2020-12-24', $response['lastDate']);
    }

    public function testGetAllComparesAgainstThePreviousDayOfTheSiteTimezoneWhenTheSiteIsListedTwice()
    {
        Date::$now = strtotime('2020-12-24 16:37:00');
        $idSite = $this->addSiteInTimezone('UTC+12');
        Piwik::addAction('MultiSites.filterSites', function (&$idSites) use ($idSite) {
            $idSites = [$idSite, $idSite];
        });

        $dataTable = APIMultiSites::getInstance()->getAll('day', 'today');

        // The archive counts the site once and resolves 'today' on its day.
        $this->assertEquals('2020-12-24', $dataTable->getMetadata('last_period_date')->toString());
    }

    public function testGetAllComparesAgainstThePreviousUtcDayForSeveralSites()
    {
        Date::$now = strtotime('2020-12-24 16:37:00');
        $this->addSiteInTimezone('UTC+12');
        $this->addSiteInTimezone('UTC+12');

        $dataTable = APIMultiSites::getInstance()->getAll('day', 'today', null, null, false, 'timezone');

        // The archive of several sites resolves 'today' in UTC, even when they share a timezone.
        $this->assertEquals('2020-12-23', $dataTable->getMetadata('last_period_date')->toString());
    }

    private function addSiteInTimezone(string $timezone): int
    {
        return APISitesManager::getInstance()->addSite(
            'timezone site',
            'http://timezone.example',
            null,
            null,
            null,
            null,
            null,
            null,
            $timezone
        );
    }

    public function getTimezonesAndTheirPreviousDay()
    {
        return [
            // 04:37 on the 25th for a site twelve hours ahead, still the 24th for the server.
            ['UTC+12', '2020-12-24 16:37:00', '2020-12-24'],
            ['UTC', '2020-12-24 16:37:00', '2020-12-23'],
            // 15:00 on the 24th for a site twelve hours behind, already the 25th for the server.
            ['UTC-12', '2020-12-25 03:00:00', '2020-12-23'],
        ];
    }

    /**
     * Testing that getOne returns a row even when there are no data
     * This is necessary otherwise ResponseBuilder throws 'Call to a member function getColumns() on a non-object'
     *
     * @group Plugins
     */
    public function testWhenNoDataGetOneReturnsRow()
    {
        $dataTable = APIMultiSites::getInstance()->getOne($this->idSiteAccess, 'month', '01-01-2010');
        $this->assertEquals(1, $dataTable->getRowsCount());

        // safety net
        $this->assertEquals(0, $dataTable->getFirstRow()->getColumn('nb_visits'));
    }

    /**
     * Testing that getOne does not error out when format=rss, #10407
     *
     * @group Plugins
     */
    public function testWhenRssFormatGetOneDoesNotError()
    {
        $_GET = [
            'method' => 'MultiSites.getOne',
            'idSite' => $this->idSiteAccess,
            'period' => 'month',
            'date'   => 'last10',
            'format'   => 'rss',
        ];

        $output = FrontController::getInstance()->fetchDispatch('API');

        self::assertStringContainsString('<item>', $output);
        self::assertStringContainsString('</rss>', $output);
        self::assertStringNotContainsString('error', $output);

        $_GET = [];
    }
}
