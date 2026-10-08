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
use Piwik\Plugins\PrivacyManager\Settings\CampaignParameterValuesMasked;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
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

    /** @var Date */
    private $testDate;

    /** @var \MatomoTracker */
    private $tracker;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'Campaign consent', 'http://example.com');
        $this->testDate = Date::factory('2024-01-02 10:00:00');

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

    private function enforceCampaignMasking(): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, $this->idSite);
        Cache::deleteTrackerCache();
    }

    private function trackPageView(string $page, bool $withCampaign, ?string $consent): void
    {
        $this->testDate = $this->testDate->addPeriod(1, 'minute');
        $this->tracker->setForceVisitDateTime($this->testDate->getDatetime());

        $this->tracker->clearCustomTrackingParameters();

        if (null !== $consent) {
            $this->tracker->setCustomTrackingParameter('consent', $consent);
        }

        $url = 'http://example.com/' . $page;
        if ($withCampaign) {
            $url .= '?' . http_build_query([
                'pk_campaign' => self::CAMPAIGN_NAME,
                'pk_kwd' => self::CAMPAIGN_KEYWORD,
            ]);
        }

        $this->tracker->setUrl($url);
        $this->tracker->setUrlReferrer('');

        Fixture::checkResponse($this->tracker->doTrackPageView(ucfirst($page)));
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
    private function fetchVisitColumn(string $column)
    {
        return Db::fetchOne(
            'SELECT `' . $column . '` FROM ' . Common::prefixTable('log_visit')
            . ' WHERE idsite = ? ORDER BY idvisit ASC LIMIT 1',
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
