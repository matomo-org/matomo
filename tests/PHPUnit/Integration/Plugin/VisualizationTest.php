<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Plugin;

use Piwik\Plugin\Visualization;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

/**
 * @group Plugin
 * @group VisualizationTest
 */
class VisualizationTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser(true);
        $idSite = Fixture::createWebsite('2015-01-01 00:00:00');

        foreach (['2015-01-06 10:00:00', '2015-01-07 10:00:00', '2015-02-10 10:00:00'] as $dateTime) {
            $tracker = Fixture::getTracker($idSite, $dateTime);
            Fixture::checkResponse($tracker->doTrackPageView('/page'));
        }

        $_GET['idSite'] = '1';
        $_GET['period'] = 'day';
        $_GET['date']   = '2015-02-10';
    }

    public function tearDown(): void
    {
        unset($_GET['idSite'], $_GET['period'], $_GET['date'], $_GET['comparePeriods'], $_GET['compareDates']);

        parent::tearDown();
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
        $_GET['comparePeriods'] = [$comparePeriod];
        $_GET['compareDates']   = [$compareDate];

        /** @var Visualization $view */
        $view = ViewDataTableFactory::build(
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
}
