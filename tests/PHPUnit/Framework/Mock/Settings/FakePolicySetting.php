<?php

namespace Piwik\Tests\Framework\Mock\Settings;

use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
// the exact spelling matters: policy requirements are keyed by class-name string
use Piwik\Tests\Framework\Mock\Policy\TestPolicy;

class FakePolicySetting implements PolicyComparisonInterface, SettingValueInterface
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * Enforcement states per scope, the instance wide scope being stored under ''.
     *
     * @var array<string, bool>
     */
    private static $enforcementStates = [];

    /**
     * @var PolicyComparisonInterface::POLICY_CONSTRAINT_*
     */
    private static $constraintType = PolicyComparisonInterface::POLICY_CONSTRAINT_EXACT;

    /**
     * @var mixed the value the policy requires of this setting
     */
    private static $requiredValue = true;

    /**
     * The value the setting resolves to while the policy is enforced, which is what a settings
     * screen pre-fills its field with. Null falls back to the required value, which is what an
     * EXACT requirement always resolves to.
     *
     * @var mixed
     */
    private static $effectiveValue = null;

    /**
     * @param mixed $value
     */
    private function __construct($value)
    {
        $this->value = $value;
    }

    public static function reset(): void
    {
        self::$enforcementStates = [];
        self::$constraintType = PolicyComparisonInterface::POLICY_CONSTRAINT_EXACT;
        self::$requiredValue = true;
        self::$effectiveValue = null;
    }

    /**
     * @param PolicyComparisonInterface::POLICY_CONSTRAINT_* $constraintType
     * @param mixed $requiredValue
     * @param mixed $effectiveValue the value the enforced setting resolves to, defaulting to the
     *                              required value
     */
    public static function configurePolicy(string $constraintType, $requiredValue, $effectiveValue = null): void
    {
        self::$constraintType = $constraintType;
        self::$requiredValue = $requiredValue;
        self::$effectiveValue = $effectiveValue;
    }

    public static function getPolicyRequirements(): array
    {
        return [
            TestPolicy::class => TestPolicy::isActive(null),
        ];
    }

    public static function getPolicyRequiredValues(?int $idSite = null): array
    {
        return [
            TestPolicy::class => self::$requiredValue,
        ];
    }

    public static function getPolicyValuesAgainstProvided($settingValue, ?int $idSite = null)
    {
        return $settingValue;
    }

    public static function isCompliant(string $policy, ?int $idSite = null): bool
    {
        return true;
    }

    public static function isControlledBySpecificPolicy(string $policy, ?int $idSite = null): bool
    {
        return true;
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return 'fake policy setting compliance note';
    }

    public static function getPolicySettingId(): string
    {
        return 'Fake.FakePolicySetting';
    }

    public static function getPolicyConstraintType(string $policy): string
    {
        return self::$constraintType;
    }

    public static function isValueCompliantWithPolicy($value, string $policy): bool
    {
        // compared loosely, like the real settings do: values reach this from requests as strings
        // while requirements are declared as their native type
        switch (self::$constraintType) {
            case PolicyComparisonInterface::POLICY_CONSTRAINT_MIN:
                return $value >= self::$requiredValue;
            case PolicyComparisonInterface::POLICY_CONSTRAINT_MAX:
                return $value <= self::$requiredValue;
            default:
                return $value == self::$requiredValue;
        }
    }

    public static function getSystemSettingShortName(): string
    {
        return 'fake_policy_setting';
    }

    public static function isExternallyManagedByPolicyPage(): bool
    {
        return false;
    }

    public static function getWhatItDoes(?int $idSite = null): string
    {
        return 'fake policy setting what it does';
    }

    public static function getImpact(?int $idSite = null): string
    {
        return 'fake policy setting impact';
    }

    public static function getInstance(?int $idSite = null)
    {
        return new self(self::$effectiveValue ?? self::$requiredValue);
    }

    public function getValue()
    {
        return $this->value;
    }

    public static function getTitle(): string
    {
        return 'Fake Policy Setting';
    }

    public static function getInlineHelp(): string
    {
        return 'Fake policy setting inline help text';
    }

    public static function isEnforced(?int $idSite = null): bool
    {
        return self::getStoredEnforcementState($idSite)
            ?? self::getStoredEnforcementState(null)
            ?? TestPolicy::isActive($idSite);
    }

    public static function setEnforced(?bool $isEnforced, ?int $idSite = null): void
    {
        $scope = is_null($idSite) ? '' : (string) $idSite;

        if (is_null($isEnforced)) {
            unset(self::$enforcementStates[$scope]);
            return;
        }

        self::$enforcementStates[$scope] = $isEnforced;
    }

    public static function getStoredEnforcementState(?int $idSite = null): ?bool
    {
        return self::$enforcementStates[is_null($idSite) ? '' : (string) $idSite] ?? null;
    }

    public static function getEnforcementScope(?int $idSite = null): ?string
    {
        if (!self::isEnforced($idSite)) {
            return null;
        }

        if (true === self::getStoredEnforcementState(null)) {
            return PolicyComparisonInterface::ENFORCEMENT_SCOPE_INSTANCE;
        }

        if (!is_null($idSite) && true === self::getStoredEnforcementState($idSite)) {
            return PolicyComparisonInterface::ENFORCEMENT_SCOPE_SITE;
        }

        return TestPolicy::getSystemValue()
            ? PolicyComparisonInterface::ENFORCEMENT_SCOPE_INSTANCE
            : PolicyComparisonInterface::ENFORCEMENT_SCOPE_SITE;
    }

    public static function isEnforcementWritable(?int $idSite = null): bool
    {
        return true;
    }
}
