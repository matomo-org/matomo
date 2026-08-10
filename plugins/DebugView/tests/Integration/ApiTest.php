<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\Integration;

use Piwik\Date;
use Piwik\NoAccessException;
use Piwik\Plugins\DebugView\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group DebugView
 * @group DebugViewApiTest
 * @group Plugins
 */
class ApiTest extends IntegrationTestCase
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
            // non-UTC timezone on purpose: getRecentHits must return true UTC timestamps
            // even though Live shifts action timestamps into the site timezone
            Fixture::createWebsite(
                '2020-01-01 00:00:00',
                $ecommerce = 0,
                $siteName = 'DebugView Test Site',
                $siteUrl = 'http://example.org/',
                $siteSearch = 1,
                $searchKeywordParameters = null,
                $searchCategoryParameters = null,
                $timezone = 'Pacific/Auckland'
            );
        }
    }

    public function testGetRecentHitsReturnsFlattenedTypedSortedHits()
    {
        $now = Date::now();
        $this->trackTestHits($now);

        $result = API::getInstance()->getRecentHits($this->idSite, 30, 0);

        $this->assertSame('Pacific/Auckland', $result['timezone']);
        $this->assertEqualsWithDelta($now->getTimestamp(), $result['serverTime'], 60);

        $hits = $result['hits'];
        $this->assertCount(4, $hits);

        $this->assertSame(
            ['pageview', 'event', 'search', 'download'],
            array_column($hits, 'type')
        );

        $timestamps = array_column($hits, 'timestamp');
        $sorted = $timestamps;
        sort($sorted);
        $this->assertSame($sorted, $timestamps, 'hits must be sorted chronologically');

        // stream timestamps are true-UTC RECEIPT times (everything was just
        // tracked), unaffected by the site timezone being UTC+12/+13 and by
        // the backdated cdt event timestamps the fixture sends
        foreach ($hits as $hit) {
            $this->assertEqualsWithDelta($now->getTimestamp(), $hit['timestamp'], 60);
        }

        $this->assertSame('First Page', $hits[0]['title']);
        $this->assertSame('Videos – play – intro', $hits[1]['title']);
        $this->assertSame('shoes', $hits[2]['title']);

        $this->assertSame('Videos', $hits[1]['trackingParams']['e_c']);
        $this->assertSame('play', $hits[1]['trackingParams']['e_a']);
        $this->assertSame('products', $hits[2]['subtitle']);
        $this->assertSame('products', $hits[2]['trackingParams']['search_cat']);

        $ids = array_column($hits, 'idRawRequest');
        $this->assertCount(4, array_unique($ids), 'hit ids must be unique');
        foreach ($hits as $hit) {
            $this->assertNotEmpty($hit['idRawRequest']);
            $this->assertNotEmpty($hit['timePretty']);
            $this->assertIsArray($hit['trackingParams']);
            // the lazy Live lookup in the UI needs both references
            $this->assertNotEmpty($hit['idVisit']);
            $this->assertIsInt($hit['idLinkVa']);
            $this->assertGreaterThan(0, $hit['idLinkVa']);
            // requests on the normal visit path are never bot-flagged
            $this->assertFalse($hit['isBot']);
            $this->assertNull($hit['botName']);
        }
    }

    public function testGetRecentHitsMarksRequestsCapturedOnTheBotPath()
    {
        $now = Date::now();

        $tracker = $this->makeTracker($now->subSeconds(90));
        $tracker->setUrl('http://example.org/page');
        Fixture::checkResponse($tracker->doTrackPageView('Human Page'));

        // a crawler request: recMode=2 routes it down the tracker's bot path,
        // where no visit or action is recorded
        $tracker->setUserAgent('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setCustomTrackingParameter('recMode', '2');
        $tracker->setForceVisitDateTime($now->subSeconds(30)->getDatetime());
        $tracker->setUrl('http://example.org/robots-view');
        Fixture::checkResponse($tracker->doTrackPageView('Bot Crawl'));

        $hits = API::getInstance()->getRecentHits($this->idSite, 30, 0)['hits'];
        $this->assertCount(2, $hits);

        [$human, $bot] = $hits;
        $this->assertFalse($human['isBot']);
        $this->assertNull($human['botName']);
        $this->assertNotEmpty($human['idVisit']);

        $this->assertSame('Bot Crawl', $bot['title']);
        $this->assertSame('pageview', $bot['type']);
        $this->assertTrue($bot['isBot']);
        $this->assertSame('Googlebot', $bot['botName']);
        $this->assertNull($bot['idVisit']);
        $this->assertNull($bot['idLinkVa']);
    }

    public function testGetRecentHitsWithMinIdReturnsOnlyHitsWithGreaterIds()
    {
        $now = Date::now();
        $this->trackTestHits($now);

        $first = API::getInstance()->getRecentHits($this->idSite, 30, 0);
        $this->assertCount(4, $first['hits']);

        $cursor = (int) $first['hits'][1]['idRawRequest']; // the event hit

        $second = API::getInstance()->getRecentHits($this->idSite, 30, $cursor);

        $this->assertSame(['search', 'download'], array_column($second['hits'], 'type'));
        foreach ($second['hits'] as $hit) {
            $this->assertGreaterThan($cursor, (int) $hit['idRawRequest']);
        }
    }

    public function testGetRecentHitsShowsRequestsReceivedNowEvenWithAnOldEventTimestamp()
    {
        $now = Date::now();

        // an authenticated request backdating its event time (cdt) by 45
        // minutes, as e.g. a tracking queue produces: it arrives NOW, so it
        // must appear in the real-time stream with its receipt time (that its
        // visit may not be matchable in the UI is acceptable)
        $tracker = $this->makeTracker($now->subSeconds(45 * 60));
        $tracker->setUrl('http://example.org/queued');
        Fixture::checkResponse($tracker->doTrackPageView('Queued Request'));

        $result = API::getInstance()->getRecentHits($this->idSite, 30, 0);

        $this->assertCount(1, $result['hits']);
        $this->assertSame('Queued Request', $result['hits'][0]['title']);
        $this->assertEqualsWithDelta($now->getTimestamp(), $result['hits'][0]['timestamp'], 60);
    }

    public function testGetRecentHitsExcludesRowsReceivedBeforeTheWindow()
    {
        $model = new \Piwik\Plugins\DebugView\Model\DebugRequests(
            new \Piwik\Plugins\DebugView\Dao\RawRequestLog()
        );
        $model->markSiteActive($this->idSite);
        $model->insertFromTracker($this->idSite, null, null, time() - (45 * 60), ['url' => 'http://x/old']);
        $model->insertFromTracker($this->idSite, null, null, time(), ['url' => 'http://x/new']);

        $hits = API::getInstance()->getRecentHits($this->idSite, 30, 0)['hits'];

        $this->assertCount(1, $hits);
        $this->assertSame('http://x/new', $hits[0]['title']);
    }

    public function testGetRecentHitsSurvivesAMalformedBotRequest()
    {
        $now = Date::now();
        $this->trackTestHits($now);

        // PHP parses e_c[]=poison into an array; such a request reaches the
        // capture through the bot path, where no action validation runs — it
        // must never break the whole stream
        $model = new \Piwik\Plugins\DebugView\Model\DebugRequests(
            new \Piwik\Plugins\DebugView\Dao\RawRequestLog()
        );
        $processor = new \Piwik\Plugins\DebugView\Tracker\BotRequestProcessor(
            new \Piwik\Plugins\DebugView\Tracker\RequestCapture($model)
        );
        $processor->handleRequest(new \Piwik\Tracker\Request([
            'idsite' => $this->idSite,
            'rec' => 1,
            'debug' => '1',
            'e_c' => ['poison'],
            'e_a' => ['x'],
            'url' => 'http://example.org/evil',
        ]));

        $hits = API::getInstance()->getRecentHits($this->idSite, 30, 0)['hits'];

        $this->assertCount(5, $hits);
        $malformed = end($hits);
        $this->assertTrue($malformed['isBot']);
        // array e_c is truthy, so the type derivation still sees an event;
        // the title falls back to the string url
        $this->assertSame('event', $malformed['type']);
        $this->assertSame('http://example.org/evil', $malformed['title']);
    }

    public function testGetRecentHitsIsBoundedAndTrimsTheStorageWhenFlooded()
    {
        $model = new \Piwik\Plugins\DebugView\Model\DebugRequests(
            new \Piwik\Plugins\DebugView\Dao\RawRequestLog()
        );
        $cap = \Piwik\Plugins\DebugView\Model\DebugRequests::MAX_ROWS_PER_SITE;
        $now = time();
        for ($i = 0; $i < $cap + 30; $i++) {
            $model->insertFromTracker($this->idSite, null, null, $now, ['url' => 'http://x/' . $i]);
        }

        $hits = API::getInstance()->getRecentHits($this->idSite, 30, 0)['hits'];

        $this->assertCount($cap, $hits);
        // the poll also trimmed the storage back to the cap
        $this->assertCount($cap, $model->getForSite($this->idSite, 0));
    }

    public function testGetRecentHitsThrowsForUserWithoutViewAccess()
    {
        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'aUser';
        FakeAccess::$idSitesView = [];
        FakeAccess::$idSitesAdmin = [];

        $this->expectException(NoAccessException::class);

        API::getInstance()->getRecentHits($this->idSite, 30, 0);
    }

    public function testGetRecentHitsAllowedForUserWithViewAccess()
    {
        $now = Date::now();
        $this->trackTestHits($now);

        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'aUser';
        FakeAccess::$idSitesView = [$this->idSite];
        FakeAccess::$idSitesAdmin = [];

        $result = API::getInstance()->getRecentHits($this->idSite, 30, 0);
        $this->assertCount(4, $result['hits']);
    }

    public function testGetRecentHitsThrowsWhenVisitorLogDisabledSystemWide()
    {
        $settings = new \Piwik\Plugins\Live\SystemSettings();
        $settings->disableVisitorLog->setValue(true);
        $settings->save();
        $this->flushSettingsCache();

        $this->expectExceptionMessage('deactivated');

        API::getInstance()->getRecentHits($this->idSite, 30, 0);
    }

    public function testGetRecentHitsThrowsWhenVisitorLogDisabledForTheSite()
    {
        $settings = new \Piwik\Plugins\Live\MeasurableSettings($this->idSite);
        $settings->disableVisitorLog->setValue(true);
        $settings->save();
        $this->flushSettingsCache();

        $this->expectExceptionMessage('deactivated');

        API::getInstance()->getRecentHits($this->idSite, 30, 0);
    }

    private function flushSettingsCache(): void
    {
        \Piwik\Cache::getTransientCache()->flushAll();
    }

    private function makeTracker(Date $visitDateTime): \MatomoTracker
    {
        // only debug=1-flagged requests captured while a viewer is watching are
        // visualised, so arm capturing before tracking
        (new \Piwik\Plugins\DebugView\Model\DebugRequests(new \Piwik\Plugins\DebugView\Dao\RawRequestLog()))->markSiteActive($this->idSite);

        $tracker = Fixture::getTracker($this->idSite, $visitDateTime->getDatetime(), $defaultInit = true);
        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($visitDateTime->getDatetime());

        return $tracker;
    }

    private function trackTestHits(Date $now): void
    {
        $tracker = $this->makeTracker($now->subSeconds(240));

        $tracker->setUrl('http://example.org/page');
        Fixture::checkResponse($tracker->doTrackPageView('First Page'));

        // MatomoTracker clears custom tracking parameters after every request
        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(180)->getDatetime());
        Fixture::checkResponse($tracker->doTrackEvent('Videos', 'play', 'intro', 42));

        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(120)->getDatetime());
        Fixture::checkResponse($tracker->doTrackSiteSearch('shoes', 'products', 3));

        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(60)->getDatetime());
        Fixture::checkResponse($tracker->doTrackAction('http://example.org/file.zip', 'download'));
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
