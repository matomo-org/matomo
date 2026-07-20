<?php

namespace Piwik\Plugins\PrivacyManager\Settings;

use Piwik\Piwik;
use Piwik\Policy\CnilPolicy;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Interfaces\MeasurableSettingInterface;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\Traits\Getters\MeasurableGetterTrait;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;
use Piwik\Site;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * @implements PolicyComparisonInterface<bool>
 * @implements SettingValueInterface<bool>
 * @implements MeasurableSettingInterface<bool>
 */
class CampaignParameterValuesMasked implements
    PolicyComparisonInterface,
    SettingValueInterface,
    MeasurableSettingInterface
{
    /** @use PolicyComparisonTrait<bool> */
    use PolicyComparisonTrait;

    /** @use MeasurableGetterTrait<bool> */
    use MeasurableGetterTrait;

    public const DISCARDED_CAMPAIGN_PLACEHOLDER = '__discarded_by_policy__';

    /** @var bool */
    private $value;

    protected function __construct(bool $value)
    {
        $this->value = $value;
    }

    protected static function compareStrictness($value1, $value2)
    {
        return $value1 || $value2;
    }

    protected static function getMeasurableName(): string
    {
        return 'campaign_parameter_values_masked';
    }

    protected static function getMeasurableType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    protected static function getMeasurableDefaultValue()
    {
        return false;
    }

    public static function getTitle(): string
    {
        return Piwik::translate('PrivacyManager_CampaignParameterValuesMaskedSettingTitle');
    }

    public static function getInstance(?int $idSite = null): self
    {
        $values = self::getPolicyRequiredValues($idSite);

        if (is_null($idSite)) {
            $values['measurable'] = null;

            $ids = Site::getIdSitesFromIdSitesString('all');
            $settingStatesPerMeasurable = array_map(function ($id) {
                return self::getMeasurableValue(intval($id));
            }, $ids);

            $anyActive = in_array(true, $settingStatesPerMeasurable, true);
            $values['system'] = $anyActive;
        } else {
            $values['measurable'] = self::getMeasurableValue($idSite);
        }

        $strictest = self::getStrictestValueFromArray($values);
        return new self($strictest);
    }

    public static function getInlineHelp(): string
    {
        return '';
    }

    public static function getPolicyRequirements(): array
    {
        return [
            CnilPolicy::class => true,
        ];
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

    public function getValue()
    {
        return $this->value;
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return Piwik::translate('PrivacyManager_CampaignParameterValuesMaskedSettingRequirementNote');
    }

    public static function isEnabled(?int $idSite = null): bool
    {
        $cache = TrackerCache::getCacheWebsiteAttributes($idSite);
        $cacheKey = self::class;

        return ($cache[$cacheKey] ?? false) === true;
    }

    public static function maskValue($value)
    {
        if ($value === '' || $value === false || $value === null) {
            return $value;
        }

        return self::DISCARDED_CAMPAIGN_PLACEHOLDER;
    }

    public static function getPlaceholderValue(): string
    {
        return self::DISCARDED_CAMPAIGN_PLACEHOLDER;
    }

    public static function isPlaceholderValue($value): bool
    {
        return is_string($value) && $value === self::DISCARDED_CAMPAIGN_PLACEHOLDER;
    }

    public static function formatValue($value)
    {
        if (self::isPlaceholderValue($value)) {
            return Piwik::translate('PrivacyManager_CampaignParameterDiscarded');
        }

        return $value;
    }
}
