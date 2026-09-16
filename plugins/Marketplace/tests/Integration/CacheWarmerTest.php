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
use Piwik\CliMulti\CliPhp;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Log\NullLogger;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\CacheWarmer;
use Piwik\Plugins\Marketplace\Environment;
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

    // what CliPhp::findPhpBinary() really returns: the binary with its own arguments attached
    private const PHP_BINARY = '/usr/bin/php8.4 -q';

    private $originalCliMode;

    private $originalInternetFeatures;

    private $originalHttpHost;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalCliMode = Common::$isCliMode;
        $this->originalInternetFeatures = Config::getInstance()->General['enable_internet_features'];
        $this->originalHttpHost = $_SERVER['HTTP_HOST'] ?? null;
    }

    public function tearDown(): void
    {
        Common::$isCliMode = $this->originalCliMode;
        Config::getInstance()->General['enable_internet_features'] = $this->originalInternetFeatures;

        if (null === $this->originalHttpHost) {
            unset($_SERVER['HTTP_HOST']);
        } else {
            $_SERVER['HTTP_HOST'] = $this->originalHttpHost;
        }

        unset($GLOBALS['CONFIG_INI_PATH_RESOLVER']);

        parent::tearDown();
    }

    public function testWarmSoonDoesNothingWhenInternetFeaturesAreDisabled(): void
    {
        Config::getInstance()->General['enable_internet_features'] = 0;
        Common::$isCliMode = false;

        $api = $this->apiWithWarmLists(false);
        // defence in depth rather than a live case: Plugin\Manager drops plugins whose
        // requiresInternetConnection() is true when internet features are off, so this handler is
        // not even registered then. Pins the short-circuit order regardless.
        $api->expects($this->never())->method('hasWarmOverviewLists');

        $scheduler = $this->createMock(Scheduler::class);
        $scheduler->expects($this->never())->method('rescheduleTaskAndRunNow');

        $this->buildWarmer($scheduler, $api)->warmSoon();
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

    public function testWarmSoonMarksTheTaskDueFromTheCommandLineEvenWhenItCouldSpawnAProcess(): void
    {
        // defensive: core has no command-line installer, so nothing reaches this today. If one
        // ever calls in, nobody is waiting on a page and a fleet would all spawn at once, so it
        // must fall back to marking the task due
        Common::$isCliMode = true;

        $scheduler = $this->createMock(Scheduler::class);
        $scheduler->expects($this->once())->method('rescheduleTaskAndRunNow');

        $this->buildWarmer($scheduler, $this->apiWithWarmLists(false), $this->cliMulti(true))->warmSoon();
    }

    public function testWarmSoonDoesNotLetAFailureReachTheInstallationThatTriggeredIt(): void
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

    public function testWarmSoonStillMarksTheTaskDueWhenTheCacheProbeFails(): void
    {
        Common::$isCliMode = false;

        // the probe reaches the database and the cache backend, so it can fail on its own - the
        // cheap half of this class should survive that
        $api = $this->createMock(Client::class);
        $api->method('hasWarmOverviewLists')
            ->willThrowException(new Exception('MySQL server has gone away'));

        $scheduler = $this->createMock(Scheduler::class);
        $scheduler->expects($this->once())->method('rescheduleTaskAndRunNow');

        $this->buildWarmer($scheduler, $api)->warmSoon();
    }

    public function testTheBackgroundCommandRunsTheTaskThatWarmsTheCache(): void
    {
        Common::$isCliMode = false;

        $warmer = $this->buildRecordingWarmer($this->createMock(Scheduler::class));
        $warmer->warmSoon();

        // the command is spawned with its output discarded, so a name the scheduler cannot resolve
        // would fail silently - this is the only place that mistake is visible
        self::assertStringContainsString(
            'core:run-scheduled-tasks ' . escapeshellarg(self::WARM_CACHE_TASK) . ' >',
            (string) $warmer->command
        );
        self::assertStringContainsString(escapeshellarg(PIWIK_INCLUDE_PATH . '/console'), (string) $warmer->command);
        self::assertStringEndsWith('> /dev/null 2>&1 &', (string) $warmer->command);

        // deliberately unquoted: the binary arrives with its arguments attached, so quoting it
        // would name a file that does not exist and the shell would fail behind the redirection
        self::assertStringStartsWith(self::PHP_BINARY . ' ', (string) $warmer->command);
        self::assertStringNotContainsString(escapeshellarg(self::PHP_BINARY), (string) $warmer->command);

        // this install uses the default config file, so naming a host would be noise at best and
        // the wrong instance at worst - the paired test below covers the per-hostname case
        self::assertSame(Config::getDefaultLocalConfigPath(), Config::getLocalConfigPath());
        self::assertStringNotContainsString('--matomo-domain', (string) $warmer->command);
    }

    public function testTheBackgroundCommandNamesTheHostWhenThisInstallHasItsOwnConfigFile(): void
    {
        Common::$isCliMode = false;
        $_SERVER['HTTP_HOST'] = 'example.org';

        // a per-hostname config: without --matomo-domain the child resolves no host and loads
        // config/config.ini.php, warming an instance other than the one that just updated
        $GLOBALS['CONFIG_INI_PATH_RESOLVER'] = static function () {
            return PIWIK_USER_PATH . '/config/example.org.config.ini.php';
        };

        $warmer = $this->buildRecordingWarmer($this->createMock(Scheduler::class));
        $warmer->warmSoon();

        self::assertStringContainsString(
            '--matomo-domain=' . escapeshellarg('example.org') . ' ',
            (string) $warmer->command
        );
    }

    public function testTheBackgroundRunAlsoMarksTheTaskDueSoAChildThatDiesIsNotLost(): void
    {
        Common::$isCliMode = false;

        // the command discards its output, so nothing can observe a child that failed to boot;
        // leaving the task due means the next scheduler run warms the cache instead
        $scheduler = $this->createMock(Scheduler::class);
        $scheduler->expects($this->once())
            ->method('rescheduleTaskAndRunNow')
            ->with($this->callback(static function (Task $task) {
                return self::WARM_CACHE_TASK === $task->getName();
            }));

        $warmer = $this->buildRecordingWarmer($scheduler);
        $warmer->warmSoon();

        self::assertNotNull($warmer->command);
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
            $this->createMock(Environment::class),
            $cliMulti ?: $this->cliMulti(false),
            $this->cliPhp(),
            $logger ?: new NullLogger()
        );
    }

    private function buildRecordingWarmer(Scheduler $scheduler): RecordingCacheWarmer
    {
        return new RecordingCacheWarmer(
            $this->apiWithWarmLists(false),
            $scheduler,
            $this->createMock(Environment::class),
            $this->cliMulti(true),
            $this->cliPhp(),
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

    private function cliPhp(): CliPhp
    {
        $cliPhp = $this->createMock(CliPhp::class);
        $cliPhp->method('findPhpBinary')->willReturn(self::PHP_BINARY);

        return $cliPhp;
    }
}

/**
 * Records the command the warmer would spawn instead of spawning it.
 */
class RecordingCacheWarmer extends CacheWarmer
{
    public $command = null;

    public $result = '';

    protected function execute(string $command): ?string
    {
        $this->command = $command;

        return $this->result;
    }
}
