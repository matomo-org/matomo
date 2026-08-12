<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings;

use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Mock\Policy\TestPolicy;
use Piwik\Tests\Framework\Mock\Settings\TraitImpls\PolicyComparisonTraitImpl;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Covers the policy-value resolution paths that flip the whole-policy active
 * flag via {@link PolicyManager::setPolicyActiveStatus()}. That fans out to the
 * per-setting enforcement state and enumerates the policy-controlled settings,
 * so a database is required and these cases cannot live in the unit suite.
 *
 * @group Core
 */
class PolicyComparisonTraitTest extends IntegrationTestCase
{
    public function testGetPolicyValuesPolicyInactive()
    {
        PolicyManager::setPolicyActiveStatus(TestPolicy::class, false);
        $values = PolicyComparisonTraitImpl::getPolicyRequiredValues();
        $this->assertCount(1, $values);
        $this->assertArrayHasKey(TestPolicy::class, $values);
        $this->assertNull($values[TestPolicy::class]);
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
}
