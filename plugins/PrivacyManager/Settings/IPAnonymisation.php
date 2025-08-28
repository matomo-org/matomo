<?php

namespace Piwik\Plugins\PrivacyManager\Settings;

use Piwik\Settings\Interfaces\OptionSettingInterface;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;
use Piwik\Settings\Interfaces\Traits\Getters\OptionGetterTrait;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\HipaaPolicy;

/**
 * @implements PolicyComparisonInterface<int|null>
 * @implements SettingValueInterface<int|null>
 */
class IPAnonymisation implements OptionSettingInterface, PolicyComparisonInterface, SettingValueInterface
{
    use OptionGetterTrait;

    /**
     * @use PolicyComparisonTrait<int|null>
     */
    use PolicyComparisonTrait;

    /**
     * @var int|null
     */
    private $value;

    private function __construct(?int $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    protected static function getOptionName(): string
    {
        return 'PrivacyManager.ipAnonymizerEnabled';
    }

    public static function getTitle(): string
    {
        return 'IP Anonymisation Enabled';
    }

    public static function getComplianceRequirementNote(): string
    {
        return "Anonymisation of Visitor's IP addresses must be enabled";
    }

    public static function getInlineHelp(): string
    {
        // TODO maybe make this only required for system/measurable settings
        return '';
    }

    public static function getPolicyRequirements(): array
    {
        $policies = [];
        $policies[CnilPolicy::class] = 1;
        $policies[HipaaPolicy::class] = 1;

        return $policies;
    }

    public static function getInstance(?int $idSite = null): self
    {
        $optionValue = self::getOptionValue();

        $values = self::getPolicyValues($idSite);
        $values['option'] = $optionValue;

        $x = self::getStrictestValueFromArray($values);

        return new self($x);
    }

    public static function isCompliant(string $policy, ?int $idSite = null): bool
    {
        $policyValues = self::getPolicyRequirements();

        if (!array_key_exists($policy, $policyValues)) {
            return true;
        }

        $currentValue = self::getInstance($idSite)->getValue();

        return $currentValue >= $policyValues[$policy];
    }

    protected static function compareStrictness($value1, $value2)
    {
        if (is_null($value1)) {
            return $value2;
        }
        if (is_null($value2)) {
            return $value1;
        }
        if ($value1 > $value2) {
            return $value1;
        }
        return $value2;
    }
}
