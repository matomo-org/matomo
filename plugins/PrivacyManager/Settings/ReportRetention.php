<?php

namespace Piwik\Plugins\PrivacyManager\Settings;

use Piwik\Settings\Interfaces\ConfigSettingInterface;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;
use Piwik\Settings\Interfaces\Traits\Getters\ConfigGetterTrait;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\HipaaPolicy;

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
        return 'Data retention period';
    }

    public static function getComplianceRequirementNote(): string
    {
        // TODO add in dynamic messaging
        return 'Retention period is set to 365 days';
    }

    public static function getInlineHelp(): string
    {
        // TODO
        return '';
    }

    public static function getPolicyRequirements(): array
    {
        $policyValues = [];
        $policyValues[CnilPolicy::class] = 180;
        $policyValues[HipaaPolicy::class] = 180;

        return $policyValues;
    }

    public static function getInstance(?int $idSite = null): self
    {
        $values = self::getPolicyValues($idSite);
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
        if (is_null($value1)) {
            if (is_null($value2)) {
                return null;
            }
            return $value2;
        }
        if (is_null($value2)) {
            return $value1;
        }
        if ($value1 <= $value2) {
            return $value1;
        }
        return $value2;
    }
}
