<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\CliMulti\Lock;

/**
 * Class LockTest
 * @group Core
 */
class LockTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Lock
     */
    private $lock;

    public function setUp()
    {
        $this->lock = new Lock('test');
        $this->lock->removeLock();
    }

    public function test_isLocked_WithNoLockedFile_ShouldReturnFalse()
    {
        $this->assertFalse($this->lock->isLocked());
    }

    public function test_isLocked_WithLockedFile_ShouldReturnTrue()
    {
        $this->lock->lock();

        $this->assertTrue($this->lock->isLocked());
    }

    public function test_lock_ShouldLock()
    {
        $this->assertFalse($this->lock->isLocked());

        $this->lock->lock();

        $this->assertTrue($this->lock->isLocked());

        $this->lock->lock();

        $this->assertTrue($this->lock->isLocked());
    }

    public function test_removeLock_WithExistingLock_ShouldRemoveLock()
    {
        $this->lock->lock();

        $this->assertTrue($this->lock->isLocked());

        $this->lock->removeLock();

        $this->assertFalse($this->lock->isLocked());
    }

    public function test_removeLock_ShouldNotThrowError_IfNotLocked()
    {
        $this->lock->removeLock();

        $this->assertFalse($this->lock->isLocked());
    }
}