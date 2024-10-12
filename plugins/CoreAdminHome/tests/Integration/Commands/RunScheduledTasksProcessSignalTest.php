<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\CliMulti\ProcessSymfony;
use Piwik\Plugins\CoreAdminHome\Tasks;
use Piwik\Plugins\CoreAdminHome\tests\Fixtures\RunScheduledTasksProcessSignal as RunScheduledTasksProcessSignalFixture;
use Piwik\Scheduler\Task;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group CoreAdminHome
 * @group RunScheduledTasksProcessSignal
 */
class RunScheduledTasksProcessSignalTest extends IntegrationTestCase
{
    /**
     * @var RunScheduledTasksProcessSignalFixture
     */
    public static $fixture;

    public function setUp(): void
    {
        if (!extension_loaded('pcntl') || !function_exists('pcntl_signal')) {
            $this->markTestSkipped('signal test cannot run without ext-pcntl');
        }

        parent::setUp();

        self::$fixture->stepControl->reset();
    }

    /**
     * @dataProvider getScheduledTasksStoppedData
     */
    public function testScheduledTasksStopped(int $signal): void
    {
        self::$fixture->stepControl->blockScheduledTasks();

        $process = $this->startScheduledTasks();

        // wait until scheduled tasks are running
        $result = self::$fixture->stepControl->waitForSuccess(static function () use ($process): bool {
            return false !== strpos($process->getOutput(), 'Scheduler: executing task');
        }, $timeoutInSeconds = 30);

        self::assertTrue($result, 'Scheduled tasks did not start');

        $this->sendSignalToProcess($process, $signal);

        self::$fixture->stepControl->unblockScheduledTasks();

        $this->waitForProcessToStop($process);

        $processOutput = $process->getOutput();
        $expectedExecutedTask = Task::getTaskName(Tasks::class, 'invalidateOutdatedArchives', null);
        $expectedSkippedTask = Task::getTaskName(Tasks::class, 'purgeOutdatedArchives', null);

        self::assertStringContainsString('executing task ' . $expectedExecutedTask, $processOutput);
        self::assertStringNotContainsString('executing task ' . $expectedSkippedTask, $processOutput);

        self::assertStringContainsString(
            'Received system signal to stop scheduled tasks: ' . $signal,
            $processOutput
        );

        self::assertStringContainsString('Scheduler: Aborting due to received signal', $processOutput);
    }

    public function getScheduledTasksStoppedData(): iterable
    {
        yield 'stop using sigint' => [\SIGINT];
        yield 'stop using sigterm' => [\SIGTERM];
    }

    private function sendSignalToProcess(ProcessSymfony $process, int $signal): void
    {
        $process->signal($signal);

        $result = self::$fixture->stepControl->waitForSuccess(
            static function () use ($process, $signal): bool {
                return false !== strpos(
                    $process->getOutput(),
                    'Received system signal to stop scheduled tasks: ' . $signal
                );
            }
        );

        self::assertTrue($result, 'Process did not acknowledge signal');
    }

    private function startScheduledTasks(): ProcessSymfony
    {
        // exec is mandatory to send signals to the process
        // not using array notation because "Fixture::getCliCommandBase" contains parameters
        $process = ProcessSymfony::fromShellCommandline(sprintf(
            'exec %s scheduled-tasks:run -vvv --force',
            Fixture::getCliCommandBase()
        ));

        $process->setEnv([RunScheduledTasksProcessSignalFixture::ENV_TRIGGER => '1']);
        $process->setTimeout(null);
        $process->start();

        self::assertTrue($process->isRunning());
        self::assertNotNull($process->getPid());

        return $process;
    }

    private function waitForProcessToStop(ProcessSymfony $process): void
    {
        $result = self::$fixture->stepControl->waitForSuccess(static function () use ($process): bool {
            return !$process->isRunning();
        });

        self::assertTrue($result, 'Archiving process did not stop');
        self::assertSame(0, $process->getExitCode());
    }
}

RunScheduledTasksProcessSignalTest::$fixture = new RunScheduledTasksProcessSignalFixture();
