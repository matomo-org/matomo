<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy;

use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Interfaces\ConfigSettingInterface;
use Piwik\Settings\Interfaces\MeasurableSettingInterface;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SystemSettingInterface;
use Piwik\Settings\Interfaces\Traits\Getters\ConfigGetterTrait;
use Piwik\Settings\Interfaces\Traits\Setters\MeasurableSetterTrait;
use Piwik\Settings\Interfaces\Traits\Setters\SystemSetterTrait;
use Piwik\Settings\Measurable\MeasurableSetting;
use Piwik\Settings\Plugin\SystemSetting;

/**
 * @implements SystemSettingInterface<bool>
 * @implements MeasurableSettingInterface<bool>
 */
abstract class CompliancePolicy implements SystemSettingInterface, MeasurableSettingInterface, ConfigSettingInterface
{
    /**
     * @use SystemSetterTrait<bool>
     */
    use SystemSetterTrait;

    /**
     * @use MeasurableSetterTrait<bool>
     */
    use MeasurableSetterTrait;

    /**
     * @use ConfigGetterTrait<bool>
     */
    use ConfigGetterTrait;

    abstract public static function getName(): string;
    abstract public static function getTitle(): string;
    abstract protected static function generateDescription(): string;
    abstract protected static function generateWarnings(): string;

    public static function getDescription(): string
    {
        $description = static::generateDescription();

        /**
         * This event is triggered while the description of a compliance policy is
         * being generated. The policy description can be modified via this event.
         *
         * @param string &$description of the policy.
         */
        Piwik::postEvent('CompliancePolicy.updatePolicyDescription', [&$description, static::class]);

        $shouldShowWarnings = true;

        /**
         * This event is triggered while the description of a compliance policy is
         * being generated, and controls whether any warnings specific to the policy
         * are displayed at the end of the description.
         *
         * @param bool &$shouldShowWarnings set to false if the warnings should be hidden
         */
        Piwik::postEvent('CompliancePolicy.shouldShowWarnings', [&$shouldShowWarnings, static::class]);

        if ($shouldShowWarnings) {
            $warnings = static::generateWarnings();
            if (!empty($warnings)) {
                $description .= '<br/>' . static::generateWarnings();
            }
        }

        return $description;
    }

    /**
     * @return array<array<string>> of [['id' => (string) 'ID', 'title' => (string) 'TITLE', 'note' => (string) 'NOTE']]
     */
    abstract public static function getUnknownSettings(): array;

    /**
     * @return array<string, string>
     */
    public static function getDetails(): array
    {
        return [
            'id' => static::getName(),
            'title' => static::getTitle(),
            'description' => static::getDescription(),
        ];
    }

    protected static function getPluginManagerInstance(): Manager
    {
        return Manager::getInstance();
    }

    protected static function getSystemDefaultValue()
    {
        return false;
    }

    protected static function getSystemName(): string
    {
        return preg_replace('/\s+/', '', static::getName()) . '_policy_enabled';
    }

    protected static function getSystemType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    protected static function getMeasurableDefaultValue()
    {
        return false;
    }

    protected static function getMeasurableName(): string
    {
        return preg_replace('/\s+/', '', static::getName()) . '_policy_enabled';
    }

    protected static function getMeasurableType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    protected static function getConfigSection(): string
    {
        return Piwik::getPluginNameOfMatomoClass(static::class);
    }

    protected static function getConfigSettingName(): string
    {
        return static::getSystemName();
    }
    /**
     * If the policy is active at the instance level,
     * disabling the policy for a site will also disable it
     * for the instance.
     */
    public static function setActiveStatus(?int $idSite, bool $isActive): void
    {
        if (isset($idSite)) {
            static::setMeasurableValue($idSite, $isActive);
            if (static::getSystemValue() && !$isActive) {
                static::setSystemValue($isActive);
            }
        } else {
            static::setSystemValue($isActive);
        }

        /**
         * This event is triggered when the status of a compliance policy changes, and
         * is to be used to perform extra actions when a policy is activated/deactivated.
         *
         * The status of a policy cannot be changed via this event.
         *
         * @param bool $isActive Whether the policy is being activated or deactivated
         * @param int|null $idSite
         * @param class-string<CompliancePolicy> The compliance policy in question
         */
        Piwik::postEvent('CompliancePolicy.setActiveStatus', [$isActive, $idSite, static::class]);
    }

    /**
     * If the policy is active at the instance level, then
     * this function will return true for all sites.
     */
    public static function isActive(?int $idSite): bool
    {
        $instanceLevel = static::getSystemValue();
        if (!$instanceLevel && isset($idSite)) {
            return static::getMeasurableValue($idSite);
        }
        return $instanceLevel;
    }

    public static function isConfigControlled()
    {
        return !is_null(static::getConfigValue());
    }

    /**
     * Name under which the per-setting enforcement state of the given
     * policy-controlled setting is stored, at system and measurable scope.
     *
     * @param class-string<PolicyComparisonInterface<mixed>> $settingClass
     */
    public static function getSettingEnforcementName(string $settingClass): string
    {
        $settingId = str_replace('.', '_', $settingClass::getPolicySettingId());
        return preg_replace('/\s+/', '', static::getName()) . '_enforce__' . $settingId;
    }

    /**
     * Returns whether this policy currently enforces the given policy-controlled
     * setting for the given scope (instance-wide when $idSite is null).
     *
     * Resolution order:
     *  1. config-level enforcement of the whole policy
     *  2. explicit per-setting enforcement state; an enabled instance-wide state
     *     applies to all sites, like {@link isActive()} does for the whole policy
     *  3. the whole-policy enforcement flag, for scopes where no per-setting
     *     state has been stored yet
     *
     * @param class-string<PolicyComparisonInterface<mixed>> $settingClass
     */
    public static function isEnforcedForSetting(string $settingClass, ?int $idSite = null): bool
    {
        if (static::isConfigControlled()) {
            return (bool) static::getConfigValue();
        }

        $systemValue = static::getSettingEnforcementSystemValue($settingClass);
        if ($systemValue === true) {
            return true;
        }

        if (!is_null($idSite)) {
            $measurableValue = static::getSettingEnforcementMeasurableValue($settingClass, $idSite);
            if (!is_null($measurableValue)) {
                return $measurableValue;
            }
        }

        if (!is_null($systemValue)) {
            return false;
        }

        return static::isActive($idSite);
    }

    /**
     * Instance-wide enforcement state of the given setting, or null when no state was ever stored.
     *
     * @param class-string<PolicyComparisonInterface<mixed>> $settingClass
     */
    protected static function getSettingEnforcementSystemValue(string $settingClass): ?bool
    {
        // a null default value allows distinguishing "never stored" from an explicit value
        $setting = new SystemSetting(
            static::getSettingEnforcementName($settingClass),
            null,
            FieldConfig::TYPE_BOOL,
            Piwik::getPluginNameOfMatomoClass(static::class)
        );

        $value = $setting->getValue();

        return is_null($value) ? null : (bool) $value;
    }

    /**
     * Per-site enforcement state of the given setting, or null when no state was ever stored.
     *
     * @param class-string<PolicyComparisonInterface<mixed>> $settingClass
     */
    protected static function getSettingEnforcementMeasurableValue(string $settingClass, int $idSite): ?bool
    {
        $setting = new MeasurableSetting(
            static::getSettingEnforcementName($settingClass),
            null,
            FieldConfig::TYPE_BOOL,
            Piwik::getPluginNameOfMatomoClass(static::class),
            $idSite
        );

        $value = $setting->getValue();

        return is_null($value) ? null : (bool) $value;
    }
}
