<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\ArchiveProcessor\Rules;
use Piwik\CliMulti\ProcessSymfony;
use Piwik\Common;
use Piwik\DataAccess\Model;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\Tasks;
use Piwik\Plugins\CoreAdminHome\tests\Fixtures\CoreArchiverProcessSignal as CoreArchiverProcessSignalFixture;
use Piwik\Plugins\CoreConsole\FeatureFlags\CliMultiProcessSymfony;
use Piwik\Scheduler\Task;
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group CoreAdminHome
 * @group CoreArchiverProcessSignal
 */
class CoreArchiverProcessSignalTest extends IntegrationTestCase
{
    private const METHOD_ASYNC_CLI = 'asyncCli';
    private const METHOD_ASYNC_CLI_SYMFONY = 'asyncCliSymfony';
    private const METHOD_CURL = 'curl';
    private const METHOD_SYNC_CLI = 'syncCli';

    /**
     * @var CoreArchiverProcessSignalFixture
     */
    public static $fixture;

    /**
     * @var Model
     */
    private $dataAccessModel;

    public function setUp(): void
    {
        if (!extension_loaded('pcntl') || !function_exists('pcntl_signal')) {
            $this->markTestSkipped('signal test cannot run without ext-pcntl');
        }

        parent::setUp();

        self::$fixture->stepControl->reset();

        $this->dataAccessModel = new Model();
    }

    /**
     * @dataProvider getArchivingWithoutSignalData
     */
    public function testArchivingWithoutSignalWorks(string $method): void
    {
        $this->setUpArchivingMethod($method);

        // let archiving run completely
        $process = $this->startCoreArchiver($method);
        $process->setTimeout(60);
        $process->wait();

        self::assertFalse($process->isRunning());

        $this->assertArchiveInvalidationCount(['inProgress' => 0, 'total' => 0]);

        $processOutput = $process->getOutput();

        self::assertStringContainsString('Starting archiving for', $processOutput);
        self::assertStringContainsString('Archived website id 1, period = day', $processOutput);
        self::assertStringContainsString('Archived website id 1, period = week', $processOutput);
        self::assertStringContainsString('Archived website id 1, period = month', $processOutput);
        self::assertStringContainsString('Archived website id 1, period = year', $processOutput);
        self::assertStringContainsString('Done archiving!', $processOutput);
        self::assertStringContainsString('Starting Scheduled tasks...', $processOutput);

        if (self::METHOD_CURL === $method) {
            self::assertStringContainsString('Execute HTTP API request:', $processOutput);
        } else {
            self::assertRegExp('/Running command.*\[method = ' . $method . ']/', $processOutput);
        }
    }

    public function getArchivingWithoutSignalData(): iterable
    {
        yield 'symfony process' => [self::METHOD_ASYNC_CLI_SYMFONY];
        yield 'default process (single process)' => [self::METHOD_SYNC_CLI];
        yield 'default process (multi process)' => [self::METHOD_ASYNC_CLI];
        yield 'curl' => [self::METHOD_CURL];
    }

    /**
     * @dataProvider getArchivingStoppedDuringInitData
     */
    public function testArchivingStoppedDuringInit(int $signal): void
    {
        self::$fixture->stepControl->blockCronArchiveStart();

        // we don't care for the exact method, pick one with low setup complexity
        $this->setUpArchivingMethod(self::METHOD_ASYNC_CLI);

        $process = $this->startCoreArchiver(self::METHOD_ASYNC_CLI);

        // wait until initialization step is reached
        $result = self::$fixture->stepControl->waitForSuccess(static function () use ($process): bool {
            return false !== strpos($process->getOutput(), 'Async process archiving supported');
        });

        self::assertTrue($result, 'Archiving initialization check did not succeed');

        $this->sendSignalToProcess($process, $signal, self::METHOD_ASYNC_CLI);
        $this->waitForProcessToStop($process);

        $processOutput = $process->getOutput();

        self::assertStringContainsString('Received system signal to stop archiving: ' . $signal, $processOutput);
        self::assertStringContainsString('Archiving stopped', $processOutput);
    }

    public function getArchivingStoppedDuringInitData(): iterable
    {
        yield 'stop using sigint' => [\SIGINT];
        yield 'stop using sigterm' => [\SIGTERM];
    }

    /**
     * @dataProvider getSigintDuringArchivingData
     * @dataProvider getSigtermDuringArchivingUnsupportedFallbackToSigintData
     *
     * @param array{segment: string, period: string, date: string} $blockSpec
     * @param array{inProgress: int, total: int} $invalidationCountIntermediate
     * @param array{inProgress: int, total: int} $invalidationCountFinal
     */
    public function testSigintDuringArchiving(
        string $method,
        array $blockSpec,
        array $invalidationCountIntermediate,
        array $invalidationCountFinal,
        bool $sigtermToSigintFallback = false
    ): void {
        $signalToProcess = $sigtermToSigintFallback ? \SIGTERM : \SIGINT;
        $signalOutput = \SIGINT;

        self::$fixture->stepControl->blockCronArchiveStart();
        self::$fixture->stepControl->blockAPIArchiveReports($blockSpec);

        $this->setUpArchivingMethod($method);

        $process = $this->startCoreArchiver($method);

        self::$fixture->stepControl->unblockCronArchiveStart();

        $this->waitForArchivingToStart($process, $method, $blockSpec);
        $this->assertArchiveInvalidationCount($invalidationCountIntermediate);
        $this->sendSignalToProcess($process, $signalToProcess, $method);

        self::$fixture->stepControl->unblockAPIArchiveReports();

        $this->waitForProcessToStop($process);
        $this->assertArchivingOutput($process, $method, $signalToProcess, $signalOutput, $blockSpec);
        $this->assertArchiveInvalidationCount($invalidationCountFinal);
    }

    public function getSigintDuringArchivingData(): iterable
    {
        $specToday = ['segment' => '', 'period' => 'day', 'date' => self::$fixture->today];

        yield 'symfony process' => [
            'method' => self::METHOD_ASYNC_CLI_SYMFONY,
            'blockSpec' => $specToday,
            'invalidationCountIntermediate' => ['inProgress' => 1, 'total' => 12],
            'invalidationCountFinal' => ['inProgress' => 0, 'total' => 11],
        ];

        yield 'default process (single process)' => [
            'method' => self::METHOD_SYNC_CLI,
            'blockSpec' => $specToday,
            'invalidationCountIntermediate' => ['inProgress' => 1, 'total' => 12],
            'invalidationCountFinal' => ['inProgress' => 0, 'total' => 11],
        ];

        // empty day segment will always run as a single process
        // so we use a non-empty segment for testing asyncCli
        yield 'default process (multi process)' => [
            'method' => self::METHOD_ASYNC_CLI,
            'blockSpec' => [
                'segment' => CoreArchiverProcessSignalFixture::TEST_SEGMENT_CH,
                'period' => 'day',
                'date' => self::$fixture->today,
            ],
            'invalidationCountIntermediate' => ['inProgress' => 2, 'total' => 11],
            'invalidationCountFinal' => ['inProgress' => 0, 'total' => 9],
        ];

        yield 'curl' => [
            'method' => self::METHOD_CURL,
            'blockSpec' => $specToday,
            'invalidationCountIntermediate' => ['inProgress' => 1, 'total' => 12],
            'invalidationCountFinal' => ['inProgress' => 0, 'total' => 11],
        ];
    }

    public function getSigtermDuringArchivingUnsupportedFallbackToSigintData(): iterable
    {
        // keep in sync with getSigintDuringArchivingData
        // - remove "symfony process" case
        // - add "sigtermToSigintFallback = true" to all cases

        $specToday = ['segment' => '', 'period' => 'day', 'date' => self::$fixture->today];

        yield 'default process (single process) - signal fallback' => [
            'method' => self::METHOD_SYNC_CLI,
            'blockSpec' => $specToday,
            'invalidationCountIntermediate' => ['inProgress' => 1, 'total' => 12],
            'invalidationCountFinal' => ['inProgress' => 0, 'total' => 11],
            'sigtermToSigintFallback' => true,
        ];

        // empty day segment will always run as a single process
        // so we use a non-empty segment for testing asyncCli
        yield 'default process (multi process) - signal fallback' => [
            'method' => self::METHOD_ASYNC_CLI,
            'blockSpec' => [
                'segment' => CoreArchiverProcessSignalFixture::TEST_SEGMENT_CH,
                'period' => 'day',
                'date' => self::$fixture->today,
            ],
            'invalidationCountIntermediate' => ['inProgress' => 2, 'total' => 11],
            'invalidationCountFinal' => ['inProgress' => 0, 'total' => 9],
            'sigtermToSigintFallback' => true,
        ];

        yield 'curl - signal fallback' => [
            'method' => self::METHOD_CURL,
            'blockSpec' => $specToday,
            'invalidationCountIntermediate' => ['inProgress' => 1, 'total' => 12],
            'invalidationCountFinal' => ['inProgress' => 0, 'total' => 11],
            'sigtermToSigintFallback' => true,
        ];
    }

    /**
     * @dataProvider getSigtermDuringArchivingData
     *
     * @param array{segment: string, period: string, date: string} $blockSpec
     */
    public function testSigtermDuringArchiving(string $method, array $blockSpec): void
    {
        self::$fixture->stepControl->blockCronArchiveStart();
        self::$fixture->stepControl->blockAPIArchiveReports($blockSpec);

        $this->setUpArchivingMethod($method);

        $process = $this->startCoreArchiver($method);

        self::$fixture->stepControl->unblockCronArchiveStart();

        $this->waitForArchivingToStart($process, $method, $blockSpec);
        $this->assertArchiveInvalidationCount(['inProgress' => 1, 'total' => 12]);
        $this->sendSignalToProcess($process, \SIGTERM, $method);

        $this->waitForProcessToStop($process);
        $this->assertArchivingOutput($process, $method, \SIGTERM, \SIGTERM, $blockSpec);
        $this->assertArchiveInvalidationCount(['inProgress' => 0, 'total' => 12]);
    }

    public function getSigtermDuringArchivingData(): iterable
    {
        yield 'symfony process' => [
            'method' => self::METHOD_ASYNC_CLI_SYMFONY,
            'blockSpec' => ['segment' => '', 'period' => 'day', 'date' => self::$fixture->today],
        ];
    }

    /**
     * @dataProvider getScheduledTasksStoppedData
     */
    public function testScheduledTasksStopped(int $signal): void
    {
        self::$fixture->stepControl->blockScheduledTasks();
        self::$fixture->stepControl->executeScheduledTasks();

        // we don't care for the exact method, pick one with low setup complexity
        $this->setUpArchivingMethod(self::METHOD_ASYNC_CLI);

        $process = $this->startCoreArchiver(self::METHOD_ASYNC_CLI);

        // wait until scheduled tasks are running
        $result = self::$fixture->stepControl->waitForSuccess(static function () use ($process): bool {
            return false !== strpos($process->getOutput(), 'Scheduler: executing task');
        }, $timeoutInSeconds = 60);

        self::assertTrue($result, 'Scheduled tasks did not start');

        $this->sendSignalToProcess($process, $signal, self::METHOD_ASYNC_CLI);

        self::$fixture->stepControl->unblockScheduledTasks();

        $this->waitForProcessToStop($process);

        $processOutput = $process->getOutput();
        $expectedExecutedTask = Task::getTaskName(Tasks::class, 'invalidateOutdatedArchives', null);
        $expectedSkippedTask = Task::getTaskName(Tasks::class, 'purgeOutdatedArchives', null);

        self::assertStringContainsString('executing task ' . $expectedExecutedTask, $processOutput);
        self::assertStringNotContainsString('executing task ' . $expectedSkippedTask, $processOutput);

        self::assertStringContainsString('Received system signal to stop archiving: ' . $signal, $processOutput);
        self::assertStringContainsString('Trying to stop running tasks...', $processOutput);
        self::assertStringContainsString('Scheduler: Aborting due to received signal', $processOutput);
    }

    public function getScheduledTasksStoppedData(): iterable
    {
        yield 'stop using sigint' => [\SIGINT];
        yield 'stop using sigterm' => [\SIGTERM];
    }

    /**
     * @param array{inProgress: int, total: int} $expectedCounts
     */
    private function assertArchiveInvalidationCount(array $expectedCounts): void
    {
        $actualInProgress = $this->dataAccessModel->getInvalidationsInProgress(self::$fixture->idSite);
        $actualTotal = (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('archive_invalidations') . ' WHERE idsite = ?',
            [self::$fixture->idSite]
        );

        self::assertSame($expectedCounts['total'], $actualTotal);
        self::assertCount($expectedCounts['inProgress'], $actualInProgress);
    }

    /**
     * @param array{segment: string, period: string, date: string} $blockSpec
     */
    private function assertArchivingOutput(
        ProcessSymfony $process,
        string $method,
        int $signalToProcess,
        int $signalOutput,
        array $blockSpec
    ): void {
        $idSite = self::$fixture->idSite;
        $processOutput = $process->getOutput();

        if (self::METHOD_CURL === $method) {
            self::assertStringContainsString('Execute HTTP API request:', $processOutput);
        } else {
            self::assertRegExp('/Running command.*\[method = ' . $method . ']/', $processOutput);
        }

        self::assertStringContainsString('Starting archiving for', $processOutput);
        self::assertStringContainsString('Archiving will stop now because signal to abort received', $processOutput);

        if (self::METHOD_CURL !== $method) {
            // curl handling does not acknowledge signals properly
            // so only check this output was posted for all other methods
            self::assertStringContainsString(
                'Received system signal to stop archiving: ' . $signalToProcess,
                $processOutput
            );

            self::assertStringContainsString('Trying to stop running cli processes...', $processOutput);
        }

        if (\SIGINT === $signalOutput) {
            self::assertStringContainsString(sprintf(
                "Archived website id %u, period = %s, date = %s, segment = '%s'",
                $idSite,
                $blockSpec['period'],
                $blockSpec['date'],
                $blockSpec['segment']
            ), $processOutput);
        }

        if (\SIGTERM === $signalOutput) {
            self::assertRegExp('/Aborting command.*\[method = ' . $method . ']/', $processOutput);
            self::assertStringContainsString('Archiving process killed, reset invalidation', $processOutput);
        }
    }

    private function sendSignalToProcess(
        ProcessSymfony $process,
        int $signal,
        string $method
    ): void {
        $process->signal($signal);

        if (in_array($method, [self::METHOD_CURL, self::METHOD_SYNC_CLI], true)) {
            // not all methods are able to acknowledge the signal at this point
            // wait for 250 milliseconds and rely on final result assertions
            usleep(250 * 1000);
            return;
        }

        $result = self::$fixture->stepControl->waitForSuccess(
            static function () use ($process, $signal): bool {
                return false !== strpos(
                    $process->getOutput(),
                    'Received system signal to stop archiving: ' . $signal
                );
            }
        );

        self::assertTrue($result, 'Process did not acknowledge signal');
    }

    private function setUpArchivingMethod(string $method): void
    {
        $environment = self::$fixture->getTestEnvironment();

        $featureFlag = new CliMultiProcessSymfony();
        $featureFlagConfigName = $featureFlag->getName() . '_feature';

        if (self::METHOD_ASYNC_CLI_SYMFONY === $method) {
            $environment->overrideConfig('FeatureFlags', $featureFlagConfigName, 'enabled');
        } else {
            $environment->removeOverriddenConfig('FeatureFlags', $featureFlagConfigName);
        }

        $environment->forceCliMultiViaCurl = (int) (self::METHOD_CURL === $method);

        $environment->save();
    }

    private function startCoreArchiver(string $method): ProcessSymfony
    {
        // exec is mandatory to send signals to the process
        // not using array notation because "Fixture::getCliCommandBase" contains parameters
        $process = ProcessSymfony::fromShellCommandline(sprintf(
            'exec %s core:archive -vvv %s',
            Fixture::getCliCommandBase(),
            self::METHOD_SYNC_CLI === $method ? '--concurrent-requests-per-website=1' : ''
        ));

        $process->setEnv([CoreArchiverProcessSignalFixture::ENV_TRIGGER => '1']);
        $process->setTimeout(null);
        $process->start();

        self::assertTrue($process->isRunning());
        self::assertNotNull($process->getPid());

        return $process;
    }

    /**
     * @param array{segment: string, period: string, date: string} $blockSpec
     */
    private function waitForArchivingToStart(
        ProcessSymfony $process,
        string $method,
        array $blockSpec
    ): void {
        $segment = new Segment($blockSpec['segment'], [self::$fixture->idSite]);
        $doneFlag = Rules::getDoneFlagArchiveContainsAllPlugins($segment);

        $result = self::$fixture->stepControl->waitForSuccess(function () use ($doneFlag, $blockSpec): bool {
            $invalidations = $this->dataAccessModel->getInvalidationsInProgress(self::$fixture->idSite);

            foreach ($invalidations as $invalidation) {
                if (
                    $invalidation['name'] === $doneFlag
                    && $invalidation['period'] == Piwik::$idPeriods[$blockSpec['period']]
                    && $invalidation['date1'] == $blockSpec['date']
                ) {
                    return true;
                }
            }

            return false;
        });

        self::assertTrue($result, 'Invalidation did not start for: ' . json_encode($blockSpec));

        $result = self::$fixture->stepControl->waitForSuccess(
            static function () use ($process, $method, $blockSpec): bool {
                $processOutput = $process->getOutput();

                $needles = [
                    'date=' . $blockSpec['date'],
                    'period=' . $blockSpec['period']
                ];

                if (self::METHOD_CURL === $method) {
                    $needles[] = 'Execute HTTP API request';
                } else {
                    $needles[] = 'Running command';
                }

                if ('' !== $blockSpec['segment']) {
                    $needles[] = 'segment=' . urlencode($blockSpec['segment']);
                }

                foreach ($needles as $needle) {
                    if (false === strpos($processOutput, $needle)) {
                        return false;
                    }
                }

                return true;
            }
        );

        self::assertTrue($result, 'Archiving did not start for: ' . json_encode($blockSpec));
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

CoreArchiverProcessSignalTest::$fixture = new CoreArchiverProcessSignalFixture();
