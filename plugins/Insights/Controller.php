<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\View;

/**
 * Insights Controller
 */
class Controller extends \Piwik\Plugin\Controller
{

    public function getInsightsOverview()
    {
        $view = $this->prepareWidget($apiReport = 'getInsightsOverview');

        return $view->render();
    }

    public function getOverallMoversAndShakers()
    {
        $view = $this->prepareWidget($apiReport = 'getOverallMoversAndShakers');

        return $view->render();
    }

    private function prepareWidget($apiReport)
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');
        $period = Common::getRequestVar('period', null, 'string');
        $date   = Common::getRequestVar('date', null, 'string');

        Piwik::checkUserHasViewAccess(array($idSite));

        $view = new View('@Insights/overviewWidget.twig');
        $this->setBasicVariablesView($view);

        $view->reports = API::getInstance()->$apiReport($idSite, $period, $date);
        $view->properties = array(
            'order_by' => 'absolute'
        );

        return $view;
    }
}
