<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\tests\Integration;

use Exception;
use Piwik\Access;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\NoAccessException;
use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\API;
use Piwik\Plugins\PrivacyManager\Settings\CampaignParameterValuesMasked;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics;
use Piwik\Plugins\PrivacyManager\Settings\ReferrerAnonymisation;
use Piwik\Plugins\UserId\Settings\UserIdDisabled;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * Consent-aware analytics is turned on from the Compliance page, for the instance or for a site.
 * It lifts restrictions a compliance policy enforces, and enforcing a policy is reserved to a
 * superuser, so only a superuser may turn it on.
 *
 * @group PrivacyManager
 * @group Plugins
 */
class ConsentAwareAnalyticsSettingTest extends IntegrationTestCase
{
    /** @var int */
    private $idSite;

    /** @var int */
    private $otherIdSite;

    /** @var API */
    private $api;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'Consent aware');
        $this->otherIdSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'Other');
        $this->api = API::getInstance();

        Config::getInstance()->FeatureFlags = ['HybridTracking_feature' => 'enabled'];
    }

    public function testASiteMeasuresItWhenItIsOnForTheSiteOrForTheInstance(): void
    {
        $this->assertFalse(ConsentAwareAnalytics::isEnabledForSite($this->idSite));

        ConsentAwareAnalytics::setEnabled(true, $this->idSite);

        $this->assertTrue(ConsentAwareAnalytics::isEnabledForSite($this->idSite));
        $this->assertFalse(ConsentAwareAnalytics::isEnabledForSite($this->otherIdSite));
        $this->assertFalse(ConsentAwareAnalytics::isEnabledForInstance());

        ConsentAwareAnalytics::setEnabled(true);

        $this->assertTrue(ConsentAwareAnalytics::isEnabledForSite($this->otherIdSite));
        $this->assertTrue(ConsentAwareAnalytics::isEnabledForInstance());
    }

    public function testTurningItOnForTheInstanceReachesTheTrackerCacheOfEverySite(): void
    {
        // warm the cache of both sites before the change
        $this->assertFalse(ConsentAwareAnalytics::isEnabled($this->idSite));
        $this->assertFalse(ConsentAwareAnalytics::isEnabled($this->otherIdSite));

        ConsentAwareAnalytics::setEnabled(true);

        $this->assertTrue(ConsentAwareAnalytics::isEnabled($this->idSite));
        $this->assertTrue(ConsentAwareAnalytics::isEnabled($this->otherIdSite));
    }

    /**
     * @dataProvider getTextsThatDependOnConsentAwareAnalytics
     */
    public function testTheAllWebsitesViewDescribesTheConsentAwareBehaviourWhenItIsOnForTheInstance(
        callable $getText,
        string $plainKey
    ): void {
        ConsentAwareAnalytics::setEnabled(true, $this->idSite);

        $this->assertSame($plainKey, $getText(null));
        $this->assertSame($plainKey . 'ConsentAware', $getText($this->idSite));
        $this->assertSame($plainKey, $getText($this->otherIdSite));

        ConsentAwareAnalytics::setEnabled(true);

        $this->assertSame($plainKey . 'ConsentAware', $getText(null));
        $this->assertSame($plainKey . 'ConsentAware', $getText($this->otherIdSite));
    }

    public function getTextsThatDependOnConsentAwareAnalytics(): iterable
    {
        $settings = [
            CampaignParameterValuesMasked::class => 'PrivacyManager_CampaignParameterValuesMaskedSetting',
            ReferrerAnonymisation::class => 'PrivacyManager_ReferrerAnonymizationSetting',
            UserIdDisabled::class => 'UserId_UserIdDisabledSetting',
        ];

        foreach ($settings as $class => $keyPrefix) {
            $name = substr($class, strrpos($class, '\\') + 1);
            yield $name . ' what it does' => [[$class, 'getWhatItDoes'], $keyPrefix . 'WhatItDoes'];
            yield $name . ' impact' => [[$class, 'getImpact'], $keyPrefix . 'Impact'];
        }
    }

    public function testGetReturnsTheValueOfTheScopeAndWhetherItIsOnForEveryWebsite(): void
    {
        ConsentAwareAnalytics::setEnabled(true, $this->idSite);

        $this->assertSame(
            ['enabled' => true, 'enabledForAllWebsites' => false, 'isWritable' => true],
            $this->api->getConsentAwareAnalytics($this->idSite)
        );
        $this->assertSame(
            ['enabled' => false, 'enabledForAllWebsites' => false, 'isWritable' => true],
            $this->api->getConsentAwareAnalytics('all')
        );

        ConsentAwareAnalytics::setEnabled(true);

        $this->assertSame(
            ['enabled' => false, 'enabledForAllWebsites' => true, 'isWritable' => true],
            $this->api->getConsentAwareAnalytics($this->otherIdSite)
        );
        $this->assertSame(
            ['enabled' => true, 'enabledForAllWebsites' => true, 'isWritable' => true],
            $this->api->getConsentAwareAnalytics('all')
        );
    }

    public function testSetWritesTheValueOfTheScope(): void
    {
        $this->assertSame(
            ['enabled' => true, 'enabledForAllWebsites' => false, 'isWritable' => true],
            $this->api->setConsentAwareAnalytics($this->idSite, '1')
        );
        $this->assertFalse(ConsentAwareAnalytics::isEnabledForSite($this->otherIdSite));

        $this->api->setConsentAwareAnalytics('all', '1');
        $this->assertTrue(ConsentAwareAnalytics::isEnabledForSite($this->otherIdSite));

        // turning it off for every website leaves the website that has it on for itself
        $this->api->setConsentAwareAnalytics('all', '0');
        $this->assertFalse(ConsentAwareAnalytics::isEnabledForSite($this->otherIdSite));
        $this->assertTrue(ConsentAwareAnalytics::isEnabledForSite($this->idSite));
    }

    public function testSetReadsTheStringFalseAsOff(): void
    {
        ConsentAwareAnalytics::setEnabled(true, $this->idSite);

        $this->api->setConsentAwareAnalytics($this->idSite, 'false');

        $this->assertFalse(ConsentAwareAnalytics::isEnabledForSite($this->idSite));
    }

    /**
     * @dataProvider getValuesThatAreNotABoolean
     */
    public function testSetRejectsAValueThatIsNotABoolean($enabled): void
    {
        ConsentAwareAnalytics::setEnabled(true, $this->idSite);

        try {
            $this->api->setConsentAwareAnalytics($this->idSite, $enabled);
            $this->fail('The value was accepted');
        } catch (Exception $e) {
            $this->assertSame('Invalid value for the parameter "enabled"', $e->getMessage());
        }

        // an empty value must not read as off
        $this->assertTrue(ConsentAwareAnalytics::isEnabledForSite($this->idSite));
    }

    public function getValuesThatAreNotABoolean(): iterable
    {
        yield 'a word' => ['egg'];
        yield 'empty' => [''];
        yield 'blank' => [' '];
        yield 'an array' => [[]];
        yield 'null' => [null];
    }

    public function testGetAndSetThrowForAnUnknownSite(): void
    {
        try {
            $this->api->getConsentAwareAnalytics(99999);
            $this->fail('An unknown site was read');
        } catch (UnexpectedWebsiteFoundException $e) {
            // expected
        }

        $this->expectException(UnexpectedWebsiteFoundException::class);
        $this->api->setConsentAwareAnalytics(99999, '1');
    }

    public function testGetAndSetRequireSuperUserAccess(): void
    {
        $fakeAccess = StaticContainer::getContainer()->get(Access::class);
        $fakeAccess->setSuperUserAccess(false);
        FakeAccess::$idSitesAdmin = [$this->idSite];

        try {
            $this->api->setConsentAwareAnalytics($this->idSite, '1');
            $this->fail('A site admin was able to turn it on');
        } catch (NoAccessException $e) {
            $this->assertFalse(ConsentAwareAnalytics::isEnabledForSite($this->idSite));
        }

        $this->expectException(NoAccessException::class);
        $this->api->getConsentAwareAnalytics($this->idSite);
    }

    public function testGetAndSetRequireTheHybridTrackingFeature(): void
    {
        // the granular compliance feature alone is not enough
        Config::getInstance()->FeatureFlags = [
            'HybridTracking_feature' => 'disabled',
            'GranularPrivacyCompliance_feature' => 'enabled',
        ];

        try {
            $this->api->setConsentAwareAnalytics($this->idSite, '1');
            $this->fail('It was turned on without the feature');
        } catch (Exception $e) {
            $this->assertSame('Hybrid tracking is not enabled', $e->getMessage());
        }

        $this->expectExceptionMessage('Hybrid tracking is not enabled');
        $this->api->getConsentAwareAnalytics($this->idSite);
    }

    public function testSetRequiresThePasswordWhenUsingASessionToken(): void
    {
        $_GET['force_api_session'] = 0;
        $_POST['token_auth'] = 'postToken';
        $_POST['force_api_session'] = 1;

        try {
            $this->api->setConsentAwareAnalytics($this->idSite, '1');
            $this->fail('It was turned on without the password');
        } catch (Exception $e) {
            $this->assertSame('UsersManager_ConfirmWithReAuthentication', $e->getMessage());
            $this->assertFalse(ConsentAwareAnalytics::isEnabledForSite($this->idSite));
        } finally {
            unset($_GET['force_api_session'], $_POST['token_auth'], $_POST['force_api_session']);
        }
    }

    public function testTheInstanceValueCannotBeChangedWhenItIsSetInTheConfig(): void
    {
        Config::getInstance()->PrivacyManager = ['consent_aware_analytics' => 1];

        $this->assertSame(
            ['enabled' => true, 'enabledForAllWebsites' => true, 'isWritable' => false],
            $this->api->getConsentAwareAnalytics('all')
        );

        $this->expectExceptionMessage('CoreAdminHome_PluginSettingChangeNotAllowed');
        $this->api->setConsentAwareAnalytics('all', '0');
    }

    /**
     * A value set in the config fixes the instance value only. A website keeps its own toggle, so
     * one that was already on can still be turned off.
     */
    public function testAWebsiteCanStillBeTurnedOffWhenTheConfigSetsTheInstanceValue(): void
    {
        ConsentAwareAnalytics::setEnabled(true, $this->idSite);
        Config::getInstance()->PrivacyManager = ['consent_aware_analytics' => 0];

        $this->assertSame(
            ['enabled' => true, 'enabledForAllWebsites' => false, 'isWritable' => true],
            $this->api->getConsentAwareAnalytics($this->idSite)
        );

        $this->api->setConsentAwareAnalytics($this->idSite, '0');

        $this->assertFalse(ConsentAwareAnalytics::isEnabledForSite($this->idSite));
        $this->assertFalse(ConsentAwareAnalytics::isEnabled($this->idSite));
    }

    public function testTheScopeIsTheWebsiteWhenOneIsGivenAndTheInstanceOtherwise(): void
    {
        ConsentAwareAnalytics::setEnabled(true, $this->idSite);

        $this->assertTrue(ConsentAwareAnalytics::isEnabledForScope($this->idSite));
        $this->assertFalse(ConsentAwareAnalytics::isEnabledForScope(null));

        ConsentAwareAnalytics::setEnabled(true);

        $this->assertTrue(ConsentAwareAnalytics::isEnabledForScope(null));
        $this->assertTrue(ConsentAwareAnalytics::isEnabledForScope($this->otherIdSite));
    }

    public function testAChangeIsAnnouncedOnlyWhenTheValueChanged(): void
    {
        $updates = [];
        Piwik::addAction('PrivacyManager.consentAwareAnalyticsUpdated', static function ($update) use (&$updates): void {
            $updates[] = $update;
        });

        $this->api->setConsentAwareAnalytics($this->idSite, '1');
        $this->api->setConsentAwareAnalytics($this->idSite, '1');
        $this->api->setConsentAwareAnalytics('all', '1');
        $this->api->setConsentAwareAnalytics($this->idSite, '0');

        $this->assertSame([
            ['idSite' => $this->idSite, 'enabled' => true, 'previousEnabled' => false, 'enabledForAllWebsites' => false],
            ['idSite' => null, 'enabled' => true, 'previousEnabled' => false, 'enabledForAllWebsites' => true],
            // the website's own value is off, but it is still measured as it is on for all websites
            ['idSite' => $this->idSite, 'enabled' => false, 'previousEnabled' => true, 'enabledForAllWebsites' => true],
        ], $updates);
    }

    public function testARefusedChangeIsNotAnnounced(): void
    {
        $updates = [];
        Piwik::addAction('PrivacyManager.consentAwareAnalyticsUpdated', static function ($update) use (&$updates): void {
            $updates[] = $update;
        });

        try {
            $this->api->setConsentAwareAnalytics($this->idSite, 'egg');
            $this->fail('An invalid value was accepted');
        } catch (Exception $e) {
            $this->assertSame('Invalid value for the parameter "enabled"', $e->getMessage());
        }

        Config::getInstance()->PrivacyManager = ['consent_aware_analytics' => 1];
        try {
            $this->api->setConsentAwareAnalytics('all', '0');
            $this->fail('The instance value set in the config was changed');
        } catch (Exception $e) {
            $this->assertSame('CoreAdminHome_PluginSettingChangeNotAllowed', $e->getMessage());
        }

        $this->assertSame([], $updates);
    }

    public function testAFailingListenerDoesNotFailTheChange(): void
    {
        Piwik::addAction('PrivacyManager.consentAwareAnalyticsUpdated', static function (): void {
            throw new Exception('listener failure');
        });

        $this->api->setConsentAwareAnalytics($this->idSite, '1');

        $this->assertTrue(ConsentAwareAnalytics::isEnabledForSite($this->idSite));
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
