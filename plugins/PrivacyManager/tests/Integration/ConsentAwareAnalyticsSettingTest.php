<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\tests\Integration;

use Piwik\Plugins\PrivacyManager\MeasurableSettings;
use Piwik\Plugins\PrivacyManager\Settings\CampaignParameterValuesMasked;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics;
use Piwik\Plugins\UserId\Settings\UserIdDisabled;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * Opting a site in lifts restrictions a compliance policy enforces, and enforcing a policy is
 * reserved to a superuser. A site admin who could switch this on would be able to undo a
 * decision they cannot make.
 *
 * @group PrivacyManager
 * @group Plugins
 */
class ConsentAwareAnalyticsSettingTest extends IntegrationTestCase
{
    /** @var int */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'Consent aware');
    }

    public function testASuperUserMayOptTheSiteIn(): void
    {
        FakeAccess::$superUser = true;

        $settings = new MeasurableSettings($this->idSite);

        $this->assertTrue($settings->consentAwareAnalytics->isWritableByCurrentUser());
    }

    public function testASiteAdminMayNotOptTheSiteIn(): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesAdmin = [$this->idSite];

        $settings = new MeasurableSettings($this->idSite);

        $this->assertFalse($settings->consentAwareAnalytics->isWritableByCurrentUser());
    }

    /**
     * @return iterable<string, array{0: bool, 1: bool, 2: int, 3: int, 4: bool}>
     */
    public function getIpRestrictionCases(): iterable
    {
        // enforced anonymisation, configured anonymisation, enforced mask, configured mask, relaxable
        yield 'anonymisation enforced over the admin\'s own choice' => [true, false, 2, 2, true];
        yield 'a mask enforced over the admin\'s shorter one' => [true, true, 2, 1, true];
        yield 'the admin\'s own values, nothing enforced over them' => [true, true, 2, 2, false];
        yield 'no anonymisation configured or enforced' => [false, false, 2, 2, false];
    }

    /**
     * Consent lifts the IP anonymisation a policy enforces, so that counts as something to relax.
     * Every other setting consent relaxes is held equal here, so only the IP settings decide.
     *
     * @dataProvider getIpRestrictionCases
     */
    public function testIpAnonymisationEnforcedOverTheAdminsOwnValuesIsARelaxableRestriction(
        bool $enforcedAnonymisation,
        bool $configuredAnonymisation,
        int $enforcedMaskLength,
        int $configuredMaskLength,
        bool $expected
    ): void {
        $siteCache = TrackerCache::getCacheWebsiteAttributes($this->idSite);

        $siteCache[CampaignParameterValuesMasked::class] = false;
        $siteCache[UserIdDisabled::class] = false;
        $siteCache[UserIdDisabled::CONFIGURED_VALUE_CACHE_KEY] = false;
        $siteCache['PrivacyManager.anonymizeReferrer'] = '';
        $siteCache['PrivacyManager.anonymizeReferrerConfiguredValue'] = '';
        $siteCache['PrivacyManager.ipAnonymizerEnabled'] = $enforcedAnonymisation;
        $siteCache['PrivacyManager.ipAnonymizerEnabledConfiguredValue'] = $configuredAnonymisation;
        $siteCache['PrivacyManager.ipAddressMaskLength'] = $enforcedMaskLength;
        $siteCache['PrivacyManager.ipAddressMaskLengthConfiguredValue'] = $configuredMaskLength;

        TrackerCache::$cache->save($this->idSite, $siteCache);

        $this->assertSame($expected, ConsentAwareAnalytics::hasRelaxableRestriction($this->idSite));
    }

    public function provideContainerConfig()
    {
        FakeAccess::$superUser = true;

        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
