<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Concurrency;

interface LockBackend
{
    /**
     * Returns lock keys matching a pattern.
     *
     * @param $pattern
     * @return string[]
     */
    public function getKeysMatchingPattern($pattern);

    /**
     * Set a key value if the key is not already set.
     *
     * @param $lockKey
     * @param $lockValue
     * @param $ttlInSeconds
     * @return mixed
     */
    public function setIfNotExists($lockKey, $lockValue, $ttlInSeconds);

    /**
     * Get the lock value for a key if any.
     *
     * @param $lockKey
     * @return mixed
     */
    public function get($lockKey);

    /**
     * Delete the lock with key = $lockKey if the lock has the given value.
     *
     * @param $lockKey
     * @param $lockValue
     * @return mixed
     */
    public function deleteIfKeyHasValue($lockKey, $lockValue);

    /**
     * Update expiration for a lock if the lock with the specified key has the given value.
     *
     * @param $lockKey
     * @param $lockValue
     * @param $ttlInSeconds
     * @return mixed
     */
    public function expireIfKeyHasValue($lockKey, $lockValue, $ttlInSeconds);
}