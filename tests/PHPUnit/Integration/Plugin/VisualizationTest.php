<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Plugin;

use Piwik\Piwik;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\ViewDataTable\Factory;

/**
 * @group Plugin
 * @group VisualizationTest
 */
class VisualizationTest extends IntegrationTestCase
{
    private int $noDataEvents = 0;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2014-01-01 00:00:00');

        Piwik::addAction('Visualization.onNoData', function () {
            ++$this->noDataEvents;
        });
    }

    public function tearDown(): void
    {
        $_GET = [];

        parent::tearDown();
    }

    public function testAnEmptyReportPostsTheNoDataEvent(): void
    {
        $html = $this->renderReport('browserCode==ff');

        self::assertStringNotContainsString('is not a supported segment', $html);
        self::assertSame(1, $this->noDataEvents);
    }

    public function testAReportThatFailedToLoadShowsTheErrorWithoutPostingTheNoDataEvent(): void
    {
        $html = $this->renderReport('notASegment==1');

        self::assertStringContainsString('is not a supported segment', $html);
        self::assertSame(0, $this->noDataEvents);
    }

    private function renderReport(string $segment): string
    {
        $_GET = [
            'idSite' => '1',
            'period' => 'day',
            'date'   => '2014-01-02',
            'segment' => $segment,
        ];

        $view = Factory::build('table', 'Referrers.getWebsites', 'Referrers.getWebsites', true);

        return $view->render();
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(true),
        ];
    }
}
