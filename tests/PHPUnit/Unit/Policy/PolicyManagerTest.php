<?php

namespace Piwik\tests\PHPUnit\Unit\Policy;

use PHPUnit\Framework\TestCase;
use Piwik\Policy\CompliancePolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Tests\Framework\Mock\Settings\FakePolicySetting;
use Piwik\tests\PHPUnit\Framework\Mock\Policy\PolicyManager as MockPolicyManager;
use Piwik\tests\PHPUnit\Framework\Mock\Policy\TestPolicy;

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
        $decoratedPolicies = PolicyManager::getAllPoliciesDecorated();
        foreach ($decoratedPolicies as $decoratedPolicy) {
            $this->assertArrayHasKey('id',$decoratedPolicy);
            $this->assertArrayHasKey('title',$decoratedPolicy);
            $this->assertArrayHasKey('description',$decoratedPolicy);
        }
    }

    public function testGetPolicyByName()
    {
        $policy = MockPolicyManager::getPolicyByName(TestPolicy::getName());
        $this->assertTrue(is_a($policy, CompliancePolicy::class, true));
    }

    public function testGetAllSettings()
    {
        $settings = PolicyManager::getAllSettings();
        foreach ($settings as $setting) {
            $this->assertTrue(is_a($setting, SettingValueInterface::class, true));
            $this->assertTrue(is_a($setting, PolicyComparisonInterface::class, true));
        }
    }

    public function testGetAllControlledSettings()
    {
        $settings = MockPolicyManager::getAllControlledSettings(TestPolicy::class);
        $this->assertCount(1, $settings);
        $this->assertTrue(is_a($settings[0], FakePolicySetting::class, true));
    }
}
