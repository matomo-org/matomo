<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Integration;

use Exception;
use Piwik\Access;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\NoAccessException;
use Piwik\Policy\CnilPolicy;
use Piwik\Plugins\PrivacyManager\API;
use Piwik\Plugins\PrivacyManager\Settings\IpAddressMaskLength;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group PrivacyManager
 * @group ApiTest
 * @group Api
 * @group Plugins
 */
class ApiTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    /**
     * @var int
     */
    private $siteId;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        $this->siteId = Fixture::createWebsite('2014-01-01 01:02:03');
        $this->api = API::getInstance();
    }

    public function testSetComplianceStatusThrowsExceptionIfInvalidComplianceType(): void
    {
        $this->expectExceptionMessage('Invalid compliance type');
        $this->api->setComplianceStatus(
            (string) $this->siteId,
            'egg',
            true
        );
    }

    public function testSetComplianceStatusThrowsExceptionIfUserDoesntHaveSuperAdmin(): void
    {
        $fakeAccess = StaticContainer::getContainer()->get(Access::class);
        $fakeAccess->setSuperUserAccess(false);

        $this->expectException(NoAccessException::class);

        $this->api->setComplianceStatus(
            (string) $this->siteId,
            'cnil_v1',
            true
        );
    }

    public function testSetComplianceStatusReturnsTheNewStateIfEnabled(): void
    {
        $complianceType = 'cnil_v1';

        $result = $this->api->setComplianceStatus(
            (string) $this->siteId,
            $complianceType,
            true
        );

        $complianceStatus = $this->api->getComplianceStatus($this->siteId, $complianceType);
        $this->assertTrue($complianceStatus['complianceModeEnforced']);
    }

    public function testSetComplianceStatusDoesNotRequirePasswordWhenPostSessionFlagIsZeroEvenIfGetFlagIsOne(): void
    {
        $_GET['force_api_session'] = 1;
        $_POST['token_auth'] = 'postToken';
        $_POST['force_api_session'] = 0;

        try {
            $result = $this->api->setComplianceStatus(
                (string) $this->siteId,
                'cnil_v1',
                true
            );

            $this->assertTrue($result);
        } finally {
            unset($_GET['force_api_session']);
            unset($_POST['token_auth']);
            unset($_POST['force_api_session']);
        }
    }

    public function testSetComplianceStatusRequiresPasswordWhenPostSessionFlagIsOneEvenIfGetFlagIsZero(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('UsersManager_ConfirmWithReAuthentication');

        $_GET['force_api_session'] = 0;
        $_POST['token_auth'] = 'postToken';
        $_POST['force_api_session'] = 1;

        try {
            $this->api->setComplianceStatus(
                (string) $this->siteId,
                'cnil_v1',
                true
            );
        } finally {
            unset($_GET['force_api_session']);
            unset($_POST['token_auth']);
            unset($_POST['force_api_session']);
        }
    }

    public function testIpAddressMaskLengthComplianceUsesCurrentMaskLengthWhenIpAnonymizationIsDisabled(): void
    {
        $anonymizeIp = true;
        $maskLength = 3;
        $this->api->setAnonymizeIpSettings(
            $anonymizeIp,
            $maskLength,
            true
        );

        $this->assertSame(3, IpAddressMaskLength::getCustomValue());
        $this->assertTrue(IpAddressMaskLength::isCompliant(CnilPolicy::class, $this->siteId));

        $anonymizeIp = false;
        $this->api->setAnonymizeIpSettings(
            $anonymizeIp,
            $maskLength,
            true
        );

        $this->assertSame($maskLength, IpAddressMaskLength::getCustomValue());
        $this->assertFalse(IpAddressMaskLength::isCompliant(CnilPolicy::class, $this->siteId));
    }

    public function testGetCompliancePolicySettingsThrowsWhenFeatureIsNotEnabled(): void
    {
        $this->expectExceptionMessage('Granular compliance configuration is not enabled');

        $this->api->getCompliancePolicySettings($this->siteId, 'cnil_v1');
    }

    public function testGetCompliancePolicySettingsThrowsForInvalidPolicy(): void
    {
        $this->enableGranularComplianceFeature();

        $this->expectExceptionMessage('Invalid compliance policy');

        $this->api->getCompliancePolicySettings($this->siteId, 'egg');
    }

    public function testGetCompliancePolicySettingsThrowsWithoutSuperUserAccess(): void
    {
        $this->enableGranularComplianceFeature();

        $fakeAccess = StaticContainer::getContainer()->get(Access::class);
        $fakeAccess->setSuperUserAccess(false);

        $this->expectException(NoAccessException::class);

        $this->api->getCompliancePolicySettings($this->siteId, 'cnil_v1');
    }

    public function testGetCompliancePolicySettingsReturnsAllSettingsWithDefaultState(): void
    {
        $this->enableGranularComplianceFeature();

        // IP anonymisation is enabled by default, disable it to get a deterministic non-compliant setting
        $this->api->setAnonymizeIpSettings(false, 2, true);

        $payload = $this->api->getCompliancePolicySettings($this->siteId, 'cnil_v1');

        $this->assertSame('cnil_v1', $payload['policy']);
        $this->assertNotEmpty($payload['title']);
        $this->assertNotEmpty($payload['description']);
        $this->assertFalse($payload['configControlled']);
        $this->assertFalse($payload['policyEnforced']);

        $settings = $this->getSettingsById($payload);

        foreach ($payload['settings'] as $setting) {
            $this->assertSame(
                ['id', 'name', 'whatItDoes', 'impact', 'status', 'enforced', 'toggleable', 'section'],
                array_keys($setting)
            );
        }

        $ipAnonymisation = $settings['PrivacyManager.IPAnonymisation'];
        $this->assertSame('non_compliant', $ipAnonymisation['status']);
        $this->assertFalse($ipAnonymisation['enforced']);
        $this->assertTrue($ipAnonymisation['toggleable']);
        $this->assertSame('settings', $ipAnonymisation['section']);

        $thirdPartyCookies = $settings['Core.ThirdPartyCookies'];
        $this->assertSame('on_by_default', $thirdPartyCookies['status']);
        $this->assertNull($thirdPartyCookies['enforced']);
        $this->assertFalse($thirdPartyCookies['toggleable']);
        $this->assertSame('external', $thirdPartyCookies['section']);

        $optOut = $settings['cnil_v1.optOut'];
        $this->assertSame('manual', $optOut['status']);
        $this->assertNull($optOut['enforced']);
        $this->assertFalse($optOut['toggleable']);
        $this->assertSame('external', $optOut['section']);
    }

    public function testGetCompliancePolicySettingsReflectsLegacyEnforcedPolicy(): void
    {
        // disable the underlying setting so the requirement can only be met through enforcement
        $this->api->setAnonymizeIpSettings(false, 2, true);
        $this->api->setComplianceStatus((string) $this->siteId, 'cnil_v1', true);

        $this->enableGranularComplianceFeature();

        $payload = $this->api->getCompliancePolicySettings($this->siteId, 'cnil_v1');
        $this->assertTrue($payload['policyEnforced']);

        $settings = $this->getSettingsById($payload);

        // the underlying setting is not configured, the requirement is only met through enforcement
        $this->assertSame('enforced', $settings['PrivacyManager.IPAnonymisation']['status']);
        $this->assertTrue($settings['PrivacyManager.IPAnonymisation']['enforced']);
        $this->assertSame('enforced', $settings['PrivacyManager.DataRoundingEnabled']['status']);

        // the policy was only enforced for one site, the instance-wide view stays unenforced
        $allSitesPayload = $this->api->getCompliancePolicySettings('all', 'cnil_v1');
        $this->assertFalse($allSitesPayload['policyEnforced']);
        $this->assertFalse($this->getSettingsById($allSitesPayload)['PrivacyManager.IPAnonymisation']['enforced']);
    }

    public function testGetCompliancePolicySettingsStatusIsCompliantWhenUnderlyingSettingSatisfiesRequirement(): void
    {
        $this->enableGranularComplianceFeature();

        $this->api->setAnonymizeIpSettings(true, 2, true);

        $payload = $this->api->getCompliancePolicySettings($this->siteId, 'cnil_v1');
        $settings = $this->getSettingsById($payload);

        $this->assertSame('compliant', $settings['PrivacyManager.IPAnonymisation']['status']);
        $this->assertFalse($settings['PrivacyManager.IPAnonymisation']['enforced']);
        $this->assertSame('compliant', $settings['PrivacyManager.IpAddressMaskLength']['status']);
    }

    public function testGetCompliancePolicySettingsExternalStatusReflectsRawConfigDespiteEnforcement(): void
    {
        $this->enableGranularComplianceFeature();

        // raw non-compliant config for an externally managed setting + enforced policy
        Config::getInstance()->Tracker = array_merge(
            Config::getInstance()->Tracker ?: [],
            ['use_third_party_id_cookie' => 1]
        );
        $this->api->setComplianceStatus((string) $this->siteId, 'cnil_v1', true);

        $payload = $this->api->getCompliancePolicySettings($this->siteId, 'cnil_v1');
        $thirdPartyCookies = $this->getSettingsById($payload)['Core.ThirdPartyCookies'];

        // the policy override must not mask the raw non-compliant config
        $this->assertSame('non_compliant', $thirdPartyCookies['status']);
    }

    private function enableGranularComplianceFeature(): void
    {
        Config::getInstance()->FeatureFlags = ['GranularPrivacyCompliance_feature' => 'enabled'];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, array<string, mixed>>
     */
    private function getSettingsById(array $payload): array
    {
        $settings = [];
        foreach ($payload['settings'] as $setting) {
            $settings[$setting['id']] = $setting;
        }
        return $settings;
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
        );
    }
}
