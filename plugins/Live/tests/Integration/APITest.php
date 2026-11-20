<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\Integration;

use Piwik\Date;
use Piwik\Plugins\Live\API;
use Piwik\Plugins\Live\MeasurableSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Live
 * @group APITest
 * @group Plugins
 */
class APITest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        $this->setSuperUser();
        Fixture::createWebsite('2010-01-01');
    }

    /**
     * Regression coverage for the $intersectSegment method argument: it must be the source of truth,
     * honored even when the ambient request does not contain an "intersectSegment" parameter (as is the
     * case for a direct PHP call). If the value were read from the request instead, the direct call
     * below would silently drop the filter and return both visits.
     */
    public function testGetLastVisitsDetailsHonorsIntersectSegmentArgumentFromDirectCall()
    {
        $categoryUrl = 'https://example.org/category/exampleblue-travel-tips/essential-example/';
        $bestOfUrl = 'https://example.org/exampleblue-travel-tips/essential-example/best-of-example-1/';

        // Visit A matches the current segment (best-of page) AND the clicked row (category page).
        $this->trackVisitWithActions('2012-01-01 10:00:00', [$categoryUrl, $bestOfUrl]);
        // Visit B matches only the current segment: it never visited the category page.
        $this->trackVisitWithActions('2012-01-01 11:00:00', [$bestOfUrl]);

        $currentSegment = 'pageUrl==https%253A%252F%252Fexample.org%252Fexampleblue-travel-tips%252Fessential-example%252Fbest-of-example-1%252F';
        $clickedRowSegment = 'pageUrl=^https%253A%252F%252Fexample.org%252Fcategory';

        $api = API::getInstance();

        // Sanity check: the current segment alone matches both visits.
        $withoutIntersect = $api->getLastVisitsDetails(1, 'day', '2012-01-01', $currentSegment);
        $this->assertSame(2, $withoutIntersect->getRowsCount());

        // Passing $intersectSegment as the method argument must narrow the result to the single visit
        // that also matches it, without any "intersectSegment" request parameter being present.
        $withIntersect = $api->getLastVisitsDetails(
            1,
            'day',
            '2012-01-01',
            $currentSegment,
            $countVisitorsToFetch = false,
            $minTimestamp = false,
            $flat = false,
            $doNotFetchActions = false,
            $enhanced = false,
            $clickedRowSegment
        );
        $this->assertSame(1, $withIntersect->getRowsCount());
        // ...and it is the right one: visit A (which also visited the category page), not visit B.
        $this->assertSame(
            '2012-01-01 10:00:01',
            $withIntersect->getFirstRow()->getColumn('visit_last_action_time')
        );
    }

    /**
     * A View-only user must not be able to read a disabled site's visits by combining it with an
     * enabled site in one request. The enabled site's data is returned; the disabled site is dropped
     * from the query entirely, and an all-disabled request is still rejected.
     */
    public function testGetLastVisitsDetailsExcludesSitesWithVisitsLogDisabled()
    {
        // setUp() created site 1 with the visits log enabled. Add two more sites, track one visit on
        // each, then disable the visits log on sites 2 and 3.
        Fixture::createWebsite('2010-01-01'); // idSite 2
        Fixture::createWebsite('2010-01-01'); // idSite 3

        $this->trackVisitWithActions('2012-01-01 10:00:00', ['https://s1.example.org/page'], 1);
        $this->trackVisitWithActions('2012-01-01 11:00:00', ['https://s2.example.org/page'], 2);
        $this->trackVisitWithActions('2012-01-01 12:00:00', ['https://s3.example.org/page'], 3);

        $this->disableVisitorLog(2);
        $this->disableVisitorLog(3);
        Fixture::clearInMemoryCaches();

        $api = API::getInstance();

        // The regression: a mixed request must return the enabled site only, never the disabled one.
        $mixed = $api->getLastVisitsDetails('1,2', 'day', '2012-01-01');
        $this->assertSame(1, $mixed->getRowsCount());
        $this->assertSame(1, (int) $mixed->getFirstRow()->getColumn('idsite'));

        // The enabled site on its own is unchanged.
        $this->assertSame(1, $api->getLastVisitsDetails(1, 'day', '2012-01-01')->getRowsCount());

        // A single disabled site is denied.
        try {
            $api->getLastVisitsDetails(2, 'day', '2012-01-01');
            $this->fail('Expected getLastVisitsDetails to reject a disabled single site');
        } catch (\Exception $e) {
            $this->assertStringContainsString('deactivated', $e->getMessage());
        }

        // A request containing only disabled sites is denied.
        try {
            $api->getLastVisitsDetails('2,3', 'day', '2012-01-01');
            $this->fail('Expected getLastVisitsDetails to reject an all-disabled request');
        } catch (\Exception $e) {
            $this->assertStringContainsString('deactivated', $e->getMessage());
        }
    }

    public function testGetMostRecentVisitorIdIsDeniedForSiteWithVisitsLogDisabled()
    {
        Fixture::createWebsite('2010-01-01'); // idSite 2

        $this->trackVisitWithActions('2012-01-01 10:00:00', ['https://s1.example.org/page'], 1);
        $this->trackVisitWithActions('2012-01-01 11:00:00', ['https://s2.example.org/page'], 2);

        $this->disableVisitorLog(2);
        Fixture::clearInMemoryCaches();

        $api = API::getInstance();

        // The enabled site still resolves a visitor id.
        $this->assertNotEmpty($api->getMostRecentVisitorId(1));

        // The disabled site is denied outright, so no visitor id is leaked.
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('deactivated');
        $api->getMostRecentVisitorId(2);
    }

    public function testGetMostRecentVisitsDateTimeIsNotGatedByVisitsLogSetting()
    {
        // getMostRecentVisitsDateTime is a raw-data existence probe: ReportingPage.vue and
        // SiteWithoutData.vue read its value to detect that data exists even when reports are not
        // archived yet. It must keep returning a timestamp when the visits log is disabled, otherwise
        // the CoreHome_PeriodHasOnlyRawDataNoVisitsLog notification and the "no data yet" poller break.
        $this->trackVisitWithActions('2012-01-01 10:00:00', ['https://s1.example.org/page'], 1);

        $this->disableVisitorLog(1);
        Fixture::clearInMemoryCaches();

        $this->assertStringStartsWith(
            '2012-01-01 10:',
            API::getInstance()->getMostRecentVisitsDateTime(1)
        );
    }

    private function disableVisitorLog(int $idSite): void
    {
        $settings = new MeasurableSettings($idSite);
        $settings->disableVisitorLog->setValue(true);
        $settings->save();
    }

    private function trackVisitWithActions(string $dateTime, array $urls, int $idSite = 1): void
    {
        $tracker = Fixture::getTracker($idSite, $dateTime, $defaultInit = true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $tracker->setNewVisitorId();

        foreach ($urls as $index => $url) {
            $actionTime = Date::factory($dateTime)->addPeriod($index, 'second')->getDatetime();
            $tracker->setForceVisitDateTime($actionTime);
            $tracker->setUrl($url);
            Fixture::checkResponse($tracker->doTrackPageView('Visit action ' . $index));
        }
    }

    protected function setSuperUser()
    {
        FakeAccess::$superUser = true;
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
