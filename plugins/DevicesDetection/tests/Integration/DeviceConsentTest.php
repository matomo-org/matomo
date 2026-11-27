<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\DevicesDetection\tests\Integration;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\DevicesDetection\Reports\GetModel;
use Piwik\Plugins\DevicesDetection\Settings\DeviceModelDetectionDisabled;
use Piwik\Plugins\DevicesDetection\Settings\OnlyMajorVersions;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics as ConsentAwareAnalyticsSetting;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;

/**
 * A compliance policy can withhold the device model and all but the major browser and operating
 * system versions from every visitor. On a site measuring consent-aware analytics, a visitor who
 * has consented is measured as if the policy did not restrict them.
 *
 * @group DevicesDetection
 * @group Plugins
 */
class DeviceConsentTest extends IntegrationTestCase
{
    private const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4_1 like Mac OS X) AppleWebKit/605.1.15 '
        . '(KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1';

    private const FULL = ['iPhone', '17.4', '17.4'];
    private const RESTRICTED = ['generic smartphone', '17', '17'];

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

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'Device consent', 'http://example.com');
        $this->idSiteOther = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'Other', 'http://example.org');
        $this->testDate = Date::factory('2024-01-02 10:00:00');

        $this->tracker = $this->makeTracker($this->idSite);

        $this->setConsentAwareAnalytics($this->idSite, true);
    }

    public function tearDown(): void
    {
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
    public function testANewVisitIsRestrictedUnlessTheRequestCarriesConsent(?string $consent, array $expected): void
    {
        $this->enforcePolicy();

        $this->trackPageView($consent);

        $this->assertSame(1, $this->countVisits());
        $this->assertSame($expected, $this->fetchDeviceColumns());
    }

    public function getConsentValues(): iterable
    {
        yield 'consent=1' => ['1', self::FULL];
        yield 'no flag' => [null, self::RESTRICTED];
        yield 'consent=0' => ['0', self::RESTRICTED];
        yield 'empty consent' => ['', self::RESTRICTED];
        yield 'consent=true' => ['true', self::RESTRICTED];
        yield 'consent=2' => ['2', self::RESTRICTED];
    }

    public function testAConsentFlagSentAsAnArrayIsNotConsent(): void
    {
        $this->enforcePolicy();

        $this->prepareRequest(null, 'landing');
        $this->tracker->setCustomTrackingParameter('consent[]', '1');
        Fixture::checkResponse($this->tracker->doTrackPageView('Landing'));

        $this->assertSame(self::RESTRICTED, $this->fetchDeviceColumns());
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

        $this->assertSame(self::RESTRICTED, $this->fetchDeviceColumns());
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
        $this->assertSame(self::RESTRICTED, $this->fetchDeviceColumns());
    }

    /**
     * Baseline: where no policy is enforced, everything is detected for every visitor, as today.
     *
     * @dataProvider getConsentFlags
     */
    public function testEverythingIsDetectedWhenNoPolicyIsEnforced(?string $consent): void
    {
        $this->trackPageView($consent);

        $this->assertSame(self::FULL, $this->fetchDeviceColumns());
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

        $this->assertSame(self::RESTRICTED, $this->fetchDeviceColumns());
    }

    /**
     * Consent given while the visitor is still on their arrival page continues the same visit, and
     * what was withheld for it is detected from the request that brought the consent.
     */
    public function testConsentOnTheArrivalPageReplacesWhatWasWithheldFromTheVisit(): void
    {
        $this->enforcePolicy();

        $this->trackPageView(null, 'landing');
        $this->assertSame(self::RESTRICTED, $this->fetchDeviceColumns());

        $this->trackPageView('1', 'landing');

        $this->assertSame(1, $this->countVisits(), 'consent on the arrival page continues the visit');
        $this->assertSame('1', (string) $this->fetchVisitColumn('consent'));
        $this->assertSame(self::FULL, $this->fetchDeviceColumns());
    }

    /**
     * Consent given once the visitor has moved on starts a visit of its own. The visit measured
     * before consent keeps what was stored for it.
     */
    public function testConsentAfterNavigatingStartsAVisitThatHoldsEverything(): void
    {
        $this->enforcePolicy();

        $this->trackPageView(null, 'landing');
        $this->trackPageView(null, 'other');
        $this->trackPageView('1', 'other');

        $this->assertSame(2, $this->countVisits(), 'consent away from the arrival page starts its own visit');
        $this->assertSame(self::RESTRICTED, $this->fetchDeviceColumns(0));
        $this->assertSame(self::FULL, $this->fetchDeviceColumns(1));
    }

    /**
     * The tracker pings the server by itself when consent is given, and that ping can land after
     * the visitor has moved on. It cannot start a visit, so nothing may be detected from it for the
     * visit measured before consent.
     */
    public function testAConsentingPingDoesNotChangeTheEarlierVisit(): void
    {
        $this->enforcePolicy();

        $this->trackPageView(null, 'landing');
        $this->trackPageView(null, 'other');
        $this->ping('1', 'other');

        $this->assertSame(1, $this->countVisits(), 'a ping cannot start a visit');
        $this->assertSame(self::RESTRICTED, $this->fetchDeviceColumns());
    }

    /**
     * A ping records no page of its own, so it leaves the visitor where the visit already was. On
     * the arrival page that is still the landing consent continues, and the visit is filled in
     * as the consent column records it.
     */
    public function testAConsentingPingOnTheArrivalPageFillsInTheVisitAsItsConsentIsRecorded(): void
    {
        $this->enforcePolicy();

        $this->trackPageView(null, 'landing');
        $this->ping('1', 'landing');

        $this->assertSame(1, $this->countVisits(), 'a ping cannot start a visit');
        $this->assertSame('1', (string) $this->fetchVisitColumn('consent'));
        $this->assertSame(self::FULL, $this->fetchDeviceColumns());
    }

    public function testALaterRequestWithoutTheFlagDoesNotRestrictAConsentedVisit(): void
    {
        $this->enforcePolicy();

        $this->trackPageView('1', 'landing');
        $this->trackPageView(null, 'other');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(self::FULL, $this->fetchDeviceColumns());
    }

    /**
     * What was detected when consent reached the visit stays. A later request, consented or not,
     * does not detect it again.
     *
     * @dataProvider getConsentFlags
     */
    public function testALaterRequestDoesNotDetectAConsentedVisitAgain(?string $consent): void
    {
        $this->enforcePolicy();

        $this->trackPageView('1', 'landing');
        $this->setClientHintsForDifferentDevice();
        $this->trackPageView($consent, 'other');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(self::FULL, $this->fetchDeviceColumns());
    }

    /**
     * Baseline: where nothing is withheld there is nothing for consent to replace, so a later
     * consented request leaves the visit as it is today.
     */
    public function testALaterConsentedRequestDoesNotDetectAVisitAgainWhenNoPolicyIsEnforced(): void
    {
        $this->trackPageView(null, 'landing');
        $this->setClientHintsForDifferentDevice();
        $this->trackPageView('1', 'landing');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(self::FULL, $this->fetchDeviceColumns());
    }

    /**
     * Turning consent-aware analytics off does not take back what a consented visit holds, and
     * from then on visits are restricted whatever the request reports.
     */
    public function testTurningConsentAwareAnalyticsOffRestrictsOnlyWhatIsMeasuredAfterwards(): void
    {
        $this->enforcePolicy();

        $this->trackPageView('1', 'landing');
        $this->setConsentAwareAnalytics($this->idSite, false);
        $this->trackPageView('1', 'other');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(self::FULL, $this->fetchDeviceColumns());

        $this->forceNewVisit('1');

        $this->assertSame(2, $this->countVisits());
        $this->assertSame(self::RESTRICTED, $this->fetchDeviceColumns(1));
    }

    public function testTheDeviceModelReportShowsConsentedModelsForASiteThatHasOptedIn(): void
    {
        $this->enforcePolicy();

        $this->trackPageView('1');

        $this->assertSame(['Apple - iPhone'], $this->getModelLabels((string) $this->idSite));
    }

    public function testTheDeviceModelReportStaysUnavailableForASiteThatHasNotOptedIn(): void
    {
        $this->setConsentAwareAnalytics($this->idSite, false);
        $this->enforcePolicy();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('DeviceModelReportDisabledByCompliancePolicy');

        $this->getModelLabels((string) $this->idSite);
    }

    /**
     * @dataProvider getSiteLists
     */
    public function testTheDeviceModelReportLeavesOutOnlySitesThatHaveNotOptedIn(string $sites): void
    {
        $this->enforcePolicy($this->idSite);
        $this->enforcePolicy($this->idSiteOther);

        $this->trackPageView('1');
        $this->tracker = $this->makeTracker($this->idSiteOther);
        $this->trackPageView('1');

        $this->assertSame(['Apple - iPhone'], $this->getModelLabels($sites));
    }

    public function getSiteLists(): iterable
    {
        yield 'list' => ['1,2'];
        yield 'all' => ['all'];
    }

    public function testTheDeviceModelSegmentIsAvailableOnlyWhereEverySiteHasOptedIn(): void
    {
        $this->enforcePolicy($this->idSite);
        $this->enforcePolicy($this->idSiteOther);

        $this->assertContains('deviceModel', $this->getSegmentNames((string) $this->idSite));
        $this->assertNotContains('deviceModel', $this->getSegmentNames((string) $this->idSiteOther));
        $this->assertNotContains('deviceModel', $this->getSegmentNames($this->idSite . ',' . $this->idSiteOther));

        // the policy limits other segments as well, and those stay hidden
        $this->assertNotContains('userId', $this->getSegmentNames((string) $this->idSite));
    }

    /**
     * @dataProvider getRequestedSitesForReportVisibility
     */
    public function testTheDeviceModelReportIsOfferedWhereARequestedSiteHasOptedIn($requested, bool $expected): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true);

        if (null !== $requested) {
            $_GET['idSite'] = $requested;
        }

        $this->assertSame($expected, (new GetModel())->isEnabled());
    }

    public function getRequestedSitesForReportVisibility(): iterable
    {
        yield 'opted-in site' => ['1', true];
        yield 'site not opted in' => ['2', false];
        yield 'list including an opted-in site' => ['2,1', true];
        yield 'invalid' => ['abc', false];
        yield 'no site' => [null, false];
    }

    /**
     * Report metadata is asked for a site that need not be the one in the request, as when a
     * scheduled report is sent from the command line.
     *
     * @dataProvider getReportMetadataRequests
     */
    public function testTheDeviceModelReportIsListedInTheMetadataOfASiteThatHasOptedIn(string $siteParameter, int $optedIn): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true);

        $reports = Request::processRequest('API.getReportMetadata', [
            $siteParameter => (string) ($optedIn ? $this->idSite : $this->idSiteOther),
            'period' => 'day',
            'date' => $this->testDate->toString(),
        ]);
        $listed = in_array('DevicesDetection.getModel', array_map(static function (array $report): string {
            return $report['module'] . '.' . $report['action'];
        }, $reports), true);

        $this->assertSame((bool) $optedIn, $listed);
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
     * a device model there and there is nothing for the report or segment to show.
     */
    public function testTheDeviceModelReportAndSegmentStayHiddenWhenCookielessTrackingIsForced(): void
    {
        $this->enforcePolicy();
        $this->setForcedCookielessTracking(true);

        $this->assertNotContains('deviceModel', $this->getSegmentNames((string) $this->idSite));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('DeviceModelReportDisabledByCompliancePolicy');

        $this->getModelLabels((string) $this->idSite);
    }

    /**
     * The compliance page lists each setting as the API describes it for the site.
     */
    public function testTheCompliancePageDescribesConsentOnlyForASiteThatHasOptedIn(): void
    {
        $featureFlags = Config::getInstance()->FeatureFlags;
        Config::getInstance()->FeatureFlags = ['GranularPrivacyCompliance_feature' => 'enabled'];

        try {
            $this->assertComplianceWording();
        } finally {
            Config::getInstance()->FeatureFlags = $featureFlags;
        }
    }

    private function assertComplianceWording(): void
    {
        $expected = [
            $this->idSite => 'ConsentAware',
            $this->idSiteOther => '',
        ];

        foreach ($expected as $idSite => $suffix) {
            $policy = Request::processRequest('PrivacyManager.getCompliancePolicySettings', [
                'idSite' => $idSite,
                'compliancePolicy' => 'cnil_v1',
            ]);

            $settings = array_column($policy['settings'], null, 'id');

            $model = $settings[DeviceModelDetectionDisabled::getPolicySettingId()];
            $versions = $settings[OnlyMajorVersions::getPolicySettingId()];

            $this->assertSame('DevicesDetection_DeviceModelDetectionDisabledWhatItDoes' . $suffix, $this->translationKey($model['whatItDoes']));
            $this->assertSame('DevicesDetection_DeviceModelDetectionDisabledImpact' . $suffix, $this->translationKey($model['impact']));
            $this->assertSame('DevicesDetection_OnlyMajorVersionsSettingWhatItDoes' . $suffix, $this->translationKey($versions['whatItDoes']));
            $this->assertSame('DevicesDetection_OnlyMajorVersionsSettingImpact' . $suffix, $this->translationKey($versions['impact']));
        }
    }

    public function testTheComplianceWordingDescribesConsentOnlyForASiteThatHasOptedIn(): void
    {
        $this->assertSame(
            'DevicesDetection_DeviceModelDetectionDisabledWhatItDoesConsentAware',
            $this->translationKey(DeviceModelDetectionDisabled::getWhatItDoes($this->idSite))
        );
        $this->assertSame(
            'DevicesDetection_DeviceModelDetectionDisabledImpactConsentAware',
            $this->translationKey(DeviceModelDetectionDisabled::getImpact($this->idSite))
        );
        $this->assertSame(
            'DevicesDetection_OnlyMajorVersionsSettingWhatItDoesConsentAware',
            $this->translationKey(OnlyMajorVersions::getWhatItDoes($this->idSite))
        );
        $this->assertSame(
            'DevicesDetection_OnlyMajorVersionsSettingImpactConsentAware',
            $this->translationKey(OnlyMajorVersions::getImpact($this->idSite))
        );

        foreach ([$this->idSiteOther, null] as $idSite) {
            $this->assertSame(
                'DevicesDetection_DeviceModelDetectionDisabledWhatItDoes',
                $this->translationKey(DeviceModelDetectionDisabled::getWhatItDoes($idSite))
            );
            $this->assertSame(
                'DevicesDetection_DeviceModelDetectionDisabledImpact',
                $this->translationKey(DeviceModelDetectionDisabled::getImpact($idSite))
            );
            $this->assertSame(
                'DevicesDetection_OnlyMajorVersionsSettingWhatItDoes',
                $this->translationKey(OnlyMajorVersions::getWhatItDoes($idSite))
            );
            $this->assertSame(
                'DevicesDetection_OnlyMajorVersionsSettingImpact',
                $this->translationKey(OnlyMajorVersions::getImpact($idSite))
            );
        }
    }

    public function testTheInstanceComplianceWordingDescribesConsentWhereTheInstanceHasOptedIn(): void
    {
        ConsentAwareAnalyticsSetting::setEnabled(true);

        $this->assertSame(
            'DevicesDetection_DeviceModelDetectionDisabledWhatItDoesConsentAware',
            $this->translationKey(DeviceModelDetectionDisabled::getWhatItDoes(null))
        );
        $this->assertSame(
            'DevicesDetection_DeviceModelDetectionDisabledImpactConsentAware',
            $this->translationKey(DeviceModelDetectionDisabled::getImpact(null))
        );
        $this->assertSame(
            'DevicesDetection_OnlyMajorVersionsSettingWhatItDoesConsentAware',
            $this->translationKey(OnlyMajorVersions::getWhatItDoes(null))
        );
        $this->assertSame(
            'DevicesDetection_OnlyMajorVersionsSettingImpactConsentAware',
            $this->translationKey(OnlyMajorVersions::getImpact(null))
        );
    }

    private function translationKey(string $text): string
    {
        $translations = json_decode(
            (string) file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/lang/en.json'),
            true
        )['DevicesDetection'];

        $key = array_search($text, $translations, true);

        return false === $key ? $text : 'DevicesDetection_' . $key;
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
        $tracker->setUserAgent(self::IPHONE);

        return $tracker;
    }

    /**
     * A different device reported through client hints, which do not change the visitor's
     * configuration id, so the request is still recorded against the same visit.
     */
    private function setClientHintsForDifferentDevice(): void
    {
        $this->tracker->setClientHints('Pixel 8', 'Android', '14.0.0', '"Google Chrome";v="120"', '120.0.6099.144');
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
    private function getModelLabels(string $idSite): array
    {
        /** @var DataTable|DataTable\Map $report */
        $report = Request::processRequest('DevicesDetection.getModel', [
            'idSite' => $idSite,
            'period' => 'day',
            'date' => $this->testDate->toString(),
            'flat' => '1',
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

    /**
     * @return list<string>
     */
    private function fetchDeviceColumns(int $offset = 0): array
    {
        return [
            (string) $this->fetchVisitColumn('config_device_model', $offset),
            (string) $this->fetchVisitColumn('config_browser_version', $offset),
            (string) $this->fetchVisitColumn('config_os_version', $offset),
        ];
    }

    private function fetchVisitColumn(string $column, int $offset = 0)
    {
        return Db::fetchOne(
            'SELECT `' . $column . '` FROM ' . Common::prefixTable('log_visit')
            . ' WHERE idsite = ? ORDER BY idvisit ASC LIMIT 1 OFFSET ' . $offset,
            [$this->idSite]
        );
    }

    private function countVisits(): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ?',
            [$this->idSite]
        );
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
