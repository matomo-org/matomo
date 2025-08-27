<?php

namespace Piwik\Policy\Settings\Traits;

trait PolicyComparisonTrait
{
    /**
     * @return array<string, mixed>
     */
    abstract public static function getPolicyValues(?int $idSite): array;

    /**
     * @param array<string, mixed> $policies
     */
    protected static function getStrictestValueFromArray(array $policies)
    {
        return array_reduce($policies, [__CLASS__, 'compareStrictness']);
    }

    public static function isControlledBySpecificPolicy(string $policy): bool
    {
        return array_key_exists($policy, self::getPolicyValues(null));
    }

    abstract protected static function compareStrictness($value1, $value2);
}
