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

    public function test_singleLocking()
    {
        $lockId = "lock1";
        $lockOne = new LoaderLock($lockId);
        $lockOne->setLock();
        $formatLockKey = $lockOne->getId();
        $isLocked = Db::fetchOne('SELECT IS_FREE_LOCK(?)', [$formatLockKey]);
        $this->assertFalse((bool)$isLocked);
        $lockOne->unLock();
        $isLocked = Db::fetchOne('SELECT IS_FREE_LOCK(?)', [$formatLockKey]);
        $this->assertTrue((bool)$isLocked);
    }

    public function test_multipleLocking()
    {
        $lockId = "lock1";
        $lockOne = new LoaderLock($lockId);
        $lockOne->setLock();
        $formatLockKey =$lockOne->getId();
        $isLocked = Db::fetchOne('SELECT IS_FREE_LOCK(?)', [$formatLockKey]);
        $this->assertFalse((bool)$isLocked);

        $lockId = "lock2";
        $lockTwo = new LoaderLock($lockId);
        $lockTwo->setLock();
        $formatLockKey =$lockTwo->getId();
        $isLocked = Db::fetchOne('SELECT IS_FREE_LOCK(?)', [$formatLockKey]);
        $this->assertFalse((bool)$isLocked);

        //unlock lock 1
        $lockOne->unLock();
        $lockOneStatus = Db::fetchOne('SELECT IS_FREE_LOCK(?)', array($lockOne->getId()));
        $this->assertTrue((bool)$lockOneStatus);
        $lockTwoStatus = Db::fetchOne('SELECT IS_FREE_LOCK(?)', array($lockTwo->getId()));
        $this->assertFalse((bool)$lockTwoStatus);

        $lockTwo->unLock();

    }

    public function test_callUnlockWhenThereIsNoLock()
    {
        $result = Db::fetchOne('DO RELEASE_LOCK(?)', array("no lock"));
        $this->assertFalse((bool)$result);
    }

    public function test_lockerIdMaxLength()
    {
        $lockId = $this->generateRandomString(128);
        $this->assertEquals(strlen($lockId),128);

        $lock = new LoaderLock($lockId);
        $check = strlen($lock->getId())<64;
        $this->assertEquals($check,1);
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
