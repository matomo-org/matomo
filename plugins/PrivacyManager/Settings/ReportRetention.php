<?php

namespace Piwik\Plugins\PrivacyManager\Settings;

use Piwik\Piwik;
use Piwik\Settings\Interfaces\ConfigSettingInterface;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;
use Piwik\Settings\Interfaces\Traits\Getters\ConfigGetterTrait;
use Piwik\Policy\CnilPolicy;

/**
 * @implements ConfigSettingInterface<int|null>
 * @implements PolicyComparisonInterface<int|null>
 * @implements SettingValueInterface<int|null>
 */
class ReportRetention implements ConfigSettingInterface, PolicyComparisonInterface, SettingValueInterface
{
    /**
     * @use ConfigGetterTrait<int|null>
     */
    use ConfigGetterTrait;

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

    protected static function getConfigSettingName(): string
    {
        return 'delete_logs_older_than';
    }

    protected static function getConfigSection(): string
    {
        return 'Deletelogs';
    }

    public static function getTitle(): string
    {
        return Piwik::translate('PrivacyManager_RetentionPeriodPolicySettingTitle');
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        $currentValue = self::getInstance($idSite)->getValue();
        return Piwik::translate('PrivacyManager_RetentionPeriodPolicySettingRequirementNote', $currentValue);
    }

    public static function getInlineHelp(): string
    {
        return '';
    }

    public static function getPolicyRequirements(): array
    {
        $policyValues = [];
        $policyValues[CnilPolicy::class] = 180;

        return $policyValues;
    }

    public static function getInstance(?int $idSite = null): self
    {
        $values = self::getPolicyRequiredValues($idSite);
        $values['config'] = self::getConfigValue();
        $strictest = self::getStrictestValueFromArray($values);
        return new self($strictest);
    }

    public static function isCompliant(string $policy, ?int $idSite = null): bool
    {
        $policyValues = self::getPolicyRequirements();

        if (!array_key_exists($policy, $policyValues)) {
            return true;
        }

        $currentValue = self::getInstance($idSite)->getValue();

        return $currentValue <= $policyValues[$policy];
    }

    protected static function compareStrictness($value1, $value2)
    {
        if ($value1 <= $value2) {
            return $value1;
        }
        return $value2;
    }
}
