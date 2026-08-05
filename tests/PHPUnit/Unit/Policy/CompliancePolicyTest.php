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

    /**
     * @dataProvider possibleStatesForPolicyActive
     */
    public function testSetActiveStatusInstanceLevel(
        $idSite,
        $newActiveState,
        $currentInstanceState,
        $currentSiteState,
        $expectedInstanceState,
        $expectedSiteState
    ): void {
        TestPolicy::setState($currentInstanceState, $currentSiteState ? 99 : false);
        TestPolicy::setActiveStatus($idSite, $newActiveState);
        $this->assertSame(TestPolicy::isActive(null), $expectedInstanceState, "Instance status $expectedInstanceState is incorrect");
        $this->assertSame(TestPolicy::isActive(99), $expectedSiteState, "Site status $expectedSiteState is incorrect");
    }

    public function possibleStatesForPolicyActive()
    {
        /*
            [
                idSite,
                newActiveState,
                currentInstanceState,
                currentSiteState,
                expectedInstanceState,
                expectedSiteState
            ]
         */
        yield [null, true, true, true, true, true];
        yield [null, true, true, false, true, true];
        yield [null, true, false, true, true, true];
        yield [null, true, false, false, true, true];
        yield [null, false, true, true, false, true];
        yield [null, false, true, false, false, false];
        yield [null, false, false, true, false, true];
        yield [null, false, false, false, false, false];
        yield [99, true, true, true, true, true];
        yield [99, true, true, false, true, true];
        yield [99, true, false, true, false, true];
        yield [99, true, false, false, false, true];
        yield [99, false, true, true, false, false];
        yield [99, false, true, false, false, false];
        yield [99, false, false, true, false, false];
        yield [99, false, false, false, false, false];
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

    public function testIsEnforcedForSettingExplicitSystemOffSuppressesLegacyFlag(): void
    {
        TestPolicy::setState(true, 99);
        TestPolicy::setSettingEnforcementSystemValue(FakePolicySetting::class, false);

        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class));
        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 99));
    }

    public function testIsEnforcedForSettingExplicitSiteOffSuppressesLegacyFlag(): void
    {
        TestPolicy::setState(false, 99);
        TestPolicy::setSettingEnforcementMeasurableValue(FakePolicySetting::class, 99, false);

        $this->assertFalse(TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 99));
    }

    /**
     * @dataProvider possibleStatesForPolicyActive
     */
    public function testIsEnforcedForSettingFallsBackToLegacyPolicyFlag(
        $idSite,
        $newActiveState,
        $currentInstanceState,
        $currentSiteState,
        $expectedInstanceState,
        $expectedSiteState
    ): void {
        // without any per-setting state, enforcement must exactly mirror the legacy whole-policy flag
        TestPolicy::setState($currentInstanceState, $currentSiteState ? 99 : false);
        TestPolicy::setActiveStatus($idSite, $newActiveState);

        $this->assertSame(
            $expectedInstanceState,
            TestPolicy::isEnforcedForSetting(FakePolicySetting::class)
        );
        $this->assertSame(
            $expectedSiteState,
            TestPolicy::isEnforcedForSetting(FakePolicySetting::class, 99)
        );
    }
}
