<?php

namespace Piwik\Plugins\SitesManager\Settings;

use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;
use Piwik\Policy\CnilPolicy;
use Piwik\Settings\Interfaces\CustomSettingInterface;
use Piwik\Settings\Interfaces\Traits\Getters\CustomGetterTrait;

/**
 * @implements PolicyComparisonInterface<array|null>
 * @implements SettingValueInterface<string>
 */
class FilterPIIParameters implements
    CustomSettingInterface,
    PolicyComparisonInterface,
    SettingValueInterface
{
    /** @use CustomGetterTrait<array> */
    use CustomGetterTrait;

    /** @use PolicyComparisonTrait<array> */
    use PolicyComparisonTrait;

    /** @var string $value */
    private $value;

    protected function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function getTitle(): string
    {
        return Piwik::translate('SitesManager_FilterPIIParametersSettingTitle');
    }

    public static function getInstance(?int $idSite = null)
    {
        $values = self::getPolicyRequiredValues($idSite);
        $values['custom'] = self::getCustomValue($idSite);
        $strictest = self::getStrictestValueFromArray($values);
        return new self(implode(',', $strictest));
    }

    public static function getInlineHelp(): string
    {
        return '';
    }

    public static function getPolicyRequirements(): array
    {
        return [
            CnilPolicy::class => static::getMatomoPIIValue(),
        ];
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return Piwik::translate('SitesManager_FilterPiiParametersSettingRequirementNote');
    }

    public function getValue()
    {
        return $this->value;
    }

    public static function isCompliant(string $policy, ?int $idSite = null): bool
    {
        $policyValues = self::getPolicyRequirements();
        if (!array_key_exists($policy, $policyValues)) {
            return true;
        }

        $currentValue = explode(',', self::getInstance($idSite)->getValue());

        // current value is compliant if it contains all values defined in the policy requirements
        return !array_diff($policyValues[$policy], $currentValue);
    }

    protected static function compareStrictness($value1, $value2)
    {
        // stricter value doesn't really apply here, instead this function
        // will merge the arrays to create a stricter value
        return array_merge($value1, $value2);
    }

    public static function getCustomValue(?int $idSite = null)
    {
        return explode(',', API::getInstance()->getExcludedQueryParametersGlobal($idSite, $checkComplaincePolicy = false));
    }

    private static function getMatomoPIIValue()
    {
        return Config::getInstance()->SitesManager['CommonPIIParams'];
    }

    protected static function getCustomSettingName(): string
    {
        return '';
    }
}
