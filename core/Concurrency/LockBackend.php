<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Concurrency;

interface LockBackend
{
    /**
     * TODO
     *
     * @param $pattern
     * @return mixed
     */
    public function getKeysMatchingPattern($pattern);

    /**
     * TODO
     *
     * @param $lockKey
     * @param $lockValue
     * @param $ttlInSeconds
     * @return mixed
     */
    public function setIfNotExists($lockKey, $lockValue, $ttlInSeconds);

    /**
     * TODO
     *
     * @param $lockKey
     * @return mixed
     */
    public function get($lockKey);

    /**
     * TODO
     *
     * @param $lockKey
     * @param $lockValue
     * @return mixed
     */
    public function deleteIfKeyHasValue($lockKey, $lockValue);

    /**
     * TODO
     *
     * @param $lockKey
     * @param $lockValue
     * @param $ttlInSeconds
     * @return mixed
     */
    public function expireIfKeyHasValue($lockKey, $lockValue, $ttlInSeconds);
}