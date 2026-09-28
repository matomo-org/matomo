<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Unit;

use ArrayObject;
use Matomo\Cache\Backend\ArrayCache;
use Matomo\Cache\Lazy;
use Piwik\CliMulti;
use Piwik\CliMulti\CliPhp;
use Piwik\Date;
use Piwik\Log\NullLogger;
use Piwik\Plugins\Marketplace\BackgroundWarmer;
use Piwik\Scheduler\Scheduler;

/**
 * @group Plugins
 * @group Marketplace
 * @group BackgroundWarmerTest
 */
class BackgroundWarmerTest extends \PHPUnit\Framework\TestCase
{
    private const NOW = 1790000000;

    /**
     * @var ArrayObject
     */
    private $commands;

    /**
     * @var ArrayObject
     */
    private $maxDelays;

    /**
     * @var ArrayObject
     */
    private $cliMultisCreated;

    public function setUp(): void
    {
        Date::$now = self::NOW;
        $this->commands = new ArrayObject();
        $this->maxDelays = new ArrayObject();
        $this->cliMultisCreated = new ArrayObject();
    }

    public function tearDown(): void
    {
        Date::$now = null;
    }

    public function testAnImmediateRefreshStartsAtOnceInTheBackground()
    {
        $this->assertTrue($this->buildWarmer()->refreshNow(3600));

        $this->assertSame(
            ["(/usr/bin/php -q '" . PIWIK_INCLUDE_PATH . "/console' marketplace:warm-cache --if-older-than=3600) > /dev/null 2>&1 &"],
            $this->commands->getArrayCopy()
        );
    }

    public function testImmediateRefreshesShareOneUntilTheHoldRunsOut()
    {
        $cache = new Lazy(new ArrayCache());

        $this->assertTrue($this->buildWarmer($cache)->refreshNow(3600));
        Date::$now = self::NOW + 299;
        $this->assertTrue($this->buildWarmer($cache)->refreshNow(3600));
        $this->assertCount(1, $this->commands);

        Date::$now = self::NOW + 300;
        $this->assertTrue($this->buildWarmer($cache)->refreshNow(3600));
        $this->assertCount(2, $this->commands);
    }

    public function testAnImmediateRefreshDuringTheHoldDoesNotLookForPhp()
    {
        $cache = new Lazy(new ArrayCache());
        $this->buildWarmer($cache)->refreshNow(3600);

        $this->cliMultisCreated->exchangeArray([]);
        $cliPhp = $this->createMock(CliPhp::class);
        $cliPhp->expects($this->never())->method('findPhpBinary');

        $this->assertTrue($this->buildWarmer($cache, true, 0, null, $cliPhp)->refreshNow(3600));
        $this->assertCount(1, $this->commands);
        // building a CliMulti is itself what runs the checks
        $this->assertCount(0, $this->cliMultisCreated);
    }

    public function testPhpIsLookedForOnlyOnce()
    {
        $cliMulti = $this->createMock(CliMulti::class);
        $cliMulti->expects($this->never())->method('supportsAsync');
        $cliMulti->supportsAsync = true;
        $cliPhp = $this->createMock(CliPhp::class);
        $cliPhp->expects($this->once())->method('findPhpBinary')->willReturn('/usr/bin/php -q');

        $warmer = $this->buildWarmer(null, true, 0, $cliMulti, $cliPhp);
        $warmer->refreshNow(3600);
        $warmer->refreshAfterInstallation();

        $this->assertCount(2, $this->commands);
        $this->assertCount(1, $this->cliMultisCreated);
    }

    public function testAnImmediateRefreshIsLeftToTheCallerWhereProcessesCannotBeSpawned()
    {
        $this->assertFalse($this->buildWarmer(null, false)->refreshNow(3600));
        $this->assertCount(0, $this->commands);
    }

    public function testVisitsRecheckWhetherProcessesCanBeSpawnedOnlyOnceTheHoldRunsOut()
    {
        $cache = new Lazy(new ArrayCache());

        $this->assertFalse($this->buildWarmer($cache, false)->refreshNow(3600));
        Date::$now = self::NOW + 299;
        $this->assertFalse($this->buildWarmer($cache, false)->refreshNow(3600));
        $this->assertCount(1, $this->cliMultisCreated);

        Date::$now = self::NOW + 300;
        $this->assertFalse($this->buildWarmer($cache, false)->refreshNow(3600));
        $this->assertCount(2, $this->cliMultisCreated);
    }

    public function testAVisitThatCannotSpawnDoesNotHoldOffAPeriodicRefresh()
    {
        // the web server may forbid what the console can do
        $cache = new Lazy(new ArrayCache());
        $this->buildWarmer($cache, false)->refreshNow(3600);

        $this->buildWarmer($cache, true, 1234)->refreshPeriodically(14400);

        $this->assertCount(1, $this->commands);
    }

    public function testARefreshAfterInstallationWaitsForTheInstallerToSaveTheConfig()
    {
        $warmer = $this->buildWarmer();
        $warmer->refreshAfterInstallation();
        $warmer->refreshAfterInstallation();

        $command = "(sleep 10 && /usr/bin/php -q '" . PIWIK_INCLUDE_PATH . "/console' marketplace:warm-cache --if-older-than=0) > /dev/null 2>&1 &";
        $this->assertSame([$command, $command], $this->commands->getArrayCopy());
    }

    public function testARefreshAfterInstallationDoesNothingWhereProcessesCannotBeSpawned()
    {
        $this->buildWarmer(null, false)->refreshAfterInstallation();

        $this->assertCount(0, $this->commands);
    }

    public function testAPeriodicRefreshWaitsTheRandomDelayFirst()
    {
        $this->buildWarmer(null, true, 1234)->refreshPeriodically(14400);

        $this->assertSame(
            ["(sleep 1234 && /usr/bin/php -q '" . PIWIK_INCLUDE_PATH . "/console' marketplace:warm-cache --if-older-than=14400) > /dev/null 2>&1 &"],
            $this->commands->getArrayCopy()
        );
    }

    public function testAPeriodicRefreshPicksItsDelayWithinTheHour()
    {
        $this->buildWarmer(null, true, 1234)->refreshPeriodically(14400);

        $this->assertSame([3599], $this->maxDelays->getArrayCopy());
    }

    public function testARefreshAfterUpdatePicksItsDelayWithinTwoMinutes()
    {
        $this->buildWarmer(null, true, 45)->refreshAfterUpdate(3600);

        $this->assertSame([119], $this->maxDelays->getArrayCopy());
        $this->assertSame(
            ["(sleep 45 && /usr/bin/php -q '" . PIWIK_INCLUDE_PATH . "/console' marketplace:warm-cache --if-older-than=3600) > /dev/null 2>&1 &"],
            $this->commands->getArrayCopy()
        );
    }

    public function testSeveralUpdatesInARowShareOneRefresh()
    {
        $cache = new Lazy(new ArrayCache());

        $this->buildWarmer($cache, true, 45)->refreshAfterUpdate(3600);
        Date::$now = self::NOW + 60;
        $this->buildWarmer($cache, true, 45)->refreshAfterUpdate(3600);
        $this->buildWarmer($cache, true, 45)->refreshPeriodically(14400);

        $this->assertCount(1, $this->commands);
    }

    public function testAnUpdateDoesNotWaitForAPeriodicRefreshThatIsStillFarOff()
    {
        $cache = new Lazy(new ArrayCache());

        $this->buildWarmer($cache, true, 1234)->refreshPeriodically(14400);
        Date::$now = self::NOW + 10;
        $this->buildWarmer($cache, true, 45)->refreshAfterUpdate(3600);

        $this->assertCount(2, $this->commands);
        $this->assertStringContainsString('sleep 45 ', $this->commands[1]);

        // the update's refresh is the one now checked, long before the periodic one was due
        Date::$now = self::NOW + 10 + 45 + 300;
        $this->assertTrue($this->buildWarmer($cache)->claimFailedDelayedRefresh());
    }

    public function testAnUpdateSharesAPeriodicRefreshThatIsDueWithinItsOwnWindow()
    {
        $cache = new Lazy(new ArrayCache());

        $this->buildWarmer($cache, true, 60)->refreshPeriodically(14400);
        $this->buildWarmer($cache, true, 45)->refreshAfterUpdate(3600);

        $this->assertCount(1, $this->commands);
    }

    public function testAPeriodicRefreshIsNotSpawnedAgainWhileOneIsPending()
    {
        $cache = new Lazy(new ArrayCache());

        $this->assertTrue($this->buildWarmer($cache, true, 1234)->refreshPeriodically(14400));

        // the next hourly check, while the first refresh is still waiting or running
        Date::$now = self::NOW + 1234 + 299;
        $this->assertTrue($this->buildWarmer($cache, true, 1234)->refreshPeriodically(14400));
        $this->assertCount(1, $this->commands);

        Date::$now = self::NOW + 1234 + 300;
        $this->buildWarmer($cache, true, 1234)->refreshPeriodically(14400);
        $this->assertCount(2, $this->commands);
    }

    public function testADelayedRefreshThatNeverRanCountsAsFailedOnceAndIsThenSpawnedAgain()
    {
        $cache = new Lazy(new ArrayCache());
        $this->assertFalse($this->buildWarmer($cache)->claimFailedDelayedRefresh());

        $this->buildWarmer($cache, true, 1234)->refreshPeriodically(14400);
        $dueAt = self::NOW + 1234;

        Date::$now = $dueAt + 299;
        $this->assertFalse($this->buildWarmer($cache)->claimFailedDelayedRefresh());

        Date::$now = $dueAt + 300;
        $this->assertTrue($this->buildWarmer($cache)->claimFailedDelayedRefresh());
        $this->assertFalse($this->buildWarmer($cache)->claimFailedDelayedRefresh());

        $this->buildWarmer($cache, true, 1234)->refreshPeriodically(14400);
        $this->assertCount(2, $this->commands);
    }

    public function testADelayedRefreshThatRanIsNotCountedAsFailedWhateverItFound()
    {
        // it finds the lists already refreshed by a visit, or cannot reach the Marketplace
        $cache = new Lazy(new ArrayCache());
        $this->buildWarmer($cache, true, 1234)->refreshPeriodically(14400);

        Date::$now = self::NOW + 1234;
        $this->buildWarmer($cache)->recordRun();

        Date::$now += 86400;
        $this->assertFalse($this->buildWarmer($cache)->claimFailedDelayedRefresh());
    }

    public function testARunBeforeTheDelayedRefreshWasDueDoesNotCountForIt()
    {
        $cache = new Lazy(new ArrayCache());
        $this->buildWarmer($cache, true, 1234)->refreshPeriodically(14400);

        // an immediate refresh a visit spawned while the delayed one was still waiting
        Date::$now = self::NOW + 1233;
        $this->buildWarmer($cache)->recordRun();

        Date::$now = self::NOW + 1234 + 300;
        $this->assertTrue($this->buildWarmer($cache)->claimFailedDelayedRefresh());
    }

    public function testAPeriodicRefreshIsLeftToTheCallerWhereProcessesCannotBeSpawned()
    {
        $this->assertFalse($this->buildWarmer(null, false)->refreshPeriodically(14400));
        $this->assertCount(0, $this->commands);
    }

    private function buildWarmer(
        ?Lazy $cache = null,
        bool $supportsAsync = true,
        int $delay = 0,
        ?CliMulti $cliMulti = null,
        ?CliPhp $cliPhp = null
    ): BackgroundWarmer {
        if (null === $cliMulti) {
            $cliMulti = $this->createMock(CliMulti::class);
            // a mock skips the constructor, which is what sets this
            $cliMulti->supportsAsync = $supportsAsync;
        }

        if (null === $cliPhp) {
            $cliPhp = $this->createMock(CliPhp::class);
            $cliPhp->method('findPhpBinary')->willReturn('/usr/bin/php -q');
        }

        $cache = $cache ?? new Lazy(new ArrayCache());

        return new class (
            $cache,
            $cliPhp,
            $this->createMock(Scheduler::class),
            new NullLogger(),
            $cliMulti,
            $this->cliMultisCreated,
            $this->commands,
            $this->maxDelays,
            $delay
        ) extends BackgroundWarmer {
            private $markers;

            private $cliMulti;

            private $cliMultisCreated;

            private $commands;

            private $maxDelays;

            private $delay;

            public function __construct($cache, $cliPhp, $scheduler, $logger, CliMulti $cliMulti, ArrayObject $cliMultisCreated, ArrayObject $commands, ArrayObject $maxDelays, int $delay)
            {
                parent::__construct($cache, $cliPhp, $scheduler, $logger);
                // kept in the cache the warmers of one test share, standing in for the option table
                $this->markers = $cache;
                $this->cliMulti = $cliMulti;
                $this->cliMultisCreated = $cliMultisCreated;
                $this->commands = $commands;
                $this->maxDelays = $maxDelays;
                $this->delay = $delay;
            }

            protected function createCliMulti(): CliMulti
            {
                $this->cliMultisCreated[] = $this->cliMulti;

                return $this->cliMulti;
            }

            protected function getRandomDelaySeconds(int $maxSeconds): int
            {
                $this->maxDelays[] = $maxSeconds;

                return $this->delay;
            }

            protected function execute(string $command): void
            {
                $this->commands[] = $command;
            }

            protected function fetchMarker(string $name): ?int
            {
                $value = $this->markers->fetch('marker.' . $name);

                return false === $value ? null : $value;
            }

            protected function saveMarker(string $name, int $value): void
            {
                $this->markers->save('marker.' . $name, $value);
            }

            protected function deleteMarker(string $name): void
            {
                $this->markers->delete('marker.' . $name);
            }
        };
    }
}
