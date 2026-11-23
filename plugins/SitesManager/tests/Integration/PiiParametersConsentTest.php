<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\SitesManager\tests\Integration;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\PrivacyManager\API as PrivacyManagerApi;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics as ConsentAwareAnalyticsSetting;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;
use Piwik\Plugins\SitesManager\Settings\FilterPIIParameters;
use Piwik\Plugins\SitesManager\SitesManager;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\PageUrl;

/**
 * A compliance policy requiring Matomo's recommended PII list to be filtered from URLs strips
 * those parameters for every visitor, including one who has consented. Consent lifts what the
 * policy required, and nothing more: parameters the admin excluded themselves are still removed.
 *
 * @group SitesManager
 * @group Plugins
 */
class PiiParametersConsentTest extends IntegrationTestCase
{
    /**
     * `email` is on the recommended PII list, `sitep` is excluded by the site itself and `keep` by
     * nothing at all.
     */
    private const QUERY = 'email=someone%40example.com&sitep=1&keep=1';

    /** @var int */
    private $idSite;

    /** @var Date */
    private $testDate;

    /** @var \MatomoTracker */
    private $tracker;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = $this->createSite();
        $this->testDate = Date::factory('2024-01-02 10:00:00');
        $this->tracker = $this->getTracker($this->idSite);
    }

    public function tearDown(): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $this->idSite);
        Cache::deleteTrackerCache();

        parent::tearDown();
    }

    public function getConsentCases(): iterable
    {
        // policy enforced, site opted in
        yield 'no consent parameter' => [true, true, null, false];
        yield 'consent=0' => [true, true, '0', false];
        yield 'empty consent' => [true, true, '', false];
        yield 'consent=2' => [true, true, '2', false];
        yield 'consent=true' => [true, true, 'true', false];
        yield 'consent with a leading space' => [true, true, ' 1', false];
        yield 'consent with a trailing space' => [true, true, '1 ', false];
        yield 'consent=1' => [true, true, '1', true];

        // policy enforced, site not opted in: nothing changes, whatever the request reports
        yield 'not opted in, no consent' => [true, false, null, false];
        yield 'not opted in, consent=1' => [true, false, '1', false];

        // no policy: nothing for consent to lift, and nothing filtered beyond what it is today
        yield 'no policy, no consent' => [false, true, null, true];
        yield 'no policy, consent=1' => [false, true, '1', true];
        yield 'no policy, not opted in' => [false, false, '1', true];
    }

    /**
     * @dataProvider getConsentCases
     */
    public function testRecommendedPiiParametersAreKeptOnlyForAConsentedRequest(
        bool $enforcePolicy,
        bool $optIn,
        ?string $consent,
        bool $expectPiiKept
    ): void {
        if ($optIn) {
            $this->enableConsentAwareAnalytics();
        }

        if ($enforcePolicy) {
            $this->enforcePolicy();
        }

        $this->trackPageView($consent);

        $this->assertStoredQuery($expectPiiKept, $this->fetchEntryPageUrl());
    }

    public function testAnArrayConsentParameterDoesNotCount(): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $this->prepareRequest(null);
        $this->tracker->setCustomTrackingParameter('consent[]', '1');
        Fixture::checkResponse($this->tracker->doTrackPageView('Landing'));

        $this->assertStoredQuery(false, $this->fetchEntryPageUrl());
    }

    /**
     * Choosing the recommended PII list is the admin's own decision when they made it themselves,
     * not something the policy required of them, so consent does not undo it.
     */
    public function testConsentDoesNotLiftTheRecommendedListTheAdminChoseThemselves(): void
    {
        SitesManagerApi::getInstance()->setGlobalQueryParamExclusion(
            SitesManager::URL_PARAM_EXCLUSION_TYPE_NAME_MATOMO_RECOMMENDED_PII
        );
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $this->trackPageView('1');

        $this->assertStoredQuery(false, $this->fetchEntryPageUrl());
    }

    /**
     * Consent falls back to what the admin configured, and while the policy is in force the
     * recommended list takes the place of their own global list. A consented visitor has the
     * admin's list removed instead, and keeps the recommended parameters.
     */
    public function testConsentFallsBackToTheAdminsOwnGlobalList(): void
    {
        SitesManagerApi::getInstance()->setGlobalQueryParamExclusion(
            SitesManager::URL_PARAM_EXCLUSION_TYPE_NAME_CUSTOM,
            'adminp'
        );
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $this->trackPageView('1', 'consented', '&adminp=1');
        $consented = $this->fetchActionNamesForPage('consented')[0];

        $this->assertStringContainsString('email=', $consented);
        $this->assertStringNotContainsString('adminp', $consented);
        $this->assertStringNotContainsString('sitep', $consented);

        // a visit of its own, by a visitor who has not consented
        $this->testDate = $this->testDate->addHour(2);
        $this->tracker = $this->getTracker($this->idSite);
        $this->trackPageView(null, 'unconsented', '&adminp=1');

        $this->assertSame(2, $this->countVisits());
        $unconsented = $this->fetchActionNamesForPage('unconsented')[0];

        $this->assertStringNotContainsString('email=', $unconsented);
        $this->assertStringContainsString('adminp=1', $unconsented, 'unchanged from today for a visitor without consent');
    }

    /**
     * The tracker pings the server when consent is given on the page the visit arrived on, and
     * that page was recorded before consent, without the recommended parameters. The ping must
     * still find it there so that consent completes the visit rather than being dropped.
     */
    public function testConsentGivenOnTheArrivalPageCompletesTheVisit(): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $this->trackPageView(null);
        $this->ping('1');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(1, (int) $this->fetchVisitColumn('consent'), 'consent did not reach the visit it was given on');
    }

    /**
     * A page view reporting consent on the arrival page continues the visit in place, as it does
     * for a URL without any recommended parameters, rather than being taken for a different page.
     */
    public function testAConsentedPageViewOfTheArrivalPageContinuesTheVisit(): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $this->trackPageView(null);
        $this->trackPageView('1');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(1, (int) $this->fetchVisitColumn('consent'));
    }

    public function testEventUrlKeepsTheRecommendedParametersForAConsentedRequest(): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $this->prepareRequest('1');
        Fixture::checkResponse($this->tracker->doTrackEvent('Category', 'Action'));

        $this->assertStoredQuery(true, $this->fetchActionNamesForPage('landing')[0] ?? '');
    }

    /**
     * Consent reported part way through a visit measured without it does not apply to that visit
     * until a request that can start a new one arrives. An event sent in between is recorded on
     * the visit without consent, and so as one without consent.
     */
    public function testAConsentedEventOnAnUnconsentedVisitIsRecordedWithoutConsent(): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $this->trackPageView(null, 'landing');
        $this->trackPageView(null, 'other');

        $this->prepareRequest('1', 'other');
        Fixture::checkResponse($this->tracker->doTrackEvent('Consent', 'Given'));

        $this->assertSame(1, $this->countVisits());
        $this->assertEmpty($this->fetchVisitColumn('consent'), 'the event cannot start a consented visit');

        $names = $this->fetchActionNamesForPage('other');
        $this->assertCount(2, $names);

        foreach ($names as $name) {
            $this->assertStoredQuery(false, $name);
        }
    }

    /**
     * As for an event, a manual goal conversion reporting consent is recorded on a visit that has
     * none, and so as one without consent.
     */
    public function testAConsentedGoalConversionOnAnUnconsentedVisitIsRecordedWithoutConsent(): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $idGoal = GoalsApi::getInstance()->addGoal($this->idSite, 'Goal', 'manually', '', '');
        Cache::deleteTrackerCache();

        $this->trackPageView(null, 'landing');
        $this->trackPageView(null, 'other');

        $this->prepareRequest('1', 'other');
        Fixture::checkResponse($this->tracker->doTrackGoal($idGoal));

        $this->assertSame(1, $this->countVisits());
        $this->assertEmpty($this->fetchVisitColumn('consent'));
        $this->assertStoredQuery(false, $this->fetchConversionUrl());
    }

    /**
     * Consent belongs to the visit, so a later request that does not repeat the flag - one sent by
     * a tracker that does not remember consent, say - is recorded as the rest of the visit is, and
     * the same page is not recorded under two names in one visit.
     */
    public function testARequestWithoutTheFlagInAConsentedVisitIsRecordedWithConsent(): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $idGoal = GoalsApi::getInstance()->addGoal($this->idSite, 'Goal', 'manually', '', '');
        Cache::deleteTrackerCache();

        $this->trackPageView('1', 'landing');
        $this->trackPageView(null, 'other');
        $this->trackPageView(null, 'landing');

        $this->prepareRequest(null, 'other');
        Fixture::checkResponse($this->tracker->doTrackGoal($idGoal));

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(1, (int) $this->fetchVisitColumn('consent'));

        $this->assertStoredQuery(true, $this->fetchActionNamesForPage('other')[0]);

        $landing = $this->fetchActionNamesForPage('landing');
        $this->assertCount(2, $landing);
        $this->assertSame($landing[0], $landing[1], 'the same page recorded under two names in one visit');
        $this->assertStoredQuery(true, $landing[0]);

        $this->assertStoredQuery(true, $this->fetchConversionUrl());
    }

    /**
     * A URL cleaned again for the visit's consent is cleaned from what the request sent, so a long
     * one is recorded whole and under the same name as the page view that carried the flag.
     */
    public function testALongUrlCleanedAgainForTheVisitsConsentIsRecordedWhole(): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        // under the URL length limit as sent, over it once each & is HTML-escaped
        $longQuery = '';
        for ($i = 1; $i <= 120; $i++) {
            $longQuery .= '&p' . $i . '=v';
        }

        $this->trackPageView('1', 'landing', $longQuery);
        $this->trackPageView(null, 'landing', $longQuery);

        $landing = $this->fetchActionNamesForPage('landing');
        $this->assertCount(2, $landing);
        $this->assertSame($landing[0], $landing[1], 'the same page recorded under two names in one visit');
        $this->assertStringEndsWith('&p120=v', $landing[1]);
        $this->assertStoredQuery(true, $landing[1]);
    }

    /**
     * Where filtering the recommended parameters is the only restriction consent lifts on a site,
     * consent given once the visitor has moved on still starts a visit of its own, so that pages
     * recorded with the parameters and pages recorded without them are not one visit.
     */
    public function testConsentAfterNavigatingStartsANewVisitWhereOnlyTheFilterIsLifted(): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();
        $this->enforceOnlyThePiiFilter();

        $this->trackPageView(null, 'landing');
        $this->trackPageView(null, 'other');
        $this->trackPageView('1', 'third');

        $this->assertSame(2, $this->countVisits(), 'consent away from the arrival page starts its own visit');
        $this->assertEmpty($this->fetchVisitColumn('consent'));
        $this->assertStoredQuery(true, $this->fetchActionNamesForPage('third')[0]);
    }

    /**
     * The usual configuration: the admin excluded nothing themselves, so consent lifts the whole
     * of what is filtered from the URL.
     */
    public function testConsentKeepsTheRecommendedParametersWhereTheAdminExcludedNothing(): void
    {
        $idSite = (int) Fixture::createWebsite('2024-01-01 00:00:00', 0, 'No exclusions', 'http://example.com');
        $this->idSite = $idSite;
        $this->tracker = $this->getTracker($idSite);
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $this->trackPageView('1');

        $url = $this->fetchEntryPageUrl();
        $this->assertStringContainsString('email=', $url);
        $this->assertStringContainsString('sitep=1', $url);
        $this->assertStringContainsString('keep=1', $url);
    }

    /**
     * The admin's own list is cached beside the one in force, so it has to follow each change the
     * way that one does, without anything clearing the cache on its behalf.
     */
    public function testTheConfiguredListFollowsChangesOnceCached(): void
    {
        $this->assertSame(['sitep'], $this->getConfiguredList());

        SitesManagerApi::getInstance()->setGlobalQueryParamExclusion(SitesManager::URL_PARAM_EXCLUSION_TYPE_NAME_CUSTOM, 'adminp');
        $this->assertEqualsCanonicalizing(['sitep', 'adminp'], $this->getConfiguredList());

        SitesManagerApi::getInstance()->updateSite($this->idSite, null, null, null, null, null, null, null, 'sitep,otherp');
        $this->assertEqualsCanonicalizing(['sitep', 'otherp', 'adminp'], $this->getConfiguredList());

        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, $this->idSite);
        $attributes = Cache::getCacheWebsiteAttributes($this->idSite);
        $this->assertContains('email', $attributes['excluded_parameters'], 'the policy is in force');
        $this->assertNotContains('email', $attributes[SitesManager::CONFIGURED_EXCLUDED_PARAMETERS_CACHE_KEY]);
    }

    /**
     * Callers that clean a URL outside of tracking - searches, the overlay - have no visitor whose
     * consent could apply, and keep removing what the policy requires.
     */
    public function testCleaningAUrlOutsideOfTrackingKeepsFilteringTheRecommendedParameters(): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $url = (string) PageUrl::excludeQueryParametersFromUrl('http://example.com/landing?' . self::QUERY, $this->idSite);

        $this->assertStoredQuery(false, $url);
    }

    public function getGoalCases(): iterable
    {
        yield 'no consent' => [null, false];
        yield 'consent=1' => ['1', true];
    }

    /**
     * @dataProvider getGoalCases
     */
    public function testManualGoalConversionUrlFollowsConsent(?string $consent, bool $expectPiiKept): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $idGoal = GoalsApi::getInstance()->addGoal($this->idSite, 'Goal', 'manually', '', '');
        Cache::deleteTrackerCache();

        $this->prepareRequest($consent);
        Fixture::checkResponse($this->tracker->doTrackGoal($idGoal));

        $this->assertStoredQuery($expectPiiKept, $this->fetchConversionUrl());
    }

    /**
     * @dataProvider getGoalCases
     */
    public function testReferrerUrlFollowsConsent(?string $consent, bool $expectPiiKept): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $this->tracker->setUrlReferrer('http://referrer.example.org/page?' . self::QUERY);
        $this->trackPageView($consent);

        $referrerUrl = (string) $this->fetchVisitColumn('referer_url');

        if ($expectPiiKept) {
            $this->assertStoredQuery(true, $referrerUrl);
        } else {
            $this->assertStringNotContainsString('email=', $referrerUrl);
        }
    }

    /**
     * Each site in a bulk request is filtered by its own settings: consent lifts the policy's list
     * only for the site that measures consent-aware analytics.
     */
    public function testEachSiteInABulkRequestFollowsItsOwnOptIn(): void
    {
        $otherSite = $this->createSite();
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, $otherSite);
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $this->tracker->enableBulkTracking();

        $this->prepareRequest('1');
        $this->tracker->doTrackPageView('Opted in');

        $this->tracker->setIdSite($otherSite);
        $this->prepareRequest('1', 'other');
        $this->tracker->doTrackPageView('Not opted in');

        Fixture::checkBulkTrackingResponse($this->tracker->doBulkTrack());

        try {
            $this->assertStoredQuery(true, $this->fetchEntryPageUrl());
            $this->assertStoredQuery(false, $this->fetchEntryPageUrl($otherSite));
        } finally {
            PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $otherSite);
        }
    }

    /**
     * Parameters a plugin adds through the event are its own choice, not the policy's, so they are
     * removed whether or not the request has consent.
     */
    public function testParametersAddedThroughTheEventAreKeptOutForAConsentedRequest(): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        Piwik::addAction('Tracker.PageUrl.getQueryParametersToExclude', function (&$parameters) {
            $parameters[] = 'plug';
        });

        $consented = PageUrl::getQueryParametersToExclude($this->idSite, true);
        $unconsented = PageUrl::getQueryParametersToExclude($this->idSite);

        $this->assertContains('plug', $consented);
        $this->assertContains('sitep', $consented);
        $this->assertNotContains('email', $consented);

        $this->assertContains('plug', $unconsented);
        $this->assertContains('email', $unconsented);
    }

    /**
     * A tracker cache written before the admin's own list was kept beside the enforced one has
     * only the enforced list. Consent must not lift anything on the strength of the missing entry.
     */
    public function testAConsentedRequestKeepsTheEnforcedListWhenTheCacheHasNoConfiguredList(): void
    {
        $this->enableConsentAwareAnalytics();
        $this->enforcePolicy();

        $attributes = Cache::getCacheWebsiteAttributes($this->idSite);
        $this->assertArrayHasKey(SitesManager::CONFIGURED_EXCLUDED_PARAMETERS_CACHE_KEY, $attributes);
        unset($attributes[SitesManager::CONFIGURED_EXCLUDED_PARAMETERS_CACHE_KEY]);

        Cache::$cache->save($this->idSite, $attributes);

        $this->assertContains('email', PageUrl::getQueryParametersToExclude($this->idSite, true));
    }

    public function getConfiguredListCases(): iterable
    {
        yield 'no global type' => [null, null, ['sitep']];
        yield 'common session parameters' => [SitesManager::URL_PARAM_EXCLUSION_TYPE_NAME_COMMON_SESSION_PARAMETERS, null, ['sitep']];
        yield 'custom' => [SitesManager::URL_PARAM_EXCLUSION_TYPE_NAME_CUSTOM, 'adminp,other', ['sitep', 'adminp', 'other']];
        yield 'recommended PII' => [SitesManager::URL_PARAM_EXCLUSION_TYPE_NAME_MATOMO_RECOMMENDED_PII, null, ['sitep', 'email']];
    }

    /**
     * @dataProvider getConfiguredListCases
     */
    public function testConfiguredListIsTheAdminsOwnWithOrWithoutThePolicy(?string $type, ?string $custom, array $expectedSubset): void
    {
        if (null !== $type) {
            SitesManagerApi::getInstance()->setGlobalQueryParamExclusion($type, $custom);
        }

        foreach ([false, true] as $enforce) {
            PolicyManager::setPolicyActiveStatus(CnilPolicy::class, $enforce, $this->idSite);
            Cache::deleteTrackerCache();

            $configured = Cache::getCacheWebsiteAttributes($this->idSite)[SitesManager::CONFIGURED_EXCLUDED_PARAMETERS_CACHE_KEY];

            foreach ($expectedSubset as $parameter) {
                $this->assertContains($parameter, $configured, 'policy ' . ($enforce ? 'on' : 'off'));
            }

            if ($type !== SitesManager::URL_PARAM_EXCLUSION_TYPE_NAME_MATOMO_RECOMMENDED_PII) {
                $this->assertNotContains('email', $configured, 'policy ' . ($enforce ? 'on' : 'off'));
            }
        }
    }

    public function getWordingCases(): iterable
    {
        yield 'a site that has not opted in keeps the plain wording' => [false, ''];
        yield 'an opted-in site says consent lifts the filtering' => [true, 'ConsentAware'];
    }

    /**
     * The compliance page describes the setting as applying to visitors without consent only on a
     * site that measures consent-aware analytics.
     *
     * @dataProvider getWordingCases
     */
    public function testCompliancePolicySettingsDescribeTheFilterByTheSitesOptIn(bool $optIn, string $keySuffix): void
    {
        Config::getInstance()->FeatureFlags = ['GranularPrivacyCompliance_feature' => 'enabled'];

        if ($optIn) {
            $this->enableConsentAwareAnalytics();
        }

        try {
            $payload = PrivacyManagerApi::getInstance()->getCompliancePolicySettings($this->idSite, 'cnil_v1');
        } finally {
            Config::getInstance()->FeatureFlags = null;
        }

        $settings = array_column($payload['settings'], null, 'id');
        $setting = $settings[FilterPIIParameters::getPolicySettingId()];

        $this->assertSame(Piwik::translate('SitesManager_FilterPIIParametersSettingWhatItDoes' . $keySuffix), $setting['whatItDoes']);
        $this->assertSame(Piwik::translate('SitesManager_FilterPIIParametersSettingImpact' . $keySuffix), $setting['impact']);
    }

    private function assertStoredQuery(bool $expectPiiKept, string $url): void
    {
        if ($expectPiiKept) {
            $this->assertStringContainsString('email=', $url);
        } else {
            $this->assertStringNotContainsString('email=', $url);
        }

        $this->assertStringNotContainsString('sitep', $url, 'the site\'s own exclusion always applies');
        $this->assertStringContainsString('keep=1', $url);
    }

    private function createSite(): int
    {
        return (int) Fixture::createWebsite(
            '2024-01-01 00:00:00',
            0,
            'PII consent',
            'http://example.com',
            1,
            null,
            null,
            null,
            null,
            0,
            'sitep'
        );
    }

    private function getTracker(int $idSite): \MatomoTracker
    {
        $tracker = Fixture::getTracker($idSite, $this->testDate->getDatetime(), $defaultInit = true, $useLocal = false);
        $tracker->setUrlReferrer('');

        return $tracker;
    }

    private function enforcePolicy(): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, $this->idSite);
        Cache::deleteTrackerCache();
    }

    private function enableConsentAwareAnalytics(): void
    {
        $measurable = ConsentAwareAnalyticsSetting::getMeasurableSetting($this->idSite);
        $measurable->setValue(true);
        $measurable->save();

        Cache::deleteTrackerCache();
    }

    private function trackPageView(?string $consent, string $page = 'landing', string $extraQuery = ''): void
    {
        $this->prepareRequest($consent, $page, $extraQuery);

        Fixture::checkResponse($this->tracker->doTrackPageView(ucfirst($page)));
    }

    private function ping(?string $consent): void
    {
        $this->prepareRequest($consent);

        Fixture::checkResponse($this->tracker->doPing());
    }

    private function prepareRequest(?string $consent, string $page = 'landing', string $extraQuery = ''): void
    {
        $this->testDate = $this->testDate->addPeriod(1, 'minute');
        $this->tracker->setForceVisitDateTime($this->testDate->getDatetime());
        $this->tracker->clearCustomTrackingParameters();

        if (null !== $consent) {
            $this->tracker->setCustomTrackingParameter('consent', $consent);
        }

        $this->tracker->setUrl('http://example.com/' . $page . '?' . self::QUERY . $extraQuery);
    }

    private function enforceOnlyThePiiFilter(): void
    {
        $enforced = [];
        foreach (PolicyManager::getAllControlledSettings(CnilPolicy::class, $this->idSite) as $setting) {
            if (!$setting::isExternallyManagedByPolicyPage()) {
                $enforced[$setting::getPolicySettingId()] = $setting === FilterPIIParameters::class;
            }
        }

        PolicyManager::setPolicySettingEnforcedStatuses(CnilPolicy::class, $enforced, $this->idSite);
        Cache::deleteTrackerCache();
    }

    /**
     * @return string[]
     */
    private function getConfiguredList(): array
    {
        return array_values(Cache::getCacheWebsiteAttributes($this->idSite)[SitesManager::CONFIGURED_EXCLUDED_PARAMETERS_CACHE_KEY]);
    }

    private function fetchConversionUrl(): string
    {
        return (string) Db::fetchOne(
            'SELECT url FROM ' . Common::prefixTable('log_conversion') . ' WHERE idsite = ?',
            [$this->idSite]
        );
    }

    private function fetchEntryPageUrl(?int $idSite = null): string
    {
        return (string) Db::fetchOne(
            'SELECT a.name FROM ' . Common::prefixTable('log_visit') . ' v'
            . ' JOIN ' . Common::prefixTable('log_action') . ' a ON a.idaction = v.visit_entry_idaction_url'
            . ' WHERE v.idsite = ? ORDER BY v.idvisit ASC LIMIT 1',
            [$idSite ?? $this->idSite]
        );
    }

    /**
     * Names of the URLs recorded for this site's actions on one page, in the order recorded.
     *
     * @return string[]
     */
    private function fetchActionNamesForPage(string $page): array
    {
        return array_column(Db::fetchAll(
            'SELECT a.name FROM ' . Common::prefixTable('log_link_visit_action') . ' l'
            . ' JOIN ' . Common::prefixTable('log_action') . ' a ON a.idaction = l.idaction_url'
            . ' WHERE l.idsite = ? AND a.name LIKE ? ORDER BY l.idlink_va ASC',
            [$this->idSite, '%/' . $page . '?%']
        ), 'name');
    }

    /**
     * @return mixed
     */
    private function fetchVisitColumn(string $column)
    {
        return Db::fetchOne(
            'SELECT `' . $column . '` FROM ' . Common::prefixTable('log_visit')
            . ' WHERE idsite = ? ORDER BY idvisit ASC LIMIT 1',
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

    /**
     * Tracking at a fixed past date is only accepted on an authenticated request.
     */
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
