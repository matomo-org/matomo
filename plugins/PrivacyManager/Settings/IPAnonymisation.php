<?php

namespace Piwik\Plugins\PrivacyManager\Settings;

use Piwik\Piwik;
use Piwik\Settings\Interfaces\OptionSettingInterface;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;
use Piwik\Settings\Interfaces\Traits\Getters\OptionGetterTrait;
use Piwik\Policy\CnilPolicy;

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
        return Piwik::translate('PrivacyManager_AnonymizeIpPolicySettingTitle');
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        // TODO add dynamic messaging
        return Piwik::translate('PrivacyManager_AnonymizeIpPolicySettingRequirementNote');
    }

    public static function getInlineHelp(): string
    {
        return Piwik::translate('PrivacyManager_AnonymizeIpInlineHelp');
    }

    public static function getPolicyRequirements(): array
    {
        $policies = [];
        $policies[CnilPolicy::class] = 1;

        return $policies;
    }

    public static function getInstance(?int $idSite = null): self
    {
        $optionValue = intval(self::getOptionValue());

        $values = self::getPolicyRequiredValues($idSite);
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
        if ($value1 > $value2) {
            return $value1;
        }
        return $value2;
    }
}
