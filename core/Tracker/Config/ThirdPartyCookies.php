<?php

namespace Piwik\Tracker\Config;

use Piwik\Piwik;
use Piwik\Settings\Interfaces\ConfigSettingInterface;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;
use Piwik\Settings\Interfaces\Traits\Getters\ConfigGetterTrait;
use Piwik\Policy\CnilPolicy;

/**
 * @implements ConfigSettingInterface<bool|null>
 * @implements PolicyComparisonInterface<bool|null>
 * @implements SettingValueInterface<bool|null>
 */
class ThirdPartyCookies implements
    ConfigSettingInterface,
    PolicyComparisonInterface,
    SettingValueInterface
{
    /**
     * @use ConfigGetterTrait<bool|null>
     */
    use ConfigGetterTrait;

    /**
     * @use PolicyComparisonTrait<bool|null>
     */
    use PolicyComparisonTrait;

    /**
     * @var bool|null
     */
    private $value;

    private function __construct(?bool $value)
    {
        $this->value = $value;
    }

    protected static function getConfigSection(): string
    {
        return 'Tracker';
    }

    protected static function getConfigSettingName(): string
    {
        return 'use_third_party_id_cookie';
    }

    public static function getPolicyRequirements(): array
    {
        return [
            CnilPolicy::class => false,
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

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return Piwik::translate('General_ThirdPartyCookieSettingNote');
    }

    protected static function compareStrictness($value1, $value2): bool
    {
        if ($value1 === true && $value2 === true) {
            return true;
        }
        return false;
    }

    public static function getTitle(): string
    {
        return Piwik::translate('General_ThirdPartyCookieSettingTitle');
    }

    public static function getInstance(?int $idSite = null): self
    {
        $values = self::getPolicyRequiredValues($idSite);
        $configValue = self::getConfigValue($idSite);

        if ($configValue === null) {
            // Without site ID
            $configValue = self::getConfigValue();
        }
        $values['config'] = $configValue;
        $strictest = self::getStrictestValueFromArray($values);
        return new self($strictest);
    }

    public function getValue()
    {
        return $this->value;
    }

    public static function getInlineHelp(): string
    {
        return '';
    }
}
