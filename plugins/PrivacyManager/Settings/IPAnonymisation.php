<?php

namespace Piwik\Plugins\PrivacyManager\Settings;

use Piwik\Policy\Settings\OptionSettingInterface;
use Piwik\Policy\Settings\PolicyComparisonInterface;
use Piwik\Policy\Settings\SettingValueInterface;
use Piwik\Policy\Settings\Traits\PolicyComparisonTrait;
use Piwik\Policy\Settings\Traits\Getters\OptionGetterTrait;
use Piwik\Policy\Policies\CnilPolicy;
use Piwik\Policy\Policies\HipaaPolicy;

class IPAnonymisation implements OptionSettingInterface, PolicyComparisonInterface, SettingValueInterface
{
    use OptionGetterTrait;
    use PolicyComparisonTrait;

    /** @var int|null */
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

    public static function getPolicyRequirements(): array
    {
        $policies = [];
        $policies[CnilPolicy::class] = 1;
        $policies[HipaaPolicy::class] = 1;

        return $policies;
    }

    public static function getInstance(?int $idSite = null)
    {
        $values = self::getPolicyValues($idSite);
        $values['option'] = self::getOptionValue();
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
