<?php

namespace Piwik\tests\Unit\Policy;

use PHPUnit\Framework\TestCase;
use Piwik\Policy\CompliancePolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Mock\Settings\FakePolicySetting;
use Piwik\Tests\Framework\Mock\Policy\PolicyManager as MockPolicyManager;
use Piwik\Tests\Framework\Mock\Policy\TestPolicy;

class PolicyManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testGetAllPolicies()
    {
        $policies = PolicyManager::getAllPolicies();

        foreach ($policies as $policy) {
            $this->assertTrue(is_a($policy, CompliancePolicy::class, true));
        }
    }

    public function testGetAllPoliciesDecorated()
    {
        $decoratedPolicies = PolicyManager::getAllPoliciesDetails();
        foreach ($decoratedPolicies as $decoratedPolicy) {
            $this->assertArrayHasKey('id', $decoratedPolicy);
            $this->assertArrayHasKey('title', $decoratedPolicy);
            $this->assertArrayHasKey('description', $decoratedPolicy);
        }
    }

    public function testGetPolicyByName()
    {
        $policy = MockPolicyManager::getPolicyByName(TestPolicy::getName());
        $this->assertTrue(is_a($policy, CompliancePolicy::class, true));
    }

    public function testGetAllControlledSettings()
    {
        $settings = MockPolicyManager::getAllControlledSettings(TestPolicy::class);
        $this->assertCount(1, $settings);
        $this->assertTrue(is_a($settings[0], FakePolicySetting::class, true));
    }

    public function testGetAllUnknownSettings()
    {
        $settings = MockPolicyManager::getAllUnknownSettings(TestPolicy::class);
        $this->assertCount(1, $settings);
        foreach ($settings as $unknownSetting) {
            $this->assertArrayHasKey('title', $unknownSetting);
            $this->assertArrayHasKey('note', $unknownSetting);
        }
    }

    public function testStorePolicySettingValuesInTrackerCache()
    {
        $cacheContent = [];
        MockPolicyManager::storePolicySettingValuesInTrackerCache($cacheContent, $idSite = 1);
        $this->assertSame([FakePolicySetting::class => true], $cacheContent);
    }
}
