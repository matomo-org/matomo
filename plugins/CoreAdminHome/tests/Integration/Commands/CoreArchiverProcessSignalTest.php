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
use Piwik\Plugins\CoreAdminHome\tests\Fixtures\CoreArchiverProcessSignal as CoreArchiverProcessSignalFixture;
use Piwik\Plugins\CoreConsole\FeatureFlags\CliMultiProcessSymfony;
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
    private const METHOD_ASYNC_CLI_SYMFONY = 'asyncCliSymfony';

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
        $process = $this->startCoreArchiver();
        $process->setTimeout(60);
        $process->wait();

        self::assertFalse($process->isRunning());

        $this->assertArchiveInvalidationCount($inProgress = 0, $total = 0);

        $processOutput = $process->getOutput();

        self::assertStringContainsString('Starting archiving for', $processOutput);
        self::assertStringContainsString('Archived website id 1, period = day', $processOutput);
        self::assertStringContainsString('Archived website id 1, period = week', $processOutput);
        self::assertStringContainsString('Archived website id 1, period = month', $processOutput);
        self::assertStringContainsString('Archived website id 1, period = year', $processOutput);
        self::assertStringContainsString('Done archiving!', $processOutput);
        self::assertStringContainsString('Starting Scheduled tasks...', $processOutput);
    }

    public function getArchivingWithoutSignalData(): iterable
    {
        yield 'symfony process' => [self::METHOD_ASYNC_CLI_SYMFONY];
    }

    /**
     * @dataProvider getSigintDuringArchivingData
     *
     * @param array{segment: string, period: string, date: string} $blockSpec
     */
    public function testSigintDuringArchiving(string $method, array $blockSpec): void
    {
        self::$fixture->stepControl->blockCronArchiveStart();
        self::$fixture->stepControl->blockAPIArchiveReports($blockSpec);

        $this->setUpArchivingMethod($method);

        $process = $this->startCoreArchiver();

        self::$fixture->stepControl->unblockCronArchiveStart();

        $this->waitForArchivingToStart($process, $blockSpec);
        $this->assertArchiveInvalidationCount($inProgress = 1, $total = 12);
        $this->sendSignalToProcess($process, \SIGINT);

        self::$fixture->stepControl->unblockAPIArchiveReports();

        $this->waitForProcessToStop($process);
        $this->assertArchivingOutput($process, $method, \SIGINT, $blockSpec);
        $this->assertArchiveInvalidationCount($inProgress = 0, $total = 11);
    }

    public function getSigintDuringArchivingData(): iterable
    {
        yield 'symfony process' => [
            'method' => self::METHOD_ASYNC_CLI_SYMFONY,
            'blockSpec' => ['segment' => '', 'period' => 'day', 'date' => self::$fixture->today],
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

        $process = $this->startCoreArchiver();

        self::$fixture->stepControl->unblockCronArchiveStart();

        $this->waitForArchivingToStart($process, $blockSpec);
        $this->assertArchiveInvalidationCount($inProgress = 1, $total = 12);
        $this->sendSignalToProcess($process, \SIGTERM);

        $this->waitForProcessToStop($process);
        $this->assertArchivingOutput($process, $method, \SIGTERM, $blockSpec);
        $this->assertArchiveInvalidationCount($inProgress = 0, $total = 12);
    }

    public function getSigtermDuringArchivingData(): iterable
    {
        yield 'symfony process' => [
            'method' => self::METHOD_ASYNC_CLI_SYMFONY,
            'blockSpec' => ['segment' => '', 'period' => 'day', 'date' => self::$fixture->today],
        ];
    }

    private function assertArchiveInvalidationCount(
        int $expectedInProgress,
        int $expectedTotal
    ): void {
        $actualInProgress = $this->dataAccessModel->getInvalidationsInProgress(self::$fixture->idSite);
        $actualTotal = (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('archive_invalidations') . ' WHERE idsite = ?',
            [self::$fixture->idSite]
        );

        self::assertSame($expectedTotal, $actualTotal);
        self::assertCount($expectedInProgress, $actualInProgress);
    }

    /**
     * @param array{segment: string, period: string, date: string} $blockSpec
     */
    private function assertArchivingOutput(
        ProcessSymfony $process,
        string $method,
        int $signal,
        array $blockSpec
    ): void {
        $idSite = self::$fixture->idSite;
        $processOutput = $process->getOutput();

        self::assertRegExp('/Running command.*\[method = ' . $method . ']/', $processOutput);

        self::assertStringContainsString('Starting archiving for', $processOutput);
        self::assertStringContainsString('Trying to stop running cli processes...', $processOutput);
        self::assertStringContainsString('Archiving will stop now because signal to abort received', $processOutput);

        if (\SIGINT === $signal) {
            self::assertStringContainsString(sprintf(
                "Archived website id %u, period = %s, date = %s, segment = '%s'",
                $idSite,
                $blockSpec['period'],
                $blockSpec['date'],
                $blockSpec['segment']
            ), $processOutput);
        }

        if (\SIGTERM === $signal) {
            self::assertRegExp('/Aborting command.*\[method = ' . $method . ']/', $processOutput);
            self::assertStringContainsString('Archiving process killed, reset invalidation', $processOutput);
        }
    }

    private function sendSignalToProcess(ProcessSymfony $process, int $signal): void
    {
        $process->signal($signal);

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
        if (self::METHOD_ASYNC_CLI_SYMFONY === $method) {
            $featureFlag = new CliMultiProcessSymfony();

            $environment = self::$fixture->getTestEnvironment();
            $environment->overrideConfig(
                'FeatureFlags',
                $featureFlag->getName() . '_feature',
                'enabled'
            );

            $environment->save();
        }
    }

    private function startCoreArchiver(): ProcessSymfony
    {
        // exec is mandatory to send signals to the process
        // not using array notation because "Fixture::getCliCommandBase" contains parameters
        $process = ProcessSymfony::fromShellCommandline(sprintf(
            'exec %s core:archive -vvv',
            Fixture::getCliCommandBase()
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
    private function waitForArchivingToStart(ProcessSymfony $process, array $blockSpec): void
    {
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
            static function () use ($process, $blockSpec): bool {
                $processOutput = $process->getOutput();

                $needles = [
                    'Running command',
                    'date=' . $blockSpec['date'],
                    'period=' . $blockSpec['period']
                ];

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
