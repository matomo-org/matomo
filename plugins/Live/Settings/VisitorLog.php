<?php

namespace Piwik\Plugins\Live\Settings;

use Piwik\Piwik;
use Piwik\Policy\CnilPolicy;
use Piwik\Settings\Interfaces\MeasurableSettingInterface;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SystemSettingInterface;
use Piwik\Settings\Interfaces\Traits\Getters\MeasurableGetterTrait;
use Piwik\Settings\Interfaces\Traits\Getters\SystemGetterTrait;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Interfaces\SettingValueInterface;

/**
 * @implements MeasurableSettingInterface<bool>
 * @implements PolicyComparisonInterface<bool>
 * @implements SettingValueInterface<bool>
 * @implements SystemSettingInterface<bool>
 */
class VisitorLog implements MeasurableSettingInterface, PolicyComparisonInterface, SettingValueInterface, SystemSettingInterface
{
    /**
     * @use MeasurableGetterTrait<bool>
     */
    use MeasurableGetterTrait;

    /**
     * @use PolicyComparisonTrait<bool>
     */
    use PolicyComparisonTrait;

    /**
     * @use SystemGetterTrait<bool>
     */
    use SystemGetterTrait;

    /**
     * @var bool
     */
    private $value;

    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    protected static function getMeasurableName(): string
    {
        return 'disable_visitor_log';
    }

    protected static function getMeasurableDefaultValue()
    {
        return false;
    }

    protected static function getMeasurableType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    protected static function getSystemName(): string
    {
        return 'disable_visitor_log';
    }

    protected static function getSystemDefaultValue()
    {
        return false;
    }

    protected static function getSystemType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    public static function getTitle(): string
    {
        return Piwik::translate('Live_DisableVisitsLogAndProfile');
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        // TODO add dynamic messaging
        return Piwik::translate('Live_VisitorLogPolicySettingRequirementNote');
    }

    public static function getInlineHelp(): string
    {
        return Piwik::translate('Live_DisableVisitsLogAndProfileDescription');
    }

    public static function getPolicyRequirements(): array
    {
        $policyValues = [];
        $policyValues[CnilPolicy::class] = true;

        return $policyValues;
    }

    public static function getInstance(?int $idSite = null): self
    {
        $values = self::getPolicyRequiredValues($idSite);
        $values['measurable'] = $idSite === null ? null : self::getMeasurableValue($idSite);
        $values['system'] = self::getSystemValue();

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

        return $currentValue === $policyValues[$policy];
    }

    protected static function compareStrictness($value1, $value2)
    {
        return ($value1 || $value2);
    }
}
