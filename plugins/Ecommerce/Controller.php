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
use Piwik\Translation\Translator;
use Piwik\View;
use Piwik\Plugins\Goals\TranslationHelper;

class Controller extends \Piwik\Plugins\Goals\Controller
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator, TranslationHelper $translationHelper)
    {
        $this->translator = $translator;

        parent::__construct($translator, $translationHelper);
    }

    public function ecommerceReport()
    {
        if (!\Piwik\Plugin\Manager::getInstance()->isPluginActivated('CustomVariables')) {
            throw new Exception("Ecommerce Tracking requires that the plugin Custom Variables is enabled. Please enable the plugin CustomVariables (or ask your admin).");
        }

        $view = $this->getGoalReportView($idGoal = Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER);
        $view->displayFullReport = false;
        $view->headline = $this->translator->translate('General_EvolutionOverPeriod');

        return $view->render();
    }

    public function ecommerceLogReport($fetch = false)
    {
        $view = new View('@Ecommerce/ecommerceLog');
        $this->setGeneralVariablesView($view);

        $view->ecommerceLog = $this->getEcommerceLog($fetch);

        return $view->render();
    }

    public function getEcommerceLog($fetch = false)
    {
        $saveGET = $_GET;
        $_GET['segment'] = urlencode('visitEcommerceStatus!=none');
        $_GET['widget'] = 1;
        $output = FrontController::getInstance()->dispatch('Live', 'getVisitorLog', array($fetch));
        $_GET   = $saveGET;

        return $output;
    }

    public function index()
    {
        return $this->ecommerceReport();
    }

    public function products()
    {
        $goal = $this->getMetricsForGoal(Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER);
        $conversions = 0;
        if (!empty($goal['nb_conversions'])) {
            $conversions = $goal['nb_conversions'];
        }

        $goal = $this->getMetricsForGoal(Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART);

        $cartNbConversions = 0;
        if (!empty($goal) && array_key_exists('nb_conversions', $goal)) {
            $cartNbConversions = $goal['nb_conversions'];
        }

        $preloadAbandonedCart = $cartNbConversions !== false && $conversions == 0;

        $goalReportsByDimension = new View\ReportsByDimension('Goals');

        $ecommerceCustomParams = array();
        if ($preloadAbandonedCart) {
            $ecommerceCustomParams['abandonedCarts'] = '1';
        } else {
            $ecommerceCustomParams['abandonedCarts'] = '0';
        }

        $goalReportsByDimension->addReport(
            'Goals_Products', 'Goals_ProductSKU', 'Goals.getItemsSku', $ecommerceCustomParams);
        $goalReportsByDimension->addReport(
            'Goals_Products', 'Goals_ProductName', 'Goals.getItemsName', $ecommerceCustomParams);
        $goalReportsByDimension->addReport(
            'Goals_Products', 'Goals_ProductCategory', 'Goals.getItemsCategory', $ecommerceCustomParams);

        $view = new View('@Ecommerce/products');
        $this->setGeneralVariablesView($view);

        $view->productsByDimension = $goalReportsByDimension->render();
        return $view->render();
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
