<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Widgets;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\View;
use Piwik\Widget\WidgetConfig;
use Piwik\Plugin\Manager as PluginManager;

class SingleMetricView extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        parent::configure($config);

        $column = Common::getRequestVar('column', '', 'string');

        $config->addParameters(['column' => $column]);
        $config->setCategoryId('General_KpiMetric');
        $config->setName('General_KpiMetric');
        $config->setIsWidgetizable();
    }

    public function render()
    {
        $column = Common::getRequestVar('column', 'nb_visits', 'string');

        $goalMetrics = [];
        $goals = [];

        $idSite = Common::getRequestVar('idSite');
        $idGoal = Common::getRequestVar('idGoal', false);

        $reportMetadata = Request::processRequest('API.getMetadata', [
            'idSites' => $idSite,
            'apiModule' => 'API',
            'apiAction' => 'get',
        ]);
        $reportMetadata = reset($reportMetadata);

        $metricTranslations = array_merge($reportMetadata['metrics'], $reportMetadata['processedMetrics']);
        $metricDocumentations = $reportMetadata['metricsDocumentation'];

        if (PluginManager::getInstance()->isPluginActivated('Goals')) {
            $reportMetadata = Request::processRequest('API.getMetadata', [
                'idSites' => $idSite,
                'apiModule' => 'Goals',
                'apiAction' => 'get',
            ]);
            $reportMetadata = reset($reportMetadata);

            $goalMetrics = array_merge(
                array_keys($reportMetadata['metrics']),
                array_keys($reportMetadata['processedMetrics'])
            );
            $metricDocumentations = array_merge($metricDocumentations, $reportMetadata['metricsDocumentation']);

            $goals = Request::processRequest('Goals.getGoals', ['idSite' => $idSite, 'filter_limit' => '-1'], $default = []);
        }

        $view = new View("@CoreHome/_angularComponent.twig");
        $view->componentName = 'piwik-single-metric-view';
        $view->componentParameters = [
            'metric' => json_encode($column),
            'id-goal' => $idGoal === false ? 'undefined' : $idGoal,
            'goal-metrics' => json_encode($goalMetrics),
            'goals' => json_encode($goals),
            'metric-translations' => json_encode($metricTranslations),
            'metric-documentations' => json_encode($metricDocumentations),
        ];

        return $view->render();
    }
}