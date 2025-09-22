<?php

namespace Piwik\Policy;

use Exception;
use Piwik\Plugin\Manager;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;

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
}
