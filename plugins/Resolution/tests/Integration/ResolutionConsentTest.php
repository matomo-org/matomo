<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Resolution\tests\Integration;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics as ConsentAwareAnalyticsSetting;
use Piwik\Plugins\Resolution\Reports\GetResolution;
use Piwik\Plugins\Resolution\Settings\ScreenResolutionDetectionDisabled;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;

/**
 * A compliance policy can withhold the screen resolution from every visitor. On a site measuring
 * consent-aware analytics, a visitor who has consented is measured as if the policy did not
 * restrict them.
 *
 * @group Resolution
 * @group Plugins
 */
class ResolutionConsentTest extends IntegrationTestCase
{
    private const FULL = '1280x1024';
    private const RESTRICTED = 'unknown';

    /** @var int */
    private $idSite;

    /** @var int */
    private $idSiteOther;

    /** @var Date */
    private $testDate;

    /** @var \MatomoTracker */
    private $tracker;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'Resolution consent', 'http://example.com');
        $this->idSiteOther = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'Other', 'http://example.org');
        $this->testDate = Date::factory('2024-01-02 10:00:00');

        $this->tracker = $this->makeTracker($this->idSite);

        $this->setConsentAwareAnalytics($this->idSite, true);

        FakeAccess::$superUser = true;
    }

    public function tearDown(): void
    {
        FakeAccess::$superUser = true;
        FakeAccess::$idSitesView = [];

        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false);
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $this->idSite);
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $this->idSiteOther);
        $this->setForcedCookielessTracking(false);
        unset($_GET['idSite']);
        Cache::deleteTrackerCache();

        parent::tearDown();
    }

    /**
     * Only the exact flag consent=1 is consent. Anything else is treated as no consent.
     *
     * @dataProvider getConsentValues
     */
    public function testANewVisitIsRestrictedUnlessTheRequestCarriesConsent(?string $consent, string $expected): void
    {
        $this->enforcePolicy();

        $this->trackPageView($consent);

        $this->assertSame(1, $this->countVisits());
        $this->assertSame($expected, $this->fetchResolution());
    }

    public function getConsentValues(): iterable
    {
        yield 'consent=1' => ['1', self::FULL];
        yield 'no flag' => [null, self::RESTRICTED];
        yield 'consent=0' => ['0', self::RESTRICTED];
        yield 'empty consent' => ['', self::RESTRICTED];
        yield 'consent=abc' => ['abc', self::RESTRICTED];
        yield 'consent=2' => ['2', self::RESTRICTED];
    }

    public function testAConsentFlagSentAsAnArrayIsNotConsent(): void
    {
        $this->enforcePolicy();

        $this->prepareRequest(null, 'landing');
        $this->tracker->setCustomTrackingParameter('consent[]', '1');
        Fixture::checkResponse($this->tracker->doTrackPageView('Landing'));

        $this->assertSame(self::RESTRICTED, $this->fetchResolution());
    }

    /**
     * Baseline: the feature is opt-in per site. A site that has not opted in behaves exactly as it
     * does today, whether or not a request reports consent.
     *
     * @dataProvider getConsentFlags
     */
    public function testConsentHasNoEffectForASiteThatHasNotOptedIn(?string $consent): void
    {
        $this->setConsentAwareAnalytics($this->idSite, false);
        $this->enforcePolicy();

        $this->trackPageView($consent);

        $this->assertSame(self::RESTRICTED, $this->fetchResolution());
    }

    /**
     * Baseline: consent arriving on the arrival page of a site that has not opted in changes
     * nothing about the visit.
     */
    public function testConsentOnTheArrivalPageHasNoEffectForASiteThatHasNotOptedIn(): void
    {
        $this->setConsentAwareAnalytics($this->idSite, false);
        $this->enforcePolicy();

        $this->trackPageView(null, 'landing');
        $this->trackPageView('1', 'landing');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(self::RESTRICTED, $this->fetchResolution());
    }

    /**
     * Baseline: where no policy is enforced, the resolution is stored for every visitor, as today.
     *
     * @dataProvider getConsentFlags
     */
    public function testTheResolutionIsStoredWhenNoPolicyIsEnforced(?string $consent): void
    {
        $this->trackPageView($consent);

        $this->assertSame(self::FULL, $this->fetchResolution());
    }

    public function getConsentFlags(): iterable
    {
        yield 'consent=1' => ['1'];
        yield 'no flag' => [null];
        yield 'consent=0' => ['0'];
    }

    public function testConsentHasNoEffectWhenCookielessTrackingIsForced(): void
    {
        $this->enforcePolicy();
        $this->setForcedCookielessTracking(true);

        $this->trackPageView('1');

        $this->assertSame(self::RESTRICTED, $this->fetchResolution());
    }

    public function testAConsentedRequestWithoutAResolutionStoresItAsUnknown(): void
    {
        $this->enforcePolicy();
        $this->tracker->setResolution(0, 0);

        $this->trackPageView('1');

        $this->assertSame(self::RESTRICTED, $this->fetchResolution());
    }

    public function testAConsentedResolutionIsCappedAsWhenNoPolicyIsEnforced(): void
    {
        $this->enforcePolicy();
        $this->tracker->setResolution(123456789012, 1024);

        $this->trackPageView('1');

        $this->assertSame('123456789', $this->fetchResolution());
    }

    /**
     * A malformed resolution is stored as it would be on a site without the policy.
     */
    public function testAConsentedMalformedResolutionIsStoredAsWhenNoPolicyIsEnforced(): void
    {
        $this->enforcePolicy();

        $this->trackMalformedResolution($this->idSite);
        $this->trackMalformedResolution($this->idSiteOther);

        $this->assertSame(
            (string) $this->fetchVisitColumn('config_resolution', 0, $this->idSiteOther),
            $this->fetchResolution()
        );
    }

    /**
     * Consent given while the visitor is still on their arrival page continues the same visit, and
     * the resolution withheld for it is filled in from the request that brought the consent.
     *
     * @dataProvider getRestrictions
     */
    public function testConsentOnTheArrivalPageFillsInTheWithheldResolution(string $restriction): void
    {
        $this->restrict($restriction);

        $this->trackPageView(null, 'landing');
        $this->assertSame(self::RESTRICTED, $this->fetchResolution());

        $this->trackPageView('1', 'landing');

        $this->assertSame(1, $this->countVisits(), 'consent on the arrival page continues the visit');
        $this->assertSame('1', (string) $this->fetchVisitColumn('consent'));
        $this->assertSame(self::FULL, $this->fetchResolution());
    }

    /**
     * Consent given once the visitor has moved on starts a visit of its own. The visit measured
     * before consent keeps what was stored for it.
     *
     * @dataProvider getRestrictions
     */
    public function testConsentAfterNavigatingStartsAVisitThatHoldsTheResolution(string $restriction): void
    {
        $this->restrict($restriction);

        $this->trackPageView(null, 'landing');
        $this->trackPageView(null, 'other');
        $this->trackPageView('1', 'other');

        $this->assertSame(2, $this->countVisits(), 'consent away from the arrival page starts its own visit');
        $this->assertSame(self::RESTRICTED, $this->fetchResolution(0));
        $this->assertSame(self::FULL, $this->fetchResolution(1));
    }

    /**
     * The tracker pings the server by itself when consent is given, and that ping can land after
     * the visitor has moved on. It cannot start a visit, so the visit measured before consent
     * keeps its resolution withheld.
     *
     * @dataProvider getRestrictions
     */
    public function testAConsentingPingDoesNotFillInTheEarlierVisit(string $restriction): void
    {
        $this->restrict($restriction);

        $this->trackPageView(null, 'landing');
        $this->trackPageView(null, 'other');
        $this->ping('1', 'other');

        $this->assertSame(1, $this->countVisits(), 'a ping cannot start a visit');
        $this->assertNull($this->fetchVisitColumn('consent'));
        $this->assertSame(self::RESTRICTED, $this->fetchResolution());
    }

    /**
     * @dataProvider getRestrictions
     */
    public function testAConsentingPingOnTheArrivalPageFillsInTheVisitAsItsConsentIsRecorded(string $restriction): void
    {
        $this->restrict($restriction);

        $this->trackPageView(null, 'landing');
        $this->ping('1', 'landing');

        $this->assertSame(1, $this->countVisits(), 'a ping cannot start a visit');
        $this->assertSame('1', (string) $this->fetchVisitColumn('consent'));
        $this->assertSame(self::FULL, $this->fetchResolution());
    }

    /**
     * The compliance page can enforce the resolution restriction on its own, without the other
     * settings of the policy, and consent must keep the visits apart in the same way then.
     */
    public function getRestrictions(): iterable
    {
        yield 'policy' => ['policy'];
        yield 'resolution only' => ['resolution only'];
    }

    /**
     * Baseline: a visit measured without consent keeps its resolution withheld, whatever a later
     * request without consent reports.
     */
    public function testALaterRequestWithoutConsentDoesNotFillInAVisitMeasuredWithoutIt(): void
    {
        $this->enforcePolicy();

        $this->trackPageView(null, 'landing');
        $this->trackPageView(null, 'other');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(self::RESTRICTED, $this->fetchResolution());
    }

    /**
     * Where no policy is enforced, a resolution not known on the first request is filled in from a
     * later one. A consented visit is measured the same way, even by a request that does not
     * repeat the flag.
     *
     * @dataProvider getConsentFlags
     */
    public function testALaterRequestFillsInTheUnknownResolutionOfAConsentedVisit(?string $consent): void
    {
        $this->enforcePolicy();

        $this->tracker->setResolution(0, 0);
        $this->trackPageView('1', 'landing');
        $this->assertSame(self::RESTRICTED, $this->fetchResolution());

        $this->tracker->setResolution(1280, 1024);
        $this->trackPageView($consent, 'other');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(self::FULL, $this->fetchResolution());
    }

    /**
     * @dataProvider getConsentFlags
     */
    public function testALaterRequestDoesNotReplaceTheResolutionOfAConsentedVisit(?string $consent): void
    {
        $this->enforcePolicy();

        $this->trackPageView('1', 'landing');
        $this->tracker->setResolution(800, 600);
        $this->trackPageView($consent, 'other');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(self::FULL, $this->fetchResolution());
    }

    /**
     * Baseline: where no policy is enforced, an unknown resolution is filled in from a later
     * request whatever it reports about consent, as today.
     *
     * @dataProvider getConsentFlags
     */
    public function testALaterRequestFillsInAnUnknownResolutionWhenNoPolicyIsEnforced(?string $consent): void
    {
        $this->tracker->setResolution(0, 0);
        $this->trackPageView(null, 'landing');

        $this->tracker->setResolution(1280, 1024);
        $this->trackPageView($consent, 'other');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(self::FULL, $this->fetchResolution());
    }

    /**
     * A visit does not inherit the consent of the visitor's previous visit.
     */
    public function testANewVisitWithoutConsentIsRestrictedAfterAConsentedOne(): void
    {
        $this->enforcePolicy();

        $this->trackPageView('1', 'landing');
        $this->forceNewVisit(null);

        $this->assertSame(2, $this->countVisits());
        $this->assertSame(self::FULL, $this->fetchResolution(0));
        $this->assertSame(self::RESTRICTED, $this->fetchResolution(1));
    }

    /**
     * Turning consent-aware analytics off does not take back what a consented visit holds, and
     * from then on nothing withheld is filled in, whatever the request reports.
     */
    public function testTurningConsentAwareAnalyticsOffRestrictsOnlyWhatIsMeasuredAfterwards(): void
    {
        $this->enforcePolicy();

        $this->tracker->setResolution(0, 0);
        $this->trackPageView('1', 'landing');
        $this->setConsentAwareAnalytics($this->idSite, false);
        $this->tracker->setResolution(1280, 1024);
        $this->trackPageView('1', 'other');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(self::RESTRICTED, $this->fetchResolution());

        $this->forceNewVisit('1');

        $this->assertSame(2, $this->countVisits());
        $this->assertSame(self::RESTRICTED, $this->fetchResolution(1));
    }

    public function testTheResolutionReportShowsConsentedResolutionsForASiteThatHasOptedIn(): void
    {
        $this->enforcePolicy();

        $this->trackPageView('1');

        $this->assertSame([self::FULL], $this->getResolutionLabels((string) $this->idSite));
    }

    public function testTheResolutionReportStaysUnavailableForASiteThatHasNotOptedIn(): void
    {
        $this->setConsentAwareAnalytics($this->idSite, false);
        $this->enforcePolicy();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ScreenResolutionReportDisabledByCompliancePolicy');

        $this->getResolutionLabels((string) $this->idSite);
    }

    /**
     * @dataProvider getSiteLists
     */
    public function testTheResolutionReportLeavesOutOnlySitesThatHaveNotOptedIn(string $sites): void
    {
        $this->enforcePolicy($this->idSite);
        $this->enforcePolicy($this->idSiteOther);

        $this->trackPageView('1');
        $this->tracker = $this->makeTracker($this->idSiteOther);
        $this->tracker->setResolution(800, 600);
        $this->trackPageView('1');

        $this->assertSame([self::FULL], $this->getResolutionLabels($this->resolveSites($sites)));
    }

    public function getSiteLists(): iterable
    {
        yield 'list' => ['{opted},{other}'];
        yield 'all' => ['all'];
    }

    public function testAUserWithViewAccessSeesTheConsentedResolutions(): void
    {
        $this->enforcePolicy();
        $this->trackPageView('1');

        FakeAccess::$superUser = false;
        FakeAccess::$idSitesView = [$this->idSite];

        $this->assertSame([self::FULL], $this->getResolutionLabels((string) $this->idSite));
    }

    public function testTheResolutionReportStillNeedsViewAccess(): void
    {
        $this->enforcePolicy();
        $this->trackPageView('1');

        FakeAccess::$superUser = false;
        FakeAccess::$idSitesView = [$this->idSiteOther];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasViewAccess');

        $this->getResolutionLabels((string) $this->idSite);
    }

    public function testTheResolutionSegmentIsAvailableOnlyWhereEverySiteHasOptedIn(): void
    {
        $this->enforcePolicy($this->idSite);
        $this->enforcePolicy($this->idSiteOther);

        $this->assertContains('resolution', $this->getSegmentNames((string) $this->idSite));
        $this->assertNotContains('resolution', $this->getSegmentNames((string) $this->idSiteOther));
        $this->assertNotContains('resolution', $this->getSegmentNames($this->idSite . ',' . $this->idSiteOther));

        // the policy limits other segments as well, and those stay hidden
        $this->assertNotContains('userId', $this->getSegmentNames((string) $this->idSite));
    }

    /**
     * @dataProvider getRequestedSitesForReportVisibility
     */
    public function testTheResolutionReportIsOfferedWhereARequestedSiteHasOptedIn($requested, bool $expected): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true);

        if (null !== $requested) {
            $_GET['idSite'] = $this->resolveSites($requested);
        }

        $this->assertSame($expected, (new GetResolution())->isEnabled());
    }

    public function getRequestedSitesForReportVisibility(): iterable
    {
        yield 'opted-in site' => ['{opted}', true];
        yield 'site not opted in' => ['{other}', false];
        yield 'list including an opted-in site' => ['{other},{opted}', true];
        yield 'invalid' => ['abc', false];
        yield 'no site' => [null, false];
    }

    /**
     * Report metadata is asked for a site that need not be the one in the request, as when a
     * scheduled report is sent from the command line.
     *
     * @dataProvider getReportMetadataRequests
     */
    public function testTheResolutionReportIsListedInTheMetadataOfASiteThatHasOptedIn(string $siteParameter, int $optedIn): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true);

        $reports = Request::processRequest('API.getReportMetadata', [
            $siteParameter => (string) ($optedIn ? $this->idSite : $this->idSiteOther),
            'period' => 'day',
            'date' => $this->testDate->toString(),
        ]);
        $listed = in_array('Resolution.getResolution', array_map(static function (array $report): string {
            return $report['module'] . '.' . $report['action'];
        }, $reports), true);

        $this->assertSame((bool) $optedIn, $listed);
    }

    /**
     * The site report metadata describes is the one it is asked for, not the one in the request.
     *
     * @dataProvider getReportMetadataSites
     */
    public function testTheResolutionReportMetadataFollowsTheSiteItDescribes(int $optedInDescribed): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true);

        $described = $optedInDescribed ? $this->idSite : $this->idSiteOther;
        $_GET['idSite'] = (string) ($optedInDescribed ? $this->idSiteOther : $this->idSite);

        $reports = [];
        (new GetResolution())->configureReportMetadata($reports, ['idSite' => $described]);

        $this->assertSame((bool) $optedInDescribed, !empty($reports));
    }

    public function getReportMetadataSites(): iterable
    {
        yield 'opted-in site described, other site requested' => [1];
        yield 'other site described, opted-in site requested' => [0];
    }

    public function getReportMetadataRequests(): iterable
    {
        yield 'idSite, opted in' => ['idSite', 1];
        yield 'idSites, opted in' => ['idSites', 1];
        yield 'idSite, not opted in' => ['idSite', 0];
        yield 'idSites, not opted in' => ['idSites', 0];
    }

    /**
     * Consent is not measured where cookieless tracking is forced, so no consented visit stores
     * a resolution there and there is nothing for the report or segment to show.
     */
    public function testTheResolutionReportAndSegmentStayHiddenWhenCookielessTrackingIsForced(): void
    {
        $this->enforcePolicy();
        $this->setForcedCookielessTracking(true);

        $this->assertNotContains('resolution', $this->getSegmentNames((string) $this->idSite));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ScreenResolutionReportDisabledByCompliancePolicy');

        $this->getResolutionLabels((string) $this->idSite);
    }

    /**
     * The compliance page lists each setting as the API describes it for the site.
     */
    public function testTheCompliancePageDescribesConsentOnlyForASiteThatHasOptedIn(): void
    {
        $featureFlags = Config::getInstance()->FeatureFlags;
        Config::getInstance()->FeatureFlags = ['GranularPrivacyCompliance_feature' => 'enabled'];

        try {
            foreach ([$this->idSite => 'ConsentAware', $this->idSiteOther => ''] as $idSite => $suffix) {
                $policy = Request::processRequest('PrivacyManager.getCompliancePolicySettings', [
                    'idSite' => $idSite,
                    'compliancePolicy' => 'cnil_v1',
                ]);

                $setting = array_column($policy['settings'], null, 'id')[ScreenResolutionDetectionDisabled::getPolicySettingId()];

                $this->assertSame('Resolution_ScreenResolutionDetectionDisabledWhatItDoes' . $suffix, $this->translationKey($setting['whatItDoes']));
                $this->assertSame('Resolution_ScreenResolutionDetectionDisabledImpact' . $suffix, $this->translationKey($setting['impact']));
            }
        } finally {
            Config::getInstance()->FeatureFlags = $featureFlags;
        }
    }

    public function testTheComplianceWordingDescribesConsentOnlyForASiteThatHasOptedIn(): void
    {
        $this->assertSame(
            'Resolution_ScreenResolutionDetectionDisabledWhatItDoesConsentAware',
            $this->translationKey(ScreenResolutionDetectionDisabled::getWhatItDoes($this->idSite))
        );
        $this->assertSame(
            'Resolution_ScreenResolutionDetectionDisabledImpactConsentAware',
            $this->translationKey(ScreenResolutionDetectionDisabled::getImpact($this->idSite))
        );

        foreach ([$this->idSiteOther, null] as $idSite) {
            $this->assertSame(
                'Resolution_ScreenResolutionDetectionDisabledWhatItDoes',
                $this->translationKey(ScreenResolutionDetectionDisabled::getWhatItDoes($idSite))
            );
            $this->assertSame(
                'Resolution_ScreenResolutionDetectionDisabledImpact',
                $this->translationKey(ScreenResolutionDetectionDisabled::getImpact($idSite))
            );
        }
    }

    private function translationKey(string $text): string
    {
        $translations = json_decode(
            (string) file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/Resolution/lang/en.json'),
            true
        )['Resolution'];

        $key = array_search($text, $translations, true);

        return false === $key ? $text : 'Resolution_' . $key;
    }

    /**
     * Data providers run before the sites exist, so they name them by role.
     */
    private function resolveSites(string $sites): string
    {
        return strtr($sites, ['{opted}' => (string) $this->idSite, '{other}' => (string) $this->idSiteOther]);
    }

    private function restrict(string $restriction): void
    {
        if ('policy' === $restriction) {
            $this->enforcePolicy();
            return;
        }

        PolicyManager::setPolicySettingEnforcedStatuses(
            CnilPolicy::class,
            [ScreenResolutionDetectionDisabled::getPolicySettingId() => 1],
            $this->idSite
        );
        Cache::deleteTrackerCache();
    }

    private function enforcePolicy(?int $idSite = null): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, $idSite ?? $this->idSite);
    }

    private function setConsentAwareAnalytics(int $idSite, bool $enabled): void
    {
        $measurable = ConsentAwareAnalyticsSetting::getMeasurableSetting($idSite);
        $measurable->setValue($enabled);
        $measurable->save();

        Cache::deleteTrackerCache();
    }

    private function setForcedCookielessTracking(bool $enabled): void
    {
        $config = new PrivacyManagerConfig();
        $config->forceCookielessTracking = $enabled;

        Cache::deleteTrackerCache();
    }

    private function makeTracker(int $idSite): \MatomoTracker
    {
        $tracker = Fixture::getTracker($idSite, $this->testDate->getDatetime(), $defaultInit = true, $useLocal = false);
        $tracker->setUrlReferrer('');
        $tracker->setResolution(1280, 1024);

        return $tracker;
    }

    private function trackMalformedResolution(int $idSite): void
    {
        $this->tracker = $this->makeTracker($idSite);
        $this->tracker->setResolution(0, 0);
        $this->prepareRequest('1', 'landing');
        $this->tracker->setCustomTrackingParameter('res[]', 'x');
        Fixture::checkResponse($this->tracker->doTrackPageView('Landing'));
    }

    private function ping(?string $consent, string $page): void
    {
        $this->prepareRequest($consent, $page);
        Fixture::checkResponse($this->tracker->doPing());
    }

    private function trackPageView(?string $consent, string $page = 'landing'): void
    {
        $this->prepareRequest($consent, $page);
        Fixture::checkResponse($this->tracker->doTrackPageView(ucfirst($page)));
    }

    private function forceNewVisit(?string $consent): void
    {
        $this->prepareRequest($consent, 'forced');
        $this->tracker->setCustomTrackingParameter('new_visit', '1');
        Fixture::checkResponse($this->tracker->doTrackPageView('Forced'));
    }

    private function prepareRequest(?string $consent, string $page): void
    {
        $this->testDate = $this->testDate->addPeriod(1, 'minute');
        $this->tracker->setForceVisitDateTime($this->testDate->getDatetime());
        $this->tracker->clearCustomTrackingParameters();

        if (null !== $consent) {
            $this->tracker->setCustomTrackingParameter('consent', $consent);
        }

        $this->tracker->setUrl('http://example.com/' . $page);
    }

    /**
     * @return list<string>
     */
    private function getResolutionLabels(string $idSite): array
    {
        /** @var DataTable|DataTable\Map $report */
        $report = Request::processRequest('Resolution.getResolution', [
            'idSite' => $idSite,
            'period' => 'day',
            'date' => $this->testDate->toString(),
        ]);

        return array_values($report->getColumn('label'));
    }

    /**
     * @return list<string>
     */
    private function getSegmentNames(string $idSites): array
    {
        $segments = Request::processRequest('API.getSegmentsMetadata', ['idSites' => $idSites]);

        return array_column($segments, 'segment');
    }

    private function fetchResolution(int $offset = 0): string
    {
        return (string) $this->fetchVisitColumn('config_resolution', $offset);
    }

    private function fetchVisitColumn(string $column, int $offset = 0, ?int $idSite = null)
    {
        return Db::fetchOne(
            'SELECT `' . $column . '` FROM ' . Common::prefixTable('log_visit')
            . ' WHERE idsite = ? ORDER BY idvisit ASC LIMIT 1 OFFSET ' . $offset,
            [$idSite ?? $this->idSite]
        );
    }

    private function countVisits(): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ?',
            [$this->idSite]
        );
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
