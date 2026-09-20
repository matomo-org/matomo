<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy;

/**
 * Allows evaluating a policy-controlled setting as if no compliance policy was
 * enforcing it, e.g. to determine whether the underlying Matomo setting would
 * be compliant on its own.
 *
 * @internal
 */
class PolicyEnforcementBypass
{
    private static bool $active = false;

    public static function isActive(): bool
    {
        return self::$active;
    }

    /**
     * Runs the given callable with policy enforcement bypassed.
     *
     * @template TReturn
     * @param callable(): TReturn $fn
     * @return TReturn
     */
    public static function run(callable $fn)
    {
        $previous = self::$active;
        self::$active = true;
        try {
            return $fn();
        } finally {
            self::$active = $previous;
        }
    }
}
