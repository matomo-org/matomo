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
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

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

        Config::getInstance()->FeatureFlags = ['GranularPrivacyCompliance_feature' => 'enabled'];
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

    public function testTheAllWebsitesViewDescribesTheConsentAwareBehaviourWhenItIsOnForTheInstance(): void
    {
        ConsentAwareAnalytics::setEnabled(true, $this->idSite);

        $this->assertSame(
            'PrivacyManager_CampaignParameterValuesMaskedSettingWhatItDoes',
            CampaignParameterValuesMasked::getWhatItDoes(null)
        );
        $this->assertSame(
            'PrivacyManager_CampaignParameterValuesMaskedSettingWhatItDoes2',
            CampaignParameterValuesMasked::getWhatItDoes($this->idSite)
        );

        ConsentAwareAnalytics::setEnabled(true);

        $this->assertSame(
            'PrivacyManager_CampaignParameterValuesMaskedSettingWhatItDoes2',
            CampaignParameterValuesMasked::getWhatItDoes(null)
        );
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

    public function testSetRejectsAValueThatIsNotABoolean(): void
    {
        $this->expectExceptionMessage('Invalid value for the parameter "enabled"');

        $this->api->setConsentAwareAnalytics($this->idSite, 'egg');
    }

    public function testSetThrowsForAnUnknownSite(): void
    {
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

    public function testGetAndSetRequireTheGranularComplianceFeature(): void
    {
        Config::getInstance()->FeatureFlags = ['GranularPrivacyCompliance_feature' => 'disabled'];

        try {
            $this->api->setConsentAwareAnalytics($this->idSite, '1');
            $this->fail('It was turned on without the feature');
        } catch (Exception $e) {
            $this->assertSame('Granular compliance configuration is not enabled', $e->getMessage());
        }

        $this->expectExceptionMessage('Granular compliance configuration is not enabled');
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
        // a website can still be turned on for itself
        $this->assertTrue($this->api->getConsentAwareAnalytics($this->idSite)['isWritable']);

        $this->expectExceptionMessage('CoreAdminHome_PluginSettingChangeNotAllowed');
        $this->api->setConsentAwareAnalytics('all', '0');
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

        $this->assertSame([
            ['idSite' => $this->idSite, 'enabled' => true, 'previousEnabled' => false],
            ['idSite' => null, 'enabled' => true, 'previousEnabled' => false],
        ], $updates);
    }

    public function testAFailingListenerDoesNotFailTheChange(): void
    {
        Piwik::addAction('PrivacyManager.consentAwareAnalyticsUpdated', static function (): void {
            throw new Exception('listener failure');
        });

        $this->api->setConsentAwareAnalytics($this->idSite, '1');

        $this->assertTrue(ConsentAwareAnalytics::isEnabledForSite($this->idSite));
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
