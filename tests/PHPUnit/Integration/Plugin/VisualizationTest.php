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
use Piwik\Plugin\Visualization;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
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

    /**
     * @dataProvider getComparedPeriods
     */
    public function testDefaultColumnWhenComparingADayWithAnotherPeriod(
        string $comparePeriod,
        string $compareDate,
        string $expectedColumn,
        string $missingColumn
    ): void {
        Fixture::createSuperUser(true);

        foreach (['2015-01-06 10:00:00', '2015-01-07 10:00:00', '2015-02-10 10:00:00'] as $dateTime) {
            $tracker = Fixture::getTracker(1, $dateTime);
            Fixture::checkResponse($tracker->doTrackPageView('/page'));
        }

        $_GET = [
            'idSite' => '1',
            'period' => 'day',
            'date'   => '2015-02-10',
            'comparePeriods' => [$comparePeriod],
            'compareDates'   => [$compareDate],
        ];

        /** @var Visualization $view */
        $view = Factory::build(
            HtmlTable::ID,
            'Referrers.getReferrerType',
            $controllerAction = 'Referrers.getReferrerType',
            $forceDefault = false,
            $loadViewDataTableParametersForUser = false
        );
        $html = $view->render();

        self::assertContains($expectedColumn, $view->config->columns_to_display);
        self::assertNotContains($missingColumn, $view->config->columns_to_display);
        self::assertStringContainsString('id="' . $expectedColumn . '"', $html);
        self::assertStringNotContainsString('id="' . $missingColumn . '"', $html);
    }

    public function getComparedPeriods(): iterable
    {
        yield 'month' => ['month', '2015-01-01', 'nb_visits', 'nb_uniq_visitors'];
        yield 'week' => ['week', '2015-01-05', 'nb_visits', 'nb_uniq_visitors'];
        yield 'range' => ['range', '2015-01-01,2015-01-10', 'nb_visits', 'nb_uniq_visitors'];
        yield 'day' => ['day', '2015-01-06', 'nb_uniq_visitors', 'nb_visits'];
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
