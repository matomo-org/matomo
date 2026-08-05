<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy;

use Piwik\Common;
use Piwik\Db;
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
     *
     * Additionally sets the enforcement state of every toggleable setting the
     * policy controls, so whole-policy and per-setting enforcement stay in sync.
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

        foreach (PolicyManager::getAllControlledSettings(static::class, $idSite) as $settingClass) {
            if ($settingClass::isExternallyManagedByPolicyPage()) {
                continue;
            }
            static::setEnforcedForSetting($settingClass, $isActive, $idSite);
        }

        static::alignStoredSettingEnforcementRows($idSite, $isActive);

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
     *
     * @deprecated since Matomo 6.0, enforcement is now stored per setting — use
     *             {@link isEnforcedForSetting()} instead. This flag only reflects the
     *             whole-policy state written by {@link setActiveStatus()}; it no longer
     *             participates in enforcement resolution.
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

        return false;
    }

    /**
     * Sets the enforcement state of the given policy-controlled setting.
     *
     * Mirrors the scope rules of {@link setActiveStatus()}: disabling a setting
     * for one site while it has explicit instance-wide enforcement state also
     * clears that instance-wide state. Enforcement that is only inherited from
     * the legacy whole-policy flag is not modified here; the migration
     * materialises that state into explicit per-setting values.
     *
     * @param class-string<PolicyComparisonInterface<mixed>> $settingClass
     */
    public static function setEnforcedForSetting(string $settingClass, bool $enforced, ?int $idSite = null): void
    {
        if (isset($idSite)) {
            static::setSettingEnforcementMeasurableValue($settingClass, $idSite, $enforced);
            if (!$enforced && static::getSettingEnforcementSystemValue($settingClass) === true) {
                static::setSettingEnforcementSystemValue($settingClass, false);
            }
        } else {
            static::setSettingEnforcementSystemValue($settingClass, $enforced);
        }

        /**
         * This event is triggered when the enforcement state of a single policy-controlled
         * setting changes, and is to be used to perform extra actions when the enforcement
         * of a setting is enabled/disabled. It is also posted for every toggleable setting
         * when a whole policy is activated/deactivated.
         *
         * The enforcement state cannot be changed via this event.
         *
         * @param bool $enforced Whether enforcement of the setting is being enabled or disabled
         * @param int|null $idSite
         * @param class-string<CompliancePolicy> The compliance policy in question
         * @param class-string<PolicyComparisonInterface<mixed>> The policy-controlled setting in question
         */
        Piwik::postEvent('CompliancePolicy.setSettingEnforcedStatus', [$enforced, $idSite, static::class, $settingClass]);
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

    /**
     * Aligns every stored per-setting enforcement row of this policy with the
     * whole-policy state, including rows of settings whose plugin is currently
     * deactivated and therefore not discoverable — otherwise stale enforcement
     * would survive a whole-policy toggle and resurface on plugin reactivation.
     *
     * Rows are enumerated directly but written through the settings storage, so
     * request-cached storage instances stay coherent with the aligned values.
     */
    protected static function alignStoredSettingEnforcementRows(?int $idSite, bool $isActive): void
    {
        if (is_null($idSite)) {
            foreach (self::fetchStoredSettingEnforcementNames(null) as $settingName) {
                self::writeSettingEnforcementValue($settingName, null, $isActive);
            }
            return;
        }

        foreach (self::fetchStoredSettingEnforcementNames($idSite) as $settingName) {
            self::writeSettingEnforcementValue($settingName, $idSite, $isActive);
        }

        if (!$isActive) {
            // mirror the explicit-clear rule: disabling for one site also clears
            // instance-wide enforcement, including rows of undiscoverable settings
            foreach (self::fetchStoredSettingEnforcementNames(null) as $settingName) {
                self::writeSettingEnforcementValue($settingName, null, false);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private static function fetchStoredSettingEnforcementNames(?int $idSite): array
    {
        $namePrefix = str_replace('_', '\_', preg_replace('/\s+/', '', static::getName()) . '_enforce__') . '%';
        $pluginName = Piwik::getPluginNameOfMatomoClass(static::class);

        if (is_null($idSite)) {
            $rows = Db::fetchAll(
                'SELECT setting_name FROM ' . Common::prefixTable('plugin_setting')
                . ' WHERE plugin_name = ? AND user_login = ? AND setting_name LIKE ?',
                [$pluginName, '', $namePrefix]
            );
        } else {
            $rows = Db::fetchAll(
                'SELECT setting_name FROM ' . Common::prefixTable('site_setting')
                . ' WHERE idsite = ? AND plugin_name = ? AND setting_name LIKE ?',
                [$idSite, $pluginName, $namePrefix]
            );
        }

        return array_column($rows, 'setting_name');
    }

    private static function writeSettingEnforcementValue(string $settingName, ?int $idSite, bool $value): void
    {
        $pluginName = Piwik::getPluginNameOfMatomoClass(static::class);

        if (is_null($idSite)) {
            $setting = new SystemSetting($settingName, null, FieldConfig::TYPE_BOOL, $pluginName);
        } else {
            $setting = new MeasurableSetting($settingName, null, FieldConfig::TYPE_BOOL, $pluginName, $idSite);
        }

        $setting->setValue($value);
        $setting->save();
    }

    /**
     * @param class-string<PolicyComparisonInterface<mixed>> $settingClass
     */
    protected static function setSettingEnforcementSystemValue(string $settingClass, bool $value): void
    {
        $setting = new SystemSetting(
            static::getSettingEnforcementName($settingClass),
            null,
            FieldConfig::TYPE_BOOL,
            Piwik::getPluginNameOfMatomoClass(static::class)
        );

        $setting->setValue($value);
        $setting->save();
    }

    /**
     * @param class-string<PolicyComparisonInterface<mixed>> $settingClass
     */
    protected static function setSettingEnforcementMeasurableValue(string $settingClass, int $idSite, bool $value): void
    {
        $setting = new MeasurableSetting(
            static::getSettingEnforcementName($settingClass),
            null,
            FieldConfig::TYPE_BOOL,
            Piwik::getPluginNameOfMatomoClass(static::class),
            $idSite
        );

        $setting->setValue($value);
        $setting->save();
    }
}
