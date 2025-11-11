<?php

namespace Piwik\Plugins\PrivacyManager\Settings;

use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;

/**
 * Settings extending this abstract class will be enforced when the policy is enforced
 *
 * @implements PolicyComparisonInterface<bool>
 * @implements SettingValueInterface<bool>
 */
abstract class CompliancePolicyEnforcedSetting implements
    PolicyComparisonInterface,
    SettingValueInterface
{
    /** @use PolicyComparisonTrait<bool> */
    use PolicyComparisonTrait;

    /** @var bool */
    private $value;

    abstract public static function getTitle(): string;

    abstract public static function getComplianceRequirementNote(?int $idSite = null): string;

    abstract public static function getPolicyRequirements(): array;

    protected function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function getInlineHelp(): string
    {
        return '';
    }

    protected static function compareStrictness($value1, $value2)
    {
        return $value1 || $value2;
    }

    public static function getInstance(?int $idSite = null): self
    {
        $values = self::getPolicyRequiredValues($idSite);
        $strictest = (bool) self::getStrictestValueFromArray($values);

        return new static($strictest);
    }

    public static function isCompliant(string $policy, ?int $idSite = null): bool
    {
        $policyValues = static::getPolicyRequirements();
        if (!array_key_exists($policy, $policyValues)) {
            return true;
        }

        $currentValue = self::getInstance($idSite)->getValue();

        return $currentValue === $policyValues[$policy];
    }

    public function getValue()
    {
        return $this->value;
    }
}
