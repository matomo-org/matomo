<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome;

use Exception;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable\Renderer\Json;
use Piwik\Date;
use Piwik\FrontController;
use Piwik\Notification\Manager as NotificationManager;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugins\Marketplace\Marketplace;
use Piwik\SettingsPiwik;
use Piwik\Widget\Widget;
use Piwik\Plugins\CoreHome\DataTableRowAction\MultiRowEvolution;
use Piwik\Plugins\CoreHome\DataTableRowAction\RowEvolution;
use Piwik\Plugins\Dashboard\DashboardManagerControl;
use Piwik\Plugins\UsersManager\API;
use Piwik\Translation\Translator;
use Piwik\UpdateCheck;
use Piwik\Url;
use Piwik\View;
use Piwik\ViewDataTable\Manager as ViewDataTableManager;
use Piwik\Widget\WidgetConfig;

class Controller extends \Piwik\Plugin\Controller
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

        parent::__construct();
    }

    public function getDefaultAction()
    {
        return 'redirectToCoreHomeIndex';
    }

    public function renderReportWidget(Report $report)
    {
        Piwik::checkUserHasSomeViewAccess();
        $this->checkSitePermission();

        $report->checkIsEnabled();

        return $report->render();
    }

    /**
     * This is only used for exported widgets
     * @return string
     * @throws Exception
     * @throws \Piwik\NoAccessException
     */
    public function renderWidgetContainer()
    {
        Piwik::checkUserHasSomeViewAccess();
        $this->checkSitePermission();

        $view = new View('@CoreHome/widgetContainer');
        $view->isWidgetized = (bool) Common::getRequestVar('widget', 0, 'int');
        $view->containerId  = Common::getRequestVar('containerId', null, 'string');

        return $view->render();
    }

    /**
     * @param Widget $widget
     * @return mixed
     * @throws Exception
     */
    public function renderWidget($widget)
    {
        Piwik::checkUserHasSomeViewAccess();

        $config = new WidgetConfig();
        $widget::configure($config);

        $content = $widget->render();

        if ($config->getName() && Common::getRequestVar('showtitle', '', 'string') === '1') {
            if (strpos($content, '<h2') !== false
                || strpos($content, ' content-title=') !== false
                || strpos($content, ' piwik-enriched-headline') !== false
                || strpos($content, '<h1') !== false ) {
                // already includes title
                return $content;
            }

            if (strpos($content, 'piwik-content-block') === false
                && strpos($content, 'class="card"') === false
                && strpos($content, "class='card'") === false
                && strpos($content, 'class="card-content"') === false
                && strpos($content, "class='card-content'") === false) {
                $view = new View('@CoreHome/_singleWidget');
                $view->title = $config->getName();
                $view->content = $content;
                return $view->render();
            }
        }

        return $content;
    }

    function redirectToCoreHomeIndex()
    {
        $defaultReport = API::getInstance()->getUserPreference(
            API::PREFERENCE_DEFAULT_REPORT,
            Piwik::getCurrentUserLogin()
        );
        $module = 'CoreHome';
        $action = 'index';

        // User preference: default report to load is the All Websites dashboard
        if ($defaultReport == 'MultiSites'
            && \Piwik\Plugin\Manager::getInstance()->isPluginActivated('MultiSites')
        ) {
            $module = 'MultiSites';
        }

        if ($defaultReport == Piwik::getLoginPluginName()) {
            $module = Piwik::getLoginPluginName();
        }

        parent::redirectToIndex($module, $action, $this->idSite);
    }

    public function showInContext()
    {
        $controllerName = Common::getRequestVar('moduleToLoad');
        $actionName     = Common::getRequestVar('actionToLoad', 'index');

        if($controllerName == 'API') {
            throw new Exception("Showing API requests in context is not supported for security reasons. Please change query parameter 'moduleToLoad'.");
        }
        if ($actionName == 'showInContext') {
            throw new Exception("Preventing infinite recursion...");
        }

        $view = $this->getDefaultIndexView();
        $view->content = FrontController::getInstance()->fetchDispatch($controllerName, $actionName);
        return $view->render();
    }

    public function markNotificationAsRead()
    {
        Piwik::checkUserHasSomeViewAccess();
        $this->checkTokenInUrl();

        $notificationId = Common::getRequestVar('notificationId');
        NotificationManager::cancel($notificationId);

        Json::sendHeaderJSON();
        return json_encode(true);
    }

    protected function getDefaultIndexView()
    {
        if (SettingsPiwik::isInternetEnabled() && Marketplace::isMarketplaceEnabled()) {
            $this->securityPolicy->addPolicy('img-src', '*.matomo.org');
            $this->securityPolicy->addPolicy('default-src', '*.matomo.org');
        }

        $view = new View('@CoreHome/getDefaultIndexView');
        $this->setGeneralVariablesView($view);
        $view->showMenu = true;
        $view->dashboardSettingsControl = new DashboardManagerControl();
        $view->content = '';
        return $view;
    }

    protected function setDateTodayIfWebsiteCreatedToday()
    {
        $date = Common::getRequestVar('date', false);
        if ($date == 'today'
            || Common::getRequestVar('period', false) == 'range'
        ) {
            return;
        }

        if ($this->site) {
            $datetimeCreationDate      = $this->site->getCreationDate()->getDatetime();
            $creationDateLocalTimezone = Date::factory($datetimeCreationDate, $this->site->getTimezone())->toString('Y-m-d');
            $todayLocalTimezone        = Date::factory('now', $this->site->getTimezone())->toString('Y-m-d');

            if ($creationDateLocalTimezone == $todayLocalTimezone) {
                Piwik::redirectToModule('CoreHome', 'index',
                    array('date'   => 'today',
                          'idSite' => $this->idSite,
                          'period' => Common::getRequestVar('period'))
                );
            }
        }
    }

    public function index()
    {
        $this->setDateTodayIfWebsiteCreatedToday();
        $view = $this->getDefaultIndexView();
        return $view->render();
    }

    //  --------------------------------------------------------
    //  ROW EVOLUTION
    //  The following methods render the popover that shows the
    //  evolution of a single or multiple rows in a data table
    //  --------------------------------------------------------

    /** Render the entire row evolution popover for a single row */
    public function getRowEvolutionPopover()
    {
        $rowEvolution = $this->makeRowEvolution($isMulti = false);
        $view = new View('@CoreHome/getRowEvolutionPopover');
        return $rowEvolution->renderPopover($this, $view);
    }

    /** Render the entire row evolution popover for multiple rows */
    public function getMultiRowEvolutionPopover()
    {
        $rowEvolution = $this->makeRowEvolution($isMulti = true);
        $view = new View('@CoreHome/getMultiRowEvolutionPopover');
        return $rowEvolution->renderPopover($this, $view);
    }

    /** Generic method to get an evolution graph or a sparkline for the row evolution popover */
    public function getRowEvolutionGraph($fetch = false, $rowEvolution = null)
    {
        if (empty($rowEvolution)) {
            $label = Common::getRequestVar('label', '', 'string');
            $isMultiRowEvolution = strpos($label, ',') !== false;

            $rowEvolution = $this->makeRowEvolution($isMultiRowEvolution, $graphType = 'graphEvolution');
            $rowEvolution->useAvailableMetrics();
        }

        $view = $rowEvolution->getRowEvolutionGraph();
        return $this->renderView($view);
    }

    /** Utility function. Creates a RowEvolution instance. */
    private function makeRowEvolution($isMultiRowEvolution, $graphType = null)
    {
        if ($isMultiRowEvolution) {
            return new MultiRowEvolution($this->idSite, $this->date, $graphType);
        } else {
            return new RowEvolution($this->idSite, $this->date, $graphType);
        }
    }

    /**
     * Forces a check for updates and re-renders the header message.
     *
     * This will check piwik.org at most once per 10s.
     */
    public function checkForUpdates()
    {
        Piwik::checkUserHasSomeAdminAccess();
        $this->checkTokenInUrl();

        // perform check (but only once every 10s)
        UpdateCheck::check($force = false, UpdateCheck::UI_CLICK_CHECK_INTERVAL);

        $view = new View('@CoreHome/checkForUpdates');
        $this->setGeneralVariablesView($view);
        return $view->render();
    }

    /**
     * Redirects the user to a paypal so they can donate to Piwik.
     */
    public function redirectToPaypal()
    {
        $parameters = Request::getRequestArrayFromString($request = null);
        foreach ($parameters as $name => $param) {
            if ($name == 'idSite'
                || $name == 'module'
                || $name == 'action'
            ) {
                unset($parameters[$name]);
            }
        }
        $paypalParameters = [
            "cmd" => "_s-xclick"
        ];
        if (empty($parameters["onetime"]) || $parameters["onetime"] != "true") {
            $paypalParameters["hosted_button_id"] = "DVKLY73RS7JTE";
            $paypalParameters["currency_code"] = "USD";
            $paypalParameters["on0"] = "Piwik Supporter";
            if (!empty($parameters["os0"])) {
                $paypalParameters["os0"] = $parameters["os0"];
            }
        } else {
            $paypalParameters["hosted_button_id"] = "RPL23NJURMTFA";
        }

        $url = "https://www.paypal.com/cgi-bin/webscr?" . Url::getQueryStringFromParameters($paypalParameters);

        Url::redirectToUrl($url);
        exit;
    }

    public function saveViewDataTableParameters()
    {
        Piwik::checkUserIsNotAnonymous();
        $this->checkTokenInUrl();

        $reportId   = Common::getRequestVar('report_id', null, 'string');
        $parameters = (array) Common::getRequestVar('parameters', null, 'json');
        $login      = Piwik::getCurrentUserLogin();
        $containerId = Common::getRequestVar('containerId', '', 'string');

        ViewDataTableManager::saveViewDataTableParameters($login, $reportId, $parameters, $containerId);
    }

}
