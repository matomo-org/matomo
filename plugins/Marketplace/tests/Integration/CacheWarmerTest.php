<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Exception;
use Piwik\CliMulti;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Log\NullLogger;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\CacheWarmer;
use Piwik\Plugins\Marketplace\Environment;
use Piwik\Plugins\Marketplace\Tasks;
use Piwik\Plugins\Marketplace\UpdateCommunication;
use Piwik\Scheduler\Scheduler;
use Piwik\Scheduler\Task;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group CacheWarmerTest
 * @group Plugins
 */
class CacheWarmerTest extends IntegrationTestCase
{
    private const WARM_CACHE_TASK = 'Piwik\Plugins\Marketplace\Tasks.warmCacheEntries';

    private $originalCliMode;

    private $originalInternetFeatures;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalCliMode = Common::$isCliMode;
        $this->originalInternetFeatures = Config::getInstance()->General['enable_internet_features'];
    }

    public function tearDown(): void
    {
        Common::$isCliMode = $this->originalCliMode;
        Config::getInstance()->General['enable_internet_features'] = $this->originalInternetFeatures;

        parent::tearDown();
    }

    public function testWarmSoonDoesNothingWhenInternetFeaturesAreDisabled(): void
    {
        Config::getInstance()->General['enable_internet_features'] = 0;
        Common::$isCliMode = false;

        $scheduler = $this->createMock(Scheduler::class);
        $scheduler->expects($this->never())->method('rescheduleTaskAndRunNow');

        $this->buildWarmer($scheduler, $this->apiWithWarmLists(false))->warmSoon();
    }

    public function testWarmSoonDoesNothingWhenTheOverviewListsAreAlreadyWarm(): void
    {
        Common::$isCliMode = false;

        $scheduler = $this->createMock(Scheduler::class);
        $scheduler->expects($this->never())->method('rescheduleTaskAndRunNow');

        $this->buildWarmer($scheduler, $this->apiWithWarmLists(true))->warmSoon();
    }

    public function testWarmSoonMarksTheTaskDueWhenTheHostCannotRunItInBackground(): void
    {
        Common::$isCliMode = false;

        $scheduler = $this->createMock(Scheduler::class);
        $scheduler->expects($this->once())
            ->method('rescheduleTaskAndRunNow')
            ->with($this->callback(static function (Task $task) {
                return self::WARM_CACHE_TASK === $task->getName();
            }));

        $this->buildWarmer($scheduler, $this->apiWithWarmLists(false), $this->cliMulti(false))->warmSoon();
    }

    public function testWarmSoonMarksTheTaskDueForACommandLineUpdateEvenWhenItCouldSpawnAProcess(): void
    {
        // an unattended deployment has nobody waiting on a page, and a fleet of them would reach
        // this at the same moment, so each instance is left to warm on its own next scheduler run
        Common::$isCliMode = true;

        $scheduler = $this->createMock(Scheduler::class);
        $scheduler->expects($this->once())->method('rescheduleTaskAndRunNow');

        $this->buildWarmer($scheduler, $this->apiWithWarmLists(false), $this->cliMulti(true))->warmSoon();
    }

    public function testWarmSoonDoesNotLetAFailureReachTheInstallOrUpdateThatTriggeredIt(): void
    {
        Common::$isCliMode = false;

        $scheduler = $this->createMock(Scheduler::class);
        $scheduler->method('rescheduleTaskAndRunNow')
            ->willThrowException(new Exception('the timetable could not be written'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('Could not warm the Marketplace cache ahead of time'));

        $warmer = $this->buildWarmer(
            $scheduler,
            $this->apiWithWarmLists(false),
            $this->cliMulti(false),
            $logger
        );

        $warmer->warmSoon();
    }

    public function testTheBackgroundCommandRunsTheTaskThatWarmsTheCache(): void
    {
        $warmer = new ExposedCacheWarmer(
            $this->apiWithWarmLists(false),
            $this->createMock(Scheduler::class),
            $this->buildTasks(),
            $this->createMock(Environment::class),
            $this->cliMulti(true),
            new NullLogger()
        );

        $command = $warmer->buildWarmCommand('/usr/bin/php');

        // the command is spawned with its output discarded, so a name the scheduler cannot resolve
        // would fail silently - this is the only place that mistake is visible
        self::assertStringContainsString(
            '/console core:run-scheduled-tasks ' . escapeshellarg(self::WARM_CACHE_TASK) . ' >',
            $command
        );
        self::assertStringStartsWith('/usr/bin/php ', $command);
        self::assertStringEndsWith('> /dev/null 2>&1 &', $command);
    }

    public function testTheWarmerCanBeBuiltFromTheContainer(): void
    {
        // the plugin's event handlers resolve it this way, and every test above supplies its own
        // collaborators, so nothing else here would notice an unresolvable dependency
        self::assertInstanceOf(CacheWarmer::class, StaticContainer::get(CacheWarmer::class));
    }

    private function buildWarmer(
        Scheduler $scheduler,
        Client $api,
        ?CliMulti $cliMulti = null,
        ?LoggerInterface $logger = null
    ): CacheWarmer {
        return new CacheWarmer(
            $api,
            $scheduler,
            $this->buildTasks(),
            $this->createMock(Environment::class),
            $cliMulti ?: $this->cliMulti(false),
            $logger ?: new NullLogger()
        );
    }

    private function buildTasks(): Tasks
    {
        return new Tasks(
            $this->createMock(UpdateCommunication::class),
            $this->createMock(Client::class),
            new NullLogger()
        );
    }

    private function apiWithWarmLists(bool $isWarm): Client
    {
        $api = $this->createMock(Client::class);
        $api->method('hasWarmOverviewLists')->willReturn($isWarm);

        return $api;
    }

    private function cliMulti(bool $supportsAsync): CliMulti
    {
        $cliMulti = $this->createMock(CliMulti::class);
        $cliMulti->method('supportsAsync')->willReturn($supportsAsync);

        return $cliMulti;
    }
}

/**
 * Reads the command the warmer would spawn without spawning it.
 */
class ExposedCacheWarmer extends CacheWarmer
{
    public function buildWarmCommand(string $phpBinary): string
    {
        return parent::buildWarmCommand($phpBinary);
    }
}
