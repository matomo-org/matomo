<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Monolog\tests\Integration;

use Piwik\Application\Environment;
use Piwik\Plugins\Monolog\Handler\ErrorLogHandler;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Monolog
 * @group TrackerLogWritersTest
 */
class TrackerLogWritersTest extends IntegrationTestCase
{
    public function testTrackingRequestsLogToPhpErrorLogIfOnlyTheScreenWriterIsConfigured(): void
    {
        $classes = $this->getTrackerLogHandlerClasses(['screen']);

        $this->assertSame(ErrorLogHandler::class, $classes['screen']);
    }

    /**
     * @dataProvider getWritersWithoutFallback
     * @param string[] $writers
     */
    public function testTrackingRequestsOnlyUseTheConfiguredWritersIfAnotherWriterIsConfigured(array $writers): void
    {
        $classes = $this->getTrackerLogHandlerClasses($writers);

        $this->assertArrayNotHasKey('screen', $classes);
    }

    /**
     * @return iterable<string, array{string[]}>
     */
    public function getWritersWithoutFallback(): iterable
    {
        yield 'screen and file' => [['screen', 'file']];
        yield 'file only' => [['file']];
        yield 'none' => [[]];
    }

    /**
     * @param string[] $writers
     * @return array<string, string>
     */
    private function getTrackerLogHandlerClasses(array $writers): array
    {
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
