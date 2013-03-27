<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CoreHome
 */

/**
 *
 * @package Piwik_CoreHome
 */
class Piwik_CoreHome_Controller extends Piwik_Controller
{
    function getDefaultAction()
    {
        return 'redirectToCoreHomeIndex';
    }

    function redirectToCoreHomeIndex()
    {
        $defaultReport = Piwik_UsersManager_API::getInstance()->getUserPreference(Piwik::getCurrentUserLogin(), Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT);
        $module = 'CoreHome';
        $action = 'index';

        // User preference: default report to load is the All Websites dashboard
        if ($defaultReport == 'MultiSites'
            && Piwik_PluginsManager::getInstance()->isPluginActivated('MultiSites')
        ) {
            $module = 'MultiSites';
        }
        if ($defaultReport == Piwik::getLoginPluginName()) {
            $module = Piwik::getLoginPluginName();
        }
        $idSite = Piwik_Common::getRequestVar('idSite', false, 'int');

        parent::redirectToIndex($module, $action, !empty($idSite) ? $idSite : null);
    }

    public function showInContext()
    {
        $controllerName = Piwik_Common::getRequestVar('moduleToLoad');
        $actionName = Piwik_Common::getRequestVar('actionToLoad', 'index');
        if ($actionName == 'showInContext') {
            throw new Exception("Preventing infinite recursion...");
        }
        $view = $this->getDefaultIndexView();
        $view->content = Piwik_FrontController::getInstance()->fetchDispatch($controllerName, $actionName);
        echo $view->render();
    }

    protected function getDefaultIndexView()
    {
        $view = Piwik_View::factory('index');
        $this->setGeneralVariablesView($view);
        $view->menu = Piwik_GetMenu();
        $view->content = '';
        return $view;
    }

    protected function setDateTodayIfWebsiteCreatedToday()
    {
        $date = Piwik_Common::getRequestVar('date', false);
        if ($date == 'today'
            || Piwik_Common::getRequestVar('period', false) == 'range'
        ) {
            return;
        }
        $websiteId = Piwik_Common::getRequestVar('idSite', false, 'int');
        if ($websiteId) {
            $website = new Piwik_Site($websiteId);
            $datetimeCreationDate = $this->site->getCreationDate()->getDatetime();
            $creationDateLocalTimezone = Piwik_Date::factory($datetimeCreationDate, $website->getTimezone())->toString('Y-m-d');
            $todayLocalTimezone = Piwik_Date::factory('now', $website->getTimezone())->toString('Y-m-d');
            if ($creationDateLocalTimezone == $todayLocalTimezone) {
                Piwik::redirectToModule('CoreHome', 'index',
                    array('date'   => 'today',
                          'idSite' => $websiteId,
                          'period' => Piwik_Common::getRequestVar('period'))
                );
            }
        }
    }

    public function index()
    {
        $this->setDateTodayIfWebsiteCreatedToday();
        $view = $this->getDefaultIndexView();
        echo $view->render();
    }

    /*
     * This method is called when the asset manager is configured in merged mode.
     * It returns the content of the css merged file.
     *
     * @see core/AssetManager.php
     */
    public function getCss()
    {
        $cssMergedFile = Piwik_AssetManager::getMergedCssFileLocation();
        Piwik::serveStaticFile($cssMergedFile, "text/css");
    }

    /*
     * This method is called when the asset manager is configured in merged mode.
     * It returns the content of the js merged file.
     *
     * @see core/AssetManager.php
     */
    public function getJs()
    {
        $jsMergedFile = Piwik_AssetManager::getMergedJsFileLocation();
        Piwik::serveStaticFile($jsMergedFile, "application/javascript; charset=UTF-8");
    }


    //  --------------------------------------------------------
    //  ROW EVOLUTION
    //  The following methods render the popover that shows the
    //  evolution of a singe or multiple rows in a data table
    //  --------------------------------------------------------

    /**
     * This static cache is necessary because the signature cannot be modified
     * if the method renders a ViewDataTable. So we use it to pass information
     * to getRowEvolutionGraph()
     * @var Piwik_CoreHome_DataTableAction_Evolution
     */
    private static $rowEvolutionCache = null;

    /** Render the entire row evolution popover for a single row */
    public function getRowEvolutionPopover()
    {
        $rowEvolution = $this->makeRowEvolution($isMulti = false);
        self::$rowEvolutionCache = $rowEvolution;
        $view = Piwik_View::factory('popover_rowevolution');
        echo $rowEvolution->renderPopover($this, $view);
    }

    /** Render the entire row evolution popover for multiple rows */
    public function getMultiRowEvolutionPopover()
    {
        $rowEvolution = $this->makeRowEvolution($isMulti = true);
        self::$rowEvolutionCache = $rowEvolution;
        $view = Piwik_View::factory('popover_multirowevolution');
        echo $rowEvolution->renderPopover($this, $view);
    }

    /** Generic method to get an evolution graph or a sparkline for the row evolution popover */
    public function getRowEvolutionGraph($fetch = false)
    {
        $rowEvolution = self::$rowEvolutionCache;
        if ($rowEvolution === null) {
            $paramName = Piwik_CoreHome_DataTableRowAction_MultiRowEvolution::IS_MULTI_EVOLUTION_PARAM;
            $isMultiRowEvolution = Piwik_Common::getRequestVar($paramName, false, 'int');

            $rowEvolution = $this->makeRowEvolution($isMultiRowEvolution, $graphType = 'graphEvolution');
            $rowEvolution->useAvailableMetrics();
            self::$rowEvolutionCache = $rowEvolution;
        }

        $view = $rowEvolution->getRowEvolutionGraph();
        return $this->renderView($view, $fetch);
    }

    /** Utility function. Creates a RowEvolution instance. */
    private function makeRowEvolution($isMultiRowEvolution, $graphType = null)
    {
        if ($isMultiRowEvolution) {
            return new Piwik_CoreHome_DataTableRowAction_MultiRowEvolution($this->idSite, $this->date, $graphType);
        } else {
            return new Piwik_CoreHome_DataTableRowAction_RowEvolution($this->idSite, $this->date, $graphType);
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
        Piwik_UpdateCheck::check($force = false, Piwik_UpdateCheck::UI_CLICK_CHECK_INTERVAL);

        $view = Piwik_View::factory('header_message');
        $this->setGeneralVariablesView($view);
        echo $view->render();
    }

    /**
     * Renders and echo's the in-app donate form w/ slider.
     */
    public function getDonateForm()
    {
        $view = Piwik_View::factory('donate');
        if (Piwik_Common::getRequestVar('widget', false)
            && Piwik::isUserIsSuperUser()
        ) {
            $view->footerMessage = Piwik_Translate('CoreHome_OnlyForAdmin');
        }
        echo $view->render();
    }

    /**
     * Renders and echo's HTML that displays the Piwik promo video.
     */
    public function getPromoVideo()
    {
        $view = Piwik_View::factory('promo_video');
        $view->shareText = Piwik_Translate('CoreHome_SharePiwikShort');
        $view->shareTextLong = Piwik_Translate('CoreHome_SharePiwikLong');
        $view->promoVideoUrl = 'http://www.youtube.com/watch?v=OslfF_EH81g';
        echo $view->render();
    }
}
