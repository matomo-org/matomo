<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\CliMulti\Pid;

/**
 * Class PidTest
 * @group Core
 */
class PidTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Pid
     */
    private $pid;

    public function setUp()
    {
        $this->pid = new Pid('test');
    }

    public function tearDown()
    {
        $this->pid->finishProcess();
    }

    public function test_construct_shouldBeNotStarted_IfPidJustCreated()
    {
        $this->assertFalse($this->pid->hasStarted());
    }

    public function test_construct_shouldBeNotRunning_IfPidJustCreated()
    {
        $this->assertFalse($this->pid->isRunning());
    }

    public function test_startProcess_finishProcess_ShouldMarkProcessAsStarted()
    {
        $this->assertFalse($this->pid->isRunning());
        $this->assertFalse($this->pid->hasStarted());

        $this->pid->startProcess();

        $this->assertTrue($this->pid->isRunning());
        $this->assertTrue($this->pid->hasStarted());
        $this->assertTrue($this->pid->isRunning());
        $this->assertTrue($this->pid->hasStarted());

        $this->pid->startProcess();

        $this->assertTrue($this->pid->isRunning());
        $this->assertTrue($this->pid->hasStarted());

        $this->pid->finishProcess();

        $this->assertFalse($this->pid->isRunning());
        $this->assertTrue($this->pid->hasStarted());
    }

    public function test_finishProcess_ShouldNotThrowError_IfNotStartedBefore()
    {
        $this->pid->finishProcess();

        $this->assertFalse($this->pid->isRunning());
        $this->assertTrue($this->pid->hasStarted());
    }
}