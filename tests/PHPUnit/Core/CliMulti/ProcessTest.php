<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\CliMulti\Process;

/**
 * Class ProcessTest
 * @group Core
 */
class ProcessTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Process
     */
    private $process;

    public function setUp()
    {
        $this->process = new Process('testPid');
    }

    public function tearDown()
    {
        $this->process->finishProcess();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The given pid has an invalid format
     */
    public function test_construct_shouldFailInCasePidIsInvalid()
    {
        new Process('../../htaccess');
    }

    public function test_construct_shouldBeNotStarted_IfPidJustCreated()
    {
        $this->assertFalse($this->process->hasStarted());
    }

    public function test_construct_shouldBeNotRunning_IfPidJustCreated()
    {
        $this->assertFalse($this->process->isRunning());
    }

    public function test_startProcess_finishProcess_ShouldMarkProcessAsStarted()
    {
        $this->assertFalse($this->process->isRunning());
        $this->assertFalse($this->process->hasStarted());
        $this->assertFalse($this->process->hasFinished());

        $this->process->startProcess();

        $this->assertTrue($this->process->isRunning());
        $this->assertTrue($this->process->hasStarted());
        $this->assertTrue($this->process->isRunning());
        $this->assertTrue($this->process->hasStarted());
        $this->assertFalse($this->process->hasFinished());

        $this->process->startProcess();

        $this->assertTrue($this->process->isRunning());
        $this->assertTrue($this->process->hasStarted());
        $this->assertFalse($this->process->hasFinished());

        $this->process->finishProcess();

        $this->assertFalse($this->process->isRunning());
        $this->assertTrue($this->process->hasStarted());
        $this->assertTrue($this->process->hasFinished());
    }

    public function test_finishProcess_ShouldNotThrowError_IfNotStartedBefore()
    {
        $this->process->finishProcess();

        $this->assertFalse($this->process->isRunning());
        $this->assertTrue($this->process->hasStarted());
        $this->assertTrue($this->process->hasFinished());
    }

    public function test_hasStarted()
    {
        $this->assertTrue($this->process->hasStarted(false));
        $this->assertTrue($this->process->hasStarted('6341'));

        $this->assertFalse($this->process->hasStarted(''));
    }

    public function test_isSupported()
    {
        $this->assertTrue(Process::isSupported(), 'This test does not work on windows or if the commands ps and awk are not available');
    }

    public function test_getSecondsSinceCreation()
    {
        sleep(3);
        $seconds = $this->process->getSecondsSinceCreation();

        $this->assertGreaterThanOrEqual(3, $seconds);
        $this->assertLessThanOrEqual(4, $seconds);
    }

}