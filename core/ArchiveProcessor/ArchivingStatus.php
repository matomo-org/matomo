<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\ArchiveProcessor;

use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Container\StaticContainer;

class ArchivingStatus
{
    const LOCK_KEY_PREFIX = 'Archiving';
    const DEFAULT_ARCHIVING_TTL = 7200; // 2 hours

    /**
     * @var LockBackend
     */
    private $lockBackend;

    /**
     * @var int
     */
    private $archivingTTLSecs;

    /**
     * @var Lock[]
     */
    private $lockStack = [];

    public function __construct(LockBackend $lockBackend, $archivingTTLSecs = self::DEFAULT_ARCHIVING_TTL)
    {
        $this->lockBackend = $lockBackend;
        $this->archivingTTLSecs = $archivingTTLSecs;
    }

    public function archiveStarted(Parameters $params)
    {
        $lock = $this->makeArchivingLock($params);
        $locked = $lock->acquireLock('', $this->archivingTTLSecs);
        if ($locked) {
            array_push($this->lockStack, $lock);
        }
        return $locked;
    }

    /**
     * Try to acquire the lock that is acquired before starting archiving. If it is acquired, it
     * means archiving is not ongoing. If it is not acquired, then archiving is ongoing.
     *
     * @param Parameters $params
     * @param $doneFlag
     * @return Lock
     */
    public function acquireArchiveInProgressLock($idSite, $date1, $date2, $period, $doneFlag)
    {
        $lock = $this->makeArchivingLockFromDoneFlag($idSite, $date1, $date2, $period, $doneFlag);
        $lock->acquireLock('', $ttl = 1);
        return $lock;
    }

    public function archiveFinished()
    {
        $lock = array_pop($this->lockStack);
        $lock->unlock();
    }

    public function getCurrentArchivingLock()
    {
        if (empty($this->lockStack)) {
            return null;
        }
        return end($this->lockStack);
    }

    public function getSitesCurrentlyArchiving()
    {
        $lockMeta = new Lock($this->lockBackend, self::LOCK_KEY_PREFIX . '.');
        $acquiredLocks = $lockMeta->getAllAcquiredLockKeys();

        $sitesCurrentlyArchiving = [];
        foreach ($acquiredLocks as $lockKey) {
            $parts = explode('.', $lockKey);
            if (!isset($parts[1])) {
                continue;
            }
            $sitesCurrentlyArchiving[] = (int) $parts[1];
        }
        $sitesCurrentlyArchiving = array_unique($sitesCurrentlyArchiving);
        $sitesCurrentlyArchiving = array_values($sitesCurrentlyArchiving);

        return $sitesCurrentlyArchiving;
    }

    /**
     * @return Lock
     */
    private function makeArchivingLock(Parameters $params)
    {
        $doneFlag = Rules::getDoneStringFlagFor([$params->getSite()->getId()], $params->getSegment(),
            $params->getPeriod()->getLabel(), $params->getRequestedPlugin());
        return $this->makeArchivingLockFromDoneFlag($params->getSite()->getId(), $params->getSite()->getId(), $params->getPeriod()->getDateStart()->toString(),
            $params->getPeriod()->getDateEnd()->toString(), $doneFlag);
    }

    private function makeArchivingLockFromDoneFlag($idSite, $date1, $date2, $period, $doneFlag)
    {
        $lockKeyParts = [
            self::LOCK_KEY_PREFIX,
            $idSite,

            // md5 to keep it within the 70 char limit in the table
            md5($period . $date1 . ',' . $date2 . $doneFlag),
        ];

        $lockKeyPrefix = implode('.', $lockKeyParts);
        return new Lock(StaticContainer::get(LockBackend::class), $lockKeyPrefix, $this->archivingTTLSecs);
    }
}