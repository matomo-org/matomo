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

    private function __construct(bool $value)
    {
        $this->value = $value;
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

    public static function getSettingName(): string
    {
        return 'fake_setting_name';
    }
}
