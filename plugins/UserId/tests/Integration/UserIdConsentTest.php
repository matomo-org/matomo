<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\UserId\tests\Integration;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\UserId\Settings\UserIdDisabled;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics as ConsentAwareAnalyticsSetting;
use Piwik\Tracker\Cache;

/**
 * A compliance policy requiring User ID collection to be off withholds it from every visitor,
 * including one who has consented. Consent lifts what the policy required, and nothing more.
 *
 * @group UserId
 * @group Plugins
 */
class UserIdConsentTest extends IntegrationTestCase
{
    private const USER_ID = 'someone@example.com';

    /** @var int */
    private $idSite;

    /** @var Date */
    private $testDate;

    /** @var \MatomoTracker */
    private $tracker;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'User ID consent', 'http://example.com');
        $this->testDate = Date::factory('2024-01-02 10:00:00');

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
     * The tracker pings the server by itself when consent is given, and that ping can land after
     * the visitor has moved on. It cannot start a visit, so the User ID it carries must not be
     * written onto the visit measured before consent either.
     */
    public function testAConsentingPingAfterNavigatingDoesNotPutTheUserIdOnTheEarlierVisit(): void
    {
        $this->enforcePolicy();

        $this->trackPageView(null, 'landing');
        $this->trackPageView(null, 'other');
        $this->ping('1', 'other');

        $this->assertSame(1, $this->countVisits(), 'a ping cannot start a visit');
        $this->assertSame('', (string) $this->fetchVisitColumn('user_id'), 'the ping named the earlier visit');

        $this->trackPageView('1', 'other');

        $this->assertSame(2, $this->countVisits());
        $this->assertSame('', (string) $this->fetchVisitColumn('user_id'));
        $this->assertSame(self::USER_ID, $this->fetchVisitColumn('user_id', 1));
    }

    /**
     * Consent given once the visitor has moved on starts a visit of its own. The campaign and the
     * original referrer are gone from the request by then, but the User ID is not - the site keeps
     * sending it - so the new visit carries it.
     */
    public function testTheVisitConsentStartsAfterNavigatingCarriesTheUserId(): void
    {
        $this->enforcePolicy();

        $this->trackPageView(null, 'landing');
        $this->trackPageView('1', 'other');

        $this->assertSame(2, $this->countVisits(), 'consent away from the arrival page starts its own visit');
        $this->assertSame('', (string) $this->fetchVisitColumn('user_id'), 'the unconsented visit keeps no User ID');
        $this->assertSame(self::USER_ID, $this->fetchVisitColumn('user_id', 1), 'the consented visit carries it');
    }

    /**
     * The feature is opt-in per site. A site that has not opted in behaves exactly as it does
     * today, whether or not a request reports consent.
     */
    public function testConsentHasNoEffectForASiteThatHasNotOptedIn(): void
    {
        $this->disableConsentAwareAnalytics();
        $this->enforcePolicy();

        $this->trackPageView($consent = '1');

        $this->assertSame('', (string) $this->fetchVisitColumn('user_id'));
    }

    private function disableConsentAwareAnalytics(): void
    {
        $measurable = ConsentAwareAnalyticsSetting::getMeasurableSetting($this->idSite);
        $measurable->setValue(false);
        $measurable->save();

        Cache::deleteTrackerCache();
    }

    /**
     * Consent-aware measurement is opt-in per site, so the tests opt theirs in.
     */
    private function enableConsentAwareAnalytics(): void
    {
        $measurable = ConsentAwareAnalyticsSetting::getMeasurableSetting($this->idSite);
        $measurable->setValue(true);
        $measurable->save();

        Cache::deleteTrackerCache();
    }

    public function tearDown(): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $this->idSite);
        Cache::deleteTrackerCache();

        parent::tearDown();
    }

    public function testUserIdIsWithheldFromAVisitorWhoHasNotConsented(): void
    {
        $this->enforcePolicy();

        $this->trackPageView($consent = null);

        $this->assertSame('', (string) $this->fetchVisitColumn('user_id'));
    }

    public function testUserIdIsCollectedWhenTheRequestCarriesConsent(): void
    {
        $this->enforcePolicy();

        $this->trackPageView($consent = '1');

        $this->assertSame(self::USER_ID, $this->fetchVisitColumn('user_id'));
    }

    /**
     * Turning the setting on is the admin's own decision, not something the policy required of
     * them, so consent does not undo it.
     */
    public function testConsentDoesNotOverrideTheAdminsOwnSetting(): void
    {
        $this->enforcePolicy();
        $this->disableUserIdForSite();

        $this->trackPageView($consent = '1');

        $this->assertSame('', (string) $this->fetchVisitColumn('user_id'));
    }

    /**
     * With nothing enforced there is no restriction for consent to lift, and a visitor who does
     * not signal consent is measured exactly as before.
     */
    public function testUserIdIsCollectedWithoutConsentWhenNoPolicyIsEnforced(): void
    {
        $this->trackPageView($consent = null);

        $this->assertSame(self::USER_ID, $this->fetchVisitColumn('user_id'));
    }

    private function enforcePolicy(): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, $this->idSite);
        Cache::deleteTrackerCache();
    }

    private function disableUserIdForSite(): void
    {
        $setting = UserIdDisabled::getMeasurableSetting($this->idSite);
        $setting->setValue(true);
        $setting->save();
        Cache::deleteTrackerCache();
    }

    private function ping(?string $consent, string $page = 'landing'): void
    {
        $this->prepareRequest($consent, $page);

        Fixture::checkResponse($this->tracker->doPing());
    }

    private function trackPageView(?string $consent, string $page = 'landing'): void
    {
        $this->prepareRequest($consent, $page);

        Fixture::checkResponse($this->tracker->doTrackPageView(ucfirst($page)));
    }

    private function prepareRequest(?string $consent, string $page): void
    {
        $this->testDate = $this->testDate->addPeriod(1, 'minute');
        $this->tracker->setForceVisitDateTime($this->testDate->getDatetime());
        $this->tracker->clearCustomTrackingParameters();
        $this->tracker->setUserId(self::USER_ID);

        if (null !== $consent) {
            $this->tracker->setCustomTrackingParameter('consent', $consent);
        }

        $this->tracker->setUrl('http://example.com/' . $page);
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
