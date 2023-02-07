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
    const MAX_KEY_LEN = 70;
    const DEFAULT_TTL = 60;

    /**
     * @var LockBackend
     */
    private $backend;

    private $lockKeyStart;

    private $lockKey   = null;
    private $lockValue = null;
    private $defaultTtl = null;
    private $lastExpireTime = null;

    public function __construct(LockBackend $backend, $lockKeyStart, $defaultTtl = null)
    {
        $this->backend = $backend;
        $this->lockKeyStart = $lockKeyStart;
        $this->lockKey = $this->lockKeyStart;
        $this->defaultTtl = $defaultTtl ?: self::DEFAULT_TTL;
    }

    public function reexpireLock()
    {
        $timeBetweenReexpires = $this->defaultTtl - ($this->defaultTtl / 4);

        $now = Date::getNowTimestamp();
        if (!empty($this->lastExpireTime) &&
            $now <= $this->lastExpireTime + $timeBetweenReexpires
        ) {
            return false;
        }

        return $this->expireLock($this->defaultTtl);
    }

    public function getNumberOfAcquiredLocks()
    {
        return count($this->getAllAcquiredLockKeys());
    }

    public function getAllAcquiredLockKeys()
    {
        return $this->backend->getKeysMatchingPattern($this->lockKeyStart . '*');
    }

    public function execute($id, $callback)
    {
        $i = 0;
        while (!$this->acquireLock($id)) {
            $i++;
            usleep( 100 * 1000 ); // 100ms
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

    public function acquireLock($id, $ttlInSeconds = 60)
    {
        $this->lockKey = $this->lockKeyStart . $id;

        if (mb_strlen($this->lockKey) > self::MAX_KEY_LEN) {
            // Lock key might be too long for DB column, so we hash it but leave the start of the original as well
            // to make it more readable
            $md5Len = 32;
            $this->lockKey = mb_substr($id, 0, self::MAX_KEY_LEN - $md5Len - 1) . md5($id);
        }

        $lockValue = substr(Common::generateUniqId(), 0, 12);
        $locked    = $this->backend->setIfNotExists($this->lockKey, $lockValue, $ttlInSeconds);
        if ($locked) {
            $this->lockValue = $lockValue;
            $this->lastExpireTime = Date::getNowTimestamp();
        }

        return $locked;
    }

    public function isLocked()
    {
        if (!$this->lockValue) {
            return false;
        }

        return $this->lockValue === $this->backend->get($this->lockKey);
    }

    public function unlock()
    {
        if ($this->lockValue) {
            $this->backend->deleteIfKeyHasValue($this->lockKey, $this->lockValue);
            $this->lockValue = null;
        }
    }

    public function expireLock($ttlInSeconds)
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

                $this->lastExpireTime = Date::getNowTimestamp();

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
