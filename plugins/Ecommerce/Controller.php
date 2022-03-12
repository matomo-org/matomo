<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\FrontController;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Live\Live;
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

    public function getSparklines()
    {
        $idGoal = Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER;

        $view = new View('@Ecommerce/getSparklines');
        $view->onlyConversionOverview = false;
        $view->conversionsOverViewEnabled = true;

        if ($idGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER) {
            $goalDefinition['name'] = $this->translator->translate('Goals_Ecommerce');
            $goalDefinition['allow_multiple'] = true;
        } else {
            $goals = Request::processRequest('Goals.getGoals', ['idSite' => $this->idSite, 'filter_limit' => '-1'], $default = []);
            if (!isset($goals[$idGoal])) {
                Piwik::redirectToModule('Goals', 'index', array('idGoal' => null));
            }
            $goalDefinition = $goals[$idGoal];
        }

        $this->setGeneralVariablesView($view);

        $goal = $this->getMetricsForGoal($idGoal);
        foreach ($goal as $name => $value) {
            $view->$name = $value;
        }

        if ($idGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER) {
            $goal = $this->getMetricsForGoal(Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART);
            foreach ($goal as $name => $value) {
                $name = 'cart_' . $name;
                $view->$name = $value;
            }
        }

        $view->idGoal = $idGoal;
        $view->goalAllowMultipleConversionsPerVisit = $goalDefinition['allow_multiple'];

        return $view->render();
    }

    public function getConversionsOverview()
    {
        $view = new View('@Ecommerce/conversionOverview');
        $idGoal = Common::getRequestVar('idGoal', null, 'string');
        $period = Common::getRequestVar('period', null, 'string');
        $segment = Common::getRequestVar('segment', '', 'string');
        $date = Common::getRequestVar('date', '', 'string');

        $goalMetrics = Request::processRequest('Goals.get', [
            'idGoal'       => $idGoal,
            'idSite'       => $this->idSite,
            'date'         => $date,
            'period'       => $period,
            'segment'      => Common::unsanitizeInputValue($segment),
            'filter_limit' => '-1'
        ], $default = []);

        $dataRow = $goalMetrics->getFirstRow();

        $view->visitorLogEnabled = Manager::getInstance()->isPluginActivated('Live') && Live::isVisitorLogEnabled($this->idSite);
        $view->idSite = $this->idSite;
        $view->idGoal = $idGoal;

        if ($dataRow) {
            $view->revenue          = $dataRow->getColumn('revenue');
            $view->revenue_subtotal = $dataRow->getColumn('revenue_subtotal');
            $view->revenue_tax      = $dataRow->getColumn('revenue_tax');
            $view->revenue_shipping = $dataRow->getColumn('revenue_shipping');
            $view->revenue_discount = $dataRow->getColumn('revenue_discount');
        }

        return $view->render();
    }

    public function getEcommerceLog($fetch = false)
    {
        $saveGET = $_GET;
        $originalQuery = $_SERVER['QUERY_STRING'];

        if (!empty($_GET['segment'])) {
            $_GET['segment'] = $_GET['segment'] . ';' . 'visitEcommerceStatus!=none';
        } else {
            $_GET['segment'] = 'visitEcommerceStatus!=none';
        }
        $_SERVER['QUERY_STRING'] = Http::buildQuery($_GET);

        $_GET['widget'] = 1;
        $output = FrontController::getInstance()->dispatch('Live', 'getVisitorLog', array($fetch));
        $_GET   = $saveGET;
        $_SERVER['QUERY_STRING'] = $originalQuery;

        return $output;
    }

}
