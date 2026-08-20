<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy;

use Exception;
use Piwik\Piwik;
use Piwik\Tracker\Cache;
use Piwik\Plugin\Manager;
use Piwik\Policy\Exceptions\CompliancePolicyNotFoundException;
use Piwik\Policy\Exceptions\CompliancePolicyViolationException;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\Traits\Getters\ConfigGetterTrait;
use Piwik\Settings\Interfaces\Traits\Getters\CustomGetterTrait;
use Piwik\Settings\Interfaces\Traits\Getters\MeasurableGetterTrait;
use Piwik\Settings\Interfaces\Traits\Getters\OptionGetterTrait;
use Piwik\Settings\Interfaces\Traits\Getters\SystemGetterTrait;
use Piwik\Settings\Measurable\MeasurableProperty;
use Piwik\Settings\Measurable\MeasurableSetting;
use Piwik\Settings\Plugin\SystemConfigSetting;
use Piwik\Settings\Plugin\SystemSetting;
use Piwik\Settings\Setting;
use Piwik\Tracker\Config\ThirdPartyCookies;

class PolicyManager
{
    public const SETTING_TYPE_SYSTEM = 'system';
    public const SETTING_TYPE_MEASURABLE = 'measurable';
    public const SETTING_TYPE_CUSTOM = 'custom';
    public const SETTING_TYPE_OPTION = 'option';
    public const SETTING_TYPE_CONFIG = 'config';

    // TODO: In Matomo 6, get*Name methods will change visibility from protected to public,
    //  so we will need to replace the method names here
    private static $settingTypesMap = [
        self::SETTING_TYPE_SYSTEM     => [
            'trait' => SystemGetterTrait::class,
            'method' => 'getSystemSettingShortName',
        ],
        self::SETTING_TYPE_MEASURABLE => [
            'trait' => MeasurableGetterTrait::class,
            'method' => 'getMeasurableSettingShortName',
        ],
        self::SETTING_TYPE_CUSTOM     => [
            'trait' => CustomGetterTrait::class,
            'method' => 'getCustomSettingShortName',
        ],
        self::SETTING_TYPE_OPTION     => [
            'trait' => OptionGetterTrait::class,
            'method' => 'getOptionSettingShortName',
        ],
        self::SETTING_TYPE_CONFIG     => [
            'trait' => ConfigGetterTrait::class,
            'method' => 'getConfigSettingShortName',
        ],
    ];

    /**
     * @return array<class-string<CompliancePolicy>>
     */
    public static function getAllPolicies(): array
    {
        return [
            CnilPolicy::class,
        ];
    }

    /**
     * @return array<array<string, string>>
     */
    public static function getAllPoliciesDetails(): array
    {
        $policies = static::getAllPolicies();
        return array_map(function ($policyClass) {
            return $policyClass::getDetails();
        }, $policies);
    }

    /**
     * @return class-string<CompliancePolicy>|null
     */
    public static function getPolicyByName(string $policyName): ?string
    {
        $policies = static::getAllPolicies();
        foreach ($policies as $policyClass) {
            if ($policyName === $policyClass::getName()) {
                return $policyClass;
            }
        }

        return null;
    }

    /**
     * @return array<class-string<PolicyComparisonInterface<mixed>&SettingValueInterface<mixed>>>
     */
    protected static function getAllSettings(?string $settingType = null): array
    {
        $settings = Manager::getInstance()->findMultipleComponents('Settings', SettingValueInterface::class);
        $underPolicy = [];

        // Add core specific settings
        $settings[] = ThirdPartyCookies::class;

        foreach ($settings as $setting) {
            if (!is_a($setting, PolicyComparisonInterface::class, true)) {
                continue;
            }
            if ($settingType && !in_array(self::$settingTypesMap[$settingType]['trait'], class_uses($setting), true)) {
                continue;
            }

            $underPolicy[] = $setting;
        }

        return $underPolicy;
    }

    /**
     * @param class-string<CompliancePolicy> $policyClass
     * @return array<class-string<PolicyComparisonInterface<mixed>&SettingValueInterface<mixed>>>
     */
    public static function getAllControlledSettings(string $policyClass, ?int $idSite = null, ?string $settingType = null): array
    {
        $settings = static::getAllSettings($settingType);
        $underPolicy = [];

        foreach ($settings as $setting) {
            if (!$setting::isControlledBySpecificPolicy($policyClass, $idSite)) {
                continue;
            }

            $underPolicy[] = $setting;
        }

        return $underPolicy;
    }

    /**
     * @param class-string<CompliancePolicy> $policyClass
     * @throws CompliancePolicyNotFoundException when $policyClass is not a valid policy
     */
    public static function isPolicyActive(string $policyClass, ?int $idSite = null): bool
    {
        self::checkPolicyIsValid($policyClass);
        return $policyClass::isActive($idSite);
    }

    /**
     * @param class-string<CompliancePolicy> $policyClass
     * @throws \Exception when $policyClass is not a valid policy
     */
    public static function isPolicyConfigControlled(string $policyClass): bool
    {
        self::checkPolicyIsValid($policyClass);
        return $policyClass::isConfigControlled();
    }

    /**
     * @param class-string<CompliancePolicy> $policyClass
     * @return array<array<string>> of [['id' => (string) 'ID', 'title' => (string) 'TITLE', 'note' => (string) 'NOTE']]
     * @throws CompliancePolicyNotFoundException when $policyClass is not a valid policy
     */
    public static function getAllUnknownSettings(string $policyClass): array
    {
        self::checkPolicyIsValid($policyClass);
        return $policyClass::getUnknownSettings();
    }

    /**
     * @param class-string<CompliancePolicy> $policyClass
     * @throws CompliancePolicyNotFoundException when $policyClass is not a valid policy

     */
    private static function checkPolicyIsValid(string $policyClass): void
    {
        if (!is_a($policyClass, CompliancePolicy::class, true)) {
            throw new CompliancePolicyNotFoundException('Invalid compliance policy.');
        }
    }

    /**
     * @param class-string<CompliancePolicy> $policyClass
     * @throws CompliancePolicyNotFoundException when $policyClass is not a valid policy
     */
    public static function setPolicyActiveStatus(string $policyClass, bool $isActive, ?int $idSite = null): void
    {
        self::checkPolicyIsValid($policyClass);
        $policyClass::setActiveStatus($idSite, $isActive);
        if (!is_null($idSite)) {
            Cache::deleteCacheWebsiteAttributes($idSite);
        }
        Cache::deleteTrackerCache();
    }

    /**
     * Sets the enforcement state of multiple policy-controlled settings at once.
     *
     * @param class-string<CompliancePolicy> $policyClass
     * @param array<string, bool|int|string> $settingIdToEnforced Map of policy setting id => whether to enforce it
     * @throws CompliancePolicyNotFoundException when $policyClass is not a valid policy
     * @throws Exception when a setting id is unknown or the setting cannot be toggled
     */
    public static function setPolicySettingEnforcedStatuses(string $policyClass, array $settingIdToEnforced, ?int $idSite = null): void
    {
        self::checkPolicyIsValid($policyClass);

        $toggleableSettingsById = [];
        foreach (self::getAllControlledSettings($policyClass, $idSite) as $settingClass) {
            if (!$settingClass::isExternallyManagedByPolicyPage()) {
                $toggleableSettingsById[$settingClass::getPolicySettingId()] = $settingClass;
            }
        }

        // validate everything upfront: a failure mid-write would leave a partial
        // change behind without the tracker caches ever being invalidated
        $normalised = [];
        foreach ($settingIdToEnforced as $settingId => $enforced) {
            if (!array_key_exists($settingId, $toggleableSettingsById)) {
                throw new Exception(sprintf('The setting "%s" is unknown or cannot be toggled', $settingId));
            }

            $enforced = filter_var($enforced, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (is_null($enforced)) {
                throw new Exception(sprintf('Invalid enforcement value for the setting "%s"', $settingId));
            }

            $normalised[$settingId] = $enforced;
        }

        foreach ($normalised as $settingId => $enforced) {
            $toggleableSettingsById[$settingId]::setEnforced($enforced, $idSite);
        }

        if (!is_null($idSite)) {
            Cache::deleteCacheWebsiteAttributes($idSite);
        }
        Cache::deleteTrackerCache();
    }

    public static function storePolicySettingValuesInTrackerCache(array &$cacheContent, int $idSite): array
    {
        $settings = static::getAllSettings();
        foreach ($settings as $setting) {
            $cacheContent[$setting] = $setting::getInstance($idSite)->getValue();
        }
        return $cacheContent;
    }

    /**
     * Return setting type from a given Setting instance, including subclasses
     */
    public static function getSettingTypeFromSettingClass(Setting $setting): ?string
    {
        $map = [
            MeasurableSetting::class   => self::SETTING_TYPE_MEASURABLE,
            MeasurableProperty::class  => self::SETTING_TYPE_MEASURABLE,
            SystemSetting::class       => self::SETTING_TYPE_SYSTEM,
            SystemConfigSetting::class => self::SETTING_TYPE_CONFIG,
        ];

        foreach ($map as $class => $type) {
            if ($setting instanceof $class) {
                return $type;
            }
        }

        return null;
    }

    /**
     * For a given setting name, return an information on policies that may control the setting and its required value.
     *
     * @return array<string, array{
     *      requiredValue: mixed,
     *      effectiveValue: mixed,
     *      constraintType: PolicyComparisonInterface::POLICY_CONSTRAINT_*,
     *      scope: PolicyComparisonInterface::ENFORCEMENT_SCOPE_*,
     *      policyTitle: string
     *  }>
     * @throws Exception
     */
    public static function getCompliancePoliciesControllingASetting(string $settingName, ?int $idSite = null, ?string $settingType = null): array
    {
        $settings = [];

        foreach (self::findControllingPolicies($settingName, $idSite, $settingType) as $policyClass => $controlledSetting) {
            $settings[$policyClass::getName()] = [
                'requiredValue' => $controlledSetting::getPolicyRequirements()[$policyClass],
                'effectiveValue' => $controlledSetting::getInstance($idSite)->getValue(),
                'constraintType' => $controlledSetting::getPolicyConstraintType($policyClass),
                'scope' => $controlledSetting::getEnforcementScope($idSite),
                'policyTitle' => $policyClass::getTitle(),
            ];
        }

        return $settings;
    }

    /**
     * Validates a value that is about to be stored for a setting compliance policies may control.
     *
     * A value that a policy leaves no alternative to is not stored at all: the field it comes from
     * is rendered read-only, so the settings form only ever posts the enforced value back, and
     * storing it would replace whatever the user had configured before the policy started applying.
     *
     * @param mixed $value
     * @return bool whether the value may be persisted
     * @throws CompliancePolicyViolationException when the value breaks a policy that is enforced
     * @throws Exception
     */
    public static function checkSettingValueAgainstPolicies(string $settingName, $value, ?int $idSite = null, ?string $settingType = null): bool
    {
        $controllingPolicies = self::findControllingPolicies($settingName, $idSite, $settingType);
        $mayBePersisted = true;

        foreach ($controllingPolicies as $policyClass => $controlledSetting) {
            if (!$controlledSetting::isValueCompliantWithPolicy($value, $policyClass)) {
                throw new CompliancePolicyViolationException(Piwik::translate(
                    'General_PolicyControlledSettingChangeRejected',
                    [$controlledSetting::getTitle(), $policyClass::getTitle()]
                ));
            }

            if ($controlledSetting::getPolicyConstraintType($policyClass) === PolicyComparisonInterface::POLICY_CONSTRAINT_EXACT) {
                $mayBePersisted = false;
            }
        }

        return $mayBePersisted;
    }

    /**
     * The value compliance policies enforce for a setting, which is what its settings screen has
     * to show: the stored value is only in effect while no policy overrides it.
     *
     * All policies controlling one setting resolve it through the same policy-controlled setting,
     * so they agree on the effective value.
     *
     * @param array<string, array{effectiveValue: mixed}> $controllingPolicies as returned by
     *        {@link getCompliancePoliciesControllingASetting()}
     * @param mixed $storedValue returned unchanged when no policy controls the setting
     * @return mixed
     */
    public static function getPolicyEnforcedValue(array $controllingPolicies, $storedValue)
    {
        foreach ($controllingPolicies as $policy) {
            return $policy['effectiveValue'];
        }

        return $storedValue;
    }

    /**
     * Whether the given policies leave no compliant alternative to the value they enforce, so the
     * field they control has to be shown read-only rather than merely restricted to fewer choices.
     *
     * @param array<string, array{constraintType: string}> $controllingPolicies as returned by
     *        {@link getCompliancePoliciesControllingASetting()}
     */
    public static function isFieldLockedByPolicies(array $controllingPolicies): bool
    {
        foreach ($controllingPolicies as $policy) {
            if ($policy['constraintType'] === PolicyComparisonInterface::POLICY_CONSTRAINT_EXACT) {
                return true;
            }
        }

        return false;
    }

    /**
     * The subset of the given values that every policy currently enforcing the setting allows, so
     * that a field bounded by a policy keeps offering the choices which stay compliant instead of
     * being locked to a single one.
     *
     * Selectable values are described in several shapes across settings screens, so this takes and
     * returns plain values and leaves callers to filter their own structure against the result.
     *
     * @param array<int, mixed> $values
     * @return array<int, mixed>
     * @throws Exception
     */
    public static function filterValuesAllowedByPolicies(array $values, string $settingName, ?int $idSite = null, ?string $settingType = null): array
    {
        foreach (self::findControllingPolicies($settingName, $idSite, $settingType) as $policyClass => $controlledSetting) {
            $values = array_filter(
                $values,
                static function ($value) use ($controlledSetting, $policyClass) {
                    return $controlledSetting::isValueCompliantWithPolicy($value, $policyClass);
                }
            );
        }

        return array_values($values);
    }

    /**
     * Policies currently enforcing the given setting, mapped to the policy-controlled setting
     * that describes what they require of it.
     *
     * @return array<class-string<CompliancePolicy>, class-string<PolicyComparisonInterface<mixed>&SettingValueInterface<mixed>>>
     * @throws Exception
     */
    private static function findControllingPolicies(string $settingName, ?int $idSite, ?string $settingType): array
    {
        if (!$settingType || !in_array($settingType, array_keys(self::$settingTypesMap), true)) {
            return [];
        }

        $controllingPolicies = [];

        foreach (static::getAllPolicies() as $policyClass) {
            foreach (self::getAllControlledSettings($policyClass, $idSite, $settingType) as $controlledSetting) {
                if ($settingName !== call_user_func([$controlledSetting, self::$settingTypesMap[$settingType]['method']])) {
                    continue;
                }

                // a setting subscribes to its policies regardless of whether it is being
                // enforced, so the enforcement state of the setting itself is what decides
                // whether a policy currently controls it
                if (!$controlledSetting::isEnforced($idSite)) {
                    continue;
                }

                $controllingPolicies[$policyClass] = $controlledSetting;
            }
        }

        return $controllingPolicies;
    }
}
