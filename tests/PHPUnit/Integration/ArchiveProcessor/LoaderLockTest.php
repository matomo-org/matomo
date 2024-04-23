<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\ArchiveProcessor;

use Piwik\ArchiveProcessor\LoaderLock;
use Piwik\Common;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ArchiveProcessor
 * @group ArchiveProcessorLoaderLock
 */
class LoaderLockTest extends IntegrationTestCase
{
    public function testLockerIdShort()
    {
        $lockId = Common::getRandomString(60);

        $lock = new LoaderLock($lockId);
        $this->assertSame($lockId, $lock->getId());
    }

    public function testLockerIdMaxLength()
    {
        $lockId = str_pad('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 128, '5');
        $this->assertEquals(strlen($lockId), 128);

        $lock = new LoaderLock($lockId);
        $this->assertSame('0123456789abcdefghijklmnopqrstuvbafb96951317fae753ab8ec1b2dad6e6', $lock->getId());
    }

    public function testSingleLocking()
    {
        $lockId = "lock1";
        $lockOne = new LoaderLock($lockId);
        $lockOne->setLock();
        $formatLockKey = $lockOne->getId();
        $isLocked = LoaderLock::isLockAvailable($formatLockKey);
        $this->assertFalse($isLocked);
        $lockOne->unLock();
        $isLocked = LoaderLock::isLockAvailable($formatLockKey);
        $this->assertTrue($isLocked);
    }

    public function testMultipleLocking()
    {
        $lockId = "lock1";
        $lockOne = new LoaderLock($lockId);
        $lockOne->setLock();
        $formatLockKey = $lockOne->getId();
        $isLocked = LoaderLock::isLockAvailable($formatLockKey);
        $this->assertFalse($isLocked);

        $lockId = "lock2";
        $lockTwo = new LoaderLock($lockId);
        $lockTwo->setLock();
        $formatLockKey = $lockTwo->getId();
        $isLocked = LoaderLock::isLockAvailable($formatLockKey);
        $this->assertFalse($isLocked);

        //unlock lock 1
        $lockOne->unLock();
        $lockOneStatus = LoaderLock::isLockAvailable($lockOne->getId());
        $this->assertTrue($lockOneStatus);
        $lockTwoStatus = LoaderLock::isLockAvailable($lockTwo->getId());
        $this->assertFalse($lockTwoStatus);

        $lockTwo->unLock();
    }

    public function testCallUnlockWhenThereIsNoLock()
    {
        $result = LoaderLock::isLockAvailable("no lock");
        $this->assertTrue($result);
    }
}
