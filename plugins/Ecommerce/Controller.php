<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce;

use Exception;
use Piwik\DataTable;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\View;

class Controller extends \Piwik\Plugins\Goals\Controller
{
    public function ecommerceReport()
    {
        if (!\Piwik\Plugin\Manager::getInstance()->isPluginActivated('CustomVariables')) {
            throw new Exception("Ecommerce Tracking requires that the plugin Custom Variables is enabled. Please enable the plugin CustomVariables (or ask your admin).");
        }

        $view = $this->getGoalReportView($idGoal = Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER);
        $view->displayFullReport = false;
        $view->headline = Piwik::translate('General_EvolutionOverPeriod');
        return $view->render();
    }

    public function getEcommerceLog($fetch = false)
    {
        $view = new View('@Ecommerce/ecommerceLog');
        $this->setGeneralVariablesView($view);

        $saveGET = $_GET;
        $_GET['segment'] = urlencode('visitEcommerceStatus!=none');
        $_GET['widget'] = 1;
        $view->ecommerceLog = FrontController::getInstance()->dispatch('Live', 'getVisitorLog', array($fetch));
        $_GET = $saveGET;

        return $view->render();
    }

    public function index()
    {
        return $this->ecommerceReport();
    }

    public function sales()
    {
        $viewOverview = $this->getGoalReportView(Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER);
        $reportsByDimension = $viewOverview->goalReportsByDimension;

        $view = new View('@Ecommerce/sales');
        $this->setGeneralVariablesView($view);

        $view->goalReportsByDimension = $reportsByDimension;
        return $view->render();
    }

}
