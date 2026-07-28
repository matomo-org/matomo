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

    private function trackVisitWithActions(string $dateTime, array $urls): void
    {
        $tracker = Fixture::getTracker(1, $dateTime, $defaultInit = true);
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
