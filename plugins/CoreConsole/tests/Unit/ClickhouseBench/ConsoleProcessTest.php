<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\tests\Unit\ClickhouseBench;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\CoreConsole\ClickhouseBench\ConsoleProcess;

/**
 * @group CoreConsole
 * @group ClickhouseBench
 * @group Plugins
 */
class ConsoleProcessTest extends TestCase
{
    /**
     * Every child needs --matomo-domain on a multi-instance host, and the ones that were easy
     * to forget were the preflight and calibration children rather than the measured ones.
     * A child without it does not fail: it resolves to no install and reports honestly that it
     * is not using the analytics database, which the preflight then reports as the operator
     * having misconfigured the engine. Found on a Cloud box; invisible on a single-instance
     * install, which is why it survived the first round of testing.
     */
    public function testGlobalOptionsAreSplicedAfterTheCommandName(): void
    {
        $process = $this->process(['--matomo-domain=example.org']);

        self::assertSame(
            ['clickhouse:benchmark', '--matomo-domain=example.org', '--report-engine'],
            $process->withGlobalOptions(['clickhouse:benchmark', '--report-engine'])
        );
    }

    /**
     * The option has to land before the '--' separator, or it becomes part of the URL query
     * argument that climulti:request takes rather than an option.
     */
    public function testGlobalOptionsLandBeforeTheArgumentSeparator(): void
    {
        $process = $this->process(['--matomo-domain=example.org']);

        self::assertSame(
            ['climulti:request', '--matomo-domain=example.org', '--superuser', '--', 'module=API'],
            $process->withGlobalOptions(['climulti:request', '--superuser', '--', 'module=API'])
        );
    }

    public function testNoGlobalOptionsLeavesArgumentsAlone(): void
    {
        $arguments = ['clickhouse:benchmark', '--report-engine'];

        self::assertSame($arguments, $this->process([])->withGlobalOptions($arguments));
    }

    public function testEmptyArgumentsAreNotGivenAnOptionToAttachTo(): void
    {
        self::assertSame([], $this->process(['--matomo-domain=example.org'])->withGlobalOptions([]));
    }

    public function testPhpCliOptionsAreRenderedForCoreArchiveForwarding(): void
    {
        $process = new ConsoleProcess('/tmp/console', PHP_BINARY, null, ['tideways.enable_cli=1', 'x=2']);

        self::assertSame('-d tideways.enable_cli=1 -d x=2', $process->getPhpCliOptionsString());
    }

    /**
     * @param string[] $globalOptions
     */
    private function process(array $globalOptions): ConsoleProcess
    {
        return new ConsoleProcess('/tmp/console', PHP_BINARY, null, [], $globalOptions);
    }
}
