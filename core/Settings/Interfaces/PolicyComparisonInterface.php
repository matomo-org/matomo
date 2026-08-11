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
 * Implement this interface via {@link \Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait},
 * which provides default implementations for all dashboard-metadata methods.
 * Methods may be added to this interface in major releases; the trait always
 * ships matching defaults, so trait users are unaffected.
 *
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
    public static function getPolicyValuesAgainstProvided($settingValue, ?int $idSite = null);

    /**
     * @return array<class-string<CompliancePolicy>, T|null>
     */
    public static function getPolicyRequiredValues(?int $idSite = null): array;

    public static function isCompliant(string $policy, ?int $idSite = null): bool;

    public static function isControlledBySpecificPolicy(string $policy, ?int $idSite = null): bool;

    public static function getComplianceRequirementNote(?int $idSite = null): string;

    /**
     * Stable identifier of this policy-controlled setting, e.g. used to store
     * and address its per-policy enforcement state.
     *
     * @since Matomo 6.0.0 — {@link PolicyComparisonTrait} provides a default.
     */
    public static function getPolicySettingId(): string;

    /**
     * Whether this setting cannot be enforced from the compliance dashboard and
     * is instead managed outside of it (e.g. via a config file).
     *
     * @since Matomo 6.0.0 — {@link PolicyComparisonTrait} provides a default.
     */
    public static function isExternallyManagedByPolicyPage(): bool;

    /**
     * Short description of what enforcing this setting does.
     *
     * @since Matomo 6.0.0 — {@link PolicyComparisonTrait} provides a default.
     */
    public static function getWhatItDoes(): string;

    /**
     * Short description of the impact enforcing this setting has on reports and tracking.
     *
     * @since Matomo 6.0.0 — {@link PolicyComparisonTrait} provides a default.
     */
    public static function getImpact(): string;
}
