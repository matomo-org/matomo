<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\Integration;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\DebugView\API;
use Piwik\Plugins\DebugView\Dao\RawRequestLog;
use Piwik\Plugins\DebugView\Model\DebugRequests;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group DebugView
 * @group DebugViewRawRequestTest
 * @group Plugins
 */
class RawRequestTest extends IntegrationTestCase
{
    /**
     * @var int
     */
    private $idSite = 1;

    public function setUp(): void
    {
        parent::setUp();

        if (class_exists('\Piwik\Plugins\TagManager\TagManager')) {
            \Piwik\Plugins\TagManager\TagManager::$enableAutoContainerCreation = false;
        }

        Fixture::createSuperUser();
        FakeAccess::$superUser = true;

        if (!Fixture::siteCreated($this->idSite)) {
            Fixture::createWebsite('2020-01-01 00:00:00');
        }
    }

    public function testCapturesRawParamsWhileActiveAndJoinsThemOntoHits()
    {
        $now = Date::now();

        // a poll marks capturing active before any hits arrive
        API::getInstance()->getRecentHits($this->idSite, 30, 0);

        $tracker = Fixture::getTracker($this->idSite, $now->subSeconds(90)->getDatetime(), $defaultInit = true);
        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(90)->getDatetime());
        $tracker->setUrl('http://example.org/raw');
        Fixture::checkResponse($tracker->doTrackPageView('Raw Page'));

        // MatomoTracker clears custom tracking parameters after every request,
        // so debug=1 must be re-set per tracked hit
        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(60)->getDatetime());
        Fixture::checkResponse($tracker->doTrackEvent('RawCat', 'play'));

        $result = API::getInstance()->getRecentHits($this->idSite, 30, 0);
        $hits = $result['hits'];
        $this->assertCount(2, $hits);

        $this->assertSame('pageview', $hits[0]['type']);
        $this->assertIsArray($hits[0]['trackingParams']);
        $this->assertSame('Raw Page', $hits[0]['trackingParams']['action_name']);
        $this->assertSame('http://example.org/raw', $hits[0]['trackingParams']['url']);
        $this->assertSame('1', $hits[0]['trackingParams']['debug']);

        $this->assertSame('event', $hits[1]['type']);
        $this->assertIsArray($hits[1]['trackingParams']);
        $this->assertSame('RawCat', $hits[1]['trackingParams']['e_c']);

        // passively received request data is captured alongside the parameters
        $defaults = $hits[0]['trackingParamsDefaults'];
        $this->assertIsArray($defaults);
        $this->assertArrayHasKey('userAgent', $defaults);
        $this->assertNotSame('', $defaults['userAgent']);
        $this->assertArrayHasKey('browserLanguage', $defaults);
        $this->assertArrayHasKey('clientHints', $defaults);
        $this->assertEqualsWithDelta(time(), $defaults['serverTimeReceived'], 300);

        // the tracker also records whether the request was authenticated
        $this->assertIsArray($hits[0]['trackingParamsOther']);
        $this->assertArrayHasKey('isAuthenticated', $hits[0]['trackingParamsOther']);
    }

    public function testDoesNotCaptureRequestsWithoutDebugParameter()
    {
        $now = Date::now();

        // a viewer is watching, but the request is not flagged with debug=1
        API::getInstance()->getRecentHits($this->idSite, 30, 0);

        $tracker = Fixture::getTracker($this->idSite, $now->subSeconds(60)->getDatetime(), $defaultInit = true);
        $tracker->setForceVisitDateTime($now->subSeconds(60)->getDatetime());
        $tracker->setUrl('http://example.org/nodebug');
        Fixture::checkResponse($tracker->doTrackPageView('No Debug Page'));

        $count = (int) Db::fetchOne(
            'SELECT COUNT(*) FROM `' . Common::prefixTable(RawRequestLog::TABLE) . '`'
        );
        $this->assertSame(0, $count);

        // requests without debug=1 are not visualised in the stream
        $result = API::getInstance()->getRecentHits($this->idSite, 30, 0);
        $this->assertCount(0, $result['hits']);
    }

    public function testDoesNotCaptureWhenNoDebugViewIsWatching()
    {
        $now = Date::now();

        // debug=1 is set, but nobody is watching Debug View
        $tracker = Fixture::getTracker($this->idSite, $now->subSeconds(60)->getDatetime(), $defaultInit = true);
        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(60)->getDatetime());
        $tracker->setUrl('http://example.org/quiet');
        Fixture::checkResponse($tracker->doTrackPageView('Quiet Page'));

        $count = (int) Db::fetchOne(
            'SELECT COUNT(*) FROM `' . Common::prefixTable(RawRequestLog::TABLE) . '`'
        );
        $this->assertSame(0, $count);

        // hits without captured raw parameters are not visualised at all
        $result = API::getInstance()->getRecentHits($this->idSite, 30, 0);
        $this->assertCount(0, $result['hits']);
    }

    public function testLongParameterValuesAreTruncatedOnStorage()
    {
        $now = Date::now();

        API::getInstance()->getRecentHits($this->idSite, 30, 0);

        $longValue = str_repeat('x', DebugRequests::MAX_PARAM_VALUE_LENGTH + 800);

        $tracker = Fixture::getTracker($this->idSite, $now->subSeconds(60)->getDatetime(), $defaultInit = true);
        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setCustomTrackingParameter('hsr_payload', $longValue);
        $tracker->setForceVisitDateTime($now->subSeconds(60)->getDatetime());
        $tracker->setUrl('http://example.org/long');
        Fixture::checkResponse($tracker->doTrackPageView('Long Param Page'));

        $result = API::getInstance()->getRecentHits($this->idSite, 30, 0);
        $this->assertCount(1, $result['hits']);

        $stored = $result['hits'][0]['trackingParams']['hsr_payload'];
        $this->assertSame(
            str_repeat('x', DebugRequests::MAX_PARAM_VALUE_LENGTH) . DebugRequests::TRUNCATION_MARKER,
            $stored
        );
    }

    public function testTokenAuthIsNeverStored()
    {
        $now = Date::now();

        API::getInstance()->getRecentHits($this->idSite, 30, 0);

        $realToken = Fixture::getTokenAuth();

        $tracker = Fixture::getTracker($this->idSite, $now->subSeconds(60)->getDatetime(), $defaultInit = true);
        $tracker->setTokenAuth($realToken);
        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(60)->getDatetime());
        $tracker->setUrl('http://example.org/secret');
        Fixture::checkResponse($tracker->doTrackPageView('Secret Page'));

        $storedParams = Db::fetchAll(
            'SELECT parameters FROM `' . Common::prefixTable(RawRequestLog::TABLE) . '`'
        );
        $this->assertNotEmpty($storedParams);
        foreach ($storedParams as $row) {
            $this->assertStringNotContainsString($realToken, $row['parameters']);
            $decoded = json_decode($row['parameters'], true);
            if (array_key_exists('token_auth', $decoded['query'])) {
                $this->assertSame('__redacted__', $decoded['query']['token_auth']);
            }
        }

        // a request with a valid token is recorded as authenticated
        $result = API::getInstance()->getRecentHits($this->idSite, 30, 0);
        $this->assertCount(1, $result['hits']);
        $this->assertTrue($result['hits'][0]['trackingParamsOther']['isAuthenticated']);
    }

    public function testMarkSiteActiveDoesNotInvalidateGeneralTrackerCache()
    {
        // regression: arming capture used to clear the shared tracker cache,
        // which is expensive on high-traffic installations
        \Piwik\Tracker\Cache::setCacheGeneral(['debugViewCacheMarker' => 42]);

        $rawRequestLog = new DebugRequests(new RawRequestLog());
        $rawRequestLog->markSiteActive($this->idSite);
        $rawRequestLog->markSiteActive($this->idSite + 1);

        $cache = \Piwik\Tracker\Cache::getCacheGeneral();
        $this->assertSame(42, $cache['debugViewCacheMarker'] ?? null);

        // the tracker still sees the armed state via the direct option read
        $this->assertTrue($rawRequestLog->isSiteActiveForTracker($this->idSite));
        $this->assertFalse($rawRequestLog->isSiteActiveForTracker(999));
    }

    public function testTrimTaskIsScheduledHourly()
    {
        $tasks = new \Piwik\Plugins\DebugView\Tasks(new DebugRequests(new RawRequestLog()));
        $tasks->schedule();

        $scheduled = $tasks->getScheduledTasks();
        $this->assertCount(1, $scheduled);
        $this->assertSame('trimRawRequests', $scheduled[0]->getMethodName());
        $this->assertInstanceOf(\Piwik\Scheduler\Schedule\Hourly::class, $scheduled[0]->getScheduledTime());
    }

    public function testTrimDeletesRequestsOlderThanTheLongestApiWindow()
    {
        $rawRequestLog = new DebugRequests(new RawRequestLog());
        $now = time();
        $tooOld = $now - ((\Piwik\Plugins\DebugView\API::MAX_LAST_MINUTES + 5) * 60);

        // well under the per-site cap, so any deletion is purely age-based
        $rawRequestLog->insertFromTracker($this->idSite, null, null, $tooOld, ['age' => 'old']);
        $rawRequestLog->insertFromTracker($this->idSite, null, null, $tooOld + 60, ['age' => 'old2']);
        $rawRequestLog->insertFromTracker($this->idSite, null, null, $now, ['age' => 'fresh']);

        (new \Piwik\Plugins\DebugView\Tasks($rawRequestLog))->trimRawRequests();

        $table = Common::prefixTable(RawRequestLog::TABLE);
        $remaining = Db::fetchAll("SELECT parameters FROM `$table` WHERE idsite = ?", [$this->idSite]);

        $this->assertCount(1, $remaining);
        $decoded = json_decode($remaining[0]['parameters'], true);
        $this->assertSame('fresh', $decoded['query']['age']);
    }

    public function testHourlyTrimTaskCapsRowsPerSite()
    {
        $now = time();
        $otherIdSite = 2;
        $rawRequestLog = new DebugRequests(new RawRequestLog());

        // interleave inserts for two sites, each well beyond the cap
        for ($i = 0; $i < DebugRequests::MAX_ROWS_PER_SITE + 50; $i++) {
            $rawRequestLog->insertFromTracker($this->idSite, null, null, $now, ['n' => $i, 'site' => 'a']);
            $rawRequestLog->insertFromTracker($otherIdSite, null, null, $now, ['n' => $i, 'site' => 'b']);
        }

        $table = Common::prefixTable(RawRequestLog::TABLE);

        // inserts alone no longer trim: a tracking request must not pay for cleanup
        $countSiteA = (int) Db::fetchOne("SELECT COUNT(*) FROM `$table` WHERE idsite = ?", [$this->idSite]);
        $this->assertSame(DebugRequests::MAX_ROWS_PER_SITE + 50, $countSiteA);

        // the hourly task enforces the cap
        $tasks = new \Piwik\Plugins\DebugView\Tasks($rawRequestLog);
        $tasks->trimRawRequests();

        $countSiteA = (int) Db::fetchOne("SELECT COUNT(*) FROM `$table` WHERE idsite = ?", [$this->idSite]);
        $countSiteB = (int) Db::fetchOne("SELECT COUNT(*) FROM `$table` WHERE idsite = ?", [$otherIdSite]);

        // the cap is per site: each site keeps exactly its newest MAX_ROWS_PER_SITE
        // rows, one site's trim never evicts another site's rows
        $this->assertSame(DebugRequests::MAX_ROWS_PER_SITE, $countSiteA);
        $this->assertSame(DebugRequests::MAX_ROWS_PER_SITE, $countSiteB);

        foreach ([$this->idSite, $otherIdSite] as $site) {
            $newest = Db::fetchOne(
                "SELECT parameters FROM `$table` WHERE idsite = ? ORDER BY idrawrequest DESC LIMIT 1",
                [$site]
            );
            $decoded = json_decode($newest, true);
            $this->assertSame(DebugRequests::MAX_ROWS_PER_SITE + 49, $decoded['query']['n']);

            $oldest = Db::fetchOne(
                "SELECT parameters FROM `$table` WHERE idsite = ? ORDER BY idrawrequest ASC LIMIT 1",
                [$site]
            );
            $decoded = json_decode($oldest, true);
            $this->assertSame(50, $decoded['query']['n'], 'the oldest kept row must be the (cap)-newest one');
        }
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
