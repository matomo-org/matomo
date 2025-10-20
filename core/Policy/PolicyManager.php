<?php

namespace Piwik\Policy;

use Exception;
use Piwik\Plugin\Manager;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use ReflectionMethod;

class PolicyManager
{
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
    public static function getAllSettings(?int $idSite = null): array
    {
        $settings = Manager::getInstance()->findMultipleComponents('Settings', SettingValueInterface::class);
        $underPolicy = [];

        foreach ($settings as $setting) {
            if (!is_a($setting, PolicyComparisonInterface::class, true)) {
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
    public static function getAllControlledSettings(string $policyClass, ?int $idSite = null): array
    {
        $settings = static::getAllSettings($idSite);
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
     * @throws \Exception when $policyClass is not a valid policy
     */
    public static function isPolicyActive(string $policyClass, ?int $idSite = null): bool
    {
        if (!is_a($policyClass, CompliancePolicy::class, true)) {
            throw new Exception('Invalid compliance policy.');
        }
        return $policyClass::isActive($idSite);
    }

    /**
     * @param class-string<CompliancePolicy> $policyClass
     * @return array<array<string>> of [['title' => (string) 'TITLE', 'note' => (string) 'NOTE']]
     * @throws \Exception when $policyClass is not a valid policy
     */
    public static function getAllUnknownSettings(string $policyClass): array
    {
        if (!is_a($policyClass, CompliancePolicy::class, true)) {
            throw new Exception('Invalid compliance policy.');
        }

        return $policyClass::getUnknownSettings();
    }

    /**
     * Get a name from a policy controlled setting based on which method is available
     *
     * Note: used this cascading mechanism as some settings have already been implemented and released
     * in premium plugins, so it's harder to provide a new single method that would return a setting name.
     *
     * @param class-string<PolicyComparisonInterface<mixed>&SettingValueInterface<mixed>> $controlledSettingClass
     * @param int|null $idSite
     * @return string
     * @throws \ReflectionException
     * @throws Exception
     * @deprecated will be removed in Matomo 6 in favour of `public static function getSettingName` on `SettingValueInterface`
     */
    public static function getControlledSettingName(string $controlledSettingClass, ?int $idSite = null): string
    {
        $methodName = null;
        $args = [];

        // list of methods to check for and whether they take idSite as param
        $methods = [
            'getSystemName' => false,
            'getMeasurableName' => false,
            'getCustomSettingName' => false,
            'getOptionName' => true,
            'getConfigSettingName' => false,
        ];

        foreach ($methods as $method => $hasIdSiteParam) {
            if (method_exists($controlledSettingClass, $method)) {
                $methodName = $method;
                if ($hasIdSiteParam) {
                    $args = [$idSite];
                }
                break;
            }
        }

        // if we found a method name, use reflection to make it accessible and then call it
        if ($methodName) {
            $reflection = new ReflectionMethod($controlledSettingClass, $methodName);
            $reflection->setAccessible(true);

            return $reflection->invokeArgs(null, $args);
        }

        throw new Exception(
            sprintf("No suitable method found for privacy policy controlled setting class '%s' to get its name.", $controlledSettingClass)
        );
    }

    /**
     * For a given setting name, return an information on policies that may control the setting and its required value.
     *
     * @param string $settingName
     * @param int|null $idSite
     * @return array<string, array{
     *      requiredValue: mixed
     *  }>
     * @throws \ReflectionException
     */
    public static function getCompliancePoliciesControllingASetting(string $settingName, ?int $idSite = null): array
    {
        $policies = static::getAllPolicies();
        $settings = [];

        foreach ($policies as $policyClass) {
            if (false === $policyClass::isActive($idSite)) {
                continue;
            }
            $controlledSettings = self::getAllControlledSettings($policyClass, $idSite);

            foreach ($controlledSettings as $controlledSetting) {
                // TODO: For Matomo 6, use `getSettingName` from `SettingValueInterface` and remove `self::getControlledSettingName` implementation
                if ($settingName === self::getControlledSettingName($controlledSetting)) {
                    $settings[$policyClass::getName()] = [
                        'requiredValue' => $controlledSetting::getPolicyRequirements()[$policyClass],
                    ];
                }
            }
        }

        return $settings;
    }
}
