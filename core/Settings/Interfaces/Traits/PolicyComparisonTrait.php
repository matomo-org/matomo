<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings\Interfaces\Traits;

use Piwik\Policy\CompliancePolicy;

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
            if (!$policy::isActive($idSite)) {
                $policyValues[$policy] = null;
            }
        }

        return $policyValues;
    }

    /**
     * @param T|null $settingValue
     * @return T|null
     */
    public static function getPolicyValuesAgainstProvided($settingValue, int $idSite = null)
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
        return array_key_exists($policy, self::getPolicyRequiredValues($idSite));
    }

    /**
     * @param T $value1
     * @param T $value2
     *
     * @return T
     */
    abstract protected static function compareStrictness($value1, $value2);
}
