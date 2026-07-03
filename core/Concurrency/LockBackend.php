<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Concurrency;

interface LockBackend
{
    /**
     * Returns lock keys matching a pattern.
     *
     * @param string $pattern
     * @return string[]
     */
    public function getKeysMatchingPattern($pattern);

    /**
     * Set a key value if the key is not already set.
     *
     * @param string $lockKey
     * @param string $lockValue
     * @param int $ttlInSeconds
     * @return bool
     */
    public function setIfNotExists($lockKey, $lockValue, $ttlInSeconds);

    /**
     * Get the lock value for a key if any.
     *
     * @param string $lockKey
     * @return string|false
     */
    public function get($lockKey);

    /**
     * Delete the lock with key = $lockKey if the lock has the given value.
     *
     * @param string $lockKey
     * @param string $lockValue
     * @return bool
     */
    public function deleteIfKeyHasValue($lockKey, $lockValue);

    /**
     * Update expiration for a lock if the lock with the specified key has the given value.
     *
     * @param string $lockKey
     * @param string $lockValue
     * @param int $ttlInSeconds
     * @return bool
     */
    public function expireIfKeyHasValue($lockKey, $lockValue, $ttlInSeconds);
}
