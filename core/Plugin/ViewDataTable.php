<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\View;
use Piwik\View\ViewInterface;
use Piwik\ViewDataTable\Config as VizConfig;
use Piwik\ViewDataTable\Manager as ViewDataTableManager;
use Piwik\ViewDataTable\Request as ViewDataTableRequest;
use Piwik\ViewDataTable\RequestConfig as VizRequest;

/**
 * The base class of all report visualizations.
 *
 * ViewDataTable instances load analytics data via Piwik's Reporting API and then output some
 * type of visualization of that data.
 *
 * Visualizations can be in any format. HTML-based visualizations should extend
 * {@link Visualization}. Visualizations that use other formats, such as visualizations
 * that output an image, should extend ViewDataTable directly.
 *
 * ### Creating ViewDataTables
 *
 * ViewDataTable instances are not created via the new operator, instead the {@link Piwik\ViewDataTable\Factory}
 * class is used.
 *
 * The specific subclass to create is determined, first, by the **viewDataTable** query paramater.
 * If this parameter is not set, then the default visualization type for the report being
 * displayed is used.
 *
 * ### Configuring ViewDataTables
 *
 * **Display properties**
 *
 * ViewDataTable output can be customized by setting one of many available display
 * properties. Display properties are stored as fields in {@link Piwik\ViewDataTable\Config} objects.
 * ViewDataTables store a {@link Piwik\ViewDataTable\Config} object in the {@link $config} field.
 *
 * Display properties can be set at any time before rendering.
 *
 * **Request properties**
 *
 * Request properties are similar to display properties in the way they are set. They are,
 * however, not used to customize ViewDataTable instances, but in the request to Piwik's
 * API when loading analytics data.
 *
 * Request properties are set by setting the fields of a {@link Piwik\ViewDataTable\RequestConfig} object stored in
 * the {@link $requestConfig} field. They can be set at any time before rendering.
 * Setting them after data is loaded will have no effect.
 *
 * **Customizing how reports are displayed**
 *
 * Each individual report should be rendered in its own controller method. There are two
 * ways to render a report within its controller method. You can either:
 *
 * 1. manually create and configure a ViewDataTable instance
 * 2. invoke {@link Piwik\Plugin\Controller::renderReport} and configure the ViewDataTable instance
 *    in the {@hook ViewDataTable.configure} event.
 *
 * ViewDataTable instances are configured by setting and modifying display properties and request
 * properties.
 *
 * ### Creating new visualizations
 *
 * New visualizations can be created by extending the ViewDataTable class or one of its
 * descendants. To learn more [read our guide on creating new visualizations](/guides/visualizing-report-data#creating-new-visualizations).
 *
 * ### Examples
 *
 * **Manually configuring a ViewDataTable**
 *
 *     // a controller method that displays a single report
 *     public function myReport()
 *     {
 *         $view = \Piwik\ViewDataTable\Factory::build('table', 'MyPlugin.myReport');
 *         $view->config->show_limit_control = true;
 *         $view->config->translations['myFancyMetric'] = "My Fancy Metric";
 *         // ...
 *         return $view->render();
 *     }
 *
 * **Using {@link Piwik\Plugin\Controller::renderReport}**
 *
 * First, a controller method that displays a single report:
 *
 *     public function myReport()
 *     {
 *         return $this->renderReport(__FUNCTION__);`
 *     }
 *
 * Then the event handler for the {@hook ViewDataTable.configure} event:
 *
 *     public function configureViewDataTable(ViewDataTable $view)
 *     {
 *         switch ($view->requestConfig->apiMethodToRequestDataTable) {
 *             case 'MyPlugin.myReport':
 *                 $view->config->show_limit_control = true;
 *                 $view->config->translations['myFancyMetric'] = "My Fancy Metric";
 *                 // ...
 *                 break;
 *         }
 *     }
 *
 * **Using custom configuration objects in a new visualization**
 *
 *     class MyVisualizationConfig extends Piwik\ViewDataTable\Config
 *     {
 *         public $my_new_property = true;
 *     }
 *
 *     class MyVisualizationRequestConfig extends Piwik\ViewDataTable\RequestConfig
 *     {
 *         public $my_new_property = false;
 *     }
 *
 *     class MyVisualization extends Piwik\Plugin\ViewDataTable
 *     {
 *         public static function getDefaultConfig()
 *         {
 *             return new MyVisualizationConfig();
 *         }
 *
 *         public static function getDefaultRequestConfig()
 *         {
 *             return new MyVisualizationRequestConfig();
 *         }
 *     }
 *
 *
 * @api
 */
abstract class ViewDataTable implements ViewInterface
{
    const ID = '';

    /**
     * DataTable loaded from the API for this ViewDataTable.
     *
     * @var DataTable
     */
    protected $dataTable = null;

    /**
     * Contains display properties for this visualization.
     *
     * @var \Piwik\ViewDataTable\Config
     */
    public $config;

    /**
     * Contains request properties for this visualization.
     *
     * @var \Piwik\ViewDataTable\RequestConfig
     */
    public $requestConfig;

    /**
     * @var ViewDataTableRequest
     */
    protected $request;

    /**
     * Constructor. Initializes display and request properties to their default values.
     * Posts the {@hook ViewDataTable.configure} event which plugins can use to configure the
     * way reports are displayed.
     */
    public function __construct($controllerAction, $apiMethodToRequestDataTable, $overrideParams = array())
    {
        list($controllerName, $controllerAction) = explode('.', $controllerAction);

        $this->requestConfig = static::getDefaultRequestConfig();
        $this->config        = static::getDefaultConfig();
        $this->config->subtable_controller_action = $controllerAction;
        $this->config->setController($controllerName, $controllerAction);

        $this->request = new ViewDataTableRequest($this->requestConfig);

        $this->requestConfig->idSubtable = Common::getRequestVar('idSubtable', false, 'int');
        $this->config->self_url          = Request::getBaseReportUrl($controllerName, $controllerAction);

        $this->requestConfig->apiMethodToRequestDataTable = $apiMethodToRequestDataTable;

        $report = Report::factory($this->requestConfig->getApiModuleToRequest(), $this->requestConfig->getApiMethodToRequest());

        if (!empty($report)) {
            /** @var Report $report */
            $subtable = $report->getActionToLoadSubTables();
            if (!empty($subtable)) {
                $this->config->subtable_controller_action = $subtable;
            }

            $this->config->show_goals = $report->hasGoalMetrics();

            $relatedReports = $report->getRelatedReports();
            if (!empty($relatedReports)) {
                foreach ($relatedReports as $relatedReport) {
                    $widgetTitle = $relatedReport->getWidgetTitle();

                    if ($widgetTitle && Common::getRequestVar('widget', 0, 'int')) {
                        $relatedReportName = $widgetTitle;
                    } else {
                        $relatedReportName = $relatedReport->getName();
                    }

                    $this->config->addRelatedReport($relatedReport->getModule() . '.' . $relatedReport->getAction(),
                                                    $relatedReportName);
                }
            }

            $metrics = $report->getMetrics();
            if (!empty($metrics)) {
                $this->config->addTranslations($metrics);
            }

            $processedMetrics = $report->getProcessedMetrics();
            if (!empty($processedMetrics)) {
                $this->config->addTranslations($processedMetrics);
            }

            $report->configureView($this);
        }

        /**
         * Triggered during {@link ViewDataTable} construction. Subscribers should customize
         * the view based on the report that is being displayed.
         *
         * Plugins that define their own reports must subscribe to this event in order to
         * specify how the Piwik UI should display the report.
         *
         * **Example**
         *
         *     // event handler
         *     public function configureViewDataTable(ViewDataTable $view)
         *     {
         *         switch ($view->requestConfig->apiMethodToRequestDataTable) {
         *             case 'VisitTime.getVisitInformationPerServerTime':
         *                 $view->config->enable_sort = true;
         *                 $view->requestConfig->filter_limit = 10;
         *                 break;
         *         }
         *     }
         *
         * @param ViewDataTable $view The instance to configure.
         */
        Piwik::postEvent('ViewDataTable.configure', array($this));

        $this->assignRelatedReportsTitle();

        $this->config->show_footer_icons = (false == $this->requestConfig->idSubtable);

        // the exclude low population threshold value is sometimes obtained by requesting data.
        // to avoid issuing unecessary requests when display properties are determined by metadata,
        // we allow it to be a closure.
        if (isset($this->requestConfig->filter_excludelowpop_value)
            && $this->requestConfig->filter_excludelowpop_value instanceof \Closure
        ) {
            $function = $this->requestConfig->filter_excludelowpop_value;
            $this->requestConfig->filter_excludelowpop_value = $function();
        }

        $this->overrideViewPropertiesWithParams($overrideParams);
        $this->overrideViewPropertiesWithQueryParams();
    }

    protected function assignRelatedReportsTitle()
    {
        if (!empty($this->config->related_reports_title)) {
            // title already assigned by a plugin
            return;
        }
        if (count($this->config->related_reports) == 1) {
            $this->config->related_reports_title = Piwik::translate('General_RelatedReport') . ':';
        } else {
            $this->config->related_reports_title = Piwik::translate('General_RelatedReports') . ':';
        }
    }

    /**
     * Returns the default config instance.
     *
     * Visualizations that define their own display properties should override this method and
     * return an instance of their new {@link Piwik\ViewDataTable\Config} descendant.
     *
     * See the last example {@link ViewDataTable here} for more information.
     *
     * @return \Piwik\ViewDataTable\Config
     */
    public static function getDefaultConfig()
    {
        return new VizConfig();
    }

    /**
     * Returns the default request config instance.
     *
     * Visualizations that define their own request properties should override this method and
     * return an instance of their new {@link Piwik\ViewDataTable\RequestConfig} descendant.
     *
     * See the last example {@link ViewDataTable here} for more information.
     *
     * @return \Piwik\ViewDataTable\RequestConfig
     */
    public static function getDefaultRequestConfig()
    {
        return new VizRequest();
    }

    protected function loadDataTableFromAPI()
    {
        if (!is_null($this->dataTable)) {
            // data table is already there
            // this happens when setDataTable has been used
            return $this->dataTable;
        }

        $this->dataTable = $this->request->loadDataTableFromAPI();

        return $this->dataTable;
    }

    /**
     * Returns the viewDataTable ID for this DataTable visualization.
     *
     * Derived classes should not override this method. They should instead declare a const ID field
     * with the viewDataTable ID.
     *
     * @throws \Exception
     * @return string
     */
    public static function getViewDataTableId()
    {
        $id = static::ID;

        if (empty($id)) {
            $message = sprintf('ViewDataTable %s does not define an ID. Set the ID constant to fix this issue', get_called_class());
            throw new \Exception($message);
        }

       return $id;
    }

    /**
     * Returns `true` if this instance's or any of its ancestors' viewDataTable IDs equals the supplied ID,
     * `false` if otherwise.
     *
     * Can be used to test whether a ViewDataTable object is an instance of a certain visualization or not,
     * without having to know where that visualization is.
     *
     * @param  string $viewDataTableId The viewDataTable ID to check for, eg, `'table'`.
     * @return bool
     */
    public function isViewDataTableId($viewDataTableId)
    {
        $myIds = ViewDataTableManager::getIdsWithInheritance(get_called_class());

        return in_array($viewDataTableId, $myIds);
    }

    /**
     * Returns the DataTable loaded from the API.
     *
     * @return DataTable
     * @throws \Exception if not yet loaded.
     */
    public function getDataTable()
    {
        if (is_null($this->dataTable)) {
            throw new \Exception("The DataTable object has not yet been created");
        }

        return $this->dataTable;
    }

    /**
     * To prevent calling an API multiple times, the DataTable can be set directly.
     * It won't be loaded from the API in this case.
     *
     * @param DataTable $dataTable The DataTable to use.
     * @return void
     */
    public function setDataTable($dataTable)
    {
        $this->dataTable = $dataTable;
    }

    /**
     * Checks that the API returned a normal DataTable (as opposed to DataTable\Map)
     * @throws \Exception
     * @return void
     */
    protected function checkStandardDataTable()
    {
        Piwik::checkObjectTypeIs($this->dataTable, array('\Piwik\DataTable'));
    }

    /**
     * Requests all needed data and renders the view.
     *
     * @return string The result of rendering.
     */
    public function render()
    {
        $view = $this->buildView();
        return $view->render();
    }

    abstract protected function buildView();

    protected function getDefaultDataTableCssClass()
    {
        return 'dataTableViz' . Piwik::getUnnamespacedClassName(get_class($this));
    }

    /**
     * Returns the list of view properties that can be overriden by query parameters.
     *
     * @return array
     */
    protected function getOverridableProperties()
    {
        return array_merge($this->config->overridableProperties, $this->requestConfig->overridableProperties);
    }

    private function overrideViewPropertiesWithQueryParams()
    {
        $properties = $this->getOverridableProperties();

        foreach ($properties as $name) {
            if (property_exists($this->requestConfig, $name)) {
                $this->requestConfig->$name = $this->getPropertyFromQueryParam($name, $this->requestConfig->$name);
            } elseif (property_exists($this->config, $name)) {
                $this->config->$name = $this->getPropertyFromQueryParam($name, $this->config->$name);
            }
        }

        // handle special 'columns' query parameter
        $columns = Common::getRequestVar('columns', false);

        if (false !== $columns) {
            $this->config->columns_to_display = Piwik::getArrayFromApiParameter($columns);
            array_unshift($this->config->columns_to_display, 'label');
        }
    }

    protected function getPropertyFromQueryParam($name, $defaultValue)
    {
        $type = is_numeric($defaultValue) ? 'int' : null;
        return Common::getRequestVar($name, $defaultValue, $type);
    }

    /**
     * Returns `true` if this instance will request a single DataTable, `false` if requesting
     * more than one.
     *
     * @return bool
     */
    public function isRequestingSingleDataTable()
    {
        $requestArray = $this->request->getRequestArray() + $_GET + $_POST;
        $date   = Common::getRequestVar('date', null, 'string', $requestArray);
        $period = Common::getRequestVar('period', null, 'string', $requestArray);
        $idSite = Common::getRequestVar('idSite', null, 'string', $requestArray);

        if (Period::isMultiplePeriod($date, $period)
            || strpos($idSite, ',') !== false
            || $idSite == 'all'
        ) {
            return false;
        }

        return true;
    }

    /**
     * Returns `true` if this visualization can display some type of data or not.
     *
     * New visualization classes should override this method if they can only visualize certain
     * types of data. The evolution graph visualization, for example, can only visualize
     * sets of DataTables. If the API method used results in a single DataTable, the evolution
     * graph footer icon should not be displayed.
     *
     * @param  ViewDataTable $view Contains the API request being checked.
     * @return bool
     */
    public static function canDisplayViewDataTable(ViewDataTable $view)
    {
        return $view->config->show_all_views_icons;
    }

    private function overrideViewPropertiesWithParams($overrideParams)
    {
        if (empty($overrideParams)) {
            return;
        }

        foreach ($overrideParams as $key => $value) {
            if (property_exists($this->requestConfig, $key)) {
                $this->requestConfig->$key = $value;
            } elseif (property_exists($this->config, $key)) {
                $this->config->$key = $value;
            } elseif ($key != 'enable_filter_excludelowpop') {
                $this->config->custom_parameters[$key] = $value;
            }
        }
    }

}
