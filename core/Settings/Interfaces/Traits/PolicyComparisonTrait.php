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
use Piwik\Policy\PolicyEnforcementBypass;

/**
 * @template T of mixed
 *
 * @phpstan-require-implements \Piwik\Settings\Interfaces\PolicyComparisonInterface<T>
 */
trait PolicyComparisonTrait
{
    /**
     * @return array<class-string<CompliancePolicy>, T|null>
     */
    public static function getPolicyRequiredValues(?int $idSite = null): array
    {
        $policyValues = static::getPolicyRequirements();

        /** @var class-string<CompliancePolicy> $policy */
        foreach (array_keys($policyValues) as $policy) {
            if (PolicyEnforcementBypass::isActive() || !$policy::isActive($idSite)) {
                $policyValues[$policy] = null;
            }
        }

        return $policyValues;
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
        return array_key_exists($policy, self::getPolicyRequiredValues($idSite));
    }

    public static function getPolicySettingId(): string
    {
        $shortClassName = substr(strrchr(static::class, '\\'), 1);
        return Piwik::getPluginNameOfMatomoClass(static::class) . '.' . $shortClassName;
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

    /**
     * @deprecated since Matomo 6.0.0 — use {@link getWhatItDoes()} instead.
     *
     * Delegates so that settings which already moved to getWhatItDoes() need not define both,
     * and so third-party callers of this method keep receiving the same text.
     */
    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return static::getWhatItDoes($idSite);
    }

    /**
     * Declared so that the getComplianceTitle() default below cannot fail at runtime. Every
     * setting reaching the compliance dashboard already has it via SettingValueInterface; naming
     * it here turns a missing implementation into a load-time error rather than a fatal on render.
     */
    abstract public static function getTitle(): string;

    /**
     * Name to show for this setting on the compliance dashboard.
     *
     * The dashboard phrases every row as the state the policy requires, which reads differently
     * from the setting's own name. Override this where getTitle() would read wrong there. It
     * defaults to getTitle() so that implementers using this trait keep working unchanged.
     */
    public static function getComplianceTitle(?int $idSite = null): string
    {
        return static::getTitle();
    }

    /**
     * @param T $value1
     * @param T $value2
     *
     * @return T
     */
    abstract protected static function compareStrictness($value1, $value2);
}
