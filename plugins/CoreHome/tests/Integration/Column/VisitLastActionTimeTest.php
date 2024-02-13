<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration\Column;

use Piwik\Common;
use Piwik\Date;
use Piwik\Plugins\CoreHome\Columns\VisitLastActionTime;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Visitor;

/**
 * @group CoreHome
 * @group VisitLastActionTimeTest
 * @group Plugins
 * @group Column
 */
class VisitLastActionTimeTest extends IntegrationTestCase
{
    /**
     * @var VisitLastActionTime
     */
    private $lastAction;

    public function setUp(): void
    {
        parent::setUp();
        $this->lastAction = new VisitLastActionTime();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }


    private function makeRequest($request)
    {
        $request['idsite'] = 1;

        return new Request($request);
    }

    public function test_convertHourToHourInSiteTimezone_UTC()
    {
        $idSite = Fixture::createWebsite('2020-01-02 03:04:05');
        $hourConverted = VisitLastActionTime::convertHourToHourInSiteTimezone(5, $idSite);
        $this->assertEquals(5, $hourConverted);
    }

    public function test_convertHourToHourInSiteTimezone_WithTimezone()
    {
        $idSite = Fixture::createWebsite(
            '2020-01-02 03:04:05',
            $ecommerce = 1,
            'Site',
            $siteUrl = false,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $timezone = 'Asia/Jakarta'
        );
        $hourConverted = VisitLastActionTime::convertHourToHourInSiteTimezone(5, $idSite);
        $this->assertEquals(12, $hourConverted);
    }

    public function test_convertHourToHourInSiteTimezone_WithTimezoneAndCustomDate()
    {
        $_GET['period'] = 'day';
        $_GET['date'] = '2020-01-02 03:04:05';
        $idSite = Fixture::createWebsite(
            '2020-01-02 03:04:05',
            $ecommerce = 1,
            'Site',
            $siteUrl = false,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $timezone = 'Asia/Jakarta'
        );
        $hourConverted = VisitLastActionTime::convertHourToHourInSiteTimezone(5, $idSite);
        unset($_GET['period'], $_GET['date']);
        $this->assertEquals(12, $hourConverted);
    }

    private function getVisitor(VisitProperties $previousProperties = null)
    {
        $visit = new VisitProperties();
        $visit->setProperty('idvisit', '321');
        $visit->setProperty('idvisitor', Common::hex2bin('1234567890234567'));
        $visitor = new Visitor($visit, $isKnown = false, $previousProperties);

        return $visitor;
    }

    public function test_onExistingVisit_whenPing()
    {
        $request = $this->makeRequest(array('ping' => 1));
        $visitor = $this->getVisitor();
        $this->assertFalse($this->lastAction->onExistingVisit($request, $visitor, $action = null));
    }

    public function test_onExistingVisit_whenNewVisitReturnsTimeFromRequest()
    {
        $now = time() - 5; // -5 so we make sure this time is used and not actually now
        $request = $this->makeRequest(array('cdt' => $now));
        $this->assertEquals($now, $request->getCurrentTimestamp());

        $visitor = $this->getVisitor();

        $expected = Date::factory($now)->getDatetime();
        $this->assertSame($expected, $this->lastAction->onExistingVisit($request, $visitor, $action = null));
    }

    public function test_onExistingVisit_whenKnownVisitRequestTimeIsNewer()
    {
        $now = time() - 5; // -5 so we make sure this time is used and not actually now
        $previousTime = $now - 10; // is older
        $request = $this->makeRequest(array('cdt' => $now));
        $this->assertEquals($now, $request->getCurrentTimestamp());

        $visitor = $this->getVisitor(new VisitProperties([
            'visit_last_action_time' => Date::factory($previousTime)->getDatetime(),
        ]));

        $expected = Date::factory($now)->getDatetime();
        $this->assertSame($expected, $this->lastAction->onExistingVisit($request, $visitor, $action = null));
    }

    public function test_onExistingVisit_whenKnownVisitAndPreviousVisitTimeIsNewer()
    {
        $now = time() - 5; // -5 so we make sure this time is used and not actually now
        $previousTime = $now + 10; // is newer
        $request = $this->makeRequest(array('cdt' => $now));
        $this->assertEquals($now, $request->getCurrentTimestamp());

        $visitor = $this->getVisitor(new VisitProperties([
            'visit_last_action_time' => Date::factory($previousTime)->getDatetime(),
        ]));

        $expected = Date::factory($previousTime)->getDatetime();
        // should keep existing visit last action time
        $this->assertSame($expected, $this->lastAction->onExistingVisit($request, $visitor, $action = null));
    }
}
