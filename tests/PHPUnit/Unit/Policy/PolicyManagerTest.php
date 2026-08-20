<?php

namespace Piwik\tests\Unit\Policy;

use PHPUnit\Framework\TestCase;
use Piwik\Policy\CompliancePolicy;
use Piwik\Policy\Exceptions\CompliancePolicyViolationException;
use Piwik\Policy\PolicyManager;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
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

    public function testGetCompliancePoliciesControllingASettingDescribesTheEnforcement()
    {
        TestPolicy::reset();
        TestPolicy::setSystemValue(true);

        $policies = MockPolicyManager::getCompliancePoliciesControllingASetting(
            'fake_policy_setting',
            null,
            PolicyManager::SETTING_TYPE_SYSTEM
        );

        $this->assertSame([
            'test_policy_v1' => [
                'requiredValue' => true,
                'effectiveValue' => true,
                'constraintType' => PolicyComparisonInterface::POLICY_CONSTRAINT_EXACT,
                'scope' => PolicyComparisonInterface::ENFORCEMENT_SCOPE_INSTANCE,
                'policyTitle' => 'Test Policy',
            ],
        ], $policies);

        TestPolicy::reset();
    }

    public function testGetCompliancePoliciesControllingASettingReportsWebsiteLevelEnforcement()
    {
        TestPolicy::reset();
        TestPolicy::setMeasurableValue(1, true);

        $policies = MockPolicyManager::getCompliancePoliciesControllingASetting(
            'fake_policy_setting',
            1,
            PolicyManager::SETTING_TYPE_SYSTEM
        );

        $this->assertSame(
            PolicyComparisonInterface::ENFORCEMENT_SCOPE_SITE,
            $policies['test_policy_v1']['scope']
        );

        TestPolicy::reset();
    }

    public function testGetCompliancePoliciesControllingASettingIsEmptyWhenPolicyIsNotEnforced()
    {
        TestPolicy::reset();

        $this->assertSame([], MockPolicyManager::getCompliancePoliciesControllingASetting(
            'fake_policy_setting',
            null,
            PolicyManager::SETTING_TYPE_SYSTEM
        ));
    }

    public function testGetCompliancePoliciesControllingASettingIsEmptyForAnotherSetting()
    {
        TestPolicy::reset();
        TestPolicy::setSystemValue(true);

        $this->assertSame([], MockPolicyManager::getCompliancePoliciesControllingASetting(
            'some_other_setting',
            null,
            PolicyManager::SETTING_TYPE_SYSTEM
        ));

        TestPolicy::reset();
    }

    public function testCheckSettingValueAgainstPoliciesRejectsANonCompliantValue()
    {
        TestPolicy::reset();
        TestPolicy::setSystemValue(true);

        try {
            $this->expectException(CompliancePolicyViolationException::class);

            MockPolicyManager::checkSettingValueAgainstPolicies(
                'fake_policy_setting',
                false,
                null,
                PolicyManager::SETTING_TYPE_SYSTEM
            );
        } finally {
            TestPolicy::reset();
        }
    }

    public function testCheckSettingValueAgainstPoliciesDoesNotStoreAValueThePolicyDetermines()
    {
        TestPolicy::reset();
        TestPolicy::setSystemValue(true);

        // the field is rendered read-only, so what comes back is the enforced value and storing it
        // would replace whatever the user had configured before the policy started applying
        $this->assertFalse(MockPolicyManager::checkSettingValueAgainstPolicies(
            'fake_policy_setting',
            true,
            null,
            PolicyManager::SETTING_TYPE_SYSTEM
        ));

        TestPolicy::reset();
    }

    public function testCheckSettingValueAgainstPoliciesAllowsUncontrolledSettings()
    {
        TestPolicy::reset();
        TestPolicy::setSystemValue(true);

        $this->assertTrue(MockPolicyManager::checkSettingValueAgainstPolicies(
            'some_other_setting',
            'anything',
            null,
            PolicyManager::SETTING_TYPE_SYSTEM
        ));

        TestPolicy::reset();
    }

    public function testGetPolicyEnforcedValueFallsBackToTheStoredValue()
    {
        $this->assertSame('stored', PolicyManager::getPolicyEnforcedValue([], 'stored'));
        $this->assertSame(759, PolicyManager::getPolicyEnforcedValue(
            ['cnil_v1' => ['effectiveValue' => 759]],
            180
        ));
    }

    public function testIsFieldLockedByPolicies()
    {
        $this->assertFalse(PolicyManager::isFieldLockedByPolicies([]));
        $this->assertFalse(PolicyManager::isFieldLockedByPolicies([
            'cnil_v1' => ['constraintType' => PolicyComparisonInterface::POLICY_CONSTRAINT_MAX],
        ]));
        $this->assertTrue(PolicyManager::isFieldLockedByPolicies([
            'cnil_v1' => ['constraintType' => PolicyComparisonInterface::POLICY_CONSTRAINT_EXACT],
        ]));
    }
}
