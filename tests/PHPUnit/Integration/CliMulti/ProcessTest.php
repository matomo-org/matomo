<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\CliMulti;

use Piwik\CliMulti\Process;
use Piwik\Tests\Framework\Mock\File;
use ReflectionProperty;

/**
 * @group CliMulti
 */
class ProcessTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Process
     */
    private $process;

    public function setUp(): void
    {
        parent::setUp();

        File::reset();
        $this->process = new Process('testPid');
    }

    public function tearDown(): void
    {
        if (is_object($this->process)) {
            $this->process->finishProcess();
        }
        File::reset();
    }

    public function testConstructShouldFailInCasePidIsInvalid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The given pid has an invalid format');

        new Process('../../htaccess');
    }

    public function testGetPid()
    {
        $this->assertSame('testPid', $this->process->getPid());
    }

    public function testConstructShouldBeNotStartedIfPidJustCreated()
    {
        $this->assertFalse($this->process->hasStarted());
    }

    public function testConstructShouldBeNotRunningIfPidJustCreated()
    {
        if (! Process::isSupported()) {
            $this->markTestSkipped('Not supported');
        }

        $this->assertFalse($this->process->isRunning());
    }

    public function testStartProcessFinishProcessShouldMarkProcessAsStarted()
    {
        if (! Process::isSupported()) {
            $this->markTestSkipped('Not supported');
        }

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

    public function testIsRunningShouldMarkProcessAsFinishedIfPidFileIsTooBig()
    {
        if (! Process::isSupported()) {
            $this->markTestSkipped('Not supported');
        }

        $this->process->startProcess();
        $this->assertTrue($this->process->isRunning());
        $this->assertFalse($this->process->hasFinished());

        $this->process->writePidFileContent(str_pad('1', 505, '1'));

        $this->assertFalse($this->process->isRunning());
        $this->assertTrue($this->process->hasFinished());
    }

    public function testFinishProcessShouldNotThrowErrorIfNotStartedBefore()
    {
        $this->process->finishProcess();

        $this->assertFalse($this->process->isRunning());
        $this->assertTrue($this->process->hasStarted());
        $this->assertTrue($this->process->hasFinished());
    }

    public function testHasStartedStartedWhenContentFalse()
    {
        $this->assertTrue($this->process->hasStarted(false));
    }

    public function testHasStartedStartedWhenPidGiven()
    {
        $this->assertTrue($this->process->hasStarted('6341'));
        // remembers the process was started at some point
        $this->assertTrue($this->process->hasStarted(''));
    }

    public function testHasStartedNotStartedYetEmptyContentInPid()
    {
        $this->assertFalse($this->process->hasStarted(''));
    }

    public function testGetSecondsSinceCreation()
    {
        // This is not proper, but it avoids using sleep and stopping the tests for several seconds
        $r = new ReflectionProperty($this->process, 'timeCreation');
        $r->setAccessible(true);
        $r->setValue($this->process, time() - 2);

        $seconds = $this->process->getSecondsSinceCreation();

        $this->assertEquals(2, $seconds);
    }
}
