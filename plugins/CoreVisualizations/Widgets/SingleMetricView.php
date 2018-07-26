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
            'metric-translations' => json_encode($metricTranslations),
            'metric-documentations' => json_encode($report->getMetricDocumentationForReport()),
        ];

        return $view->render();
    }
}