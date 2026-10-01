<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings;

use Piwik\Common;
use Piwik\Db;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Plugins\PrivacyManager\Settings\IPAnonymisation;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Until a setting's own enforcement state is set, it follows the enforcement state of the
 * policies it subscribes to. That is what keeps installs which enforced a whole policy
 * before per-setting enforcement existed enforced afterwards, without any of their stored
 * state having to be converted.
 *
 * @group PolicySettings
 */
class PolicyEnforcementInheritanceTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2020-01-01 00:00:00');
        Fixture::createWebsite('2020-01-01 00:00:00');
    }

    public function testEveryControlledSettingIsEnforcedWhenOnlyTheWholePolicyFlagIsSet()
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true);

        $settings = $this->getControlledSettings();
        $this->assertNotEmpty($settings);

        foreach ($settings as $setting) {
            $this->assertTrue(
                $setting::isEnforced(),
                sprintf('%s should be enforced by the active policy', $setting::getPolicySettingId())
            );
        }
    }

    public function testNoEnforcementStateIsStoredWhenTheWholePolicyIsEnforced()
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true);

        foreach ($this->getControlledSettings() as $setting) {
            $this->assertNull(
                $setting::getStoredEnforcementState(),
                sprintf('%s should not have stored an enforcement state', $setting::getPolicySettingId())
            );
        }

        $this->assertSame(0, $this->countStoredEnforcementRows());
    }

    public function testNoControlledSettingIsEnforcedWhileThePolicyIsInactive()
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false);

        foreach ($this->getControlledSettings() as $setting) {
            $this->assertFalse(
                $setting::isEnforced(),
                sprintf('%s should not be enforced by an inactive policy', $setting::getPolicySettingId())
            );
        }
    }

    public function testSettingsFollowThePolicyPerSite()
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, 1);

        foreach ($this->getControlledSettings(1) as $setting) {
            $this->assertTrue($setting::isEnforced(1), $setting::getPolicySettingId());
            $this->assertFalse($setting::isEnforced(2), $setting::getPolicySettingId());
        }

        $this->assertSame(0, $this->countStoredEnforcementRows());
    }

    public function testASingleSettingCanOptOutWhileTheRestKeepFollowingThePolicy()
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true);

        IPAnonymisation::setEnforced(false);

        $this->assertFalse(IPAnonymisation::isEnforced());
        $this->assertFalse(IPAnonymisation::getStoredEnforcementState());

        foreach ($this->getControlledSettings() as $setting) {
            if ($setting === IPAnonymisation::class) {
                continue;
            }

            $this->assertTrue($setting::isEnforced(), $setting::getPolicySettingId());
            $this->assertNull($setting::getStoredEnforcementState(), $setting::getPolicySettingId());
        }
    }

    public function testASingleSettingCanBeEnforcedWhileThePolicyStaysInactive()
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false);

        IPAnonymisation::setEnforced(true);

        $this->assertTrue(IPAnonymisation::isEnforced());

        foreach ($this->getControlledSettings() as $setting) {
            if ($setting === IPAnonymisation::class) {
                continue;
            }

            $this->assertFalse($setting::isEnforced(), $setting::getPolicySettingId());
        }
    }

    public function testEnforcingASingleSettingAppliesItsRequiredValue()
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false);

        $required = IPAnonymisation::getPolicyRequirements()[CnilPolicy::class];

        IPAnonymisation::setEnforced(true);
        $this->assertGreaterThanOrEqual($required, IPAnonymisation::getInstance()->getValue());
        $this->assertTrue(IPAnonymisation::isCompliant(CnilPolicy::class));

        IPAnonymisation::setEnforced(false);
        $this->assertNull(IPAnonymisation::getPolicyRequiredValues()[CnilPolicy::class]);
    }

    /**
     * @return array<class-string<\Piwik\Settings\Interfaces\PolicyComparisonInterface<mixed>>>
     */
    private function getControlledSettings(?int $idSite = null): array
    {
        return PolicyManager::getAllControlledSettings(CnilPolicy::class, $idSite);
    }

    private function countStoredEnforcementRows(): int
    {
        $pluginRows = (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('plugin_setting')
            . " WHERE setting_name LIKE '%\_policy\_enforced'"
        );

        $siteRows = (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('site_setting')
            . " WHERE setting_name LIKE '%\_policy\_enforced'"
        );

        return $pluginRows + $siteRows;
    }
}
