<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\Settings\CampaignParameterValuesMasked;
use Piwik\Plugins\Referrers\API as ReferrersAPI;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request as TrackerRequest;
use Piwik\Tracker\Settings as TrackerSettings;

/**
 * Behaviour specification for consent-aware analytics.
 *
 * A visitor arrives on a site with a compliance policy active, performs some actions without
 * consenting, then consents and performs more. The visit must stay a single visit, the consent
 * decision must be recorded on it, and the data captured before consent must keep its exempt
 * treatment afterwards.
 *
 * Each test is named after the scenario id in the behaviour spec so the two can be read together.
 *
 * Counts are read straight from the log tables rather than via Live.getCounters, because the CNIL
 * policy enforces DataRoundingEnabled and rounding would collapse the small exact counts these
 * assertions rely on (1 and 2 would both be reported as 10).
 *
 * @group Tracker
 * @group ConsentAware
 */
class ConsentAwareVisitTest extends IntegrationTestCase
{
    private const CAMPAIGN_NAME = 'spring sale';
    private const CAMPAIGN_KEYWORD = 'shoes';

    /** @var int */
    private $idSite;

    /** @var Date */
    private $testDate;

    /** @var \MatomoTracker */
    private $tracker;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2026-01-01 00:00:00', 0, 'ConsentAware', 'http://example.com');
        $this->testDate = Date::factory('2026-01-02 10:00:00');

        $this->tracker = Fixture::getTracker(
            $this->idSite,
            $this->testDate->getDatetime(),
            $defaultInit = true,
            $useLocal = false
        );
    }

    public function tearDown(): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $this->idSite);

        $privacyConfig = new PrivacyManagerConfig();
        $privacyConfig->randomizeConfigId = false;
        Cache::deleteTrackerCache();

        parent::tearDown();
    }

    // ---------------------------------------------------------------------------------------
    // Rule 1 - identity is consent-invariant
    // ---------------------------------------------------------------------------------------

    /**
     * S1: consent granted mid-visit must not split the visit.
     *
     * This only became a real assertion once campaign masking turned consent-aware: before that
     * the campaign stayed masked on both sides of the transition, so the referrer comparison saw
     * no change and the test passed for the wrong reason. Now the consented request produces a
     * real campaign value, which is exactly the situation S7 shows will split a visit without the
     * placeholder-aware comparison. Keep both.
     */
    public function testS1ConsentGrantedMidVisitKeepsASingleVisit(): void
    {
        $this->enableCampaignMasking();

        $this->trackPageViews(3, null, 'unconsented');
        $this->trackPageViews(3, '1', 'consented');

        $this->assertSame(1, $this->countVisits(), 'consent granted mid-visit split the visit');
        $this->assertSame(6, $this->countActions());
    }

    /**
     * S2: the same, when the browser starts sending a different visitor id at the moment of
     * consent (the pre-consent id was never persisted, so a fresh one arrives afterwards).
     * This leaves the config_id fallback in Model::findVisitor as the only thing holding the
     * visit together.
     */
    public function testS2ConsentGrantedMidVisitKeepsASingleVisitWhenVisitorIdChanges(): void
    {
        $this->enableCampaignMasking();

        $this->trackPageViews(3, null, 'unconsented');

        $this->tracker->setNewVisitorId();

        $this->trackPageViews(3, '1', 'consented');

        $this->assertSame(1, $this->countVisits(), 'a new visitor id at the consent transition split the visit');
        $this->assertSame(6, $this->countActions());
    }

    /**
     * S3: randomizeConfigId is incompatible with consent-aware analytics, by design. Every
     * request becomes its own visit, so there is no visit for a later consent to attach to.
     *
     * This test pins documented behaviour - it is evidence for the spike, not a defect.
     */
    public function testS3RandomizeConfigIdMakesEveryActionItsOwnVisit(): void
    {
        $this->enableCampaignMasking();

        $privacyConfig = new PrivacyManagerConfig();
        $privacyConfig->randomizeConfigId = true;
        Cache::deleteTrackerCache();

        $this->trackPageViews(3, null, 'unconsented');
        $this->trackPageViews(3, '1', 'consented');

        $this->assertSame(6, $this->countVisits(), 'randomizeConfigId is expected to make every action a visit');
    }

    /**
     * S4: the consent flag itself must not perturb the fingerprint. Two requests differing only
     * in their consent parameter have to produce the same config_id, otherwise the fallback that
     * S2 relies on cannot match across the transition.
     *
     * This is the guard that stops a future consent-sensitive setting silently breaking matching.
     */
    public function testS4ConsentParameterDoesNotChangeTheConfigId(): void
    {
        $settings = new TrackerSettings($isSameFingerprintAcrossWebsites = false);
        $ip = '203.0.113.10';

        $base = ['idsite' => $this->idSite, 'cookie' => 1, 'pdf' => 1, 'fla' => 0];

        $withoutConsent = new TrackerRequest($base);
        $withConsent = new TrackerRequest($base + ['consent' => 1]);

        $this->assertSame(
            $settings->getConfigId($withoutConsent, $ip),
            $settings->getConfigId($withConsent, $ip),
            'the consent parameter changed the visitor fingerprint'
        );
    }

    // ---------------------------------------------------------------------------------------
    // Rule 2 - newly visible is not newly different
    // ---------------------------------------------------------------------------------------

    /**
     * S6: a genuinely different campaign must still force a new visit. Guards against the S1/S5
     * fix disabling the split wholesale.
     */
    public function testS6ADifferentCampaignStillForcesANewVisit(): void
    {
        $this->trackPageViews(1, '1', 'first', self::CAMPAIGN_NAME);
        $this->trackPageViews(1, '1', 'second', 'another campaign');

        $this->assertSame(2, $this->countVisits(), 'a real campaign change must still start a new visit');
    }

    /**
     * S7: campaign masking turned off mid-visit, with no consent involved at all, must not split
     * the visit either. The placeholder becoming legible is not a change of campaign.
     */
    public function testS7UnmaskingMidVisitWithoutConsentDoesNotSplitTheVisit(): void
    {
        $this->enableCampaignMasking();

        $this->trackPageViews(2, null, 'masked');

        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $this->idSite);
        Cache::deleteTrackerCache();

        $this->trackPageViews(2, null, 'unmasked');

        $this->assertSame(1, $this->countVisits(), 'unmasking mid-visit split the visit');
        $this->assertSame(4, $this->countActions());
    }

    // ---------------------------------------------------------------------------------------
    // Rule 3 - consent is recorded per action, the visit carries a derived summary
    //
    // Consent is a property of each action, because each request carries its own decision. The
    // visit-level column is a summary of its actions, kept so segments and report filters do not
    // have to join to the (much larger) action table:
    //
    //   NULL  no consent signal was sent      0  signalled non-consent
    //   1     every action consented          2  mixed - some actions predate the consent
    //
    // NULL and 0 are stored apart only because writing nothing is the natural no-op for a site
    // that does not use consent. Every read path treats them identically.
    // ---------------------------------------------------------------------------------------

    /**
     * S8: a site that sends no consent signal at all must be entirely unaffected.
     */
    public function testS8NoConsentSignalLeavesTheColumnNull(): void
    {
        $this->trackPageViews(2, null, 'page');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame([null, null], $this->fetchActionConsents());
        $this->assertNull($this->fetchConsent());
    }

    /**
     * S9: an explicit non-consent signal is recorded on every action, and summarised as 0.
     */
    public function testS9ExplicitNonConsentIsRecordedAsZero(): void
    {
        $this->trackPageViews(2, '0', 'page');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(['0', '0'], $this->fetchActionConsents());
        $this->assertSame('0', $this->fetchConsent());
    }

    /**
     * S10: consent from the first action is recorded on every action, and summarised as 1.
     */
    public function testS10ConsentFromTheFirstActionIsRecordedAsOne(): void
    {
        $this->trackPageViews(2, '1', 'page');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(['1', '1'], $this->fetchActionConsents());
        $this->assertSame('1', $this->fetchConsent());
    }

    /**
     * S11: consent granted part way through a visit. Each action keeps the decision that applied
     * when it was tracked; the visit summarises that as 2 (mixed), which is what tells downstream
     * features that some of this visit predates the consent.
     */
    public function testS11ConsentGrantedMidVisitIsRecordedAsMixed(): void
    {
        $this->trackPageViews(2, '0', 'unconsented');
        $this->trackPageViews(2, '1', 'consented');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(['0', '0', '1', '1'], $this->fetchActionConsents());
        $this->assertSame('2', $this->fetchConsent());
    }

    /**
     * S12: the visit summary does not go backwards. Withdrawal mid-visit is out of MVP scope, but
     * the summary must not claim the consented actions never consented.
     *
     * KNOWN GAP: a visit that consented and then withdrew still summarises as consented, so a
     * read-side feature gating on the summary would show it. Recorded deliberately, not solved.
     */
    public function testS12ConsentSummaryIsNotDowngradedWithinAVisit(): void
    {
        $this->trackPageViews(2, '1', 'consented');
        $this->trackPageViews(2, '0', 'withdrawn');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(['1', '1', '0', '0'], $this->fetchActionConsents());
        $this->assertContains($this->fetchConsent(), ['1', '2'], 'the visit summary was downgraded');
    }

    // ---------------------------------------------------------------------------------------
    // Rule 4 - consent lifts a restriction, it never imposes one
    //
    // For each policy-controlled setting independently: the restriction applies when the setting
    // is enforced for the site AND the action is not consented. Every other combination is
    // unrestricted. The enforced settings are the floor that non-consented actions receive; with
    // no settings enforced there is no floor, and consent is recorded but nothing is gated on it.
    // ---------------------------------------------------------------------------------------

    /**
     * S15: with no policy enforced, an explicitly non-consenting visitor is still tracked in full.
     *
     * Consent-awareness must not quietly start restricting sites that never asked for it, and a
     * site that wants decliners restricted declares that by enforcing settings.
     */
    public function testS15NoPolicyEnforcedMeansNonConsentIsNotRestricted(): void
    {
        $this->trackPageViews(1, '0', 'declined');

        $this->assertSame(
            self::CAMPAIGN_NAME,
            $this->fetchVisitColumn('referer_name'),
            'a non-consented action was restricted on a site with no enforced settings'
        );
    }

    /**
     * S16: with the setting enforced, the restriction applies to the non-consented action and is
     * lifted for the consented one. This is the whole of the treatment rule in one scenario.
     */
    public function testS16EnforcedSettingRestrictsOnlyTheNonConsentedAction(): void
    {
        $this->enableCampaignMasking();

        $this->trackPageViews(1, '0', 'declined');

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name'),
            'the enforced restriction did not apply to a non-consented action'
        );

        $consentedSite = Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Consented', 'http://example.com');
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, $consentedSite);
        Cache::deleteTrackerCache();

        $consentedTracker = Fixture::getTracker(
            $consentedSite,
            $this->testDate->addPeriod(1, 'minute')->getDatetime(),
            $defaultInit = true,
            $useLocal = false
        );
        $consentedTracker->setCustomTrackingParameter('consent', '1');
        $consentedTracker->setUrl($this->buildUrl('consented', self::CAMPAIGN_NAME));
        Fixture::checkResponse($consentedTracker->doTrackPageView('consented'));

        $name = Db::fetchOne(
            'SELECT referer_name FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ?',
            [$consentedSite]
        );

        $this->assertSame(
            self::CAMPAIGN_NAME,
            $name,
            'consent did not lift the enforced restriction for a consented action'
        );

        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $consentedSite);
    }

    /**
     * S17: BLOCKED - pending the privacy answer from Paulina.
     *
     * A visit that entered unconsented under campaign masking, then consented. The campaign
     * parameter was only ever on the entry URL and was replaced with the placeholder at that
     * moment, so filling it in afterwards would require having retained the discarded value
     * through the non-consented period. Whether that retention is permissible is a privacy
     * question, not a technical one, and it decides which way this test is written.
     *
     * Contrast S18: values the consented request supplies again need no retention at all.
     */
    public function testS17EntryCampaignAfterConsentIsBlockedOnPrivacyAnswer(): void
    {
        $this->markTestIncomplete(
            'Blocked on the privacy decision: may Matomo retain discarded campaign values through '
            . 'the non-consented period so they can be filled in once consent arrives?'
        );
    }

    /**
     * S18: values that the consented request supplies again may be filled in on the visit, because
     * doing so uses only consented data and retains nothing from the non-consented period.
     *
     * Resolution is the worked example, and Matomo already implements exactly this fill-if-unknown
     * behaviour in Resolution::onExistingVisit.
     */
    public function testS18ReObservableValuesAreFilledInOnceConsented(): void
    {
        $this->enableCampaignMasking();

        $this->trackPageViews(1, '0', 'declined');

        $this->assertSame(
            TrackerRequest::UNKNOWN_RESOLUTION,
            $this->fetchVisitColumn('config_resolution'),
            'resolution was collected for a non-consented action'
        );

        $this->trackPageViews(1, '1', 'consented');

        $this->assertSame(
            '1024x768',
            $this->fetchVisitColumn('config_resolution'),
            'resolution was not filled in from the consented action'
        );
    }

    /**
     * S13: consent is not retroactive. The campaign parameters that arrived before consent were
     * discarded at that moment and must not reappear on the visit afterwards.
     *
     * Like S1, this only became a real assertion once consent started lifting the masking: the
     * consented request now does produce a real campaign value, so the fact that the visit's entry
     * attribution stays masked is a genuine result rather than a side effect of nothing happening.
     */
    public function testS13EntryAttributionStaysMaskedAfterConsent(): void
    {
        $this->enableCampaignMasking();

        $this->trackPageViews(1, null, 'unconsented');
        $this->trackPageViews(1, '1', 'consented');

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name'),
            'the campaign discarded before consent reappeared on the visit'
        );
    }

    // ---------------------------------------------------------------------------------------
    // API surface - a headless consumer must not see a different answer than the UI
    // ---------------------------------------------------------------------------------------

    /**
     * S14: the report API must not leak the internal placeholder sentinel as a campaign label.
     *
     * Referrers.getCampaigns applies no placeholder formatting (unlike Live, VisitorDetails and
     * the GDPR export, which all route through CampaignParameterValuesMasked::formatValue), so
     * this asserts the contract we want rather than the behaviour we expect to find.
     */
    public function testS14ReportApiDoesNotLeakThePlaceholderSentinel(): void
    {
        $this->enableCampaignMasking();

        $this->trackPageViews(1, null, 'unconsented');

        $table = ReferrersAPI::getInstance()->getCampaigns(
            $this->idSite,
            'day',
            $this->testDate->toString('Y-m-d')
        );

        $labels = $table->getColumn('label');

        $this->assertNotContains(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $labels,
            'the raw placeholder sentinel leaked through the report API'
        );
    }

    // ---------------------------------------------------------------------------------------
    // helpers
    // ---------------------------------------------------------------------------------------

    private function enableCampaignMasking(): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, $this->idSite);
        Cache::deleteTrackerCache();
    }

    /**
     * @param string|null $consent value for the consent tracking parameter, or null to send none
     */
    private function trackPageViews(
        int $count,
        ?string $consent,
        string $pathPrefix,
        string $campaign = self::CAMPAIGN_NAME
    ): void {
        for ($i = 0; $i < $count; $i++) {
            $this->advanceTime();

            $this->tracker->clearCustomTrackingParameters();

            if (null !== $consent) {
                $this->tracker->setCustomTrackingParameter('consent', $consent);
            }

            $this->tracker->setUrl($this->buildUrl($pathPrefix . '-' . $i, $campaign));

            Fixture::checkResponse($this->tracker->doTrackPageView($pathPrefix . ' ' . $i));
        }
    }

    private function buildUrl(string $path, string $campaign): string
    {
        return 'http://example.com/' . $path . '?' . http_build_query([
            'pk_campaign' => $campaign,
            'pk_kwd' => self::CAMPAIGN_KEYWORD,
        ]);
    }

    private function advanceTime(): void
    {
        $this->testDate = $this->testDate->addPeriod(1, 'minute');
        $this->tracker->setForceVisitDateTime($this->testDate->getDatetime());
    }

    private function countVisits(): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ?',
            [$this->idSite]
        );
    }

    private function countActions(): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('log_link_visit_action') . ' WHERE idsite = ?',
            [$this->idSite]
        );
    }

    /**
     * The consent recorded on each action of the visit, in tracking order.
     *
     * log_link_visit_action has no consent column yet, so this reports the missing column as a
     * plain assertion failure rather than letting the SQL error surface as a test error.
     *
     * @return array<int, string|null>
     */
    private function fetchActionConsents(): array
    {
        if (!$this->actionConsentColumnExists()) {
            self::fail(
                'log_link_visit_action has no consent column. Storing consent per action needs an'
                . ' ActionDimension alongside the existing log_visit one, which is a schema change'
                . ' for the Matomo 6 window.'
            );
        }

        $rows = Db::fetchAll(
            'SELECT consent FROM ' . Common::prefixTable('log_link_visit_action')
            . ' WHERE idsite = ? ORDER BY idlink_va ASC',
            [$this->idSite]
        );

        return array_map(
            static function (array $row) {
                return null === $row['consent'] ? null : (string) $row['consent'];
            },
            $rows
        );
    }

    private function actionConsentColumnExists(): bool
    {
        $columns = Db::fetchAll('SHOW COLUMNS FROM ' . Common::prefixTable('log_link_visit_action'));

        foreach ($columns as $column) {
            if ('consent' === $column['Field']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string|null the raw consent column of the first visit, as returned by the driver
     */
    private function fetchConsent(): ?string
    {
        $value = $this->fetchVisitColumn('consent');

        return null === $value ? null : (string) $value;
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

    /**
     * These tests track at a fixed date in the past, which the tracker only accepts with an
     * authenticated request, so the fixture needs a super user for getTracker() to have a token.
     */
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
