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
use Piwik\Common;
use Piwik\Plugins\Monolog\Handler\EchoHandler;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Monolog
 * @group TrackerLogWritersTest
 */
class TrackerLogWritersTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        unset($GLOBALS['PIWIK_TRACKER_DEBUG']);
    }

    public function tearDown(): void
    {
        Common::$isCliMode = null;
        unset($GLOBALS['PIWIK_TRACKER_DEBUG']);

        parent::tearDown();
    }

    /**
     * @dataProvider getConfiguredWriters
     * @param string[] $writers
     */
    public function testTrackingRequestsDoNotUseTheScreenWriter(array $writers): void
    {
        $classes = $this->getTrackerLogHandlerClasses($writers, false);

        $this->assertArrayNotHasKey('screen', $classes);
    }

    /**
     * @return iterable<string, array{string[]}>
     */
    public function getConfiguredWriters(): iterable
    {
        yield 'screen only' => [['screen']];
        yield 'screen and file' => [['screen', 'file']];
    }

    public function testTrackerDebugModeEchoesTheScreenWriterOutput(): void
    {
        $classes = $this->getTrackerLogHandlerClasses(['screen'], true);

        $this->assertSame(EchoHandler::class, $classes['screen']);
    }

    /**
     * Builds a tracker environment per call, as the config file must be loadable more than once in a process.
     *
     * @param string[] $writers
     * @return array<string, string>
     */
    private function getTrackerLogHandlerClasses(array $writers, bool $isDebug): array
    {
        // a web request: in CLI the tracker adds the screen writer when debugging
        Common::$isCliMode = false;
        $environment = new Environment('tracker', [
            'ini.log.log_writers' => $writers,
            'ini.Tracker.debug' => $isDebug ? 1 : 0,
        ]);
        $environment->init();

        try {
            return $environment->getContainer()->get('log.handler.classes');
        } finally {
            $environment->destroy();
        }
    }
}
