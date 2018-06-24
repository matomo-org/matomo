<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Widgets;

use Piwik\Common;
use Piwik\DataTable\Filter\CalculateEvolutionFilter;
use Piwik\Period\Range;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ReportsProvider;
use Piwik\Site;
use Piwik\View;
use Piwik\Widget\WidgetConfig;

class SingleMetricView extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        parent::configure($config);

        $column = Common::getRequestVar('column', '', 'string');

        // TODO: adding the widget again causes all of them to reload...
        // TODO: widget preview doesn't seem to work
        $config->addParameters(['column' => $column]);
        $config->setCategoryId('General_Generic');
        $config->setName('General_Metric');
        $config->setIsWidgetizable();
        $config->setIsReusable();
    }

    public function render()
    {
        $column = Common::getRequestVar('column', 'nb_visits', 'string');

        $report = ReportsProvider::factory('API', 'get');

        $metricTranslations = $report->getMetrics();

        $currentData = $report->fetch();
        $metricValue = $currentData->getRowsCount() == 0 ? 0 : (int)$currentData->getFirstRow()->getColumn($column);

        $documentations = $report->getMetricDocumentationForReport();
        $metricDocumentation = $documentations[$column];

        $changePercent = null;

        list($lastPeriodDate, $ignore) = Range::getLastDate();
        if ($lastPeriodDate !== false) {
            $pastData = $report->fetch([
                'date' => $lastPeriodDate,
            ]);

            $pastValue = $pastData->getRowsCount() == 0 ? 0 : $pastData->getFirstRow()->getColumn($column);
            $changePercent = CalculateEvolutionFilter::calculate($metricValue, $pastValue, $precision = 1);
        }

        $view = new View("@CoreHome/_angularComponent.twig");
        $view->componentName = 'piwik-single-metric-view';
        $view->componentParameters = [
            'metric' => json_encode($column),
            'metric-name' => json_encode($metricTranslations[$column]),
            'sparkline-range' => json_encode($this->getSparklineRange()),
            'metric-value' => json_encode($metricValue),
            'metric-documentation' => json_encode($metricDocumentation),
        ];

        if (isset($changePercent)) {
            $view->componentParameters['metric-change-percent'] = json_encode($changePercent);
            $view->componentParameters['past-value'] = json_encode($pastValue);
            $view->componentParameters['past-period'] = json_encode($this->getPeriodStr($lastPeriodDate));
        }

        return $view->render();
    }

    private function getSparklineRange()
    {
        $period = Common::getRequestVar('period');
        $endDate = Common::getRequestVar('date', 'yesterday', 'string');
        $idSite = Common::getRequestVar('idSite');

        $sparklineRange = Range::getRelativeToEndDate($period, 'last30', $endDate, new Site($idSite));
        return $sparklineRange;
    }

    private function getPeriodStr($lastPeriodDate)
    {
        $period = Common::getRequestVar('period');
        $period = Period\Factory::build($period, $lastPeriodDate);
        return $period instanceof Period\Day ? $period->getDateStart()->toString() : $period->getRangeString();
    }
}