<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Live\tests\Integration;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\Live\API;
use Piwik\Plugins\Live\Live;
use Piwik\Plugins\CoreHome\Columns\VisitGoalBuyer;
use Piwik\Plugins\Live\MeasurableSettings;
use Piwik\Plugins\Live\Model;
use Piwik\Plugins\Live\SystemSettings;
use Piwik\Policy\PolicyEnforcementBypass;
use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * On a website whose compliance policy disables the visits log, the visits log and the visitor profile only show
 * the visits of visitors who gave consent.
 *
 * @group Live
 * @group ConsentedVisitsTest
 * @group Plugins
 */
class ConsentedVisitsTest extends IntegrationTestCase
{
    private const FIREFOX = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0';
    private const SAFARI = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_1) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15';
    private const CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    /**
     * Site under the CNIL policy.
     */
    private int $policySite;

    /**
     * Site without any policy.
     */
    private int $otherSite;

    /**
     * Visit ids keyed by "<site>:<user>:<consent>".
     *
     * @var array<string, int>
     */
    private array $visits = [];

    private Date $time;

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$superUser = true;
        Fixture::createSuperUser();
        Fixture::loadAllTranslations();

        $this->policySite = Fixture::createWebsite('2020-01-01 00:00:00');
        $this->otherSite = Fixture::createWebsite('2020-01-01 00:00:00');
        $this->time = Date::factory('2024-03-01 00:00:00');

        // alice browses with Firefox and bob with Chrome; carol never consents. Each visitor first visits without
        // any stored decision, then refuses, then consents, so their first visit is not a consented one
        $users = ['alice' => self::FIREFOX, 'bob' => self::CHROME, 'carol' => self::CHROME];
        foreach (['policy' => $this->policySite, 'other' => $this->otherSite] as $siteKey => $idSite) {
            foreach ($users as $userId => $userAgent) {
                $consents = $userId === 'carol' ? ['none' => null, '0' => 0] : ['none' => null, '0' => 0, '1' => 1];
                foreach ($consents as $consentKey => $consent) {
                    $this->visits["$siteKey:$userId:$consentKey"] = $this->trackVisit($idSite, $userId, $userAgent, $consent);
                }
            }
        }

        // the visits are tracked first, as the policy discards the user id while tracking
        CnilPolicy::setActiveStatus($this->policySite, true);
        Fixture::clearInMemoryCaches();
    }

    public function tearDown(): void
    {
        FakeAccess::$superUser = true;
        CnilPolicy::setActiveStatus(null, false);
        CnilPolicy::setActiveStatus($this->policySite, false);
        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function testTheVisitsLogOfThePolicySiteIsLimitedToConsentedVisits(): void
    {
        $this->assertTrue(Live::isVisitorLogLimitedToConsentedVisits($this->policySite));
        $this->assertTrue(Live::isVisitorLogAvailable($this->policySite));
        $this->assertTrue(Live::isVisitorProfileAvailable($this->policySite));
        $this->assertFalse(Live::isVisitorLogEnabled($this->policySite));
        $this->assertFalse(Live::isVisitorProfileEnabled($this->policySite));

        $this->assertFalse(Live::isVisitorLogLimitedToConsentedVisits($this->otherSite));
    }

    /**
     * @dataProvider getSegmentsOfThePolicySite
     *
     * @param string[] $expectedVisits
     */
    public function testGetLastVisitsDetailsOnlyReturnsConsentedVisitsOnThePolicySite(string $segment, array $expectedVisits): void
    {
        $this->assertSame($this->getVisitIds($expectedVisits), $this->getLastVisitIds($this->policySite, $segment));
    }

    /**
     * @return iterable<string, array{0: string, 1: string[]}>
     */
    public function getSegmentsOfThePolicySite(): iterable
    {
        yield 'no segment' => ['', ['policy:alice:1', 'policy:bob:1']];
        yield 'segment of the caller' => ['browserCode==FF', ['policy:alice:1']];
        yield 'or segment holding the consent condition' => ['consentState==consented,browserCode==FF', ['policy:alice:1', 'policy:bob:1']];
        yield 'segment asking for refused visits' => ['consentState==refused', []];
        yield 'segment asking for visits without consent' => ['consentState!=consented', []];
    }

    public function testGetLastVisitsDetailsReturnsNoVisitWithoutErrorWhenThePolicySiteHasNoConsentedVisit(): void
    {
        $visits = API::getInstance()->getLastVisitsDetails($this->policySite, 'day', '2024-03-01', 'browserCode==FF;consentState==refused');

        $this->assertSame(0, $visits->getRowsCount());
    }

    public function testGetLastVisitsDetailsReturnsEveryVisitOnASiteWithoutPolicy(): void
    {
        $this->assertSame(
            $this->getVisitIds([
                'other:alice:none', 'other:alice:0', 'other:alice:1',
                'other:bob:none', 'other:bob:0', 'other:bob:1',
                'other:carol:none', 'other:carol:0',
            ]),
            $this->getLastVisitIds($this->otherSite)
        );
    }

    public function testGetLastVisitsDetailsStillSkipsThePolicySiteInARequestForSeveralSites(): void
    {
        $visits = API::getInstance()->getLastVisitsDetails($this->policySite . ',' . $this->otherSite, 'day', '2024-03-01');
        $visits->applyQueuedFilters();

        $this->assertSame([$this->otherSite], array_values(array_unique($visits->getColumn('idSite'))));
    }

    public function testEachVisitTellsItsConsentState(): void
    {
        $visits = API::getInstance()->getLastVisitsDetails($this->otherSite, 'day', '2024-03-01', 'browserCode==FF');
        $visits->applyQueuedFilters();

        $statesByVisit = array_combine($visits->getColumn('idVisit'), $visits->getColumn('consentState'));
        ksort($statesByVisit);

        // a visit without a stored decision has no consent state
        $this->assertSame([
            $this->visits['other:alice:none'] => false,
            $this->visits['other:alice:0'] => 'refused',
            $this->visits['other:alice:1'] => 'consented',
        ], $statesByVisit);
    }

    public function testGetVisitorProfileOnlyHoldsTheConsentedVisitsOfTheVisitor(): void
    {
        $profile = API::getInstance()->getVisitorProfile($this->policySite, $this->getVisitorId('alice'));

        $this->assertSame([$this->visits['policy:alice:1']], $profile['lastVisits']->getColumn('idVisit'));
        $this->assertSame(1, $profile['totalVisits']);

        // the adjacent visitors are consented ones, or none
        foreach (['previousVisitorId', 'nextVisitorId'] as $adjacentVisitor) {
            $this->assertContains($profile[$adjacentVisitor], [false, $this->getVisitorId('bob')]);
        }
    }

    public function testGetVisitorProfileIsEmptyForAVisitorWhoNeverConsented(): void
    {
        $this->assertSame([], API::getInstance()->getVisitorProfile($this->policySite, $this->getVisitorId('carol')));
    }

    public function testGetMostRecentVisitorIdReturnsTheMostRecentConsentedVisitor(): void
    {
        // carol visited last, without consent
        $this->assertSame($this->getVisitorId('bob'), API::getInstance()->getMostRecentVisitorId($this->policySite));
    }

    public function testGetFirstVisitForVisitorIdReturnsTheFirstConsentedVisit(): void
    {
        $firstVisit = API::getInstance()->getFirstVisitForVisitorId($this->policySite, $this->getVisitorId('alice'));
        $firstVisit->applyQueuedFilters();

        $this->assertSame([$this->visits['policy:alice:1']], $firstVisit->getColumn('idVisit'));
    }

    public function testTheVisitsLogSettingOfThePolicySiteStillDisablesTheVisitsLog(): void
    {
        $settings = new MeasurableSettings($this->policySite);
        $settings->disableVisitorLog->setValue(true);
        $settings->save();
        Fixture::clearInMemoryCaches();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Visits log is deactivated in website settings');

        API::getInstance()->getLastVisitsDetails($this->policySite, 'day', '2024-03-01');
    }

    public function testTheVisitorProfileSettingOfThePolicySiteStillDisablesTheVisitorProfile(): void
    {
        $settings = new MeasurableSettings($this->policySite);
        $settings->disableVisitorProfile->setValue(true);
        $settings->save();
        Fixture::clearInMemoryCaches();

        // the visits log still lists the consented visits
        $this->assertSame($this->getVisitIds(['policy:alice:1', 'policy:bob:1']), $this->getLastVisitIds($this->policySite));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Visitor profile is deactivated in website settings');

        API::getInstance()->getVisitorProfile($this->policySite, $this->getVisitorId('alice'));
    }

    public function testTheVisitsLogOfThePolicySiteTellsTheSegmentsItCannotFilterBy(): void
    {
        $this->assertSame([], $this->getUnavailableSegmentsGlobal($this->otherSite));

        // userId and visitorId, plus the segments the policy removes by limiting segments
        $segments = $this->getUnavailableSegmentsGlobal($this->policySite);
        $this->assertSame(['userId', 'visitorId'], array_slice($segments, 0, 2));
        $this->assertContains('visitIp', $segments);
        $this->assertNotContains('browserCode', $segments);
    }

    /**
     * @return string[]
     */
    private function getUnavailableSegmentsGlobal(int $idSite): array
    {
        $get = $_GET;
        $_GET['idSite'] = $idSite;

        try {
            $out = '';
            (new Live())->addJsGlobalVariables($out);
        } finally {
            $_GET = $get;
        }

        $this->assertSame(1, preg_match('/piwik\.visitorLogUnavailableSegments = (.*);/', $out, $matches));

        return json_decode($matches[1], true);
    }

    public function testThePolicyDoesNotLimitTheVisitsLogWhileItsEnforcementIsBypassed(): void
    {
        $this->assertFalse(PolicyEnforcementBypass::run(function () {
            return Live::isVisitorLogLimitedToConsentedVisits($this->policySite);
        }));
    }

    public function testTheModelOnlyKeepsTheConsentedVisitsOfThePolicySiteWhenQueryingSeveralSites(): void
    {
        $visits = (new Model())->queryLogVisits(
            [$this->policySite, $this->otherSite],
            'day',
            '2024-03-01',
            false,
            0,
            100,
            false,
            false,
            'DESC'
        );

        $idVisits = array_map('intval', array_column($visits, 'idvisit'));
        sort($idVisits);

        $this->assertSame($this->getVisitIds([
            'policy:alice:1', 'policy:bob:1',
            'other:alice:none', 'other:alice:0', 'other:alice:1',
            'other:bob:none', 'other:bob:0', 'other:bob:1',
            'other:carol:none', 'other:carol:0',
        ]), $idVisits);
    }

    public function testTheEcommerceLogOnlyListsConsentedVisitsOnThePolicySite(): void
    {
        Db::query(
            'UPDATE ' . Common::prefixTable('log_visit') . ' SET visit_goal_buyer = ? WHERE idvisit IN (?, ?, ?)',
            [VisitGoalBuyer::TYPE_BUYER_ORDERED, $this->visits['policy:alice:none'], $this->visits['policy:alice:0'], $this->visits['policy:alice:1']]
        );

        // the segment the Ecommerce log adds to list the visits with ecommerce activity
        $this->assertSame(
            $this->getVisitIds(['policy:alice:1']),
            $this->getLastVisitIds($this->policySite, 'visitEcommerceStatus!=none')
        );
    }

    public function testGetVisitorProfileWithoutVisitorIdShowsTheMostRecentConsentedVisitor(): void
    {
        $profile = API::getInstance()->getVisitorProfile($this->policySite);

        $this->assertSame([$this->visits['policy:bob:1']], $profile['lastVisits']->getColumn('idVisit'));
    }

    public function testTheSystemVisitsLogSettingStillDisablesTheVisitsLogOfThePolicySite(): void
    {
        $settings = new SystemSettings();
        $settings->disableVisitorLog->setValue(true);
        $settings->save();
        Fixture::clearInMemoryCaches();

        $this->assertFalse(Live::isVisitorLogLimitedToConsentedVisits($this->policySite));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Visits log is deactivated globally');

        API::getInstance()->getLastVisitsDetails($this->policySite, 'day', '2024-03-01');
    }

    public function testTheSystemVisitorProfileSettingStillDisablesTheVisitorProfileOfThePolicySite(): void
    {
        $settings = new SystemSettings();
        $settings->disableVisitorProfile->setValue(true);
        $settings->save();
        Fixture::clearInMemoryCaches();

        // the visits log still lists the consented visits
        $this->assertSame($this->getVisitIds(['policy:alice:1', 'policy:bob:1']), $this->getLastVisitIds($this->policySite));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Visitor profile is deactivated globally');

        API::getInstance()->getVisitorProfile($this->policySite, $this->getVisitorId('alice'));
    }

    public function testTheRealTimeCountersStillCountEveryVisitOfThePolicySite(): void
    {
        $this->trackRecentVisit($this->policySite, self::FIREFOX, 1);
        $this->trackRecentVisit($this->policySite, self::SAFARI, 0);
        $this->trackRecentVisit($this->policySite, self::SAFARI, null);

        // the counters behind Live.getCounters, which the policy then rounds, include the visits without consent
        $model = new Model();
        $this->assertSame(3, (int) $model->getNumVisits($this->policySite, 30, false));
        $this->assertSame(3, (int) $model->getNumActions($this->policySite, 30, false));

        // while the real-time list only holds the consented one
        // a range, so a visit tracked just before midnight is still found
        $this->assertSame(1, API::getInstance()->getLastVisitsDetails($this->policySite, 'range', 'last2')->getRowsCount());
    }

    public function testSegmentValuesAreOnlySuggestedFromConsentedVisitsOnThePolicySite(): void
    {
        $this->trackRecentVisit($this->policySite, self::FIREFOX, 1);
        $this->trackRecentVisit($this->policySite, self::SAFARI, 0);
        $this->trackRecentVisit($this->otherSite, self::FIREFOX, 1);
        $this->trackRecentVisit($this->otherSite, self::SAFARI, 0);

        $this->assertSame(['FF'], $this->getSuggestedBrowserCodes($this->policySite));
        $this->assertSame(['FF', 'SF'], $this->getSuggestedBrowserCodes($this->otherSite));
    }

    /**
     * @return string[]
     */
    private function getSuggestedBrowserCodes(int $idSite): array
    {
        $values = Request::processRequest('API.getSuggestedValuesForSegment', [
            'segmentName' => 'browserCode',
            'idSite' => $idSite,
        ]);
        sort($values);

        return $values;
    }

    private function trackRecentVisit(int $idSite, string $userAgent, ?int $consent): void
    {
        $tracker = Fixture::getTracker($idSite, Date::now()->subPeriod(5, 'minute')->getDatetime(), true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $tracker->setUserAgent($userAgent);
        $tracker->setNewVisitorId();
        $tracker->setForceNewVisit();
        Fixture::checkResponse($tracker->doTrackPageView('recent page'));

        Db::query(
            'UPDATE ' . Common::prefixTable('log_visit') . ' SET consent = ? WHERE idvisit = ?',
            [$consent, (int) Db::fetchOne('SELECT MAX(idvisit) FROM ' . Common::prefixTable('log_visit'))]
        );
    }

    /**
     * @return int[]
     */
    private function getLastVisitIds(int $idSite, string $segment = ''): array
    {
        $visits = API::getInstance()->getLastVisitsDetails($idSite, 'day', '2024-03-01', $segment ?: false);
        $this->assertInstanceOf(DataTable::class, $visits);
        $visits->applyQueuedFilters();

        $idVisits = array_map('intval', $visits->getColumn('idVisit'));
        sort($idVisits);

        return $idVisits;
    }

    /**
     * @param string[] $keys
     * @return int[]
     */
    private function getVisitIds(array $keys): array
    {
        $idVisits = array_map(function (string $key): int {
            return $this->visits[$key];
        }, $keys);
        sort($idVisits);

        return $idVisits;
    }

    private function getVisitorId(string $userId): string
    {
        return substr(sha1($userId), 0, 16);
    }

    private function trackVisit(int $idSite, string $userId, string $userAgent, ?int $consent): int
    {
        $this->time = $this->time->addHour(1);

        $tracker = Fixture::getTracker($idSite, $this->time->getDatetime(), true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $tracker->setUserAgent($userAgent);
        $tracker->setUserId($userId);
        $tracker->setForceNewVisit();
        Fixture::checkResponse($tracker->doTrackPageView('page'));

        $idVisit = (int) Db::fetchOne('SELECT MAX(idvisit) FROM ' . Common::prefixTable('log_visit'));

        // the consent is written directly, as tracking only records it on a site measuring consent-aware analytics
        Db::query(
            'UPDATE ' . Common::prefixTable('log_visit') . ' SET consent = ? WHERE idvisit = ?',
            [$consent, $idVisit]
        );

        return $idVisit;
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
