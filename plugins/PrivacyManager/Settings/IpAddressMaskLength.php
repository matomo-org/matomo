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
class IpAddressMaskLength implements OptionSettingInterface, PolicyComparisonInterface, SettingValueInterface
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
        return 'PrivacyManager.IpAddressMaskLength';
    }

    public static function getTitle(): string
    {
        return 'IP Address Mask Length';
    }

    public static function getComplianceRequirementNote(): string
    {
        return 'Must be set to at least 2 bytes.';
    }

    public static function getInlineHelp(): string
    {
        // TODO
        return '';
    }

    public static function getPolicyRequirements(): array
    {
        $policies = [];
        $policies[CnilPolicy::class] = 2;
        $policies[HipaaPolicy::class] = 2;

        return $policies;
    }

    public static function getInstance(?int $idSite = null): self
    {
        $values = self::getPolicyValues($idSite);
        $values['option'] = intval(self::getOptionValue());
        return new self(self::getStrictestValueFromArray($values));
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
