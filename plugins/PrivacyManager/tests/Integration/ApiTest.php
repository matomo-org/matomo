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
use Piwik\Option;
use Piwik\Policy\CnilPolicy;
use Piwik\Plugins\PrivacyManager\API;
use Piwik\Plugins\PrivacyManager\Settings\IPAnonymisation;
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

    public function testSetDeleteLogsSettingsKeepsTheStoredRetentionWhileAPolicyCapsIt(): void
    {
        // what the user had configured before any policy applied
        $this->api->setDeleteLogsSettings(1, 1000, Fixture::ADMIN_USER_PASSWORD);
        $this->assertSame(1000, (int) Option::get('delete_logs_older_than'));

        CnilPolicy::setActiveStatus(null, true);

        try {
            // the field is pre-filled with the capped value the policy puts in effect and the form
            // posts it back whether or not it was touched, so storing it would lose the user's own
            $this->api->setDeleteLogsSettings(1, 759, Fixture::ADMIN_USER_PASSWORD);

            $this->assertSame(1000, (int) Option::get('delete_logs_older_than'));
        } finally {
            CnilPolicy::setActiveStatus(null, false);
        }
    }

    public function testSetDeleteLogsSettingsStoresARetentionStricterThanThePolicyRequires(): void
    {
        $this->api->setDeleteLogsSettings(1, 1000, Fixture::ADMIN_USER_PASSWORD);

        CnilPolicy::setActiveStatus(null, true);

        try {
            // keeping data for less than the cap is the user's own choice and has to be kept
            $this->api->setDeleteLogsSettings(1, 180, Fixture::ADMIN_USER_PASSWORD);

            $this->assertSame(180, (int) Option::get('delete_logs_older_than'));
        } finally {
            CnilPolicy::setActiveStatus(null, false);
        }
    }

    public function testGetCompliancePolicySettingsThrowsWhenFeatureIsNotEnabled(): void
    {
        $this->disableGranularComplianceFeature();

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

    public function testSetCompliancePolicySettingsThrowsWhenFeatureIsNotEnabled(): void
    {
        $this->disableGranularComplianceFeature();

        $this->expectExceptionMessage('Granular compliance configuration is not enabled');

        $this->api->setCompliancePolicySettings($this->siteId, 'cnil_v1', ['PrivacyManager.IPAnonymisation' => 1]);
    }

    public function testSetCompliancePolicySettingsThrowsForUnknownSettingId(): void
    {
        $this->enableGranularComplianceFeature();

        $this->expectExceptionMessage('The setting "PrivacyManager.Egg" is unknown or cannot be toggled');

        $this->api->setCompliancePolicySettings($this->siteId, 'cnil_v1', ['PrivacyManager.Egg' => 1]);
    }

    public function testSetCompliancePolicySettingsThrowsForExternallyManagedSetting(): void
    {
        $this->enableGranularComplianceFeature();

        $this->expectExceptionMessage('The setting "Core.ThirdPartyCookies" is unknown or cannot be toggled');

        $this->api->setCompliancePolicySettings($this->siteId, 'cnil_v1', ['Core.ThirdPartyCookies' => 1]);
    }

    public function testSetCompliancePolicySettingsThrowsWhenPolicyIsConfigControlled(): void
    {
        $this->enableGranularComplianceFeature();
        Config::getInstance()->CnilPolicy = ['cnil_v1_policy_enabled' => 1];

        $this->expectExceptionMessage('The compliance policy is controlled by the config file and cannot be changed');

        $this->api->setCompliancePolicySettings($this->siteId, 'cnil_v1', ['PrivacyManager.IPAnonymisation' => 1]);
    }

    public function testSetCompliancePolicySettingsRequiresPasswordWhenUsingSessionToken(): void
    {
        $this->enableGranularComplianceFeature();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('UsersManager_ConfirmWithReAuthentication');

        $_GET['force_api_session'] = 0;
        $_POST['token_auth'] = 'postToken';
        $_POST['force_api_session'] = 1;

        try {
            $this->api->setCompliancePolicySettings($this->siteId, 'cnil_v1', ['PrivacyManager.IPAnonymisation' => 1]);
        } finally {
            unset($_GET['force_api_session']);
            unset($_POST['token_auth']);
            unset($_POST['force_api_session']);
        }
    }

    public function testSetCompliancePolicySettingsEnforcesAndReleasesIndividualSettings(): void
    {
        $this->enableGranularComplianceFeature();

        // make the underlying setting non-compliant so enforcement is observable
        $this->api->setAnonymizeIpSettings(false, 2, true);

        $payload = $this->api->setCompliancePolicySettings(
            $this->siteId,
            'cnil_v1',
            ['PrivacyManager.IPAnonymisation' => 1]
        );

        $settings = $this->getSettingsById($payload);
        $this->assertTrue($settings['PrivacyManager.IPAnonymisation']['enforced']);
        $this->assertSame('enforced', $settings['PrivacyManager.IPAnonymisation']['status']);
        $this->assertFalse($payload['policyEnforced']);

        // settings not present in the request keep their state
        $this->assertFalse($settings['PrivacyManager.DataRoundingEnabled']['enforced']);
        $this->assertSame('non_compliant', $settings['PrivacyManager.DataRoundingEnabled']['status']);

        // the enforced value is applied at read time
        $this->assertGreaterThanOrEqual(1, IPAnonymisation::getInstance($this->siteId)->getValue());

        $payload = $this->api->setCompliancePolicySettings(
            $this->siteId,
            'cnil_v1',
            ['PrivacyManager.IPAnonymisation' => 0]
        );

        $settings = $this->getSettingsById($payload);
        $this->assertFalse($settings['PrivacyManager.IPAnonymisation']['enforced']);
        $this->assertSame('non_compliant', $settings['PrivacyManager.IPAnonymisation']['status']);
    }

    public function testEnforceCompliancePolicySettingsEnforcesAllToggleableSettings(): void
    {
        $this->enableGranularComplianceFeature();

        $payload = $this->api->enforceCompliancePolicySettings($this->siteId, 'cnil_v1');

        $this->assertTrue($payload['policyEnforced']);
        foreach ($payload['settings'] as $setting) {
            if ($setting['toggleable']) {
                $this->assertTrue($setting['enforced'], "setting {$setting['id']} should be enforced");
            }
        }

        // enforcement is written per setting: the legacy whole-policy flag is left untouched,
        // it is only an inheritance default and no longer the source of truth
        $complianceStatus = $this->api->getComplianceStatus($this->siteId, 'cnil_v1');
        $this->assertFalse($complianceStatus['complianceModeEnforced']);
    }

    public function testSetCompliancePolicySettingsCanDisableSingleSettingAfterPolicyWasEnforced(): void
    {
        $this->enableGranularComplianceFeature();

        $this->api->enforceCompliancePolicySettings($this->siteId, 'cnil_v1');

        $payload = $this->api->setCompliancePolicySettings(
            $this->siteId,
            'cnil_v1',
            ['PrivacyManager.DataRoundingEnabled' => 0]
        );

        $settings = $this->getSettingsById($payload);
        $this->assertFalse($settings['PrivacyManager.DataRoundingEnabled']['enforced']);
        $this->assertSame('non_compliant', $settings['PrivacyManager.DataRoundingEnabled']['status']);
        $this->assertTrue($settings['PrivacyManager.CampaignParameterValuesMasked']['enforced']);
        $this->assertFalse($payload['policyEnforced']);
    }

    public function testSetCompliancePolicySettingsCanDisableSingleSettingAfterLegacyPolicyFlagWasSet(): void
    {
        // enforce via the deprecated whole-policy API first
        $this->api->setComplianceStatus((string) $this->siteId, 'cnil_v1', true);

        $this->enableGranularComplianceFeature();

        $payload = $this->api->setCompliancePolicySettings(
            $this->siteId,
            'cnil_v1',
            ['PrivacyManager.DataRoundingEnabled' => 0]
        );

        $settings = $this->getSettingsById($payload);
        $this->assertFalse($settings['PrivacyManager.DataRoundingEnabled']['enforced']);
        $this->assertTrue($settings['PrivacyManager.CampaignParameterValuesMasked']['enforced']);
    }

    public function testSetCompliancePolicySettingsThrowsForUnknownSite(): void
    {
        $this->enableGranularComplianceFeature();

        $this->expectException(\Piwik\Exception\UnexpectedWebsiteFoundException::class);

        $this->api->setCompliancePolicySettings(99999, 'cnil_v1', ['PrivacyManager.IPAnonymisation' => 1]);
    }

    public function testSetCompliancePolicySettingsThrowsForGarbageEnforcementValue(): void
    {
        $this->enableGranularComplianceFeature();

        $this->expectExceptionMessage('Invalid enforcement value for the setting "PrivacyManager.IPAnonymisation"');

        $this->api->setCompliancePolicySettings($this->siteId, 'cnil_v1', ['PrivacyManager.IPAnonymisation' => 'banana']);
    }

    private function enableGranularComplianceFeature(): void
    {
        Config::getInstance()->FeatureFlags = ['GranularPrivacyCompliance_feature' => 'enabled'];
    }

    private function disableGranularComplianceFeature(): void
    {
        Config::getInstance()->FeatureFlags = ['GranularPrivacyCompliance_feature' => 'disabled'];
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
