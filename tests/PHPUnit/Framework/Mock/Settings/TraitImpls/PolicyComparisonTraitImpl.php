<?php

namespace Piwik\Tests\Framework\Mock\Settings\TraitImpls;

use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;
use Piwik\Settings\PolicyEnforcementState;
use Piwik\Tests\Framework\Mock\Policy\TestPolicy;
use Piwik\Tests\Framework\Mock\Settings\InMemoryPolicyEnforcementState;

class PolicyComparisonTraitImpl implements PolicyComparisonInterface
{
    /**
     * @use PolicyComparisonTrait<bool>
     */
    use PolicyComparisonTrait;

    protected static function compareStrictness($value1, $value2)
    {
        return $value1;
    }

    public static function getPolicyRequirements(): array
    {
        return [
            TestPolicy::class => true,
        ];
    }

    public static function isCompliant(string $policy, ?int $idSite = null): bool
    {
        return true;
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return 'test PolicyComparisonTrait';
    }

    protected static function getPolicySettingPluginName(): string
    {
        // the mock does not live in a plugin, so the name cannot be derived from its namespace
        return 'TestPlugin';
    }

    protected static function getPolicyEnforcementState(): PolicyEnforcementState
    {
        return new InMemoryPolicyEnforcementState(
            static::getPolicySettingPluginName(),
            static::getPolicySettingShortName() . PolicyEnforcementState::SETTING_NAME_SUFFIX
        );
    }
}
