<?php

namespace Piwik\Plugins\WebsiteMeasurable\Settings;

use Piwik\Piwik;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\HipaaPolicy;
use Piwik\Settings\Interfaces\MeasurableSettingInterface;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\Traits\Getters\MeasurableGetterTrait;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Site;

/**
 * @implements MeasurableSettingInterface<int>
 * @implements PolicyComparisonInterface<int>
 * @implements SettingValueInterface<int>
 */
class Ecommerce implements MeasurableSettingInterface, PolicyComparisonInterface, SettingValueInterface
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
     * @var int
     */
    private $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    protected static function getMeasurableName(): string
    {
        return 'ecommerce';
    }

    protected static function getMeasurableDefaultValue()
    {
        return false;
    }

    protected static function getMeasurableType(): string
    {
        return FieldConfig::TYPE_INT;
    }

    public static function getTitle(): string
    {
        return 'Ecommerce Enabled per site';
    }

    public static function getComplianceRequirementNote(): string
    {
        return 'Ecommerce must be disabled';
    }

    public static function getInlineHelp(): string
    {
        return Piwik::translate('Live_DisableVisitsLogAndProfileDescription');
    }

    public static function getPolicyRequirements(): array
    {
        $policyValues = [];
        $policyValues[CnilPolicy::class] = 0;
        $policyValues[HipaaPolicy::class] = 0;

        return $policyValues;
    }

    public static function getInstance(?int $idSite = null): self
    {
        $values = self::getPolicyValues($idSite);
        if (is_null($idSite)) {
            $values['measurable'] = null;

            $ids = Site::getIdSitesFromIdSitesString('all');
            $settingStatesPerMeasurable = array_map(function ($id) {
                return self::getMeasurableValue(intval($id), true);
            }, $ids);

            $anyActive = array_sum($settingStatesPerMeasurable) !== 0;

            $values['system'] = $anyActive;
        } else {
            $values['measurable'] = self::getMeasurableValue($idSite, true);
        }

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
        if (is_null($value1)) {
            return $value2;
        }
        if (is_null($value2)) {
            return $value1;
        }
        if ($value1 < $value2) {
            return $value1;
        }
        return $value2;
    }
}
