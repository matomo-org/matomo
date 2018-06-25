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
use Piwik\Period\Range;
use Piwik\Period;
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

        $view = new View("@CoreHome/_angularComponent.twig");
        $view->componentName = 'piwik-single-metric-view';
        $view->componentParameters = [
            'metric' => json_encode($column),
            'sparkline-range' => json_encode($this->getSparklineRange()),
            'metric-translations' => json_encode($metricTranslations),
            'metric-documentations' => json_encode($report->getMetricDocumentationForReport()),
        ];

        list($lastPeriodDate, $ignore) = Range::getLastDate();
        if ($lastPeriodDate !== false) {
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