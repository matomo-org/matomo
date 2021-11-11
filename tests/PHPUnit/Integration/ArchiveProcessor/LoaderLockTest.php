<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Integration\ArchiveProcessor;

use Piwik\ArchiveProcessor\LoaderLock;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class LoaderLockTest extends IntegrationTestCase
{


    public function test_lockerIdMaxLength()
    {
        $lockId = $this->generateRandomString(128);
        $this->assertEquals(strlen($lockId),128);

        $lock = new LoaderLock($lockId);
        $this->assertSame(64, strlen($lock->getId()));
    }

    public function test_singleLocking()
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

    public function test_multipleLocking()
    {
        $lockId = "lock1";
        $lockOne = new LoaderLock($lockId);
        $lockOne->setLock();
        $formatLockKey =$lockOne->getId();
        $isLocked = LoaderLock::isLockAvailable($formatLockKey);
        $this->assertFalse($isLocked);

        $lockId = "lock2";
        $lockTwo = new LoaderLock($lockId);
        $lockTwo->setLock();
        $formatLockKey =$lockTwo->getId();
        $isLocked = LoaderLock::isLockAvailable($formatLockKey);
        $this->assertFalse($isLocked);

        //unlock lock 1
        $lockOne->unLock();
        $lockOneStatus = LoaderLock::isLockAvailable($lockOne->getId());
        $this->assertTrue($lockOneStatus);
        $lockTwoStatus =  LoaderLock::isLockAvailable($lockTwo->getId());
        $this->assertFalse($lockTwoStatus);

        $lockTwo->unLock();

    }

    public function test_callUnlockWhenThereIsNoLock()
    {
        $result = LoaderLock::isLockAvailable("no lock");
        $this->assertTrue($result);
    }



    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}
