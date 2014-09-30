<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\Cache\LanguageAwareStaticCache;
use Piwik\Columns\Dimension;
use Piwik\DataTable;
use Piwik\Menu\MenuReporting;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Translate;
use Piwik\WidgetsList;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;
use Exception;

/**
 * Defines a new report. This class contains all information a report defines except the corresponding API method which
 * needs to be defined in the 'API.php'. You can define the name of the report, a documentation, the supported metrics,
 * how the report should be displayed, which features the report has (eg search) and much more.
 *
 * You can create a new report using the console command `./console generate:report`. The generated report will guide
 * you through the creation of a report.
 *
 * @since 2.5.0
 * @api
 */
class Report
{
    /**
     * The sub-namespace name in a plugin where Report components are stored.
     */
    const COMPONENT_SUBNAMESPACE = 'Reports';

    /**
     * When added to the menu, a given report eg 'getCampaigns'
     * will be routed as &action=menuGetCampaigns
     */
    const PREFIX_ACTION_IN_MENU = 'menu';

    /**
     * The name of the module which is supposed to be equal to the name of the plugin. The module is detected
     * automatically.
     * @var string
     */
    protected $module;

    /**
     * The name of the action. The action is detected automatically depending on the file name. A corresponding action
     * should exist in the API as well.
     * @var string
     */
    protected $action;

    /**
     * The translated name of the report. The name will be used for instance in the mobile app or if another report
     * defines this report as a related report.
     * @var string
     * @api
     */
    protected $name;

    /**
     * A translated documentation which explains the report.
     * @var string
     */
    protected $documentation;

    /**
     * The translation key of the category the report belongs to.
     * @var string
     * @api
     */
    protected $category;

    /**
     * The translation key of the widget title. If a widget title is set, the platform will automatically configure/add
     * a widget for this report. Alternatively, this behavior can be overwritten in {@link configureWidget()}.
     * @var string
     * @api
     */
    protected $widgetTitle;

    /**
     * Optional widget params that will be appended to the widget URL if a {@link $widgetTitle} is set.
     * @var array
     * @api
     */
    protected $widgetParams = array();

    /**
     * The translation key of the menu title. If a menu title is set, the platform will automatically add a menu item
     * to the reporting menu. Alternatively, this behavior can be overwritten in {@link configureReportingMenu()}.
     * @var string
     * @api
     */
    protected $menuTitle;

    /**
     * An array of supported metrics. Eg `array('nb_visits', 'nb_actions', ...)`. Defaults to the platform default
     * metrics see {@link Metrics::getDefaultProcessedMetrics()}.
     * @var array
     * @api
     */
    protected $metrics = array('nb_visits', 'nb_uniq_visitors', 'nb_actions', 'nb_users');
    // for a little performance improvement we avoid having to call Metrics::getDefaultMetrics for each report

    /**
     * The processed metrics this report supports, eg `avg_time_on_site` or `nb_actions_per_visit`. Defaults to the
     * platform default processed metrics, see {@link Metrics::getDefaultProcessedMetrics()}. Set it to boolean `false`
     * if your report does not support any processed metrics at all. Otherwise an array of metric names.
     * Eg `array('avg_time_on_site', 'nb_actions_per_visit', ...)`
     * @var array|false
     * @api
     */
    protected $processedMetrics = array('nb_actions_per_visit', 'avg_time_on_site', 'bounce_rate', 'conversion_rate');
    // for a little performance improvement we avoid having to call Metrics::getDefaultProcessedMetrics for each report

    /**
     * Set this property to true in case your report supports goal metrics. In this case, the goal metrics will be
     * automatically added to the report metadata and the report will be displayed in the Goals UI.
     * @var bool
     * @api
     */
    protected $hasGoalMetrics = false;

    /**
     * Set it to boolean `true` if your report always returns a constant count of rows, for instance always 24 rows
     * for 1-24 hours.
     * @var bool
     * @api
     */
    protected $constantRowsCount = false;

    /**
     * Set it to boolean `true` if this report is a subtable report and won't be used as a standalone report.
     * @var bool
     * @api
     */
    protected $isSubtableReport = false;

    /**
     * Some reports may require additonal URL parameters that need to be sent when a report is requested. For instance
     * a "goal" report might need a "goalId": `array('idgoal' => 5)`.
     * @var null|array
     * @api
     */
    protected $parameters = null;

    /**
     * An instance of a dimension if the report has one. You can create a new dimension using the Piwik console CLI tool
     * if needed.
     * @var \Piwik\Columns\Dimension
     */
    protected $dimension;

    /**
     * The name of the API action to load a subtable if supported. The action has to be of the same module. For instance
     * a report "getKeywords" might support a subtable "getSearchEngines" which shows how often a keyword was searched
     * via a specific search engine.
     * @var string
     * @api
     */
    protected $actionToLoadSubTables = '';

    /**
     * The order of the report. Depending on the order the report gets a different position in the list of widgets,
     * the menu and the mobile app.
     * @var int
     * @api
     */
    protected $order = 1;

    /**
     * @var array
     * @ignore
     */
    public static $orderOfReports = array(
        'General_MultiSitesSummary',
        'VisitsSummary_VisitsSummary',
        'Goals_Ecommerce',
        'General_Actions',
        'Events_Events',
        'Actions_SubmenuSitesearch',
        'Referrers_Referrers',
        'Goals_Goals',
        'General_Visitors',
        'DevicesDetection_DevicesDetection',
        'UserSettings_VisitorSettings',
    );

    /**
     * The constructur initializes the module, action and the default metrics. If you want to overwrite any of those
     * values or if you want to do any work during initializing overwrite the method {@link init()}.
     * @ignore
     */
    final public function __construct()
    {
        $classname    = get_class($this);
        $parts        = explode('\\', $classname);

        if (5 === count($parts)) {
            $this->module = $parts[2];
            $this->action = lcfirst($parts[4]);
        }

        $this->init();
    }

    /**
     * Here you can do any instance initialization and overwrite any default values. You should avoid doing time
     * consuming initialization here and if possible delay as long as possible. An instance of this report will be
     * created in most page requests.
     * @api
     */
    protected function init()
    {
    }

    /**
     * Defines whether a report is enabled or not. For instance some reports might not be available to every user or
     * might depend on a setting (such as Ecommerce) of a site. In such a case you can perform any checks and then
     * return `true` or `false`. If your report is only available to users having super user access you can do the
     * following: `return Piwik::hasUserSuperUserAccess();`
     * @return bool
     * @api
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * This method checks whether the report is available, see {@isEnabled()}. If not, it triggers an exception
     * containing a message that will be displayed to the user. You can overwrite this message in case you want to
     * customize the error message. Eg.
     * ```
     if (!$this->isEnabled()) {
         throw new Exception('Setting XYZ is not enabled or the user has not enough permission');
     }
     * ```
     * @throws \Exception
     * @api
     */
    public function checkIsEnabled()
    {
        if (!$this->isEnabled()) {
            throw new Exception(Piwik::translate('General_ExceptionReportNotEnabled'));
        }
    }

    /**
     * Returns the id of the default visualization for this report. Eg 'table' or 'pie'. Defaults to the HTML table.
     * @return string
     * @api
     */
    public function getDefaultTypeViewDataTable()
    {
        return HtmlTable::ID;
    }

    /**
     * Here you can configure how your report should be displayed and which capabilities your report has. For instance
     * whether your report supports a "search" or not. EG `$view->config->show_search = false`. You can also change the
     * default request config. For instance you can change how many rows are displayed by default:
     * `$view->requestConfig->filter_limit = 10;`. See {@link ViewDataTable} for more information.
     * @param ViewDataTable $view
     * @api
     */
    public function configureView(ViewDataTable $view)
    {

    }

    /**
     * Renders a report depending on the configured ViewDataTable see {@link configureView()} and
     * {@link getDefaultTypeViewDataTable()}. If you want to customize the render process or just render any custom view
     * you can overwrite this method.
     *
     * @return string
     * @throws \Exception In case the given API action does not exist yet.
     * @api
     */
    public function render()
    {
        $apiProxy = Proxy::getInstance();

        if (!$apiProxy->isExistingApiAction($this->module, $this->action)) {
            throw new Exception("Invalid action name '$this->action' for '$this->module' plugin.");
        }

        $apiAction = $apiProxy->buildApiActionName($this->module, $this->action);

        $view      = ViewDataTableFactory::build(null, $apiAction, $this->module . '.' . $this->action);
        $rendered  = $view->render();

        return $rendered;
    }

    /**
     * By default a widget will be configured for this report if a {@link $widgetTitle} is set. If you want to customize
     * the way the widget is added or modify any other behavior you can overwrite this method.
     * @param WidgetsList $widget
     * @api
     */
    public function configureWidget(WidgetsList $widget)
    {
        if ($this->widgetTitle) {
            $params = array();
            if (!empty($this->widgetParams) && is_array($this->widgetParams)) {
                $params = $this->widgetParams;
            }
            $widget->add($this->category, $this->widgetTitle, $this->module, $this->action, $params);
        }
    }

    /**
     * By default a menu item will be added to the reporting menu if a {@link $menuTitle} is set. If you want to
     * customize the way the item is added or modify any other behavior you can overwrite this method. For instance
     * in case you need to add additional url properties beside module and action which are added by default.
     * @param \Piwik\Menu\MenuReporting $menu
     * @api
     */
    public function configureReportingMenu(MenuReporting $menu)
    {
        if ($this->menuTitle) {
            $action = $this->getMenuControllerAction();
            if ($this->isEnabled()) {
                $menu->addItem($this->category,
                               $this->menuTitle,
                               array('module' => $this->module, 'action' => $action),
                               $this->order);
            }
        }
    }

    /**
     * Returns an array of supported metrics and their corresponding translations. Eg `array('nb_visits' => 'Visits')`.
     * By default the given {@link $metrics} are used and their corresponding translations are looked up automatically.
     * If a metric is not translated, you should add the default metric translation for this metric using
     * the {@hook Metrics.getDefaultMetricTranslations} event. If you want to overwrite any default metric translation
     * you should overwrite this method, call this parent method to get all default translations and overwrite any
     * custom metric translations.
     * @return array
     * @api
     */
    public function getMetrics()
    {
        return $this->getMetricTranslations($this->metrics);
    }

    /**
     * Returns an array of supported processed metrics and their corresponding translations. Eg
     * `array('nb_visits' => 'Visits')`. By default the given {@link $processedMetrics} are used and their
     * corresponding translations are looked up automatically. If a metric is not translated, you should add the
     * default metric translation for this metric using the {@hook Metrics.getDefaultMetricTranslations} event. If you
     * want to overwrite any default metric translation you should overwrite this method, call this parent method to
     * get all default translations and overwrite any custom metric translations.
     * @return array
     * @api
     */
    public function getProcessedMetrics()
    {
        if (!is_array($this->processedMetrics)) {
            return $this->processedMetrics;
        }

        return $this->getMetricTranslations($this->processedMetrics);
    }

    /**
     * Returns an array of metric documentations and their corresponding translations. Eg
     * `array('nb_visits' => 'If a visitor comes to your website for the first time or if he visits a page more than 30 minutes after...')`.
     * By default the given {@link $metrics} are used and their corresponding translations are looked up automatically.
     * If there is a metric documentation not found, you should add the default metric documentation translation for
     * this metric using the {@hook Metrics.getDefaultMetricDocumentationTranslations} event. If you want to overwrite
     * any default metric translation you should overwrite this method, call this parent method to get all default
     * translations and overwrite any custom metric translations.
     * @return array
     * @api
     */
    protected function getMetricsDocumentation()
    {
        $translations  = Metrics::getDefaultMetricsDocumentation();
        $documentation = array();

        foreach ($this->metrics as $metric) {
            if (!empty($translations[$metric])) {
                $documentation[$metric] = $translations[$metric];
            }
        }

        return $documentation;
    }

    /**
     * @return bool
     * @ignore
     */
    public function hasGoalMetrics()
    {
        return $this->hasGoalMetrics;
    }

    /**
     * If the report is enabled the report metadata for this report will be built and added to the list of available
     * reports. Overwrite this method and leave it empty in case you do not want your report to be added to the report
     * metadata. In this case your report won't be visible for instance in the mobile app and scheduled reports
     * generator. We recommend to change this behavior only if you are familiar with the Piwik core. `$infos` contains
     * the current requested date, period and site.
     * @param $availableReports
     * @param $infos
     * @api
     */
    public function configureReportMetadata(&$availableReports, $infos)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $report = $this->buildReportMetadata();

        if (!empty($report)) {
            $availableReports[] = $report;
        }
    }

    /**
     * Builts the report metadata for this report. Can be useful in case you want to change the behavior of
     * {@link configureReportMetadata()}.
     * @return array
     * @ignore
     */
    protected function buildReportMetadata()
    {
        $report = array(
            'category' => $this->getCategory(),
            'name'     => $this->getName(),
            'module'   => $this->getModule(),
            'action'   => $this->getAction()
        );

        if (null !== $this->parameters) {
            $report['parameters'] = $this->parameters;
        }

        if (!empty($this->dimension)) {
            $report['dimension'] = $this->dimension->getName();
        }

        if (!empty($this->documentation)) {
            $report['documentation'] = $this->documentation;
        }

        if (true === $this->isSubtableReport) {
            $report['isSubtableReport'] = $this->isSubtableReport;
        }

        $report['metrics']              = $this->getMetrics();
        $report['metricsDocumentation'] = $this->getMetricsDocumentation();
        $report['processedMetrics']     = $this->getProcessedMetrics();

        if (!empty($this->actionToLoadSubTables)) {
            $report['actionToLoadSubTables'] = $this->actionToLoadSubTables;
        }

        if (true === $this->constantRowsCount) {
            $report['constantRowsCount'] = $this->constantRowsCount;
        }

        $report['order'] = $this->order;

        return $report;
    }

    /**
     * Get the list of related reports if there are any. They will be displayed for instance below a report as a
     * recommended related report.
     *
     * @return Report[]
     * @api
     */
    public function getRelatedReports()
    {
        return array();
    }

    /**
     * Gets the translated widget title if one is defined.
     * @return string
     * @ignore
     */
    public function getWidgetTitle()
    {
        if ($this->widgetTitle) {
            return Piwik::translate($this->widgetTitle);
        }
    }

    /**
     * Get the name of the report
     * @return string
     * @ignore
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the name of the module.
     * @return string
     * @ignore
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Get the name of the action.
     * @return string
     * @ignore
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get the translated name of the category the report belongs to.
     * @return string
     * @ignore
     */
    public function getCategory()
    {
        return Piwik::translate($this->category);
    }

    /**
     * @return \Piwik\Columns\Dimension
     * @ignore
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * Returns the order of the report
     * @return int
     * @ignore
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get the menu title if one is defined.
     * @return string
     * @ignore
     */
    public function getMenuTitle()
    {
        return $this->menuTitle;
    }

    /**
     * Get the action to load sub tables if one is defined.
     * @return string
     * @ignore
     */
    public function getActionToLoadSubTables()
    {
        return $this->actionToLoadSubTables;
    }

    /**
     * Returns the Dimension instance of this report's subtable report.
     *
     * @return Dimension|null The subtable report's dimension or null if there is subtable report or
     *                        no dimension for the subtable report.
     * @api
     */
    public function getSubtableDimension()
    {
        if (empty($this->actionToLoadSubTables)) {
            return null;
        }

        list($subtableReportModule, $subtableReportAction) = $this->getSubtableApiMethod();

        $subtableReport = self::factory($subtableReportModule, $subtableReportAction);
        if (empty($subtableReport)) {
            return null;
        }

        return $subtableReport->getDimension();
    }

    /**
     * Returns true if the report is for another report's subtable, false if otherwise.
     *
     * @return bool
     */
    public function isSubtableReport()
    {
        return $this->isSubtableReport;
    }

    /**
     * Fetches the report represented by this instance.
     *
     * @param array $paramOverride Query parameter overrides.
     * @return DataTable
     * @api
     */
    public function fetch($paramOverride = array())
    {
        return Request::processRequest($this->module . '.' . $this->action, $paramOverride);
    }

    /**
     * Fetches a subtable for the report represented by this instance.
     *
     * @param int $idSubtable The subtable ID.
     * @param array $paramOverride Query parameter overrides.
     * @return DataTable
     * @api
     */
    public function fetchSubtable($idSubtable, $paramOverride = array())
    {
        $paramOverride = array('idSubtable' => $idSubtable) + $paramOverride;

        list($module, $action) = $this->getSubtableApiMethod();
        return Request::processRequest($module . '.' . $action, $paramOverride);
    }

    /**
     * Get an instance of a specific report belonging to the given module and having the given action.
     * @param  string $module
     * @param  string $action
     * @return null|\Piwik\Plugin\Report
     * @api
     */
    public static function factory($module, $action)
    {
        return ComponentFactory::factory($module, ucfirst($action), __CLASS__);
    }

    /**
     * Returns a list of all available reports. Even not enabled reports will be returned. They will be already sorted
     * depending on the order and category of the report.
     * @return \Piwik\Plugin\Report[]
     * @api
     */
    public static function getAllReports()
    {
        $reports = PluginManager::getInstance()->findMultipleComponents('Reports', '\\Piwik\\Plugin\\Report');
        $cache   = new LanguageAwareStaticCache('Reports' . implode('', $reports));

        if (!$cache->has()) {
            $instances = array();

            foreach ($reports as $report) {
                $instances[] = new $report();
            }

            usort($instances, array('self', 'sort'));

            $cache->set($instances);
        }

        return $cache->get();
    }

    /**
     * API metadata are sorted by category/name,
     * with a little tweak to replicate the standard Piwik category ordering
     *
     * @param Report $a
     * @param Report $b
     * @return int
     */
    private static function sort($a, $b)
    {
        return ($category = strcmp(array_search($a->category, self::$orderOfReports), array_search($b->category, self::$orderOfReports))) == 0
            ? ($a->order < $b->order ? -1 : 1)
            : $category;
    }

    private function getMetricTranslations($metricsToTranslate)
    {
        $translations = Metrics::getDefaultMetricTranslations();
        $metrics = array();

        foreach ($metricsToTranslate as $metric) {
            if (!empty($translations[$metric])) {
                $metrics[$metric] = $translations[$metric];
            } else {
                $metrics[$metric] = $metric;
            }
        }

        return $metrics;
    }

    private function getMenuControllerAction()
    {
        return self::PREFIX_ACTION_IN_MENU . ucfirst($this->action);
    }

    private function getSubtableApiMethod()
    {
        if (strpos($this->actionToLoadSubTables, '.') !== false) {
            return explode('.', $this->actionToLoadSubTables);
        } else {
            return array($this->module, $this->actionToLoadSubTables);
        }
    }

    /**
     * Finds a top level report that provides stats for a specific Dimension.
     *
     * @param Dimension $dimension The dimension whose report we're looking for.
     * @return Report|null The
     * @api
     */
    public static function getForDimension(Dimension $dimension)
    {
        return ComponentFactory::getComponentif (__CLASS__, $dimension->getModule(), function (Report $report) use ($dimension) {
            return !$report->isSubtableReport()
                && $report->getDimension()
                && $report->getDimension()->getId() == $dimension->getId();
        });
    }
}
