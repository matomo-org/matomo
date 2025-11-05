<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents\tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\AIAgents\Providers\NovaAct;
use Piwik\Tracker\Request;

/**
 * @group AIAgents
 * @group AIAgentsProviders
 * @group Plugins
 */
class NovaActTest extends TestCase
{
    /**
     * @var NovaAct
     */
    private $novaAct;

    public function setUp(): void
    {
        parent::setUp();

        $this->novaAct = NovaAct::getInstance();
    }

    /**
     * @dataProvider getIsDetectedForTrackerRequestTestData
     */
    public function testIsDetectedForTrackerRequestThroughUserAgent(
        ?string $userAgent,
        bool $expectDetection
    ): void {
        $request  = new Request(['ua' => $userAgent]);
        $actual   = $this->novaAct->isDetectedForTrackerRequest($request);

        self::assertSame($expectDetection, $actual);
    }

    public function getIsDetectedForTrackerRequestTestData(): iterable
    {
        yield 'random useragent' => [
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.16 Safari/537.36',
            false,
        ];

        yield 'no real NovaAct Agent' => [
            'Mozilla/5.0 (X11; Linux x86_64) NovaActing AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.16 Safari/537.36',
            false,
        ];

        yield 'NovaAct Agent' => [
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.16 Safari/537.36 Agent-NovaAct/0.9',
            true,
        ];
    }
}
