<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\Plugin;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\MetricsFormatter;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Site;
use Piwik\View;
use Piwik\View\ViewInterface;
use Piwik\ViewDataTable\Config as VizConfig;
use Piwik\ViewDataTable\Manager as ViewDataTableManager;
use Piwik\ViewDataTable\Request as ViewDataTableRequest;
use Piwik\ViewDataTable\RequestConfig as VizRequest;

/**
 * This class is used to load (from the API) and customize the output of a given DataTable.
 *
 * You can build your own ViewDataTable by extending this class and implementing the buildView() method which defines
 * which data should be loaded and which view should be rendered.
 *
 * Example usage:
 * In the Controller of the plugin VisitorInterest
 * <pre>
 *    function getNumberOfVisitsPerVisitDuration( $fetch = false)
 *  {
 *        $view = ViewDataTable::factory( 'cloud' );
 *        $view->init( $this->pluginName,  __FUNCTION__, 'VisitorInterest.getNumberOfVisitsPerVisitDuration' );
 *        $view->setColumnsToDisplay( array('label','nb_visits') );
 *        $view->disableSort();
 *        $view->disableExcludeLowPopulation();
 *        $view->disableOffsetInformation();
 *
 *        return $this->renderView($view, $fetch);
 *    }
 * </pre>
 *
 * @see factory() for all the available output (cloud tags, html table, pie chart, vertical bar chart)
 * @package Piwik
 * @subpackage ViewDataTable
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
     * @var \Piwik\ViewDataTable\Config
     */
    public $config;

    /**
     * @var \Piwik\ViewDataTable\RequestConfig
     */
    public $requestConfig;

    /**
     * @var ViewDataTableRequest
     */
    protected $request;

    /**
     * Constructor. Initializes the default config, requestConfig and the request itself. After configuring some
     * mandatory properties reports can modify the view by listening to the hook 'ViewDataTable.configure'.
     */
    public function __construct($controllerAction, $apiMethodToRequestDataTable)
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

        /**
         * Triggered during [ViewDataTable](#) is constructed. Subscribers should customize
         * the view based on the report that it is displaying.
         * 
         * Plugins that define their own reports must subscribe to this event in order to
         * customize how the Piwik UI will display and navigate the report.
         * 
         * **Example**
         *
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

        $this->overrideViewPropertiesWithQueryParams();
    }

    /**
     * Returns the default config. Custom viewDataTables can change the default config to their needs by either
     * modifying this config or creating an own Config class that extends the default Config.
     *
     * @return \Piwik\ViewDataTable\Config
     */
    public static function getDefaultConfig()
    {
        return new VizConfig();
    }

    /**
     * Returns the default request config. Custom viewDataTables can change the default config to their needs by either
     * modifying this config or creating an own RequestConfig class that extends the default RequestConfig.
     *
     * @return \Piwik\ViewDataTable\RequestConfig
     */
    public static function getDefaultRequestConfig()
    {
        return new VizRequest();
    }

    protected function loadDataTableFromAPI($fixedRequestParams = array())
    {
        if (!is_null($this->dataTable)) {
            // data table is already there
            // this happens when setDataTable has been used
            return $this->dataTable;
        }

        $this->dataTable = $this->request->loadDataTableFromAPI($fixedRequestParams);

        return $this->dataTable;
    }

    /**
     * Returns the viewDataTable ID for this DataTable visualization. Derived classes  should declare a const ID field
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
     * Detects whether the viewDataTable or one of its ancestors has the given id.
     *
     * @param  string $viewDataTableId
     *
     * @return bool
     */
    public function isViewDataTableId($viewDataTableId)
    {
        $myIds = ViewDataTableManager::getIdsWithInheritance(get_called_class());

        return in_array($viewDataTableId, $myIds);
    }

    /**
     * Returns the DataTable loaded from the API
     *
     * @return DataTable
     * @throws \Exception if not yet defined
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
     * It won't be loaded again from the API in this case
     *
     * @param $dataTable
     * @return void $dataTable DataTable
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
                $this->requestConfig->name = $this->getPropertyFromQueryParam($name, $this->requestConfig->$name);
            } elseif (property_exists($this->config, $name)) {
                $this->config->name  = $this->getPropertyFromQueryParam($name, $this->config->$name);
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
     * Determine if the view data table requests a single data table or not.
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
     * Here you can define whether your visualization can display a specific data table or not. For instance you may
     * only display your visualization in case a single data table is requested. If the method returns true, the footer
     * icon will be displayed.
     *
     * @param  ViewDataTable $view
     * @return bool
     */
    public static function canDisplayViewDataTable(ViewDataTable $view)
    {
        return $view->config->show_all_views_icons;
    }
}