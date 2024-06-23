<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Concurrency;

use Piwik\Common;
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend\MySqlLockBackend;
use Piwik\Date;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class LockTest extends IntegrationTestCase
{
    /**
     * @var Lock
     */
    public $lock;

    public function setUp(): void
    {
        parent::setUp();

        $mysql = new MySqlLockBackend();
        $this->lock = $this->createLock($mysql);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testTooLongNamespaceIsNotSupported()
    {
        $this->expectException(\Exception::class);

        new Lock(new MySqlLockBackend(), 'aLongStringWithOver38CharactersIsNotSupported');
    }

    public function testAcquireLockShouldLockInCaseItIsNotLockedYet()
    {
        $this->assertTrue($this->lock->acquireLock(0));
        $this->assertFalse($this->lock->acquireLock(0));

        $this->lock->unlock();

        $this->assertTrue($this->lock->acquireLock(0));
        $this->assertFalse($this->lock->acquireLock(0));
    }

    public function testAcquireLockShouldBeAbleToLockMany()
    {
        $this->assertTrue($this->lock->acquireLock(0));
        $this->assertFalse($this->lock->acquireLock(0));
        $this->assertTrue($this->lock->acquireLock(1));
        $this->assertTrue($this->lock->acquireLock(2));
        $this->assertFalse($this->lock->acquireLock(1));
    }

    public function testIsLockedShouldDetermineWhetherALockIsLocked()
    {
        $this->assertFalse($this->lock->isLocked());
        $this->lock->acquireLock(0);

        $this->assertTrue($this->lock->isLocked());

        $this->lock->unlock();

        $this->assertFalse($this->lock->isLocked());
    }

    public function testUnlockOnlyUnlocksTheLastOne()
    {
        $this->assertTrue($this->lock->acquireLock(0));
        $this->assertTrue($this->lock->acquireLock(1));
        $this->assertTrue($this->lock->acquireLock(2));

        $this->lock->unlock();

        $this->assertFalse($this->lock->acquireLock(0));
        $this->assertFalse($this->lock->acquireLock(1));
        $this->assertTrue($this->lock->acquireLock(2));
    }

    public function testExtendLockShouldReturnTrueOnSuccess()
    {
        $this->lock->acquireLock(0);
        $this->assertTrue($this->lock->extendLock(2));
    }

    public function testExtendLockShouldReturnFalseIfNoTimeoutGiven()
    {
        $this->lock->acquireLock(0);
        $this->assertFalse($this->lock->extendLock(0));
    }

    public function testExtendLockShouldReturnFalseIfNotLocked()
    {
        $this->assertFalse($this->lock->extendLock(2));
    }

    public function testGetNumberOfAcquiredLocksShouldReturnNumberOfLocks()
    {
        $this->assertNumberOfLocksEquals(0);

        $this->lock->acquireLock(0);
        $this->assertNumberOfLocksEquals(1);

        $this->lock->acquireLock(4);
        $this->lock->acquireLock(5);
        $this->assertNumberOfLocksEquals(3);

        $this->lock->unlock();
        $this->assertNumberOfLocksEquals(2);
    }

    public function testGetAllAcquiredLockKeysShouldReturnUsedKeysThatAreLocked()
    {
        $this->assertSame([], $this->lock->getAllAcquiredLockKeys());

        $this->lock->acquireLock(0);
        $this->assertSame(['TestLock0'], $this->lock->getAllAcquiredLockKeys());

        $this->lock->acquireLock('veryverylongidthatwillgetshortenedasthereisamaximumof70charsinthedatabase');
        $this->lock->acquireLock(5);

        $locks = $this->lock->getAllAcquiredLockKeys();
        sort($locks);
        $this->assertSame(['TestLock0', 'TestLock5', 'TestLockveryverylongidthatwillgetshorc93f8252040e73dacbeaaf93ae9c19d2'], $locks);
    }

    public function testReacquireOnlyReacquiresWhenCloseToOriginalExpirationTime()
    {
        Date::$now = strtotime('2015-03-04 03:04:05');

        $this->lock->acquireLock(0);

        $expireTime = $this->getLockExpirationTime();

        sleep(1);
        $this->lock->reacquireLock();
        $newExpireTime = $this->getLockExpirationTime();
        $this->assertEquals($expireTime, $newExpireTime);

        // 30s after initial, no update
        Date::$now = strtotime('2015-03-04 03:04:35');

        sleep(1);
        $this->lock->reacquireLock();
        $newExpireTime = $this->getLockExpirationTime();
        $this->assertEquals($expireTime, $newExpireTime);

        // 50s after initial, update
        Date::$now = strtotime('2015-03-04 03:04:55');

        sleep(1);
        $this->lock->reacquireLock();
        $newExpireTime = $this->getLockExpirationTime();
        $this->assertNotEquals($expireTime, $newExpireTime);

        $expireTime = $newExpireTime;

        // 60s after initial, no update
        Date::$now = strtotime('2015-03-04 03:05:05');

        sleep(1);
        $this->lock->reacquireLock();
        $newExpireTime = $this->getLockExpirationTime();
        $this->assertEquals($expireTime, $newExpireTime);

        // 1m 50s after initial, update
        Date::$now = strtotime('2015-03-04 03:05:55');

        sleep(1);
        $this->lock->reacquireLock();
        $newExpireTime = $this->getLockExpirationTime();
        $this->assertNotEquals($expireTime, $newExpireTime);
    }

    private function assertNumberOfLocksEquals($numExpectedLocks)
    {
        $this->assertSame($numExpectedLocks, $this->lock->getNumberOfAcquiredLocks());
    }

    private function createLock($mysql)
    {
        return new Lock($mysql, 'TestLock');
    }

    private function getLockExpirationTime()
    {
        return Db::fetchOne("SELECT expiry_time FROM `" . Common::prefixTable('locks') . "`");
    }
}
