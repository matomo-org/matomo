<?php

namespace Piwik\Policy\Compliance;

use Piwik\Policy\Compliance\Policies\CnilPolicy;
use Piwik\Policy\Compliance\Policies\HipaaPolicy;
use Piwik\Policy\SettingValues\SettingValue;

/**
 * The policy engine is responsible for processing policies and returning 
 * the desired value that would make a setting compliant with all provided
 * policies
 */
final class PolicyEngine
{
    /**
     * @return CompliancePolicy[]
     */
    public static function getRegisteredPolicies(): array
    {
        $repo = new PolicyStateRepository();
        $policies[] = new CnilPolicy($repo);
        $policies[] = new HipaaPolicy($repo);
        return $policies;
    }

    /**
     * @param CompliancePolicy[] $policies
     */
    public static function getSettingFromPolicies(array $policies, string $setting, ?int $idSite = null): ?SettingValue
    {
        $settingValues = [];
        foreach ($policies as $policy) {
            $settingValues[] = $policy->getSetting($setting, $idSite);
        }

        return self::getMostStrictSettingValue($settingValues);
    }

    /**
     * @param CompliancePolicy[] $policies
     */
    public static function isSettingGovernedByActivePolicy(array $policies, string $setting, ?int $idSite): bool
    {
        foreach ($policies as $policy) {
            if ($policy->isActiveFor($idSite) && $policy->hasSetting($setting)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param SettingValue[] $settings
     */
    private static function getMostStrictSettingValue(array $settings): ?SettingValue
    {
        $strictest = null;
        foreach ($settings as $setting) {
            if (!is_null($setting)) {
                $strictest = $setting->compare($strictest);
            }
        }

        return $strictest;
    }
}
