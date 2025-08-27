<?php

namespace Piwik\Policy\Settings;

use Piwik\Policy\Policies\CompliancePolicy;

interface PolicyComparisonInterface
{
    /**
     * @return array<class-string<CompliancePolicy>, mixed>
     */
    public static function getPolicyRequirements(): array;

    /**
     * @return array<class-string<CompliancePolicy>, mixed>
     */
    public static function getPolicyValues(?int $idSite = null): array;

    public static function isCompliant(string $policy, ?int $idSite = null): bool;

    public static function isControlledBySpecificPolicy(string $policy, ?int $idSite = null): bool;
}
