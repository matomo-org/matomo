<?php

namespace Piwik\Tests\Unit\Settings;

use PHPUnit\Framework\TestCase;
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

    public function testIsControlledBySpecificPolicy()
    {
        $this->assertTrue(
            PolicyComparisonTraitImpl::isControlledBySpecificPolicy(TestPolicy::class)
        );
    }
}
