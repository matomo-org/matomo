<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings\Interfaces\Traits;

use Piwik\Piwik;
use Piwik\Policy\CompliancePolicy;
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

        if (!static::isEnforced($idSite)) {
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

        // mirrors CompliancePolicy::isActive(): an instance wide state applies to every site
        if (true === $instanceState) {
            return true;
        }

        if (!is_null($idSite)) {
            $siteState = static::getStoredEnforcementState($idSite);

            if (!is_null($siteState)) {
                return $siteState;
            }
        }

        if (!is_null($instanceState)) {
            return false;
        }

        return static::isEnforcedByPolicies($idSite);
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
            // mirrors CompliancePolicy::setActiveStatus(): an instance wide state applies to
            // every site, so it has to give way before a single site can opt out of it
            static::writeEnforcementState(null, static::reduceToDeviation(false, null));
        }

        static::writeEnforcementState($idSite, static::reduceToDeviation($isEnforced, $idSite));

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

        return static::getPolicyEnforcementState()->isWritableByCurrentUser($idSite);
    }

    /**
     * Only states that deviate from what would otherwise be inherited are worth storing.
     * Resetting a setting back to what its policies already say removes the stored state,
     * so a stored row always represents a deliberate deviation and never merely pins the
     * value a policy happened to have at the time.
     */
    protected static function reduceToDeviation(?bool $isEnforced, ?int $idSite): ?bool
    {
        if (is_null($isEnforced)) {
            return null;
        }

        return $isEnforced === static::getInheritedEnforcement($idSite) ? null : $isEnforced;
    }

    /**
     * The enforcement state this setting would have for the given scope if no state was
     * stored for that scope.
     */
    protected static function getInheritedEnforcement(?int $idSite): bool
    {
        if (!is_null($idSite)) {
            $instanceState = static::getStoredEnforcementState(null);

            if (!is_null($instanceState)) {
                return $instanceState;
            }
        }

        return static::isEnforcedByPolicies($idSite);
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

    public static function getPolicySettingId(): string
    {
        return static::getPolicySettingPluginName() . '.' . static::getPolicySettingShortName();
    }

    public static function isExternallyManagedByPolicyPage(): bool
    {
        return false;
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

    /**
     * @param T $value1
     * @param T $value2
     *
     * @return T
     */
    abstract protected static function compareStrictness($value1, $value2);
}
