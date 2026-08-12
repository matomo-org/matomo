<?php

namespace Piwik\Tests\Unit\Settings;

use PHPUnit\Framework\TestCase;
use Piwik\Policy\PolicyEnforcementBypass;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Mock\Policy\TestPolicy;
use Piwik\Tests\Framework\Mock\Settings\InMemoryPolicyEnforcementState;
use Piwik\Tests\Framework\Mock\Settings\TraitImpls\ExternallyManagedPolicyComparisonTraitImpl;
use Piwik\Tests\Framework\Mock\Settings\TraitImpls\PolicyComparisonTraitImpl;

class PolicyComparisonTraitTest extends TestCase
{
    private const IDSITE = 1;
    private const OTHER_IDSITE = 2;

    public function setUp(): void
    {
        parent::setUp();

        TestPolicy::reset();
        InMemoryPolicyEnforcementState::reset();
    }

    public function tearDown(): void
    {
        TestPolicy::reset();
        InMemoryPolicyEnforcementState::reset();

        parent::tearDown();
    }

    public function testGetPolicyValuesPolicyActive()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);
        $values = PolicyComparisonTraitImpl::getPolicyRequiredValues();
        $this->assertCount(1, $values);
        $this->assertArrayHasKey(TestPolicy::class, $values);
        $this->assertNotNull($values[TestPolicy::class]);
    }

    public function testGetPolicyValuesPolicyInactive()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, false);
        $values = PolicyComparisonTraitImpl::getPolicyRequiredValues();
        $this->assertCount(1, $values);
        $this->assertArrayHasKey(TestPolicy::class, $values);
        $this->assertNull($values[TestPolicy::class]);
    }

    public function testGetPolicyValuesAppliesRequirementWhenTheSettingIsEnforcedWhileThePolicyIsNot()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, false);
        PolicyComparisonTraitImpl::setEnforced(true);

        $values = PolicyComparisonTraitImpl::getPolicyRequiredValues();

        $this->assertSame([TestPolicy::class => true], $values);
    }

    public function testGetPolicyValuesDropsRequirementWhenTheSettingIsNotEnforcedWhileThePolicyIs()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);
        PolicyComparisonTraitImpl::setEnforced(false);

        $values = PolicyComparisonTraitImpl::getPolicyRequiredValues();

        $this->assertSame([TestPolicy::class => null], $values);
    }

    public function testIsControlledBySpecificPolicy()
    {
        $this->assertTrue(
            PolicyComparisonTraitImpl::isControlledBySpecificPolicy(TestPolicy::class)
        );
    }

    public function testIsControlledBySpecificPolicyDoesNotDependOnTheEnforcementState()
    {
        PolicyComparisonTraitImpl::setEnforced(false);

        $this->assertTrue(
            PolicyComparisonTraitImpl::isControlledBySpecificPolicy(TestPolicy::class)
        );
    }

    public function testGetPolicyValuesNulledWhileEnforcementIsBypassed()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);

        $values = PolicyEnforcementBypass::run(function () {
            return PolicyComparisonTraitImpl::getPolicyRequiredValues();
        });

        $this->assertCount(1, $values);
        $this->assertArrayHasKey(TestPolicy::class, $values);
        $this->assertNull($values[TestPolicy::class]);

        // the bypass must not leak outside of the callable
        $valuesAfterBypass = PolicyComparisonTraitImpl::getPolicyRequiredValues();
        $this->assertNotNull($valuesAfterBypass[TestPolicy::class]);
    }

    public function testBypassIsClearedWhenCallableThrows()
    {
        try {
            PolicyEnforcementBypass::run(function (): void {
                throw new \Exception('test');
            });
            $this->fail('expected the exception to bubble up');
        } catch (\Exception $e) {
            $this->assertSame('test', $e->getMessage());
        }

        $this->assertFalse(PolicyEnforcementBypass::isActive());
    }

    // enforcement state resolution

    public function testIsEnforcedFollowsThePolicyWhenNoStateWasStored()
    {
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced());

        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);

        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced());
    }

    public function testIsEnforcedFollowsThePolicyPerSiteWhenNoStateWasStored()
    {
        TestPolicy::setMeasurableValue(self::IDSITE, true);

        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced(self::IDSITE));
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced(self::OTHER_IDSITE));
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced());
    }

    public function testIsEnforcedUsesTheStoredInstanceStateOverAnInactivePolicy()
    {
        PolicyComparisonTraitImpl::setEnforced(true);

        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced());
        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced(self::IDSITE));
    }

    public function testIsEnforcedUsesTheStoredInstanceStateOverAnActivePolicy()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);
        PolicyComparisonTraitImpl::setEnforced(false);

        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced());
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced(self::IDSITE));
    }

    public function testIsEnforcedUsesTheStoredSiteStateOverThePolicy()
    {
        PolicyComparisonTraitImpl::setEnforced(true, self::IDSITE);

        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced(self::IDSITE));
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced(self::OTHER_IDSITE));
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced());
    }

    public function testIsEnforcedLetsAnInstanceWideStateApplyToEverySite()
    {
        PolicyComparisonTraitImpl::setEnforced(true);

        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced(self::IDSITE));
        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced(self::OTHER_IDSITE));
    }

    public function testIsEnforcedAppliesAnInstanceWideOptOutToSitesWithoutTheirOwnState()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);
        PolicyComparisonTraitImpl::setEnforced(false);

        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced(self::OTHER_IDSITE));
    }

    public function testIsEnforcedIsPinnedOnByAConfigControlledPolicy()
    {
        TestPolicy::setConfigValue(true);
        PolicyComparisonTraitImpl::setEnforced(false);

        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced());
        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced(self::IDSITE));
    }

    public function testIsEnforcedIsPinnedOffByAConfigControlledPolicy()
    {
        TestPolicy::setConfigValue(false);
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);

        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced());
    }

    // storing the enforcement state

    public function testSetEnforcedOnlyStoresAStateThatDeviatesFromThePolicy()
    {
        PolicyComparisonTraitImpl::setEnforced(false);

        $this->assertSame([], InMemoryPolicyEnforcementState::getAllStates());
        $this->assertNull(PolicyComparisonTraitImpl::getStoredEnforcementState());
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced());
    }

    public function testSetEnforcedStoresAStateThatDeviatesFromThePolicy()
    {
        PolicyComparisonTraitImpl::setEnforced(true);

        $this->assertTrue(PolicyComparisonTraitImpl::getStoredEnforcementState());
    }

    public function testSetEnforcedRemovesAStoredStateWhenItMatchesThePolicyAgain()
    {
        PolicyComparisonTraitImpl::setEnforced(true);
        $this->assertTrue(PolicyComparisonTraitImpl::getStoredEnforcementState());

        PolicyComparisonTraitImpl::setEnforced(false);

        $this->assertNull(PolicyComparisonTraitImpl::getStoredEnforcementState());
        $this->assertSame([], InMemoryPolicyEnforcementState::getAllStates());
    }

    public function testSetEnforcedWithNullResetsTheSettingToFollowItsPolicies()
    {
        PolicyComparisonTraitImpl::setEnforced(true);

        PolicyComparisonTraitImpl::setEnforced(null);

        $this->assertNull(PolicyComparisonTraitImpl::getStoredEnforcementState());

        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);
        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced());
    }

    public function testSetEnforcedGivesWayForASiteOptOutOfAnInstanceWideState()
    {
        PolicyComparisonTraitImpl::setEnforced(true);

        PolicyComparisonTraitImpl::setEnforced(false, self::IDSITE);

        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced(self::IDSITE));
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced());
        $this->assertNull(PolicyComparisonTraitImpl::getStoredEnforcementState());
    }

    public function testSetEnforcedPinsASiteOptOutWhileThePolicyStaysActive()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);

        PolicyComparisonTraitImpl::setEnforced(false, self::IDSITE);

        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced(self::IDSITE));
        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced(self::OTHER_IDSITE));
        $this->assertFalse(PolicyComparisonTraitImpl::getStoredEnforcementState(self::IDSITE));
    }

    // externally managed settings

    public function testExternallyManagedSettingFollowsItsPoliciesOnly()
    {
        $this->assertFalse(ExternallyManagedPolicyComparisonTraitImpl::isEnforced());

        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);

        $this->assertTrue(ExternallyManagedPolicyComparisonTraitImpl::isEnforced());
        $this->assertSame([], InMemoryPolicyEnforcementState::getAllStates());
    }

    public function testExternallyManagedSettingCannotBeEnforcedDirectly()
    {
        $this->expectExceptionMessage('cannot be changed');

        ExternallyManagedPolicyComparisonTraitImpl::setEnforced(true);
    }

    public function testExternallyManagedSettingIsNotWritable()
    {
        $this->assertFalse(ExternallyManagedPolicyComparisonTraitImpl::isEnforcementWritable());
    }

    // identity and writability

    public function testGetPolicySettingIdDefaultsToPluginAndShortClassName()
    {
        $this->assertSame(
            'TestPlugin.PolicyComparisonTraitImpl',
            PolicyComparisonTraitImpl::getPolicySettingId()
        );
    }

    public function testDefaultPolicyPageMetadata()
    {
        $this->assertFalse(PolicyComparisonTraitImpl::isExternallyManagedByPolicyPage());
        $this->assertSame('', PolicyComparisonTraitImpl::getWhatItDoes());
        $this->assertSame('', PolicyComparisonTraitImpl::getImpact());
    }

    public function testIsEnforcementWritable()
    {
        $this->assertTrue(PolicyComparisonTraitImpl::isEnforcementWritable());

        InMemoryPolicyEnforcementState::setIsWritable(false);

        $this->assertFalse(PolicyComparisonTraitImpl::isEnforcementWritable());
    }

    public function testIsEnforcementNotWritableWhenThePolicyIsConfigControlled()
    {
        TestPolicy::setConfigValue(true);

        $this->assertFalse(PolicyComparisonTraitImpl::isEnforcementWritable());
    }
}
