<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\Insights\Visualizations\Insight;
use Piwik\View;

/**
 * Insights Controller
 */
class Controller extends \Piwik\Plugin\Controller
{

    public function __construct()
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');

        Piwik::checkUserHasViewAccess($idSite);
    }

    public function getInsightsOverview()
    {
        $view = $this->prepareWidgetView('insightsOverviewWidget.twig');
        $view->reports = $this->requestApiReport('getInsightsOverview');

        return $view->render();
    }

    public function getOverallMoversAndShakers()
    {
        $view = $this->prepareWidgetView('moversAndShakersOverviewWidget.twig');
        $view->reports = $this->requestApiReport('getMoversAndShakersOverview');

        return $view->render();
    }

    private function prepareWidgetView($template)
    {
        if (!$this->canGenerateInsights()) {

            $view = new View('@Insights/cannotDisplayReport.twig');
            $this->setBasicVariablesView($view);
            return $view;
        }

        $view = new View('@Insights/' . $template);
        $this->setBasicVariablesView($view);

        $view->properties = array(
            'order_by' => InsightReport::ORDER_BY_ABSOLUTE
        );

        return $view;
    }

    private function requestApiReport($apiReport)
    {
        if (!$this->canGenerateInsights()) {
            return;
        }

        $idSite  = Common::getRequestVar('idSite', null, 'int');
        $period  = Common::getRequestVar('period', null, 'string');
        $date    = Common::getRequestVar('date', null, 'string');
        $segment = Request::getRawSegmentFromRequest();

        return API::getInstance()->$apiReport($idSite, $period, $date, $segment);
    }

    private function canGenerateInsights()
    {
        $period = Common::getRequestVar('period', null, 'string');
        $date   = Common::getRequestVar('date', null, 'string');

        return API::getInstance()->canGenerateInsights($date, $period);
    }
}
