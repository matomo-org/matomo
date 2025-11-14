<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy;

use Exception;
use Piwik\Tracker\Cache;
use Piwik\Plugin\Manager;
use Piwik\Policy\Exceptions\CompliancePolicyNotFoundException;
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
     * @return array<array<string>> of [['title' => (string) 'TITLE', 'note' => (string) 'NOTE']]
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
     *
     * @param Setting $setting
     * @return string|null
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
     * @param string $settingName
     * @param int|null $idSite
     * @param string|null $settingType
     * @return array<string, array{
     *      requiredValue: mixed
     *  }>
     * @throws Exception
     */
    public static function getCompliancePoliciesControllingASetting(string $settingName, ?int $idSite = null, ?string $settingType = null): array
    {
        if (!$settingType || !in_array($settingType, array_keys(self::$settingTypesMap), true)) {
            return [];
        }

        $policies = static::getAllPolicies();
        $settings = [];

        foreach ($policies as $policyClass) {
            if (false === $policyClass::isActive($idSite)) {
                continue;
            }
            $controlledSettings = self::getAllControlledSettings($policyClass, $idSite, $settingType);

            foreach ($controlledSettings as $controlledSetting) {
                if ($settingName === call_user_func([$controlledSetting, self::$settingTypesMap[$settingType]['method']])) {
                    $settings[$policyClass::getName()] = [
                        'requiredValue' => $controlledSetting::getPolicyRequirements()[$policyClass],
                    ];
                }
            }
        }

        return $settings;
    }
}
