<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Contents\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Contents\Reports\GetContentNames;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\ViewDataTable\RequestConfig;

class ReportConfigurationTest extends TestCase
{
    public function testContentMetricsAreSelectableInTableAndEvolutionViews(): void
    {
        $_GET['period'] = 'day';
        $report = new GetContentNames();

        foreach ([HtmlTable::class, Evolution::class] as $viewClass) {
            $view = $this->createMock(ViewDataTable::class);
            $view->config = $viewClass::getDefaultConfig();
            $view->requestConfig = new RequestConfig();

            $report->configureView($view);

            $this->assertSame(['nb_impressions', 'nb_interactions'], $view->config->selectable_columns);
        }
    }
}
