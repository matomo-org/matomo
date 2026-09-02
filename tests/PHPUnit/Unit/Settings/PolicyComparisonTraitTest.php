<?php

namespace Piwik\Tests\Unit\Settings;

use PHPUnit\Framework\TestCase;
use Piwik\Policy\PolicyEnforcementBypass;
use Piwik\Policy\PolicyManager;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
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
        PolicyComparisonTraitImpl::setAllIdSites([self::IDSITE, self::OTHER_IDSITE]);
    }

    public function tearDown(): void
    {
        TestPolicy::reset();
        InMemoryPolicyEnforcementState::reset();
        PolicyComparisonTraitImpl::setAllIdSites([]);

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

    /**
     * Enforcement is additive: a site that enforces the setting stays enforced even while
     * the instance wide state does not enforce it.
     */
    public function testIsEnforcedLetsASiteEnforceOverAnInstanceWideOptOut()
    {
        PolicyComparisonTraitImpl::setEnforced(false);
        PolicyComparisonTraitImpl::setEnforced(true, self::IDSITE);

        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced(self::IDSITE));
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced(self::OTHER_IDSITE));
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced());
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

    public function testSetEnforcedStoresTheRequestedStateVerbatim()
    {
        PolicyComparisonTraitImpl::setEnforced(true);

        $this->assertTrue(PolicyComparisonTraitImpl::getStoredEnforcementState());
    }

    /**
     * The stored state records what was asked for, not how it differed from the policies
     * at the time, so the same intent survives a later policy change.
     */
    public function testSetEnforcedStoresAStateThatMatchesThePolicy()
    {
        PolicyComparisonTraitImpl::setEnforced(false);

        $this->assertFalse(PolicyComparisonTraitImpl::getStoredEnforcementState());
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced());

        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);

        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced());
    }

    public function testSetEnforcedKeepsAnExplicitlyEnforcedStateWhenThePolicyIsDeactivated()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);
        PolicyComparisonTraitImpl::setEnforced(true);

        PolicyManager::setPolicyActiveStatus(TestPolicy::class, false);

        $this->assertTrue(PolicyComparisonTraitImpl::getStoredEnforcementState());
        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced());
    }

    public function testSetEnforcedWithNullResetsTheSettingToFollowItsPolicies()
    {
        PolicyComparisonTraitImpl::setEnforced(true);

        PolicyComparisonTraitImpl::setEnforced(null);

        $this->assertNull(PolicyComparisonTraitImpl::getStoredEnforcementState());

        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);
        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced());
    }

    public function testSetEnforcedDemotesInstanceWideEnforcementSoOtherSitesKeepTheirs()
    {
        PolicyComparisonTraitImpl::setEnforced(true);

        PolicyComparisonTraitImpl::setEnforced(false, self::IDSITE);

        // the site that opted out is the only one that stops being enforced
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced(self::IDSITE));
        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced(self::OTHER_IDSITE));

        // the other site keeps its enforcement through a state of its own, not through
        // the instance wide one it used to inherit
        $this->assertTrue(PolicyComparisonTraitImpl::getStoredEnforcementState(self::OTHER_IDSITE));

        // the instance can no longer claim to be enforced as a whole
        $this->assertFalse(PolicyComparisonTraitImpl::getStoredEnforcementState());
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced());
    }

    public function testSetEnforcedDemotesInstanceWideEnforcementWhileThePolicyIsActive()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);
        PolicyComparisonTraitImpl::setEnforced(true);

        PolicyComparisonTraitImpl::setEnforced(false, self::IDSITE);

        // the explicit instance wide "not enforced" must not leak into the other site,
        // and must not be overridden by the still active policy either
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced(self::IDSITE));
        $this->assertTrue(PolicyComparisonTraitImpl::isEnforced(self::OTHER_IDSITE));
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforced());
    }

    public function testSetEnforcedIsRefusedWhenTheInstanceWideStateCannotBeDemoted()
    {
        PolicyComparisonTraitImpl::setEnforced(true);

        InMemoryPolicyEnforcementState::setIsWritable(false);

        try {
            PolicyComparisonTraitImpl::setEnforced(false, self::IDSITE);
            $this->fail('Expected the demotion to be refused');
        } catch (\Exception $e) {
            // nothing may have been written before the refusal
            InMemoryPolicyEnforcementState::setIsWritable(true);

            $this->assertTrue(PolicyComparisonTraitImpl::getStoredEnforcementState());
            $this->assertNull(PolicyComparisonTraitImpl::getStoredEnforcementState(self::IDSITE));
            $this->assertNull(PolicyComparisonTraitImpl::getStoredEnforcementState(self::OTHER_IDSITE));
        }
    }

    public function testIsEnforcementNotWritableForASiteWhenTheInstanceWideStateIsNot()
    {
        PolicyComparisonTraitImpl::setEnforced(true);

        $this->assertTrue(PolicyComparisonTraitImpl::isEnforcementWritable(self::IDSITE));

        InMemoryPolicyEnforcementState::setIsWritable(false);

        // changing the site would demote the instance wide state, so it needs the same
        // access as an instance wide change
        $this->assertFalse(PolicyComparisonTraitImpl::isEnforcementWritable(self::IDSITE));
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

    public function testGetEnforcementScopeIsNullWhileTheSettingIsNotEnforced()
    {
        $this->assertNull(PolicyComparisonTraitImpl::getEnforcementScope());
        $this->assertNull(PolicyComparisonTraitImpl::getEnforcementScope(self::IDSITE));
    }

    public function testGetEnforcementScopeReportsAConfigControlledPolicy()
    {
        TestPolicy::setConfigValue(true);

        // a policy pinned in config.ini.php cannot be changed from the compliance dashboard
        $this->assertSame(
            PolicyComparisonInterface::ENFORCEMENT_SCOPE_CONFIG,
            PolicyComparisonTraitImpl::getEnforcementScope()
        );
        $this->assertSame(
            PolicyComparisonInterface::ENFORCEMENT_SCOPE_CONFIG,
            PolicyComparisonTraitImpl::getEnforcementScope(self::IDSITE)
        );
    }

    public function testGetEnforcementScopeReportsAnInstanceWideEnforcementState()
    {
        PolicyComparisonTraitImpl::setEnforced(true);

        // an instance wide state covers every site, so it stays the origin for a single site too
        $this->assertSame(
            PolicyComparisonInterface::ENFORCEMENT_SCOPE_INSTANCE,
            PolicyComparisonTraitImpl::getEnforcementScope()
        );
        $this->assertSame(
            PolicyComparisonInterface::ENFORCEMENT_SCOPE_INSTANCE,
            PolicyComparisonTraitImpl::getEnforcementScope(self::IDSITE)
        );
    }

    public function testGetEnforcementScopeReportsASiteLevelEnforcementState()
    {
        PolicyComparisonTraitImpl::setEnforced(true, self::IDSITE);

        $this->assertSame(
            PolicyComparisonInterface::ENFORCEMENT_SCOPE_SITE,
            PolicyComparisonTraitImpl::getEnforcementScope(self::IDSITE)
        );
        // another site is unaffected, and nothing is enforced instance wide
        $this->assertNull(PolicyComparisonTraitImpl::getEnforcementScope(self::OTHER_IDSITE));
        $this->assertNull(PolicyComparisonTraitImpl::getEnforcementScope());
    }

    public function testGetEnforcementScopeFallsBackToAnInstanceWidePolicy()
    {
        // no state of its own, so the setting follows the policy it subscribes to
        TestPolicy::setSystemValue(true);

        $this->assertSame(
            PolicyComparisonInterface::ENFORCEMENT_SCOPE_INSTANCE,
            PolicyComparisonTraitImpl::getEnforcementScope()
        );
        $this->assertSame(
            PolicyComparisonInterface::ENFORCEMENT_SCOPE_INSTANCE,
            PolicyComparisonTraitImpl::getEnforcementScope(self::IDSITE)
        );
    }

    public function testGetEnforcementScopeFallsBackToASiteLevelPolicy()
    {
        TestPolicy::setMeasurableValue(self::IDSITE, true);

        $this->assertSame(
            PolicyComparisonInterface::ENFORCEMENT_SCOPE_SITE,
            PolicyComparisonTraitImpl::getEnforcementScope(self::IDSITE)
        );
        $this->assertNull(PolicyComparisonTraitImpl::getEnforcementScope(self::OTHER_IDSITE));
        $this->assertNull(PolicyComparisonTraitImpl::getEnforcementScope());
    }

    public function testPolicyRequirementsAreExactUnlessASettingSaysOtherwise()
    {
        // most requirements leave no compliant alternative, so the field they control can be locked
        $this->assertSame(
            PolicyComparisonInterface::POLICY_CONSTRAINT_EXACT,
            PolicyComparisonTraitImpl::getPolicyConstraintType(TestPolicy::class)
        );
    }

    public function testValuesAreCompliantWithPoliciesThatDoNotControlTheSetting()
    {
        $this->assertTrue(
            PolicyComparisonTraitImpl::isValueCompliantWithPolicy(false, 'Some\\Other\\Policy')
        );
    }
}
