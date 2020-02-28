<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Concurrency;


use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend\MySqlLockBackend;
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

    public function test_acquireLock_ShouldLockInCaseItIsNotLockedYet()
    {
        $this->assertTrue($this->lock->acquireLock(0));
        $this->assertFalse($this->lock->acquireLock(0));

        $this->lock->unlock();

        $this->assertTrue($this->lock->acquireLock(0));
        $this->assertFalse($this->lock->acquireLock(0));
    }

    public function test_acquireLock_ShouldBeAbleToLockMany()
    {
        $this->assertTrue($this->lock->acquireLock(0));
        $this->assertFalse($this->lock->acquireLock(0));
        $this->assertTrue($this->lock->acquireLock(1));
        $this->assertTrue($this->lock->acquireLock(2));
        $this->assertFalse($this->lock->acquireLock(1));
    }

    public function test_isLocked_ShouldDetermineWhetherALockIsLocked()
    {
        $this->assertFalse($this->lock->isLocked());
        $this->lock->acquireLock(0);

        $this->assertTrue($this->lock->isLocked());

        $this->lock->unlock();

        $this->assertFalse($this->lock->isLocked());
    }

    public function test_unlock_OnlyUnlocksTheLastOne()
    {
        $this->assertTrue($this->lock->acquireLock(0));
        $this->assertTrue($this->lock->acquireLock(1));
        $this->assertTrue($this->lock->acquireLock(2));

        $this->lock->unlock();

        $this->assertFalse($this->lock->acquireLock(0));
        $this->assertFalse($this->lock->acquireLock(1));
        $this->assertTrue($this->lock->acquireLock(2));
    }

    public function test_expireLock_ShouldReturnTrueOnSuccess()
    {
        $this->lock->acquireLock(0);
        $this->assertTrue($this->lock->expireLock(2));
    }

    public function test_expireLock_ShouldReturnFalseIfNoTimeoutGiven()
    {
        $this->lock->acquireLock(0);
        $this->assertFalse($this->lock->expireLock(0));
    }

    public function test_expireLock_ShouldReturnFalseIfNotLocked()
    {
        $this->assertFalse($this->lock->expireLock(2));
    }

    public function test_getNumberOfAcquiredLocks_shouldReturnNumberOfLocks()
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

    public function test_getAllAcquiredLockKeys_shouldReturnUsedKeysThatAreLocked()
    {
        $this->assertSame(array(), $this->lock->getAllAcquiredLockKeys());

        $this->lock->acquireLock(0);
        $this->assertSame(array('TestLock0'), $this->lock->getAllAcquiredLockKeys());

        $this->lock->acquireLock(4);
        $this->lock->acquireLock(5);

        $locks = $this->lock->getAllAcquiredLockKeys();
        sort($locks);
        $this->assertSame(array('TestLock0', 'TestLock4', 'TestLock5'), $locks);
    }

    private function assertNumberOfLocksEquals($numExpectedLocks)
    {
        $this->assertSame($numExpectedLocks, $this->lock->getNumberOfAcquiredLocks());
    }

    private function createLock($mysql)
    {
        return new Lock($mysql, 'TestLock');
    }

}
