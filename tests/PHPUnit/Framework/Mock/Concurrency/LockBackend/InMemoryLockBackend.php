<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock\Concurrency\LockBackend;

use Piwik\Concurrency\LockBackend;

/**
 * Lock Backend that stores locks in memory only
 */
class InMemoryLockBackend implements LockBackend
{
    public $locks = [];

    public function getKeysMatchingPattern($pattern)
    {
        $keys = [];

        // convert pattern to regex pattern
        $pattern = '/^' . str_replace(['*'], ['.*'], $pattern) . '$/i';

        foreach ($this->locks as $key => $lock) {
            if (preg_match($pattern, $key) && $lock['expiry'] > time()) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    public function setIfNotExists($key, $value, $ttlInSeconds)
    {
        if (empty($ttlInSeconds)) {
            $ttlInSeconds = 999999999;
        }

        if ($this->get($key)) {
            return false; // a value is set, won't be possible to insert
        }

        $this->locks[$key] = [
            'value' => $value,
            'expiry' => time() + $ttlInSeconds,
        ];

        // we make sure we got the lock
        return $this->get($key) === $value;
    }

    public function get($key)
    {
        return $this->locks[$key]['value'] ?? '';
    }

    public function deleteIfKeyHasValue($key, $value)
    {
        if (empty($this->locks[$key]['value'])) {
            return false;
        }

        if ($this->locks[$key]['value'] === $value) {
            unset($this->locks[$key]);
            return true;
        }

        return false;
    }

    public function expireIfKeyHasValue($key, $value, $ttlInSeconds)
    {
        if (empty($value) || $this->locks[$key]['value'] !== $value) {
            return false;
        }

        $this->locks[$key]['expiry'] = time() + $ttlInSeconds;

        return true;
    }

    public function keyExists($key)
    {
        return !empty($this->locks[$key]);
    }
}
