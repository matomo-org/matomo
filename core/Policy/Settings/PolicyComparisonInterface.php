<?php

namespace Piwik\Policy\Settings;

use Piwik\Policy\Policies\CompliancePolicy;

/**
 * @template T of mixed
 */
interface PolicyComparisonInterface
{
    /**
     * @return array<class-string<CompliancePolicy>, T>
     */
    public static function getPolicyRequirements(): array;

    /**
     * @return array<class-string<CompliancePolicy>, T|null>
     */
    public static function getPolicyValues(?int $idSite = null): array;

    public static function isCompliant(string $policy, ?int $idSite = null): bool;

    public static function isControlledBySpecificPolicy(string $policy, ?int $idSite = null): bool;
}
