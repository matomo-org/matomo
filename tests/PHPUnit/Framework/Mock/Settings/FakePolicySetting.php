<?php

namespace Piwik\Tests\Framework\Mock\Settings;

use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\tests\Framework\Mock\Policy\TestPolicy;

class FakePolicySetting implements PolicyComparisonInterface, SettingValueInterface
{
    /**
     * @var bool
     */
    private $value;

    /**
     * Enforcement states per scope, the instance wide scope being stored under ''.
     *
     * @var array<string, bool>
     */
    private static $enforcementStates = [];

    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function reset(): void
    {
        self::$enforcementStates = [];
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
            TestPolicy::class => true,
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
        return new self(true);
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

    public static function isEnforcementWritable(?int $idSite = null): bool
    {
        return true;
    }
}
