<?php

declare(strict_types=1);

namespace Piwik\Policy\Compliance;

use Piwik\Policy\Policies\CnilPolicy;
use Piwik\Policy\Policies\HipaaPolicy;
use Piwik\Policy\SettingValue;

/**
 * The policy engine is responsible for processing policies and returning 
 * the desired value that would make a setting compliant with all provided
 * policies
 */
final class PolicyEngine
{
    public static function getRegisteredPolicies(): array
    {
        $policies[] = new CnilPolicy();
        $policies[] = new HipaaPolicy();
        return $policies;
    }

    public static function getSettingFromPolicies(array $policies, string $setting, ?int $idSite = null): ?SettingValue
    {
        /** @var SettingValue[] */
        $settingValues = [];
        foreach ($policies as $policy) {
            $settingValues[] = $policy->getSetting($setting, $idSite);
        }

        return self::getMostStrictSettingValue($settingValues);
    }

    public static function isSettingGovernedByActivePolicy(array $policies, string $setting): bool
    {
        foreach ($policies as $policy) {
            if ($policy->hasSetting($setting)) {
                return true;
            }
        }
        return false;
    }

    private static function getMostStrictSettingValue(array $settings): ?SettingValue
    {
        $strictest = null;
        foreach ($settings as $setting) {
            $strictest = $setting->compare($strictest);
        }

        return $strictest;
    }
}
