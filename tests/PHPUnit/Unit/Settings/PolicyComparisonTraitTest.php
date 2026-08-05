<?php

namespace Piwik\Tests\Unit\Settings;

use PHPUnit\Framework\TestCase;
use Piwik\Policy\PolicyEnforcementBypass;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Mock\Policy\TestPolicy;
use Piwik\Tests\Framework\Mock\Settings\TraitImpls\PolicyComparisonTraitImpl;

class PolicyComparisonTraitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testGetPolicyValuesPolicyActive()
    {
        TestPolicy::setSettingEnforcementSystemValue(PolicyComparisonTraitImpl::class, true);

        try {
            $values = PolicyComparisonTraitImpl::getPolicyRequiredValues();
            $this->assertCount(1, $values);
            $this->assertArrayHasKey(TestPolicy::class, $values);
            $this->assertNotNull($values[TestPolicy::class]);
        } finally {
            TestPolicy::setSettingEnforcementSystemValue(PolicyComparisonTraitImpl::class, null);
        }
    }

    public function testGetPolicyValuesPolicyInactive()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, false);
        $values = PolicyComparisonTraitImpl::getPolicyRequiredValues();
        $this->assertCount(1, $values);
        $this->assertArrayHasKey(TestPolicy::class, $values);
        $this->assertNull($values[TestPolicy::class]);
    }

    public function testIsControlledBySpecificPolicy()
    {
        $this->assertTrue(
            PolicyComparisonTraitImpl::isControlledBySpecificPolicy(TestPolicy::class)
        );
    }

    public function testGetPolicyValuesPresentWhenSettingEnforcementIsEnabledWithoutPolicyFlag()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, false);
        TestPolicy::setSettingEnforcementSystemValue(PolicyComparisonTraitImpl::class, true);

        try {
            $values = PolicyComparisonTraitImpl::getPolicyRequiredValues();
            $this->assertNotNull($values[TestPolicy::class]);
        } finally {
            TestPolicy::setSettingEnforcementSystemValue(PolicyComparisonTraitImpl::class, null);
        }
    }

    public function testGetPolicyValuesNulledWhenSettingEnforcementIsDisabledDespiteActivePolicyFlag()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, true);
        TestPolicy::setSettingEnforcementSystemValue(PolicyComparisonTraitImpl::class, false);

        try {
            $values = PolicyComparisonTraitImpl::getPolicyRequiredValues();
            $this->assertNull($values[TestPolicy::class]);
        } finally {
            TestPolicy::setSettingEnforcementSystemValue(PolicyComparisonTraitImpl::class, null);
        }
    }

    public function testGetPolicyValuesNulledWhileEnforcementIsBypassed()
    {
        TestPolicy::setSettingEnforcementSystemValue(PolicyComparisonTraitImpl::class, true);

        try {
            $values = PolicyEnforcementBypass::run(function () {
                return PolicyComparisonTraitImpl::getPolicyRequiredValues();
            });

            $this->assertCount(1, $values);
            $this->assertArrayHasKey(TestPolicy::class, $values);
            $this->assertNull($values[TestPolicy::class]);

            // the bypass must not leak outside of the callable
            $valuesAfterBypass = PolicyComparisonTraitImpl::getPolicyRequiredValues();
            $this->assertNotNull($valuesAfterBypass[TestPolicy::class]);
        } finally {
            TestPolicy::setSettingEnforcementSystemValue(PolicyComparisonTraitImpl::class, null);
        }
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

    public function testGetPolicySettingIdDefaultsToPluginAndShortClassName()
    {
        $this->assertSame(
            'Framework.PolicyComparisonTraitImpl',
            PolicyComparisonTraitImpl::getPolicySettingId()
        );
    }

    public function testDefaultPolicyPageMetadata()
    {
        $this->assertFalse(PolicyComparisonTraitImpl::isExternallyManagedByPolicyPage());
        $this->assertSame('', PolicyComparisonTraitImpl::getWhatItDoes());
        $this->assertSame('', PolicyComparisonTraitImpl::getImpact());
    }
}
