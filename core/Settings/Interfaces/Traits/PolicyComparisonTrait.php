<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings\Interfaces\Traits;

use Piwik\Piwik;
use Piwik\Plugins\SitesManager\Model as SitesManagerModel;
use Piwik\Policy\CompliancePolicy;
use Piwik\Policy\PolicyEnforcementBypass;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\PolicyEnforcementState;

/**
 * @template T of mixed
 *
 * @phpstan-require-implements \Piwik\Settings\Interfaces\PolicyComparisonInterface<T>
 */
trait PolicyComparisonTrait
{
    /**
     * The values required of this setting that currently apply.
     *
     * The requirements only apply while the setting is being enforced. When it is not,
     * every requirement is nulled out and the underlying Matomo setting decides the value
     * on its own. The policy keys are always present, so callers can still tell which
     * policies this setting is a requirement of.
     *
     * @return array<class-string<CompliancePolicy>, T|null>
     */
    public static function getPolicyRequiredValues(?int $idSite = null): array
    {
        $policyValues = static::getPolicyRequirements();

        if (PolicyEnforcementBypass::isActive() || !static::isEnforced($idSite)) {
            return array_fill_keys(array_keys($policyValues), null);
        }

        return $policyValues;
    }

    public static function isEnforced(?int $idSite = null): bool
    {
        $configEnforcement = static::getConfigControlledEnforcement();

        if (!is_null($configEnforcement)) {
            // a policy pinned in config.ini.php cannot be overridden from the dashboard
            return $configEnforcement;
        }

        if (static::isExternallyManagedByPolicyPage()) {
            // no enforcement state is stored for these, they follow their policies directly
            return static::isEnforcedByPolicies($idSite);
        }

        $instanceState = static::getStoredEnforcementState(null);
        $siteState = is_null($idSite) ? null : static::getStoredEnforcementState($idSite);

        // enforcement is additive across scopes: the setting is enforced as soon as any
        // scope enforces it, so an instance wide state cannot un-enforce a site that
        // enforces itself, and a site cannot un-enforce itself while the instance does
        if (true === $instanceState || true === $siteState) {
            return true;
        }

        // an explicit "not enforced" is a deliberate answer and suppresses the policies,
        // which is what allows a single site to opt out of an otherwise active policy
        if (false === $instanceState || false === $siteState) {
            return false;
        }

        return static::isEnforcedByPolicies($idSite);
    }

    public static function getEnforcementScope(?int $idSite = null): ?string
    {
        if (!static::isEnforced($idSite)) {
            return null;
        }

        if (!is_null(static::getConfigControlledEnforcement())) {
            return PolicyComparisonInterface::ENFORCEMENT_SCOPE_CONFIG;
        }

        if (!static::isExternallyManagedByPolicyPage()) {
            // follows the same order as isEnforced(): an instance wide state covers every site,
            // so it is the origin even when the site enforces itself as well
            if (true === static::getStoredEnforcementState(null)) {
                return PolicyComparisonInterface::ENFORCEMENT_SCOPE_INSTANCE;
            }

            if (!is_null($idSite) && true === static::getStoredEnforcementState($idSite)) {
                return PolicyComparisonInterface::ENFORCEMENT_SCOPE_SITE;
            }
        }

        // no state of its own, so it is enforced through one of its policies
        return static::getEnforcingPolicyScope($idSite);
    }

    /**
     * Whether the first policy enforcing this setting does so for the whole instance or for the
     * given site alone, mirroring the precedence of {@link CompliancePolicy::isActive()}.
     *
     * @return PolicyComparisonInterface::ENFORCEMENT_SCOPE_*|null
     */
    protected static function getEnforcingPolicyScope(?int $idSite = null): ?string
    {
        /** @var class-string<CompliancePolicy> $policy */
        foreach (array_keys(static::getPolicyRequirements()) as $policy) {
            if (!$policy::isActive($idSite)) {
                continue;
            }

            return $policy::getSystemValue()
                ? PolicyComparisonInterface::ENFORCEMENT_SCOPE_INSTANCE
                : PolicyComparisonInterface::ENFORCEMENT_SCOPE_SITE;
        }

        return null;
    }

    public static function setEnforced(?bool $isEnforced, ?int $idSite = null): void
    {
        if (static::isExternallyManagedByPolicyPage()) {
            throw new \Exception(sprintf(
                'The enforcement state of "%s" is managed outside of the compliance page and cannot be changed',
                static::getPolicySettingId()
            ));
        }

        if (!is_null($idSite) && false === $isEnforced && true === static::getStoredEnforcementState(null)) {
            // an instance wide state enforces every site, so it has to give way before a
            // single site can opt out of it
            static::demoteInstanceWideEnforcement();
        }

        static::writeEnforcementState($idSite, $isEnforced);

        /**
         * Triggered when the enforcement state of a policy-controlled setting changes, to
         * allow performing extra actions when a setting starts or stops being enforced.
         *
         * The enforcement state cannot be changed via this event.
         *
         * @param bool|null $isEnforced Whether the setting is being enforced, or null when it
         *                              was reset to follow the policies it subscribes to
         * @param int|null $idSite The website the state was changed for, or null for the instance
         * @param class-string<\Piwik\Settings\Interfaces\PolicyComparisonInterface<mixed>> The setting in question
         */
        Piwik::postEvent('CompliancePolicy.setSettingEnforcedStatus', [$isEnforced, $idSite, static::class]);
    }

    public static function getStoredEnforcementState(?int $idSite = null): ?bool
    {
        return static::getPolicyEnforcementState()->get($idSite);
    }

    public static function isEnforcementWritable(?int $idSite = null): bool
    {
        if (static::isExternallyManagedByPolicyPage()) {
            return false;
        }

        if (!is_null(static::getConfigControlledEnforcement())) {
            return false;
        }

        $state = static::getPolicyEnforcementState();

        // changing a site while the instance wide state enforces it demotes that state,
        // which rewrites every site as well, so it needs instance wide write access even
        // though the caller only named one site
        if (!is_null($idSite) && true === static::getStoredEnforcementState(null)) {
            return $state->isWritableByCurrentUser(null) && $state->isWritableByCurrentUser($idSite);
        }

        return $state->isWritableByCurrentUser($idSite);
    }

    /**
     * Turns the instance wide enforcement state off without changing whether any site is
     * enforced: every site is first given the state it currently resolves to, so that the
     * sites which were only enforced through the instance wide state keep their
     * enforcement once it is gone.
     *
     * The sites are written before the instance wide state, so an interrupted demotion
     * leaves everything enforced rather than silently un-enforcing the sites it did not
     * reach yet.
     *
     * @throws \Exception when the current user may not change the instance wide state
     */
    protected static function demoteInstanceWideEnforcement(): void
    {
        $state = static::getPolicyEnforcementState();

        // rewriting every site is an instance wide change whichever site triggered it,
        // so refuse it upfront rather than part way through
        $state->assertWritableByCurrentUser(null);

        // resolve every site before writing anything, so each one is given back exactly
        // what it resolves to today rather than a copy of the instance wide state. Note
        // this does overwrite a site's own stored "not enforced" with the enforcement it
        // currently inherits, which is the state that was actually in effect for it.
        $resolvedStates = [];

        foreach (static::getAllIdSites() as $id) {
            $idSite = (int) $id;
            $resolvedStates[$idSite] = static::isEnforced($idSite);
        }

        foreach ($resolvedStates as $id => $isEnforced) {
            static::writeEnforcementState($id, $isEnforced);
        }

        // the instance can no longer claim to be enforced as a whole, and recording that
        // explicitly also keeps sites created later out of the demoted enforcement
        static::writeEnforcementState(null, false);
    }

    /**
     * The websites the instance wide enforcement state has to be materialised onto when
     * it is demoted.
     *
     * @return array<int, int|string>
     */
    protected static function getAllIdSites(): array
    {
        return (new SitesManagerModel())->getSitesId();
    }

    /**
     * Whether any of the policies this setting subscribes to is currently active. This is
     * the state a setting follows until its own enforcement state is set, which is what
     * keeps installs that enforced a whole policy enforced without having to convert
     * anything.
     */
    protected static function isEnforcedByPolicies(?int $idSite = null): bool
    {
        /** @var class-string<CompliancePolicy> $policy */
        foreach (array_keys(static::getPolicyRequirements()) as $policy) {
            if ($policy::isActive($idSite)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The enforcement state dictated by a policy that is pinned in `config.ini.php`, or
     * null when none of the policies this setting subscribes to is config controlled.
     */
    protected static function getConfigControlledEnforcement(): ?bool
    {
        $isEnforced = null;

        /** @var class-string<CompliancePolicy> $policy */
        foreach (array_keys(static::getPolicyRequirements()) as $policy) {
            if (!$policy::isConfigControlled()) {
                continue;
            }

            $isEnforced = ($isEnforced ?? false) || (bool) $policy::getConfigValue();
        }

        return $isEnforced;
    }

    protected static function writeEnforcementState(?int $idSite, ?bool $isEnforced): void
    {
        static::getPolicyEnforcementState()->set($idSite, $isEnforced);
    }

    protected static function getPolicyEnforcementState(): PolicyEnforcementState
    {
        return new PolicyEnforcementState(
            static::getPolicySettingPluginName(),
            static::getPolicySettingShortName() . PolicyEnforcementState::SETTING_NAME_SUFFIX
        );
    }

    /**
     * @param T|null $settingValue
     * @return T|null
     */
    public static function getPolicyValuesAgainstProvided($settingValue, ?int $idSite = null)
    {
        $values = static::getPolicyRequiredValues($idSite);
        $values[] = $settingValue;
        return static::getStrictestValueFromArray($values);
    }

    /**
     * @param array<string, T|null> $policies
     *
     * @return T|null
     */
    protected static function getStrictestValueFromArray(array $policies)
    {
        /** @var callable-string */
        $callback = [__CLASS__, 'compareValuesHandleNull'];

        return array_reduce($policies, $callback);
    }

    /**
     * @param T|null $value1
     * @param T|null $value2
     *
     * @return T|null
     */
    protected static function compareValuesHandleNull($value1, $value2)
    {
        if (is_null($value1)) {
            return $value2;
        }
        if (is_null($value2)) {
            return $value1;
        }

        return static::compareStrictness($value1, $value2);
    }

    public static function isControlledBySpecificPolicy(string $policy, ?int $idSite = null): bool
    {
        // whether the setting subscribed to the policy is all that matters here, and is
        // deliberately independent of the enforcement state: this is called for every
        // discovered setting, so it must not read the enforcement state from storage
        return array_key_exists($policy, static::getPolicyRequirements());
    }

    public static function getPolicySettingId(): string
    {
        return static::getPolicySettingPluginName() . '.' . static::getPolicySettingShortName();
    }

    public static function isExternallyManagedByPolicyPage(): bool
    {
        return false;
    }

    public static function getWhatItDoes(?int $idSite = null): string
    {
        return '';
    }

    public static function getImpact(?int $idSite = null): string
    {
        return '';
    }

    public static function getPolicyConstraintType(string $policy): string
    {
        return PolicyComparisonInterface::POLICY_CONSTRAINT_EXACT;
    }

    public static function isValueCompliantWithPolicy($value, string $policy): bool
    {
        $requirements = static::getPolicyRequirements();

        if (!array_key_exists($policy, $requirements)) {
            return true;
        }

        // compliant means the requirement cannot make the value any stricter than it already is.
        // Compared loosely on purpose: values reach this from requests and from lists of
        // selectable options as strings, while requirements are declared as their native type.
        return static::compareStrictness($value, $requirements[$policy]) == $value;
    }

    /**
     * The plugin the enforcement state of this setting is stored under, which is the plugin
     * owning the setting itself.
     */
    protected static function getPolicySettingPluginName(): string
    {
        return Piwik::getPluginNameOfMatomoClass(static::class);
    }

    protected static function getPolicySettingShortName(): string
    {
        $parts = explode('\\', static::class);

        return (string) end($parts);
    }

    /**
     * @param T $value1
     * @param T $value2
     *
     * @return T
     */
    abstract protected static function compareStrictness($value1, $value2);
}
