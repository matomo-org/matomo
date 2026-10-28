<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Referrers\tests\Integration;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\ReferrerAnonymizer;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\PrivacyManager\Settings\CampaignParameterValuesMasked;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics as ConsentAwareAnalyticsSetting;
use Piwik\Tracker\Cache;

/**
 * Campaign parameters are discarded while a compliance policy withholds them, and recorded once
 * the visitor has consented.
 *
 * The visitor is tracked throughout either way - the policy governs what is stored about them,
 * not whether they are measured. Consent only ever relaxes a restriction that is in force: with
 * no policy enforced there is nothing to relax, and a visitor who has not consented is treated
 * exactly as before.
 *
 * @group Referrers
 * @group Plugins
 */
class CampaignConsentTest extends IntegrationTestCase
{
    private const CAMPAIGN_NAME = 'spring sale';
    private const CAMPAIGN_KEYWORD = 'running shoes';

    /** @var int */
    private $idSite;

    /** @var int */
    private static $idGoal;

    /** @var Date */
    private $testDate;

    /** @var \MatomoTracker */
    private $tracker;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'Campaign consent', 'http://example.com');
        $this->testDate = Date::factory('2024-01-02 10:00:00');

        self::$idGoal = GoalsApi::getInstance()->addGoal(
            $this->idSite,
            'Converted',
            'manually',
            '',
            'contains'
        );

        $this->tracker = Fixture::getTracker(
            $this->idSite,
            $this->testDate->getDatetime(),
            $defaultInit = true,
            $useLocal = false
        );

        $this->tracker->setUrlReferrer('');

        $this->enableConsentAwareAnalytics();
    }

    /**
     * The feature is opt-in per site. A site that has not opted in behaves exactly as it does
     * today, whether or not a request reports consent.
     */
    public function testConsentHasNoEffectForASiteThatHasNotOptedIn(): void
    {
        $this->disableConsentAwareAnalytics();
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = '1');

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name')
        );
        $this->assertSame('', (string) $this->fetchVisitColumn('consent'));
    }

    private function disableConsentAwareAnalytics(): void
    {
        $measurable = ConsentAwareAnalyticsSetting::getMeasurableSetting($this->idSite);
        $measurable->setValue(false);
        $measurable->save();

        Cache::deleteTrackerCache();
    }

    /**
     * Consent-aware measurement is opt-in per site, so the tests turn it on for theirs. The
     * tracker runs in its own request, so the system setting has to be set where that request
     * will read it.
     */
    private function enableConsentAwareAnalytics(): void
    {
        self::$fixture->getTestEnvironment()->overrideConfig(
            'PrivacyManager',
            'ConsentAwareAnalytics',
            '1'
        );
        self::$fixture->getTestEnvironment()->save();

        $measurable = ConsentAwareAnalyticsSetting::getMeasurableSetting($this->idSite);
        $measurable->setValue(true);
        $measurable->save();

        Cache::deleteTrackerCache();
    }

    public function tearDown(): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $this->idSite);
        $this->setAdminReferrerAnonymisation(ReferrerAnonymizer::EXCLUDE_NONE);
        Cache::deleteTrackerCache();

        parent::tearDown();
    }

    public function testCampaignIsDiscardedForAVisitorWhoHasNotConsented(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name')
        );
        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_keyword')
        );
    }

    public function testCampaignIsRecordedWhenTheRequestCarriesConsent(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = '1');

        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchVisitColumn('referer_name'));
        $this->assertSame(self::CAMPAIGN_KEYWORD, $this->fetchVisitColumn('referer_keyword'));
    }

    /**
     * The visitor is measured before consenting, so the landing page view has already been
     * recorded with the campaign discarded. Consent then arrives while they are still on that
     * page, so the next request carries the campaign parameters again and the visit is completed
     * with them. Nothing is retained in the meantime to make this work.
     */
    public function testCampaignIsRecordedWhenConsentArrivesLaterOnTheSamePage(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name'),
            'the campaign should be discarded until the visitor consents'
        );

        $this->trackPageView('landing', $withCampaign = true, $consent = '1');

        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchVisitColumn('referer_name'));
        $this->assertSame(self::CAMPAIGN_KEYWORD, $this->fetchVisitColumn('referer_keyword'));
        $this->assertSame(1, $this->countVisits(), 'consent must not start a second visit');
    }

    /**
     * The shape a consent platform actually produces: the visitor is measured without cookies,
     * consents on the landing page, and the tracker reports it with a ping rather than a second
     * page view. The ping carries the same page URL, so the campaign can be completed from it
     * without recording an extra action.
     */
    public function testCampaignIsRecordedWhenConsentArrivesWithAPing(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name')
        );

        $this->ping('landing', $withCampaign = true, $consent = '1');

        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchVisitColumn('referer_name'));
        $this->assertSame(self::CAMPAIGN_KEYWORD, $this->fetchVisitColumn('referer_keyword'));
        $this->assertSame(1, $this->countVisits(), 'a ping must not start a second visit');
        $this->assertSame(1, $this->countActions(), 'a ping must not record an extra action');
    }

    /**
     * Not every request carries the consent flag even after the visitor has consented - a batch of
     * requests does not, for instance. Such a request still has the campaign in its URL, so it is
     * masked, and the stored campaign then differs from the incoming one. That is not the visitor
     * arriving from somewhere new and must not start a second visit.
     */
    public function testARequestWithoutTheConsentFlagDoesNotSplitAConsentedVisit(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = '1');
        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchVisitColumn('referer_name'));

        $this->trackPageView('landing', $withCampaign = true, $consent = null);

        $this->assertSame(1, $this->countVisits(), 'a request without the consent flag split the visit');
        $this->assertSame(
            self::CAMPAIGN_NAME,
            $this->fetchVisitColumn('referer_name'),
            'the recorded campaign must not be replaced by the placeholder'
        );
    }

    /**
     * The campaign we discarded cannot be compared with a campaign arriving later, because we kept
     * nothing to compare it against. Completing a visit from a different campaign would credit the
     * first campaign's visit to the second, so the completion only applies while the visit is
     * still on the page it arrived on.
     */
    public function testALaterDifferentCampaignDoesNotTakeOverTheVisit(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);
        $this->trackPageView('article', $withCampaign = false, $consent = '1');
        $this->trackPageView('offer', $withCampaign = true, $consent = '1', 'another campaign');

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name'),
            'a later campaign took over the visit it did not bring the visitor to'
        );
        $this->assertSame(2, $this->countVisits(), 'the later campaign should have started a visit of its own');
    }

    /**
     * The same takeover, but with the different campaign arriving on the very next request. The
     * window has to be the page the visitor arrived on rather than a count of actions, because by
     * the time the second page view is handled the visit still shows a single stored action.
     */
    public function testADifferentCampaignOnTheNextPageDoesNotTakeOverTheVisit(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);
        $this->trackPageView('other', $withCampaign = true, $consent = '1', 'winter clearance');

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name'),
            'the second campaign took over the visit the first one brought in'
        );
        $this->assertSame(2, $this->countVisits(), 'the second campaign should have started a visit of its own');
    }

    /**
     * A consent platform commonly records an event of its own - a banner impression, say - on the
     * landing page before the visitor answers it. An event carries no page URL, so it does not
     * move the visitor off the page they arrived on and must not close the window.
     */
    public function testCampaignIsRecordedWhenAnEventPrecedesTheConsentPing(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);
        $this->trackEvent('landing', 'Consent', 'banner shown');
        $this->ping('landing', $withCampaign = true, $consent = '1');

        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchVisitColumn('referer_name'));
        $this->assertSame(1, $this->countVisits());
    }

    /**
     * The same visit, but the page is reloaded with consent rather than reporting it by a ping.
     */
    public function testCampaignIsRecordedWhenTheLandingPageIsReloadedAfterAnEvent(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);
        $this->trackEvent('landing', 'Consent', 'banner shown');
        $this->trackPageView('landing', $withCampaign = true, $consent = '1');

        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchVisitColumn('referer_name'));
        $this->assertSame(1, $this->countVisits(), 'the reload split the visit');
    }

    /**
     * The visitor moves on and only then consents, on a request that records no action of its own.
     * The campaign in that request's URL is not the one they arrived through, and nothing was kept
     * of the one that was, so the visit cannot be credited to it.
     */
    public function testALaterCampaignDoesNotTakeOverThroughAPingOnAnotherPage(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);
        $this->trackPageView('other', $withCampaign = false, $consent = null);
        $this->ping('other', $withCampaign = true, $consent = '1', 'winter clearance');

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name'),
            'a campaign reported by a ping took over the visit it did not bring the visitor to'
        );
    }

    /**
     * Campaign parameters are stripped from stored URLs, so returning to the page the visit arrived
     * on through a different campaign looks identical to still being on it. The visit has recorded
     * another action by then, which is what tells the two apart.
     */
    public function testReturningToTheLandingPageThroughADifferentCampaignDoesNotTakeOver(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);
        $this->trackPageView('other', $withCampaign = false, $consent = null);
        $this->trackPageView('landing', $withCampaign = true, $consent = '1', 'winter clearance');

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name'),
            'returning through a different campaign took over the original visit'
        );
    }

    /**
     * Consent recorded for a visit says the visitor consented, not that every later request did.
     * A request that does not report it is treated as one that has not consented, so the campaign
     * it carries is masked and never stored in the clear - it cannot be told apart from the same
     * campaign arriving again on a request that simply does not repeat the flag, so as everywhere
     * else a masked incoming value leaves the visit as it is rather than starting a second one.
     */
    public function testACampaignArrivingWithoutConsentIsNeverStoredInTheClear(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = '1');
        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchVisitColumn('referer_name'));

        $this->trackPageView('other', $withCampaign = true, $consent = null, 'winter clearance');

        $storedCampaigns = Db::fetchAll(
            'SELECT referer_name FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ?',
            [$this->idSite]
        );

        foreach ($storedCampaigns as $row) {
            $this->assertNotSame(
                'winter clearance',
                $row['referer_name'],
                'a campaign was stored in the clear from a request that did not report consent'
            );
        }

        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchVisitColumn('referer_name'));
    }

    /**
     * Returning to the page the visit arrived on does not put the visitor back where they started.
     * The campaign that brought them in is still unknown, so a later one cannot be credited with
     * the visit just because the page matches again.
     */
    public function testReturningToTheLandingPageDoesNotReopenTheWindow(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);
        $this->trackPageView('other', $withCampaign = false, $consent = null);
        $this->trackPageView('landing', $withCampaign = false, $consent = null);
        $this->trackPageView('landing', $withCampaign = true, $consent = '1', 'winter clearance');

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name'),
            'a later campaign took over once the visitor returned to the landing page'
        );
    }

    /**
     * The positive control for the rule above: when neither campaign was discarded, a second one
     * splits the visit exactly as it does today. The placeholder handling must not suppress that.
     */
    public function testADifferentConsentedCampaignStillSplitsTheVisit(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = '1');
        $this->trackPageView('other', $withCampaign = true, $consent = '1', 'winter clearance');

        $this->assertSame(2, $this->countVisits());
        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchVisitColumn('referer_name'));
    }

    /**
     * A consented request that carries the campaign name but no keyword has nothing to complete the
     * keyword with. The placeholder says the keyword was not collected, which is still true; an
     * empty value would instead say the visitor arrived without one.
     */
    public function testAConsentedRequestWithoutTheKeywordLeavesItDiscarded(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);
        $this->trackPageViewWithCampaignNameOnly('landing', self::CAMPAIGN_NAME, $consent = '1');

        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchVisitColumn('referer_name'));
        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_keyword'),
            'the keyword was overwritten with an empty value'
        );
    }

    /**
     * The referrer can also be detected part way through a visit, when the visitor arrived as a
     * direct entry. That path updates an existing visit rather than creating one, so it has to
     * honour consent the same way.
     */
    public function testReferrerDetailIsKeptWhenConsentArrivesOnAnExistingVisit(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = false, $consent = null);

        $this->tracker->setUrlReferrer('https://www.ecosia.org/search?q=running+shoes');
        $this->trackPageView('article', $withCampaign = false, $consent = '1');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame('running shoes', $this->fetchVisitColumn('referer_keyword'));
        $this->assertStringContainsString('q=running+shoes', (string) $this->fetchVisitColumn('referer_url'));
    }

    public function testReferrerDetailIsDroppedOnAnExistingVisitWithoutConsent(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = false, $consent = null);

        $this->tracker->setUrlReferrer('https://www.ecosia.org/search?q=running+shoes');
        $this->trackPageView('article', $withCampaign = false, $consent = null);

        $this->assertSame(1, $this->countVisits());
        $this->assertSame('', $this->fetchVisitColumn('referer_keyword'));
        $this->assertStringNotContainsString('q=', (string) $this->fetchVisitColumn('referer_url'));
    }

    /**
     * Once the visitor has moved on, the campaign parameters are no longer in the URL, so there is
     * nothing to record. The discarded value stays discarded rather than being guessed at.
     */
    public function testCampaignStaysDiscardedWhenConsentArrivesOnALaterPage(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);
        $this->trackPageView('basket', $withCampaign = false, $consent = '1');

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name')
        );
        $this->assertSame(1, $this->countVisits());
    }

    /**
     * A compliance policy also shortens the referrer and drops the search keyword. The referring
     * site and the channel are kept either way, so what consent restores here is the keyword and
     * the rest of the referrer URL.
     */
    public function testReferrerDetailIsKeptWhenTheRequestCarriesConsent(): void
    {
        $this->enforceCampaignMasking();

        $this->tracker->setUrlReferrer('https://www.ecosia.org/search?q=running+shoes');
        $this->trackPageView('landing', $withCampaign = false, $consent = '1');

        $this->assertSame('Ecosia', $this->fetchVisitColumn('referer_name'));
        $this->assertSame('running shoes', $this->fetchVisitColumn('referer_keyword'));
        $this->assertStringContainsString('q=running+shoes', (string) $this->fetchVisitColumn('referer_url'));
    }

    /**
     * Without consent the referrer keeps its exempt treatment: the search engine is still
     * recorded, the keyword and the rest of the URL are not.
     */
    public function testReferrerDetailIsDroppedWithoutConsent(): void
    {
        $this->enforceCampaignMasking();

        $this->tracker->setUrlReferrer('https://www.ecosia.org/search?q=running+shoes');
        $this->trackPageView('landing', $withCampaign = false, $consent = null);

        $this->assertSame('Ecosia', $this->fetchVisitColumn('referer_name'), 'the search engine should still be recorded');
        $this->assertSame('', $this->fetchVisitColumn('referer_keyword'));
        $this->assertStringNotContainsString('q=', (string) $this->fetchVisitColumn('referer_url'));
    }

    /**
     * Consent only lifts what a compliance policy imposed. Referrer anonymisation the admin chose
     * themselves is their own measure, not a policy's, so it still applies to a consented visitor
     * - on a site with no policy enforced there is nothing for consent to relax at all.
     */
    public function testConsentDoesNotLiftTheAdminsOwnReferrerAnonymisation(): void
    {
        $this->setAdminReferrerAnonymisation(ReferrerAnonymizer::EXCLUDE_ALL);

        $this->tracker->setUrlReferrer('https://www.ecosia.org/search?q=running+shoes');
        $this->trackPageView('landing', $withCampaign = false, $consent = '1');

        $this->assertSame('Ecosia', $this->fetchVisitColumn('referer_name'), 'the search engine should still be recorded');
        $this->assertSame('', $this->fetchVisitColumn('referer_keyword'), 'consent lifted the admin\'s own anonymisation');
        $this->assertSame('', $this->fetchVisitColumn('referer_url'), 'consent lifted the admin\'s own anonymisation');
    }

    /**
     * With both in force, the admin's own measure is the floor consent relaxes down to. Here it is
     * the stricter of the two, so the consented visitor is treated exactly as an unconsented one.
     */
    public function testConsentRelaxesOnlyAsFarAsTheAdminsOwnReferrerAnonymisation(): void
    {
        $this->enforceCampaignMasking();
        $this->setAdminReferrerAnonymisation(ReferrerAnonymizer::EXCLUDE_ALL);

        $this->tracker->setUrlReferrer('https://www.ecosia.org/search?q=running+shoes');
        $this->trackPageView('landing', $withCampaign = false, $consent = '1');

        $this->assertSame('', $this->fetchVisitColumn('referer_keyword'));
        $this->assertSame('', $this->fetchVisitColumn('referer_url'));
    }

    /**
     * Consent relaxes a restriction that is in force; it never imposes one. A site with nothing
     * enforced records the campaign for everyone, consented or not, exactly as it does today.
     */
    public function testCampaignIsRecordedWithoutConsentWhenNoPolicyIsEnforced(): void
    {
        $this->trackPageView('landing', $withCampaign = true, $consent = null);

        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchVisitColumn('referer_name'));
    }

    /**
     * An absent consent parameter is not a refusal - it only means the site does not signal
     * consent at all, which must behave exactly as it did before this change.
     */
    public function testExplicitRefusalIsTreatedTheSameAsNoSignal(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = '0');

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name')
        );
    }

    /**
     * Consent is recorded against the visit, so a later request that does not repeat the flag is
     * still known to come from a visitor who consented and is not masked all over again.
     */
    public function testConsentIsRecordedOnTheVisitAndAppliesToLaterRequests(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = '1');
        $this->assertSame(1, (int) $this->fetchVisitColumn('consent'), 'consent was not recorded on the visit');

        $this->trackPageView('landing', $withCampaign = true, $consent = null);

        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchVisitColumn('referer_name'));
        $this->assertSame(1, $this->countVisits());
    }

    /**
     * Consent arriving part way through the visit is kept, so it does not have to be repeated.
     */
    public function testConsentGivenDuringAVisitIsKept(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);
        $this->assertSame('', (string) $this->fetchVisitColumn('consent'), 'no consent should be recorded yet');

        $this->ping('landing', $withCampaign = true, $consent = '1');

        $this->assertSame(1, (int) $this->fetchVisitColumn('consent'));
    }

    /**
     * A visit that never reports consent records none, which is not the same as refusing it.
     */
    public function testNoConsentIsRecordedWhenTheSiteDoesNotSignalIt(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);

        $this->assertSame('', (string) $this->fetchVisitColumn('consent'));
    }

    /**
     * A conversion is attributed from the attribution cookie, which outlives the visit. Where the
     * visit's own campaign was discarded, the conversion must not report one either - otherwise
     * the Goals and Referrers reports disagree about the same visit.
     */
    public function testAConversionDoesNotReportACampaignTheVisitDiscarded(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = null);
        $this->trackPageView('basket', $withCampaign = false, $consent = '1');

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchVisitColumn('referer_name'),
            'the visit should still hold a discarded campaign'
        );

        $this->trackGoalConversion();

        $this->assertSame(
            CampaignParameterValuesMasked::getPlaceholderValue(),
            $this->fetchConversionColumn('referer_name'),
            'the conversion reported a campaign the visit does not hold'
        );
    }

    /**
     * Where the visit does hold its campaign, the conversion reports the same one.
     */
    public function testAConversionReportsTheCampaignTheVisitHolds(): void
    {
        $this->enforceCampaignMasking();

        $this->trackPageView('landing', $withCampaign = true, $consent = '1');
        $this->trackGoalConversion();

        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchVisitColumn('referer_name'));
        $this->assertSame(self::CAMPAIGN_NAME, $this->fetchConversionColumn('referer_name'));
    }

    private function setAdminReferrerAnonymisation(string $level): void
    {
        $config = new PrivacyManagerConfig();
        $config->anonymizeReferrer = $level;
        Cache::deleteTrackerCache();
    }

    private function enforceCampaignMasking(): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, $this->idSite);
        Cache::deleteTrackerCache();
    }

    private function trackPageView(
        string $page,
        bool $withCampaign,
        ?string $consent,
        ?string $campaign = null
    ): void {
        $this->prepareRequest($page, $withCampaign, $consent, $campaign);

        Fixture::checkResponse($this->tracker->doTrackPageView(ucfirst($page)));
    }

    private function prepareRequest(
        string $page,
        bool $withCampaign,
        ?string $consent,
        ?string $campaign = null
    ): void {
        $this->testDate = $this->testDate->addPeriod(1, 'minute');
        $this->tracker->setForceVisitDateTime($this->testDate->getDatetime());

        $this->tracker->clearCustomTrackingParameters();

        if (null !== $consent) {
            $this->tracker->setCustomTrackingParameter('consent', $consent);
        }

        $url = 'http://example.com/' . $page;
        if ($withCampaign) {
            $url .= '?' . http_build_query([
                'pk_campaign' => $campaign ?? self::CAMPAIGN_NAME,
                'pk_kwd' => self::CAMPAIGN_KEYWORD,
            ]);
        }

        $this->tracker->setUrl($url);
    }

    private function trackPageViewWithCampaignNameOnly(string $page, string $campaign, ?string $consent): void
    {
        $this->testDate = $this->testDate->addPeriod(1, 'minute');
        $this->tracker->setForceVisitDateTime($this->testDate->getDatetime());
        $this->tracker->clearCustomTrackingParameters();

        if (null !== $consent) {
            $this->tracker->setCustomTrackingParameter('consent', $consent);
        }

        $this->tracker->setUrl('http://example.com/' . $page . '?' . http_build_query(['pk_campaign' => $campaign]));

        Fixture::checkResponse($this->tracker->doTrackPageView(ucfirst($page)));
    }

    private function trackEvent(string $page, string $category, string $eventAction): void
    {
        $this->prepareRequest($page, $withCampaign = true, $consent = null);

        Fixture::checkResponse($this->tracker->doTrackEvent($category, $eventAction));
    }

    private function ping(string $page, bool $withCampaign, ?string $consent, ?string $campaign = null): void
    {
        $this->prepareRequest($page, $withCampaign, $consent, $campaign);

        Fixture::checkResponse($this->tracker->doPing());
    }

    /**
     * The JS tracker sends the campaign it attributed the visitor to from its own cookie, which is
     * what makes a conversion able to report a campaign the visit itself does not hold.
     */
    private function trackGoalConversion(?string $attributedCampaign = self::CAMPAIGN_NAME): void
    {
        $this->testDate = $this->testDate->addPeriod(1, 'minute');
        $this->tracker->setForceVisitDateTime($this->testDate->getDatetime());
        $this->tracker->clearCustomTrackingParameters();

        if (null !== $attributedCampaign) {
            $this->tracker->setAttributionInfo(json_encode([
                $attributedCampaign,
                self::CAMPAIGN_KEYWORD,
                $this->testDate->getTimestamp(),
                '',
            ]));
        }

        Fixture::checkResponse($this->tracker->doTrackGoal(self::$idGoal));
    }

    /**
     * @return mixed
     */
    private function fetchConversionColumn(string $column)
    {
        return Db::fetchOne(
            'SELECT `' . $column . '` FROM ' . Common::prefixTable('log_conversion')
            . ' WHERE idsite = ? ORDER BY idvisit ASC LIMIT 1',
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

    private function countVisits(): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ?',
            [$this->idSite]
        );
    }

    /**
     * @return mixed
     */
    private function fetchVisitColumn(string $column, int $offset = 0)
    {
        return Db::fetchOne(
            'SELECT `' . $column . '` FROM ' . Common::prefixTable('log_visit')
            . ' WHERE idsite = ? ORDER BY idvisit ASC LIMIT 1 OFFSET ' . $offset,
            [$this->idSite]
        );
    }

    /**
     * Tracking at a fixed past date is only accepted on an authenticated request, so the fixture
     * needs a super user for getTracker() to be given a token.
     */
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
