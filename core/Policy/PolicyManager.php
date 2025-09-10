<?php

namespace Piwik\Policy;

class PolicyManager
{
    /**
     * @return array<class-string<CompliancePolicy>>
     */
    public static function getAllPolicies(): array
    {
        return [
            CnilPolicy::class,
            HipaaPolicy::class
        ];
    }

    /**
     * @return array<array<string, string>>
     */
    public static function getAllPoliciesDecorated(): array
    {
        $policies = self::getAllPolicies();
        return array_map(function ($policyClass) {
            return $policyClass::getDetails();
        }, $policies);
    }

    /**
     * @return class-string<CompliancePolicy>
     */
    public static function getPolicyByName(string $policyName): ?string
    {
        $policies = self::getAllPolicies();
        foreach ($policies as $policyClass) {
            if ($policyName === $policyClass::getName()) {
                return $policyClass;
            }
        }

        return null;
    }
}
