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
    /** The required value is the only compliant one. */
    public const POLICY_CONSTRAINT_EXACT = 'exact';

    /** The required value is a lower bound; values above it are compliant too. */
    public const POLICY_CONSTRAINT_MIN = 'min';

    /** The required value is an upper bound; values below it are compliant too. */
    public const POLICY_CONSTRAINT_MAX = 'max';

    /** Enforced for every site through a value set in the config file. */
    public const ENFORCEMENT_SCOPE_CONFIG = 'config';

    /** Enforced for every site, either instance wide or by an instance wide policy. */
    public const ENFORCEMENT_SCOPE_INSTANCE = 'instance';

    /** Enforced for one site only. */
    public const ENFORCEMENT_SCOPE_SITE = 'site';

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

    /**
     * Where the enforcement of this setting originates for the given scope, so that a settings
     * screen can tell the user whether the value is decided for this website alone or for the
     * whole Matomo. Null when the setting is not enforced there at all.
     *
     * Mirrors the resolution order of {@link isEnforced()}.
     *
     * @param int|null $idSite The website to check, or null for the instance wide state.
     * @return self::ENFORCEMENT_SCOPE_*|null
     *
     * @since Matomo 6.0.0 — {@link PolicyComparisonTrait} provides a default.
     */
    public static function getEnforcementScope(?int $idSite = null): ?string;

    /**
     * How the requirement of the given policy constrains this setting.
     *
     * `POLICY_CONSTRAINT_EXACT` means the required value is the only compliant one, so the
     * regular settings field can be shown read-only. A `MIN`/`MAX` requirement is only a bound:
     * a stricter value stays a valid choice, so the field must remain editable and instead offer
     * the compliant values only.
     *
     * @param class-string<CompliancePolicy> $policy
     * @return self::POLICY_CONSTRAINT_*
     *
     * @since Matomo 6.0.0 — {@link PolicyComparisonTrait} provides a default.
     */
    public static function getPolicyConstraintType(string $policy): string;

    /**
     * Whether the given value satisfies the requirement of the given policy. Values of settings
     * the policy does not control are always compliant.
     *
     * @param T $value
     * @param class-string<CompliancePolicy> $policy
     *
     * @since Matomo 6.0.0 — {@link PolicyComparisonTrait} provides a default.
     */
    public static function isValueCompliantWithPolicy($value, string $policy): bool;
}
