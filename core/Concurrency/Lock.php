<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Concurrency;

use Piwik\Common;
use Piwik\Date;

class Lock
{
    public const MAX_KEY_LEN = 70;
    public const DEFAULT_TTL = 60;

    /**
     * @var LockBackend
     */
    private $backend;

    private $namespace;

    private $lockKey   = null;
    private $lockValue = null;
    private $defaultTtl = null;
    private $lastAcquireTime = null;

    /**
     * @param LockBackend $backend
     * @param string $namespace
     * @param int|null $defaultTtl defaults to {@link self::DEFAULT_TTL}
     */
    public function __construct(LockBackend $backend, $namespace, $defaultTtl = null)
    {
        if (mb_strlen($namespace) > self::MAX_KEY_LEN - 32) {
            // A longer namespace could be cut off for too long ids, so we don't support it
            throw new \InvalidArgumentException('Lock namespace must be shorter than ' . (self::MAX_KEY_LEN - 32) . ' chars');
        }

        $this->backend = $backend;
        $this->namespace = $namespace;
        $this->lockKey = $this->namespace;
        $this->defaultTtl = $defaultTtl ?: self::DEFAULT_TTL;
    }

    /**
     * For BC only
     *
     * @todo remove in Matomo 6.0
     * @deprecated use {@link reacquireLock()} instead.
     * @return bool
     */
    public function reexpireLock(): bool
    {
        return $this->reacquireLock();
    }

    /**
     * Reacquires the current lock. The TTL will be extended if 1/4 of the TTL already passed by.
     *
     * @return bool
     */
    public function reacquireLock(): bool
    {
        $timeBetweenReexpires = $this->defaultTtl - ($this->defaultTtl / 4);

        $now = Date::getNowTimestamp();
        if (
            !empty($this->lastAcquireTime) &&
            $now <= $this->lastAcquireTime + $timeBetweenReexpires
        ) {
            return false;
        }

        return $this->expireLock($this->defaultTtl);
    }

    public function getNumberOfAcquiredLocks(): int
    {
        return count($this->getAllAcquiredLockKeys());
    }

    /**
     * Returns all acquired lock keys for the iniatially set namespace.
     *
     * @return string[]
     */
    public function getAllAcquiredLockKeys(): array
    {
        return $this->backend->getKeysMatchingPattern($this->namespace . '*');
    }

    /**
     * Executes and returns the result of the provided callback if a lock with given id can be acquired
     * The method will automatically retry to acquire the lock up to 5 minutes.
     *
     * @param string $id
     * @param callable $callback
     * @return mixed
     * @throws \Exception if lock couldn't be acquired within 5 minutes
     */
    public function execute($id, $callback)
    {
        $i = 0;
        while (!$this->acquireLock($id)) {
            $i++;
            usleep(100 * 1000); // 100ms
            if ($i > 50) { // give up after 5seconds (50 * 100ms)
                throw new \Exception('Could not get the lock for ID: ' . $id);
            }
        };
        try {
            return $callback();
        } finally {
            $this->unlock();
        }
    }

    /**
     * Acquires a lock with the given id using the provided TTL
     *
     * @param string $id
     * @param int $ttlInSeconds
     * @return bool
     */
    public function acquireLock($id, $ttlInSeconds = 60)
    {
        $this->lockKey = $this->namespace . $id;

        if (mb_strlen($this->lockKey) > self::MAX_KEY_LEN) {
            // Lock key might be too long for DB column, so we hash it but leave the start of the original as well
            // to make it more readable and to ensure the namespaceis still containe
            $md5Len = 32;
            $this->lockKey = mb_substr($this->lockKey, 0, self::MAX_KEY_LEN - $md5Len - 1) . md5($id);
        }

        $lockValue = substr(Common::generateUniqId(), 0, 12);
        $locked    = $this->backend->setIfNotExists($this->lockKey, $lockValue, $ttlInSeconds);
        if ($locked) {
            $this->lockValue = $lockValue;
            $this->lastAcquireTime = Date::getNowTimestamp();
        }

        return !!$locked;
    }

    /**
     * Return if the acquired lock is currently locked
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        if (!$this->lockValue) {
            return false;
        }

        return $this->lockValue === $this->backend->get($this->lockKey);
    }

    /**
     * Releases the acquired lock
     *
     * @return void
     */
    public function unlock(): void
    {
        if ($this->lockValue) {
            $this->backend->deleteIfKeyHasValue($this->lockKey, $this->lockValue);
            $this->lockValue = null;
        }
    }

    /**
     * For BC only
     *
     * @deprecated use {@link extendLock()} instead.
     * @todo remove in Matomo 6.0
     * @return bool
     */
    public function expireLock($ttlInSeconds): bool
    {
        return $this->extendLock($ttlInSeconds);
    }

    public function extendLock($ttlInSeconds): bool
    {
        if ($ttlInSeconds > 0) {
            if ($this->lockValue) {
                $success = $this->backend->expireIfKeyHasValue($this->lockKey, $this->lockValue, $ttlInSeconds);
                if (!$success) {
                    $value = $this->backend->get($this->lockKey);
                    $message = sprintf('Failed to expire key %s (%s / %s).', $this->lockKey, $this->lockValue, (string)$value);

                    if ($value === false) {
                        Common::printDebug($message . ' It seems like the key already expired as it no longer exists.');
                    } elseif (!empty($value) && $value == $this->lockValue) {
                        Common::printDebug($message . ' We still have the lock but for some reason it did not expire.');
                    } elseif (!empty($value)) {
                        Common::printDebug($message . ' This lock has been acquired by another process/server.');
                    } else {
                        Common::printDebug($message . ' Failed to expire key.');
                    }

                    return false;
                }

                $this->lastAcquireTime = Date::getNowTimestamp();

                return true;
            } else {
                Common::printDebug('Lock is not acquired, cannot update expiration.');
            }
        } else {
            Common::printDebug('Provided TTL ' . $ttlInSeconds . ' is in valid in Lock::expireLock().');
        }

        return false;
    }
}
