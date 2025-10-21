<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

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
     * @param T|null $settingValue
     * @return T|null
     */
    public static function getPolicyValuesAgainstProvided($settingValue, int $idSite = null);

    /**
     * @return array<class-string<CompliancePolicy>, T|null>
     */
    public static function getPolicyRequiredValues(?int $idSite = null): array;

    public static function isCompliant(string $policy, ?int $idSite = null): bool;

    public static function isControlledBySpecificPolicy(string $policy, ?int $idSite = null): bool;

    public static function getComplianceRequirementNote(?int $idSite = null): string;
}
