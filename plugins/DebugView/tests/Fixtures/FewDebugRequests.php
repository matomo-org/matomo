<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\DebugView\Dao\RawRequestLog;
use Piwik\Plugins\DebugView\Model\DebugRequests;
use Piwik\Tests\Framework\Fixture;

/**
 * Four sites: site 1 with debug requests of every core tracking type, site 2
 * with different debug requests, site 3 without any data, and site 4 with the
 * visits log disabled per site (Debug View shows its friendly disabled page).
 * Site 1 additionally receives one unflagged control request that must not be
 * captured.
 *
 * Tracking happens relative to Date::now() because DebugView streams only the
 * last MAX_LAST_MINUTES minutes; volatile response fields are removed via
 * xmlFieldsToRemove in the system test.
 */
class FewDebugRequests extends Fixture
{
    /**
     * @var int
     */
    public $idSite = 1;

    /**
     * @var int
     */
    public $idSiteTwo = 2;

    /**
     * @var int
     */
    public $idSiteEmpty = 3;

    /**
     * @var int
     */
    public $idSiteDisabledVisitsLog = 4;

    /**
     * @var int
     */
    public $idGoal;

    public function setUp(): void
    {
        // the web tracker proxy keeps file-based caches between test runs;
        // they must not be reused after the test DB was dropped and recreated
        \Piwik\Tracker\Cache::deleteTrackerCache();
        \Piwik\Tracker\Cache::clearCacheGeneral();

        // the CLI clears above may resolve a different tmp path than the web
        // server: also remove the web tracker's test cache files directly, as
        // the eager cache re-saves stale entries (e.g. dimensions of removed
        // plugins) on every request and would otherwise never recover
        $cacheFiles = glob(PIWIK_INCLUDE_PATH . '/tmp/{,*/}cache/tracker/*test*', GLOB_BRACE) ?: [];
        foreach ($cacheFiles as $cacheFile) {
            @unlink($cacheFile);
        }

        // the UI test runner occasionally sets up the test DB without the
        // dimension columns of the log tables, which makes the tracker reject
        // every request; when the schema is incomplete, run the core updater
        // (all dimension versions are unrecorded then, so it installs them)
        $logVisitColumns = \Piwik\DbHelper::getTableColumns(\Piwik\Common::prefixTable('log_visit'));
        if (!array_key_exists('visit_first_action_time', $logVisitColumns)) {
            self::updateDatabase();
            (new RawRequestLog())->install();
        }

        if (!self::siteCreated($this->idSite)) {
            self::createWebsite('2020-01-01 00:00:00', $ecommerce = 1);
        }
        foreach ([$this->idSiteTwo, $this->idSiteEmpty, $this->idSiteDisabledVisitsLog] as $idSite) {
            if (!self::siteCreated($idSite)) {
                self::createWebsite('2020-01-01 00:00:00');
            }
        }

        // site 4 has its visits log disabled per site: Debug View renders the
        // friendly disabled page for it instead of the stream
        $liveSettings = new \Piwik\Plugins\Live\MeasurableSettings($this->idSiteDisabledVisitsLog);
        $liveSettings->disableVisitorLog->setValue(true);
        $liveSettings->save();

        $this->idGoal = (int) \Piwik\Plugins\Goals\API::getInstance()->addGoal(
            $this->idSite,
            'Manual Goal',
            'manually',
            '',
            'contains'
        );

        // a viewer is watching sites 1 and 2: arm capturing before the hits
        // arrive; site 3 stays unarmed and without data
        $model = new DebugRequests(new RawRequestLog());
        $model->markSiteActive($this->idSite);
        $model->markSiteActive($this->idSiteTwo);

        $this->warmUpTracker();

        $this->trackSiteOneRequests();
        $this->trackSiteTwoRequests();
    }

    /**
     * Let the web tracker proxy bootstrap and rebuild its file caches once
     * before the first asserted request, so cache rebuild cost or a transient
     * hiccup right after a previous test run's teardown cannot fail the
     * fixture. The warm-up is sent with rec=0, so the tracker parses it but
     * never records anything: no visit, action or DebugView rows are created
     * and the ids asserted in the expected test output stay stable.
     */
    private function warmUpTracker(): void
    {
        $tracker = self::getTracker($this->idSite, Date::now()->subSeconds(300)->getDatetime(), $defaultInit = true);
        $tracker->setUrl('http://example.org/warm-up');

        for ($i = 0; $i < 3; $i++) {
            // custom tracking parameters are cleared after every request; the
            // later rec=0 wins over the rec=1 the client adds by default
            $tracker->setCustomTrackingParameter('rec', '0');
            $response = $tracker->doTrackPageView('Warm-up');
            $isGifBeacon = is_string($response)
                && strpos($response, 'GIF89a') === 0
                && strlen($response) < 100;
            if ($isGifBeacon) {
                break;
            }
        }
    }

    public function tearDown(): void
    {
        // empty
    }

    /**
     * Site 1 receives one request per core tracking type: pageview, ping,
     * event, site search, outlink, download, content interaction, goal
     * conversion and an ecommerce purchase — plus one unflagged control
     * pageview and one crawler request captured on the tracker's bot path.
     */
    private function trackSiteOneRequests(): void
    {
        $now = Date::now();

        $tracker = self::getTracker($this->idSite, $now->subSeconds(240)->getDatetime(), $defaultInit = true);
        $tracker->setVisitorId('0123456789abcdef');

        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setUrl('http://example.org/sub/page');
        self::checkResponse($tracker->doTrackPageView('First Debug Page'));

        // custom tracking parameters are cleared after every request
        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(230)->getDatetime());
        self::checkResponse($tracker->doPing());

        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(200)->getDatetime());
        self::checkResponse($tracker->doTrackEvent('Category', 'click', 'label', 7));

        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(170)->getDatetime());
        self::checkResponse($tracker->doTrackSiteSearch('keyword', 'searchcat', 2));

        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(140)->getDatetime());
        self::checkResponse($tracker->doTrackAction('http://external.example/partner-site', 'link'));

        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(110)->getDatetime());
        self::checkResponse($tracker->doTrackAction('http://example.org/files/manual.pdf', 'download'));

        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(80)->getDatetime());
        self::checkResponse($tracker->doTrackContentInteraction('click', 'Ad Banner', 'banner.jpg', 'http://ad.example/landing'));

        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(50)->getDatetime());
        self::checkResponse($tracker->doTrackGoal($this->idGoal, 42.5));

        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(20)->getDatetime());
        $tracker->addEcommerceItem('SKU-1', 'Product One', 'Category A', 45.25, 2);
        self::checkResponse($tracker->doTrackEcommerceOrder('ORDER-1', 100.50, 90.50, 5.50, 4.50, 0));

        // not flagged with debug=1: must not show up in the stream
        $tracker->setForceVisitDateTime($now->subSeconds(10)->getDatetime());
        $tracker->setUrl('http://example.org/hidden');
        self::checkResponse($tracker->doTrackPageView('Hidden Page'));

        // a crawler request: recMode=2 routes it down the tracker's bot path,
        // where no visit or action is recorded; captured with the bot's name
        $tracker->setUserAgent('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setCustomTrackingParameter('recMode', '2');
        $tracker->setForceVisitDateTime($now->subSeconds(5)->getDatetime());
        $tracker->setUrl('http://example.org/robots-view');
        self::checkResponse($tracker->doTrackPageView('Bot Crawl'));
    }

    private function trackSiteTwoRequests(): void
    {
        $now = Date::now();

        $tracker = self::getTracker($this->idSiteTwo, $now->subSeconds(150)->getDatetime(), $defaultInit = true);
        $tracker->setVisitorId('fedcba9876543210');

        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setUrl('http://site-two.example/start');
        self::checkResponse($tracker->doTrackPageView('Site Two Start'));

        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(90)->getDatetime());
        self::checkResponse($tracker->doTrackAction('http://downloads.example/tool.zip', 'download'));

        $tracker->setCustomTrackingParameter('debug', '1');
        $tracker->setForceVisitDateTime($now->subSeconds(45)->getDatetime());
        self::checkResponse($tracker->doTrackAction('http://external.example/partner', 'link'));
    }
}
