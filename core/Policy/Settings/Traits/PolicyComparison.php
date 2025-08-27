<?php

namespace Piwik\Policy\Settings\Traits;

trait PolicyComparison
{
    /**
     * @return array<string, mixed>
     */
    public abstract static function getPolicyValues(?int $idSite): array;

    /**
     * @param array<string,mixed> $policies
     */
    protected static function getStrictestValueFromArray(array $policies): mixed
    {
        return array_reduce($policies, [__CLASS__, 'compareStrictness']);
    }

    public static function isControlledBySpecificPolicy(string $policy): bool
    {
        return array_key_exists($policy, self::getPolicyValues(null));
    }

    protected abstract static function compareStrictness($value1, $value2);
}
