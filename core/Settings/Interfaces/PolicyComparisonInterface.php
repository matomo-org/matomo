<?php

namespace Piwik\Settings\Interfaces;

use Piwik\Policy\CompliancePolicy;

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
    public static function getPolicyRequiredValues(?int $idSite = null): array;

    public static function isCompliant(string $policy, ?int $idSite = null): bool;

    public static function isControlledBySpecificPolicy(string $policy, ?int $idSite = null): bool;

    public static function getComplianceRequirementNote(?int $idSite = null): string;
}
