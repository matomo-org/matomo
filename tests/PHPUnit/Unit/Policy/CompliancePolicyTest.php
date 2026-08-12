<?php

namespace Piwik\Tests\Unit\Policy;

use PHPUnit\Framework\TestCase;
use Piwik\Tests\Framework\Mock\Policy\TestPolicy;
use Piwik\Tests\Framework\Mock\Settings\FakePolicySetting;

class CompliancePolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TestPolicy::reset();
    }

    public function testGetDetailsReturnsExpectedMetadata(): void
    {
        $details = TestPolicy::getDetails();

        $this->assertSame('test_policy_v1', $details['id']);
        $this->assertSame('Test Policy', $details['title']);
        $this->assertSame('Test policy description', $details['description']);
    }

    public function testGetSettingEnforcementNameCombinesPolicyAndSettingId(): void
    {
        $this->assertSame(
            'test_policy_v1_enforce__Fake_FakePolicySetting',
            TestPolicy::getSettingEnforcementName(FakePolicySetting::class)
        );
    }

    public function testIsEnforcedForSettingConfigValueShortCircuitsEverything(): void
    {
        // per-setting state says "off", config says "on"
        TestPolicy::setSettingEnforcementSystemValue(FakePolicySetting::class, false);
        TestPolicy::setConfigValue(true);

        $this->assertTrue(TestPolicy::isEnforcedForSetting(FakePolicySetting::class));
        $this->assertTrue(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 99));

        // per-setting and legacy state say "on", config says "off"
        TestPolicy::setSettingEnforcementSystemValue(FakePolicySetting::class, true);
        TestPolicy::setState(true, 99);
        TestPolicy::setConfigValue(false);

        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class));
        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 99));
    }

    public function testIsEnforcedForSettingSystemStateAppliesToAllSites(): void
    {
        TestPolicy::setSettingEnforcementSystemValue(FakePolicySetting::class, true);
        TestPolicy::setSettingEnforcementMeasurableValue(FakePolicySetting::class, 99, false);

        $this->assertTrue(TestPolicy::isEnforcedForSetting(FakePolicySetting::class));
        $this->assertTrue(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 99));
    }

    public function testIsEnforcedForSettingSiteStateWinsWhenSystemStateIsOff(): void
    {
        TestPolicy::setSettingEnforcementSystemValue(FakePolicySetting::class, false);
        TestPolicy::setSettingEnforcementMeasurableValue(FakePolicySetting::class, 99, true);

        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class));
        $this->assertTrue(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 99));
        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 100));
    }

    public function testIsEnforcedForSettingIsFalseWithoutAnyState(): void
    {
        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class));
        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 99));
    }

    public function testSetEnforcedForSettingInstanceLevelAppliesToAllSites(): void
    {
        TestPolicy::setEnforcedForSetting(FakePolicySetting::class, true);

        $this->assertTrue(TestPolicy::isEnforcedForSetting(FakePolicySetting::class));
        $this->assertTrue(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 99));

        TestPolicy::setEnforcedForSetting(FakePolicySetting::class, false);

        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class));
        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 99));
    }

    public function testSetEnforcedForSettingSiteLevelOnlyAffectsTheSite(): void
    {
        TestPolicy::setEnforcedForSetting(FakePolicySetting::class, true, 99);

        $this->assertTrue(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 99));
        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 100));
        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class));
    }

    public function testSetEnforcedForSettingDisablingForSiteClearsInstanceLevelState(): void
    {
        TestPolicy::setEnforcedForSetting(FakePolicySetting::class, true);
        TestPolicy::setEnforcedForSetting(FakePolicySetting::class, false, 99);

        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 99));
        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class));
        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 100));
    }

    public function testSetEnforcedForSettingEnablingForSiteKeepsInstanceLevelState(): void
    {
        TestPolicy::setEnforcedForSetting(FakePolicySetting::class, true);
        TestPolicy::setEnforcedForSetting(FakePolicySetting::class, true, 99);

        $this->assertTrue(TestPolicy::isEnforcedForSetting(FakePolicySetting::class));
        $this->assertTrue(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 100));
    }

    public function testIsEnforcedForSettingIgnoresTheLegacyWholePolicyFlag(): void
    {
        // the legacy flag is converted to per-setting state by a migration and no longer resolves
        TestPolicy::setState(true, 99);

        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class));
        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 99));
    }
}
