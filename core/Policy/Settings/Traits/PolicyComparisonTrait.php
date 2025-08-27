<?php

namespace Piwik\Policy\Settings\Traits;

use Piwik\Policy\Settings\PolicyComparisonInterface;

/**
 * @phpstan-require-implements PolicyComparisonInterface
 */
trait PolicyComparisonTrait
{
    /**
     * @param array<string, mixed> $policies
     */
    protected static function getStrictestValueFromArray(array $policies)
    {
        return array_reduce($policies, [__CLASS__, 'compareStrictness']);
    }

    public static function isControlledBySpecificPolicy(string $policy, ?int $idSite = null): bool
    {
        return array_key_exists($policy, self::getPolicyValues($idSite));
    }

    abstract protected static function compareStrictness($value1, $value2);
}
