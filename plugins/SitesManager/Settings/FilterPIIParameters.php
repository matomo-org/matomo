<?php

namespace Piwik\Plugins\SitesManager\Settings;

use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API;
use Piwik\Plugins\SitesManager\SitesManager;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;
use Piwik\Policy\CnilPolicy;
use Piwik\Settings\Interfaces\OptionSettingInterface;
use Piwik\Settings\Interfaces\Traits\Getters\OptionGetterTrait;

/**
 * @implements PolicyComparisonInterface<string>
 * @implements SettingValueInterface<string>
 */
class FilterPIIParameters implements
    OptionSettingInterface,
    PolicyComparisonInterface,
    SettingValueInterface
{
    use OptionGetterTrait;

    /** @use PolicyComparisonTrait<string> */
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
        $values['option'] = self::getOptionValue($idSite) ?? '';
        $strictest = self::getStrictestValueFromArray($values);
        return new static($strictest);
    }

    public static function getInlineHelp(): string
    {
        return '';
    }

    public static function getPolicyRequirements(): array
    {
        return [
            CnilPolicy::class => SitesManager::URL_PARAM_EXCLUSION_TYPE_NAME_MATOMO_RECOMMENDED_PII,
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

        $currentValue = self::getInstance($idSite)->getValue();

        return $currentValue === $policyValues[$policy];
    }

    protected static function compareStrictness($value1, $value2)
    {
        /* strictest is simply defined as if a value equals matomo recommended PII
         * there are two possible values that matter:
         *    - matomo_PII
         *    - everything else
         */

        $matomoPII =  SitesManager::URL_PARAM_EXCLUSION_TYPE_NAME_MATOMO_RECOMMENDED_PII;

        if ($value1 === $matomoPII) {
            return $value1;
        } elseif ($value2 === $matomoPII) {
            return $value2;
        } elseif ($value1 === $value2) {
            return $value1;
        } else {
            return $value2;
        }
    }

    protected static function getOptionName(?int $idSite = null): string
    {
        return API::OPTION_EXCLUDE_TYPE_QUERY_PARAMS_GLOBAL;
    }
}
