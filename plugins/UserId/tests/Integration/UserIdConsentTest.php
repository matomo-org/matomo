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
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\UserId\Settings\UserIdDisabled;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
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

        $this->enableConsentAwareAnalytics();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'User ID consent', 'http://example.com');
        $this->testDate = Date::factory('2024-01-02 10:00:00');

        $this->tracker = Fixture::getTracker(
            $this->idSite,
            $this->testDate->getDatetime(),
            $defaultInit = true,
            $useLocal = false
        );
        $this->tracker->setUrlReferrer('');
    }

    /**
     * The whole of this behaviour is gated. With the flag off, a request reporting consent is
     * treated exactly as one that does not, which is how every install behaves until it is on.
     */
    public function testConsentHasNoEffectWhileTheFeatureFlagIsOff(): void
    {
        $this->disableConsentAwareAnalytics();
        $this->enforcePolicy();

        $this->trackPageView($consent = '1');

        $this->assertSame('', (string) $this->fetchVisitColumn('user_id'));
    }

    private function disableConsentAwareAnalytics(): void
    {
        self::$fixture->getTestEnvironment()->overrideConfig(
            'FeatureFlags',
            'ConsentAwareAnalytics_feature',
            'disabled'
        );
        self::$fixture->getTestEnvironment()->save();

        Config::getInstance()->FeatureFlags = ['ConsentAwareAnalytics_feature' => 'disabled'];
    }

    /**
     * The behaviour under test is gated by the ConsentAwareAnalytics feature flag, and the tracker
     * runs in its own request, so the flag has to be set where that request will read it.
     */
    private function enableConsentAwareAnalytics(): void
    {
        self::$fixture->getTestEnvironment()->overrideConfig(
            'FeatureFlags',
            'ConsentAwareAnalytics_feature',
            'enabled'
        );
        self::$fixture->getTestEnvironment()->save();

        Config::getInstance()->FeatureFlags = ['ConsentAwareAnalytics_feature' => 'enabled'];
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

    private function trackPageView(?string $consent): void
    {
        $this->testDate = $this->testDate->addPeriod(1, 'minute');
        $this->tracker->setForceVisitDateTime($this->testDate->getDatetime());
        $this->tracker->clearCustomTrackingParameters();
        $this->tracker->setUserId(self::USER_ID);

        if (null !== $consent) {
            $this->tracker->setCustomTrackingParameter('consent', $consent);
        }

        $this->tracker->setUrl('http://example.com/landing');

        Fixture::checkResponse($this->tracker->doTrackPageView('Landing'));
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
     * Tracking at a fixed past date is only accepted on an authenticated request.
     */
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
