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
 * Implemented by settings that a compliance policy can control.
 *
 * A setting subscribes to the policies it forms a requirement of via
 * {@link getPolicyRequirements()}, and separately owns the state that records whether
 * it is currently being enforced. That state is not tied to any single policy: a
 * setting has one enforcement state per scope, not one per policy it subscribes to.
 *
 * Implement this interface via {@link \Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait},
 * which provides default implementations for the enforcement state and dashboard
 * metadata methods. Methods may be added to this interface in major releases; the
 * trait always ships matching defaults, so trait users are unaffected.
 *
 * @template T of mixed
 */
interface PolicyComparisonInterface
{
    /**
     * The policies this setting is a requirement of, and the value each of them requires.
     *
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
     * Short description of what enforcing this setting does. Implementations may include
     * currently configured values, resolved for the given website.
     *
     * @param int|null $idSite The website to describe, or null for the instance wide state.
     * @since Matomo 6.0.0 — {@link PolicyComparisonTrait} provides a default.
     */
    public static function getWhatItDoes(?int $idSite = null): string;

    /**
     * Short description of the impact enforcing this setting has on reports and tracking.
     * Implementations may include currently configured values, resolved for the given website.
     *
     * @param int|null $idSite The website to describe, or null for the instance wide state.
     * @since Matomo 6.0.0 — {@link PolicyComparisonTrait} provides a default.
     */
    public static function getImpact(?int $idSite = null): string;

    /**
     * Whether this setting is currently being enforced for the given scope, so that the
     * values required by the policies it subscribes to override the underlying Matomo
     * setting when its value is resolved.
     *
     * @param int|null $idSite The website to check, or null for the instance wide state.
     * @since Matomo 6.0.0 — {@link PolicyComparisonTrait} provides a default.
     */
    public static function isEnforced(?int $idSite = null): bool;

    /**
     * Changes whether this setting is being enforced for the given scope.
     *
     * @param bool|null $isEnforced Null makes the setting follow the enforcement state of its policies again.
     * @param int|null $idSite The website to update, or null for the instance wide state.
     * @since Matomo 6.0.0 — {@link PolicyComparisonTrait} provides a default.
     */
    public static function setEnforced(?bool $isEnforced, ?int $idSite = null): void;

    /**
     * The enforcement state explicitly stored for the given scope, or null when none was
     * stored and the setting therefore follows the policies it subscribes to.
     *
     * @param int|null $idSite The website to read, or null for the instance wide state.
     * @since Matomo 6.0.0 — {@link PolicyComparisonTrait} provides a default.
     */
    public static function getStoredEnforcementState(?int $idSite = null): ?bool;

    /**
     * Whether the enforcement state of this setting can be changed for the given scope.
     *
     * @param int|null $idSite The website to check, or null for the instance wide state.
     * @since Matomo 6.0.0 — {@link PolicyComparisonTrait} provides a default.
     */
    public static function isEnforcementWritable(?int $idSite = null): bool;
}
