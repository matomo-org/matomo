<?php

namespace Piwik\Plugins\Live\Settings;

use Piwik\Policy\Policies\CnilPolicy;
use Piwik\Policy\Policies\HipaaPolicy;
use Piwik\Policy\Settings\MeasurableSettingInterface;
use Piwik\Policy\Settings\PolicyComparisonInterface;
use Piwik\Policy\Settings\SystemSettingInterface;
use Piwik\Policy\Settings\Traits\Getters\MeasurableGetterTrait;
use Piwik\Policy\Settings\Traits\Getters\SystemGetterTrait;
use Piwik\Policy\Settings\Traits\PolicyComparisonTrait;
use Piwik\Settings\FieldConfig;
use Piwik\Policy\Settings\SettingValueInterface;

class VisitorLog implements MeasurableSettingInterface, PolicyComparisonInterface, SettingValueInterface, SystemSettingInterface
{
    use MeasurableGetterTrait;
    use PolicyComparisonTrait;
    use SystemGetterTrait;

    /** @var bool|null */
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

    public static function getPolicyValues(?int $idSite = null): array
    {
        $policies = [];
        $policies[CnilPolicy::getName()] = CnilPolicy::isActive($idSite) ? true : null;
        $policies[HipaaPolicy::getName()] = HipaaPolicy::isActive($idSite) ? false : null;
        return $policies;
    }

    public static function getInstance(?int $idSite = null): self
    {
        $values = self::getPolicyValues($idSite);
        $values['measurable'] = self::getMeasurableValue($idSite);
        $values['system'] = self::getSystemValue();

        $strictest = self::getStrictestValueFromArray($values);
        return new self($strictest);
    }

    protected static function compareStrictness($value1, $value2)
    {
        return ($value1 || $value2);
    }
}
