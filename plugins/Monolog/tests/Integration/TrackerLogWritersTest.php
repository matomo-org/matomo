<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Monolog\tests\Integration;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use Piwik\Application\Environment;
use Piwik\Common;
use Piwik\Plugins\Monolog\Handler\TrackerErrorLogHandler;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Monolog
 * @group TrackerLogWritersTest
 */
class TrackerLogWritersTest extends IntegrationTestCase
{
    public function tearDown(): void
    {
        Common::$isCliMode = null;

        parent::tearDown();
    }

    public function testWebTrackingRequestsLogErrorsToPhpErrorLogIfOnlyTheScreenWriterIsConfigured(): void
    {
        $classes = $this->getTrackerLogHandlerClasses(['screen'], false);

        $this->assertSame(TrackerErrorLogHandler::class, $classes['screen']);
    }

    /**
     * @dataProvider getConfigsWithoutFallback
     * @param string[] $writers
     */
    public function testTrackingRequestsOnlyUseTheConfiguredWritersOtherwise(array $writers, bool $isCli): void
    {
        $classes = $this->getTrackerLogHandlerClasses($writers, $isCli);

        $this->assertArrayNotHasKey('screen', $classes);
    }

    /**
     * @return iterable<string, array{string[], bool}>
     */
    public function getConfigsWithoutFallback(): iterable
    {
        yield 'screen and file' => [['screen', 'file'], false];
        yield 'file only' => [['file'], false];
        yield 'none' => [[], false];
        yield 'screen only in CLI' => [['screen'], true];
    }

    /**
     * @dataProvider getRecordsForFallback
     * @param array<string, mixed> $context
     */
    public function testFallbackOnlyWritesTheErrorsTheTrackerAnswersWith(Level $level, array $context, bool $expectedIsHandling): void
    {
        $handler = new TrackerErrorLogHandler();
        $record = new LogRecord(new DateTimeImmutable(), 'piwik', $level, 'message', $context);

        $this->assertSame($expectedIsHandling, $handler->isHandling($record));
    }

    /**
     * @return iterable<string, array{Level, array<string, mixed>, bool}>
     */
    public function getRecordsForFallback(): iterable
    {
        yield 'tracker error' => [Level::Error, ['trackerError' => true], true];
        yield 'critical tracker error' => [Level::Critical, ['trackerError' => true], true];
        yield 'tracker warning' => [Level::Warning, ['trackerError' => true], false];
        yield 'other error, eg with bound visitor data' => [Level::Error, [], false];
        yield 'tracker error ignored by the screen writer' => [Level::Error, ['trackerError' => true, 'ignoreInScreenWriter' => true], false];
    }

    /**
     * @param string[] $writers
     * @return array<string, string>
     */
    private function getTrackerLogHandlerClasses(array $writers, bool $isCli): array
    {
        Common::$isCliMode = $isCli;
        $environment = new Environment('tracker', [
            'ini.log.log_writers' => $writers,
            'ini.Tracker.debug' => 0,
        ]);
        $environment->init();

        try {
            return $environment->getContainer()->get('log.handler.classes');
        } finally {
            $environment->destroy();
        }
    }
}
