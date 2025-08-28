<?php

namespace Piwik\Settings\Interfaces\Traits;

use Piwik\Policy\CompliancePolicy;

/**
 * @template T of mixed
 *
 * @phpstan-require-implements \Piwik\Policy\Settings\PolicyComparisonInterface<T>
 */
trait PolicyComparisonTrait
{
    /**
     * @return array<class-string<CompliancePolicy>, T|null>
     */
    public static function getPolicyValues(?int $idSite = null): array
    {
        $policyValues = self::getPolicyRequirements();

        foreach (array_keys($policyValues) as $policy) {
            if (!$policy::isActive($idSite)) {
                $policyValues[$policy] = null;
            }
        }

        return $policyValues;
    }

    /**
     * @param array<string, T> $policies
     *
     * @return T
     */
    protected static function getStrictestValueFromArray(array $policies)
    {
        /** @var callable-string */
        $callback = [__CLASS__, 'compareStrictness'];

        return array_reduce($policies, $callback);
    }

    public static function isControlledBySpecificPolicy(string $policy, ?int $idSite = null): bool
    {
        return array_key_exists($policy, self::getPolicyValues($idSite));
    }

    /**
     * @param T $value1
     * @param T $value2
     *
     * @return T
     */
    abstract protected static function compareStrictness($value1, $value2);
}
