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
use Piwik\Cache;
use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Filter\Sort;
use Piwik\Metrics;
use Piwik\Cache as PiwikCache;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;
use Exception;
use Piwik\Widget\WidgetsList;
use Piwik\Report\ReportWidgetFactory;

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
    protected $categoryId;

    /**
     * The translation key of the subcategory the report belongs to.
     * @var string
     * @api
     */
    protected $subcategoryId;

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
     * @var array
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
     * Set this property to false in case your report can't/shouldn't be flattened.
     * In this case, flattener won't be applied even if parameter is provided in a request
     * @var bool
     * @api
     */
    protected $supportsFlatten = true;

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
     * Some reports may require additional URL parameters that need to be sent when a report is requested. For instance
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
     * Separator for building recursive labels (or paths)
     * @var string
     * @api
     */
    protected $recursiveLabelSeparator = ' - ';

    /**
     * Default sort column. Either a column name or a column id.
     *
     * @var string|int
     */
    protected $defaultSortColumn = 'nb_visits';

    /**
     * Default sort desc. If true will sort by default desc, if false will sort by default asc
     *
     * @var bool
     */
    protected $defaultSortOrderDesc = true;

    /**
     * The constructur initializes the module, action and the default metrics. If you want to overwrite any of those
     * values or if you want to do any work during initializing overwrite the method {@link init()}.
     * @ignore
     */
    final public function __construct()
    {
        $classname = get_class($this);
        $parts = explode('\\', $classname);

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
     * if (!$this->isEnabled()) {
     * throw new Exception('Setting XYZ is not enabled or the user has not enough permission');
     * }
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
     * Returns if the default viewDataTable type should always be used. e.g. the type won't be changeable through config or url params.
     * Defaults to false
     * @return bool
     */
    public function alwaysUseDefaultViewDataTable()
    {
        return false;
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
        $viewDataTable = Common::getRequestVar('viewDataTable', false, 'string');
        $fixed = Common::getRequestVar('forceView', 0, 'int');

        $module = $this->getModule();
        $action = $this->getAction();

        $apiProxy = Proxy::getInstance();

        if (!$apiProxy->isExistingApiAction($module, $action)) {
            throw new Exception("Invalid action name '$module' for '$action' plugin.");
        }

        $apiAction = $apiProxy->buildApiActionName($module, $action);

        $view = ViewDataTableFactory::build($viewDataTable, $apiAction, $module . '.' . $action, $fixed);

        return $view->render();
    }

    /**
     *
     * Processing a uniqueId for each report, can be used by UIs as a key to match a given report
     * @return string
     */
    public function getId()
    {
        $params = $this->getParameters();

        $paramsKey = $this->getModule() . '.' . $this->getAction();

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $paramsKey .= '_' . $key . '--' . $value;
            }
        }

        return $paramsKey;
    }

    /**
     * lets you add any amount of widgets for this report. If a report defines a {@link $categoryId} and a
     * {@link $subcategoryId} a widget will be generated automatically.
     *
     * Example to add a widget manually by overwriting this method in your report:
     * $widgetsList->addWidgetConfig($factory->createWidget());
     *
     * If you want to have the name and the order of the widget differently to the name and order of the report you can
     * do the following:
     * $widgetsList->addWidgetConfig($factory->createWidget()->setName('Custom')->setOrder(5));
     *
     * If you want to add a widget to any container defined by your plugin or by another plugin you can do
     * this:
     * $widgetsList->addToContainerWidget($containerId = 'Products', $factory->createWidget());
     *
     * @param WidgetsList $widgetsList
     * @param ReportWidgetFactory $factory
     * @api
     */
    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        if ($this->categoryId && $this->subcategoryId) {
            $widgetsList->addWidgetConfig($factory->createWidget());
        }
    }

    /**
     * @ignore
     * @see $recursiveLabelSeparator
     */
    public function getRecursiveLabelSeparator()
    {
        return $this->recursiveLabelSeparator;
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
     * Returns the list of metrics required at minimum for a report factoring in the columns requested by
     * the report requester.
     *
     * This will return all the metrics requested (or all the metrics in the report if nothing is requested)
     * **plus** the metrics required to calculate the requested processed metrics.
     *
     * This method should be used in **Plugin.get** API methods.
     *
     * @param string[]|null $allMetrics The list of all available unprocessed metrics. Defaults to this report's
     *                                  metrics.
     * @param string[]|null $restrictToColumns The requested columns.
     * @return string[]
     */
    public function getMetricsRequiredForReport($allMetrics = null, $restrictToColumns = null)
    {
        if (empty($allMetrics)) {
            $allMetrics = $this->metrics;
        }

        if (empty($restrictToColumns)) {
            $restrictToColumns = array_merge($allMetrics, array_keys($this->getProcessedMetrics()));
        }
        $restrictToColumns = array_unique($restrictToColumns);

        $processedMetricsById = $this->getProcessedMetricsById();
        $metricsSet = array_flip($allMetrics);

        $metrics = array();
        foreach ($restrictToColumns as $column) {
            if (isset($processedMetricsById[$column])) {
                $metrics = array_merge($metrics, $processedMetricsById[$column]->getDependentMetrics());
            } elseif (isset($metricsSet[$column])) {
                $metrics[] = $column;
            }
        }
        return array_unique($metrics);
    }

    /**
     * Returns an array of supported processed metrics and their corresponding translations. Eg
     * `array('nb_visits' => 'Visits')`. By default the given {@link $processedMetrics} are used and their
     * corresponding translations are looked up automatically. If a metric is not translated, you should add the
     * default metric translation for this metric using the {@hook Metrics.getDefaultMetricTranslations} event. If you
     * want to overwrite any default metric translation you should overwrite this method, call this parent method to
     * get all default translations and overwrite any custom metric translations.
     * @return array|mixed
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
     * Returns the array of all metrics displayed by this report.
     *
     * @return array
     * @api
     */
    public function getAllMetrics()
    {
        $processedMetrics = $this->getProcessedMetrics() ?: array();
        return array_keys(array_merge($this->getMetrics(), $processedMetrics));
    }

    /**
     * Use this method to register metrics to process report totals.
     *
     * When a metric is registered, it will process the report total values and as a result show percentage values
     * in the HTML Table reporting visualization.
     *
     * @return string[]  metricId => metricColumn, if the report has only column names and no IDs, it should return
     *                   metricColumn => metricColumn, eg array('13' => 'nb_pageviews') or array('mymetric' => 'mymetric')
     */
    public function getMetricNamesToProcessReportTotals()
    {
        return array();
    }

    /**
     * Returns an array of metric documentations and their corresponding translations. Eg
     * `array('nb_visits' => 'If a visitor comes to your website for the first time or if they visit a page more than 30 minutes after...')`.
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

        $processedMetrics = $this->processedMetrics ?: array();
        foreach ($processedMetrics as $processedMetric) {
            if (is_string($processedMetric) && !empty($translations[$processedMetric])) {
                $documentation[$processedMetric] = $translations[$processedMetric];
            } elseif ($processedMetric instanceof ProcessedMetric) {
                $name = $processedMetric->getName();
                $metricDocs = $processedMetric->getDocumentation();
                if (empty($metricDocs)) {
                    $metricDocs = @$translations[$name];
                }

                if (!empty($metricDocs)) {
                    $documentation[$processedMetric->getName()] = $metricDocs;
                }
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
     * @return bool
     * @ignore
     */
    public function supportsFlatten()
    {
        return $this->supportsFlatten;
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
     * Get report documentation.
     * @return string
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Builts the report metadata for this report. Can be useful in case you want to change the behavior of
     * {@link configureReportMetadata()}.
     * @return array
     * @ignore
     *
     * TODO we should move this out to API::getReportMetadata
     */
    protected function buildReportMetadata()
    {
        $report = array(
            'category' => $this->getCategoryId(),
            'subcategory' => $this->getSubcategoryId(),
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

        $relatedReports = $this->getRelatedReports();
        if (!empty($relatedReports)) {
            $report['relatedReports'] = array();
            foreach ($relatedReports as $relatedReport) {
                if (!empty($relatedReport)) {
                    $report['relatedReports'][] = array(
                        'name' => $relatedReport->getName(),
                        'module' => $relatedReport->getModule(),
                        'action' => $relatedReport->getAction()
                    );
                }
            }
        }

        $report['order'] = $this->order;

        return $report;
    }

    /**
     * @ignore
     */
    public function getDefaultSortColumn()
    {
        return $this->defaultSortColumn;
    }

    /**
     * @ignore
     */
    public function getDefaultSortOrder()
    {
        if ($this->defaultSortOrderDesc) {
            return Sort::ORDER_DESC;
        }

        return Sort::ORDER_ASC;
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

    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get the translated name of the category the report belongs to.
     * @return string
     * @ignore
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Get the translated name of the subcategory the report belongs to.
     * @return string
     * @ignore
     */
    public function getSubcategoryId()
    {
        return $this->subcategoryId;
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

        $subtableReport = ReportsProvider::factory($subtableReportModule, $subtableReportAction);
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

    private function getMetricTranslations($metricsToTranslate)
    {
        $translations = Metrics::getDefaultMetricTranslations();
        $metrics = array();

        foreach ($metricsToTranslate as $metric) {
            if ($metric instanceof Metric) {
                $metricName  = $metric->getName();
                $translation = $metric->getTranslatedName();
            } else {
                $metricName  = $metric;
                $translation = @$translations[$metric];
            }

            $metrics[$metricName] = $translation ?: $metricName;
        }

        return $metrics;
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
        return ComponentFactory::getComponentIf(__CLASS__, $dimension->getModule(), function (Report $report) use ($dimension) {
            return !$report->isSubtableReport()
                && $report->getDimension()
                && $report->getDimension()->getId() == $dimension->getId();
        });
    }

    /**
     * Returns an array mapping the ProcessedMetrics served by this report by their string names.
     *
     * @return ProcessedMetric[]
     */
    public function getProcessedMetricsById()
    {
        $processedMetrics = $this->processedMetrics ?: array();

        $result = array();
        foreach ($processedMetrics as $processedMetric) {
            if ($processedMetric instanceof ProcessedMetric || $processedMetric instanceof ArchivedMetric) { // instanceof check for backwards compatibility
                $result[$processedMetric->getName()] = $processedMetric;
            }
        }
        return $result;
    }

    /**
     * Returns the Metrics that are displayed by a DataTable of a certain Report type.
     *
     * Includes ProcessedMetrics and Metrics.
     *
     * @param DataTable $dataTable
     * @param Report|null $report
     * @param string $baseType The base type each metric class needs to be of.
     * @return Metric[]
     * @api
     */
    public static function getMetricsForTable(DataTable $dataTable, Report $report = null, $baseType = 'Piwik\\Plugin\\Metric')
    {
        $metrics = $dataTable->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: array();

        if (!empty($report)) {
            $metrics = array_merge($metrics, $report->getProcessedMetricsById());
        }

        $result = array();

        /** @var Metric $metric */
        foreach ($metrics as $metric) {
            if (!($metric instanceof $baseType)) {
                continue;
            }

            $result[$metric->getName()] = $metric;
        }

        return $result;
    }

    /**
     * Returns the ProcessedMetrics that should be computed and formatted for a DataTable of a
     * certain report. The ProcessedMetrics returned are those specified by the Report metadata
     * as well as the DataTable metadata.
     *
     * @param DataTable $dataTable
     * @param Report|null $report
     * @return ProcessedMetric[]
     * @api
     */
    public static function getProcessedMetricsForTable(DataTable $dataTable, Report $report = null)
    {
        return self::getMetricsForTable($dataTable, $report, 'Piwik\\Plugin\\ProcessedMetric');
    }
}
