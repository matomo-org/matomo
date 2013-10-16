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
use Piwik\Log;
use Piwik\MetricsFormatter;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Site;
use Piwik\View;
use Piwik\View\ViewInterface;
use Piwik\ViewDataTable\Config as VizConfig;
use Piwik\ViewDataTable\RequestConfig as VizRequest;
use \Piwik\ViewDataTable\Request as ViewDataTableRequest;

/**
 * This class is used to load (from the API) and customize the output of a given DataTable.
 * The main() method will create an object implementing ViewInterface
 * You can customize the dataTable using the disable* methods.
 *
 * You can also customize the dataTable rendering using row metadata:
 * - 'html_label_prefix': If this metadata is present on a row, it's contents will be prepended
 *                        the label in the HTML output.
 * - 'html_label_suffix': If this metadata is present on a row, it's contents will be appended
 *                        after the label in the HTML output.
 *
 * Example:
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
    const CONFIGURE_FOOTER_ICONS_EVENT = 'Visualization.configureFooterIcons';

    /**
     * If the current dataTable refers to a subDataTable (eg. keywordsBySearchEngineId for id=X) this variable is set to the Id
     *
     * @var bool|int
     */
    protected $idSubtable = false;

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
     * Default constructor.
     */
    public function __construct($controllerAction, $apiMethodToRequestDataTable, $defaultReportProperties)
    {
        list($controllerName, $controllerAction) = explode('.', $controllerAction);

        $this->requestConfig = $this->getDefaultRequestConfig();
        $this->config        = $this->getDefaultConfig();
        $this->config->subtable_controller_action = $controllerAction;
        $this->config->setController($controllerName, $controllerAction);

        $this->request = new ViewDataTableRequest($this->requestConfig);

        $this->setViewProperties($defaultReportProperties);

        $this->idSubtable = Common::getRequestVar('idSubtable', false, 'int');

        $this->config->show_footer_icons = (false == $this->idSubtable);
        $this->config->self_url          = Request::getBaseReportUrl($controllerName, $controllerAction);

        $this->requestConfig->apiMethodToRequestDataTable = $apiMethodToRequestDataTable;

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

    public function getDefaultConfig()
    {
        return new VizConfig();
    }

    public function getDefaultRequestConfig()
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

        $request = new \Piwik\ViewDataTable\Request($this->requestConfig);

        $this->dataTable = $request->loadDataTableFromAPI();

        return $this->dataTable;
    }

    /**
     * Returns the viewDataTable ID for this DataTable visualization. Derived classes
     * should declare a const ID field with the viewDataTable ID.
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

    private function setViewProperties($values)
    {
        foreach ($values as $name => $value) {
            $this->setViewProperty($name, $value);
        }
    }

    /**
     * Sets a view property by name. This function handles special view properties
     * like 'translations' & 'related_reports' that store arrays.
     *
     * @param string $name
     * @param mixed $value For array properties, $value can be a comma separated string.
     * @throws \Exception
     */
    protected function setViewProperty($name, $value)
    {
        if (isset($this->requestConfig->$name)
            && is_array($this->requestConfig->$name)
            && is_string($value)
        ) {
            $value = Piwik::getArrayFromApiParameter($value);
        }

        if (isset($this->config->$name)
            && is_array($this->config->$name)
            && is_string($value)
        ) {
            $value = Piwik::getArrayFromApiParameter($value);
        }

        if ($name == 'translations'
            || $name == 'filters'
        ) {
            $this->config->$name = array_merge($this->config->$name, $value);
        } else if ($name == 'related_reports') { // TODO: should process after (in overrideViewProperties)
            $this->addRelatedReports($value);
        } else if ($name == 'visualization_properties') {
            $this->setVisualizationPropertiesFromMetadata($value);
        } elseif (property_exists($this->requestConfig, $name)) {
            $this->requestConfig->$name = $value;
        } else if (property_exists($this->config, $name)) {
            $this->config->$name = $value;
        } else {
            $report = $this->config->controllerName . '.' . $this->config->controllerAction;
            throw new \Exception("Invalid view property '$name' specified in view property metadata for '$report'.");
        }
    }

    /**
     * Sets visualization properties using data in a visualization's default property values
     * array.
     */
    protected function setVisualizationPropertiesFromMetadata($properties)
    {
        if (!is_array($properties)) {
            Log::debug('Cannot set properties from metadata, $properties is not an array');
            return null;
        }

        // TODO parent class should not know anything about children
        $visualizationIds = static::getIdsWithInheritance(get_class($this));

        foreach ($visualizationIds as $visualizationId) {
            if (empty($properties[$visualizationId])) {
                continue;
            }

            $this->setViewProperties($properties[$visualizationId]);
        }
    }

    /**
     * Returns the viewDataTable IDs of a visualization's class lineage.
     *
     * @see self::getVisualizationClassLineage
     *
     * @param string $klass The visualization class.
     *
     * @return array
     */
    public static function getIdsWithInheritance($klass)
    {
        $klasses = Common::getClassLineage($klass);

        $result = array();
        foreach ($klasses as $klass) {
            if ('Piwik\\Plugin\\ViewDataTable' != $klass
                && 'Piwik\\Plugin\\Visualization' != $klass) {
                $result[] = $klass::getViewDataTableId();
            }
        }

        return $result;
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

    private function addRelatedReport($module, $action, $title, $queryParams = array())
    {
        // don't add the related report if it references this report
        if ($this->config->controllerName == $module && $this->config->controllerAction == $action) {
            return;
        }

        $url = Request::getBaseReportUrl($module, $action, $queryParams);

        $this->config->related_reports[$url] = $title;
    }

    private function addRelatedReports($relatedReports)
    {
        foreach ($relatedReports as $report => $title) {
            list($module, $action) = explode('.', $report);
            $this->addRelatedReport($module, $action, $title);
        }
    }

    /**
     * Convenience function. Calls main() & renders the view that gets built.
     *
     * @return string The result of rendering.
     */
    public function render()
    {
        $view = $this->buildView();
        return $view->render();
    }

    abstract protected function buildView();

    protected function overrideViewProperties()
    {
        if (!\Piwik\Plugin\Manager::getInstance()->isPluginActivated('Goals')) {
            $this->config->show_goals = false;
        }

        if (empty($this->config->footer_icons)) {
            $this->config->footer_icons = $this->getDefaultFooterIconsToShow();
        }
    }

    private function getDefaultFooterIconsToShow()
    {
        $result = array();

        // add normal view icons (eg, normal table, all columns, goals)
        $normalViewIcons = array(
            'class'   => 'tableAllColumnsSwitch',
            'buttons' => array(),
        );

        if ($this->config->show_table) {
            $normalViewIcons['buttons'][] = array(
                'id'    => 'table',
                'title' => Piwik::translate('General_DisplaySimpleTable'),
                'icon'  => 'plugins/Zeitgeist/images/table.png',
            );
        }

        if ($this->config->show_table_all_columns) {
            $normalViewIcons['buttons'][] = array(
                'id'    => 'tableAllColumns',
                'title' => Piwik::translate('General_DisplayTableWithMoreMetrics'),
                'icon'  => 'plugins/Zeitgeist/images/table_more.png'
            );
        }

        if ($this->config->show_goals) {
            if (Common::getRequestVar('idGoal', false) == 'ecommerceOrder') {
                $icon = 'plugins/Zeitgeist/images/ecommerceOrder.gif';
            } else {
                $icon = 'plugins/Zeitgeist/images/goal.png';
            }

            $normalViewIcons['buttons'][] = array(
                'id'    => 'tableGoals',
                'title' => Piwik::translate('General_DisplayTableWithGoalMetrics'),
                'icon'  => $icon
            );
        }

        if ($this->config->show_ecommerce) {
            $normalViewIcons['buttons'][] = array(
                'id'    => 'ecommerceOrder',
                'title' => Piwik::translate('General_EcommerceOrders'),
                'icon'  => 'plugins/Zeitgeist/images/ecommerceOrder.gif',
                'text'  => Piwik::translate('General_EcommerceOrders')
            );

            $normalViewIcons['buttons'][] = array(
                'id'    => 'ecommerceAbandonedCart',
                'title' => Piwik::translate('General_AbandonedCarts'),
                'icon'  => 'plugins/Zeitgeist/images/ecommerceAbandonedCart.gif',
                'text'  => Piwik::translate('General_AbandonedCarts')
            );
        }

        if (!empty($normalViewIcons['buttons'])) {
            $result[] = $normalViewIcons;
        }

        // add graph views
        $graphViewIcons = array(
            'class'   => 'tableGraphViews tableGraphCollapsed',
            'buttons' => array(),
        );

        if ($this->config->show_all_views_icons) {
            if ($this->config->show_bar_chart) {
                $graphViewIcons['buttons'][] = array(
                    'id'    => 'graphVerticalBar',
                    'title' => Piwik::translate('General_VBarGraph'),
                    'icon'  => 'plugins/Zeitgeist/images/chart_bar.png'
                );
            }

            if ($this->config->show_pie_chart) {
                $graphViewIcons['buttons'][] = array(
                    'id'    => 'graphPie',
                    'title' => Piwik::translate('General_Piechart'),
                    'icon'  => 'plugins/Zeitgeist/images/chart_pie.png'
                );
            }

            if ($this->config->show_tag_cloud) {
                $graphViewIcons['buttons'][] = array(
                    'id'    => 'cloud',
                    'title' => Piwik::translate('General_TagCloud'),
                    'icon'  => 'plugins/Zeitgeist/images/tagcloud.png'
                );
            }

            if ($this->config->show_non_core_visualizations) {
                $nonCoreVisualizations    = \Piwik\ViewDataTable::getNonCoreVisualizations();
                $nonCoreVisualizationInfo = static::getVisualizationInfoFor($nonCoreVisualizations);

                foreach ($nonCoreVisualizationInfo as $format => $info) {
                    $graphViewIcons['buttons'][] = array(
                        'id'    => $format,
                        'title' => Piwik::translate($info['title']),
                        'icon'  => $info['table_icon']
                    );
                }
            }
        }

        if (!empty($graphViewIcons['buttons'])) {
            $result[] = $graphViewIcons;
        }

        /**
         * This event is called when determining the default set of footer icons to display
         * below a report.
         *
         * Plugins can use this event to modify the default set of footer icons. You can
         * add new icons or remove existing ones.
         *
         * $result must have the following format:
         *
         * ```
         * array(
         *     array( // footer icon group 1
         *         'class' => 'footerIconGroup1CssClass',
         *         'buttons' => array(
         *             'id' => 'myid',
         *             'title' => 'My Tooltip',
         *             'icon' => 'path/to/my/icon.png'
         *         )
         *     ),
         *     array( // footer icon group 2
         *         'class' => 'footerIconGroup2CssClass',
         *         'buttons' => array(...)
         *     ),
         *     ...
         * )
         * ```
         */
        Piwik::postEvent(self::CONFIGURE_FOOTER_ICONS_EVENT, array(&$result, $viewDataTable = $this));

        return $result;
    }

    public function getDefaultDataTableCssClass()
    {
        return 'dataTableViz' . Piwik::getUnnamespacedClassName(get_class($this));
    }

    /**
     * Returns an array mapping visualization IDs with information necessary for adding the
     * visualizations to the footer of DataTable views.
     *
     * @param array $visualizations An array mapping visualization IDs w/ their associated classes.
     * @return array
     */
    public static function getVisualizationInfoFor($visualizations)
    {
        $result = array();

        foreach ($visualizations as $vizId => $vizClass) {
            $result[$vizId] = array('table_icon' => $vizClass::FOOTER_ICON, 'title' => $vizClass::FOOTER_ICON_TITLE);
        }

        return $result;
    }

    protected function convertForJson($value)
    {
        return is_bool($value) ? (int)$value : $value;
    }

    /**
     * Returns the list of view properties that can be overriden by query parameters.
     *
     * @return array
     */
    public function getOverridableProperties()
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
}