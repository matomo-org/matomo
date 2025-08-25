<?php

namespace Piwik\Policy;

use Piwik\Policy\Compliance\PolicyEngine;
use Piwik\Policy\UnifiedSettingsAccess\UnifiedSettingsAccess;

/**
 * This is the top level class for querying a setting value
 */
class SettingsManager
{
    public function getSetting(string $setting, string $type, mixed $defaultValue = null, ?int $idSite = null, ?array $hierachy = null)
    {
        $policies = PolicyEngine::getRegisteredPolicies();
        if (PolicyEngine::isSettingGovernedByActivePolicy($policies, $setting, $idSite)) {
            return PolicyEngine::getSettingFromPolicies($policies, $setting, $idSite);
        }
        return UnifiedSettingsAccess::getSetting($setting, $type, $defaultValue, $idSite, $hierachy);
    }
}
