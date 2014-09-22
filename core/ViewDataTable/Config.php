<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\ViewDataTable;
use Piwik\API\Request as ApiRequest;
use Piwik\DataTable;
use Piwik\DataTable\Filter\PivotByDimension;
use Piwik\Metrics;
use Piwik\Plugin\Report;
use Piwik\Plugins\API\API;

/**
 * Contains base display properties for {@link Piwik\Plugin\ViewDataTable}s. Manipulating these
 * properties in a ViewDataTable instance will change how its report will be displayed.
 *
 * <a name="client-side-properties-desc"></a>
 * **Client Side Properties**
 *
 * Client side properties are properties that should be passed on to the browser so
 * client side JavaScript can use them. Only affects ViewDataTables that output HTML.
 *
 * <a name="overridable-properties-desc"></a>
 * **Overridable Properties**
 *
 * Overridable properties are properties that can be set via the query string.
 * If a request has a query parameter that matches an overridable property, the property
 * will be set to the query parameter value.
 *
 * **Reusing base properties**
 *
 * Many of the properties in this class only have meaning for the {@link Piwik\Plugin\Visualization}
 * class, but can be set for other visualizations that extend {@link Piwik\Plugin\ViewDataTable}
 * directly.
 *
 * Visualizations that extend {@link Piwik\Plugin\ViewDataTable} directly and want to re-use these
 * properties must make sure the properties are used in the exact same way they are used in
 * {@link Piwik\Plugin\Visualization}.
 *
 * **Defining new display properties**
 *
 * If you are creating your own visualization and want to add new display properties for
 * it, extend this class and add your properties as fields.
 *
 * Properties are marked as client side properties by calling the
 * {@link addPropertiesThatShouldBeAvailableClientSide()} method.
 *
 * Properties are marked as overridable by calling the
 * {@link addPropertiesThatCanBeOverwrittenByQueryParams()} method.
 *
 * ### Example
 *
 * **Defining new display properties**
 *
 *     class MyCustomVizConfig extends Config
 *     {
 *         /**
 *          * My custom property. It is overridable.
 *          *\/
 *         public $my_custom_property = false;
 *
 *         /**
 *          * Another custom property. It is available client side.
 *          *\/
 *         public $another_custom_property = true;
 *
 *         public function __construct()
 *         {
 *             parent::__construct();
 *
 *             $this->addPropertiesThatShouldBeAvailableClientSide(array('another_custom_property'));
 *             $this->addPropertiesThatCanBeOverwrittenByQueryParams(array('my_custom_property'));
 *         }
 *     }
 *
 * @api
 */
class Config
{
    /**
     * The list of ViewDataTable properties that are 'Client Side Properties'.
     */
    public $clientSideProperties = array(
        'show_limit_control',
        'pivot_by_dimension',
        'pivot_by_column',
        'pivot_dimension_name'
    );

    /**
     * The list of ViewDataTable properties that can be overriden by query parameters.
     */
    public $overridableProperties = array(
        'show_goals',
        'show_exclude_low_population',
        'show_flatten_table',
        'show_pivot_by_subtable',
        'show_table',
        'show_table_all_columns',
        'show_footer',
        'show_footer_icons',
        'show_all_views_icons',
        'show_active_view_icon',
        'show_related_reports',
        'show_limit_control',
        'show_search',
        'enable_sort',
        'show_bar_chart',
        'show_pie_chart',
        'show_tag_cloud',
        'show_export_as_rss_feed',
        'show_ecommerce',
        'search_recursive',
        'show_export_as_image_icon',
        'show_pagination_control',
        'show_offset_information',
        'hide_annotations_view',
        'export_limit'
    );

    /**
     * Controls what footer icons are displayed on the bottom left of the DataTable view.
     * The value of this property must be an array of footer icon groups. Footer icon groups
     * have set of properties, including an array of arrays describing footer icons. For
     * example:
     *
     *     array(
     *         array( // footer icon group 1
     *             'class' => 'footerIconGroup1CssClass',
     *             'buttons' => array(
     *                 'id' => 'myid',
     *                 'title' => 'My Tooltip',
     *                 'icon' => 'path/to/my/icon.png'
     *             )
     *         ),
     *         array( // footer icon group 2
     *             'class' => 'footerIconGroup2CssClass',
     *             'buttons' => array(...)
     *         )
     *     )
     *
     * By default, when a user clicks on a footer icon, Piwik will assume the 'id' is
     * a viewDataTable ID and try to reload the DataTable w/ the new viewDataTable. You
     * can provide your own footer icon behavior by adding an appropriate handler via
     * DataTable.registerFooterIconHandler in your JavaScript code.
     *
     * The default value of this property is not set here and will show the 'Normal Table'
     * icon, the 'All Columns' icon, the 'Goals Columns' icon and all jqPlot graph columns,
     * unless other properties tell the view to exclude them.
     */
    public $footer_icons = false;

    /**
     * Controls whether the buttons and UI controls around the visualization or shown or
     * if just the visualization alone is shown.
     */
    public $show_visualization_only = false;

    /**
     * Controls whether the goals footer icon is shown.
     */
    public $show_goals = false;

    /**
     * Controls whether the 'insights' footer icon is shown.
     */
    public $show_insights = true;

    /**
     * Array property mapping DataTable column names with their internationalized names.
     *
     * The default value for this property is set elsewhere. It will contain translations
     * of common metrics.
     */
    public $translations = array();

    /**
     * Controls whether the 'Exclude Low Population' option (visible in the popup that displays after
     * clicking the 'cog' icon) is shown.
     */
    public $show_exclude_low_population = true;

    /**
     * Whether to show the 'Flatten' option (visible in the popup that displays after clicking the
     * 'cog' icon).
     */
    public $show_flatten_table = true;

    /**
     * Whether to show the 'Pivot by subtable' option (visible in the popup that displays after clicking
     * the 'cog' icon).
     */
    public $show_pivot_by_subtable;

    /**
     * The ID of the dimension to pivot by when the 'pivot by subtable' option is clicked. Defaults
     * to the subtable dimension of the report being displayed.
     */
    public $pivot_by_dimension;

    /**
     * The column to display in pivot tables. Defaults to the first non-label column if not specified.
     */
    public $pivot_by_column = '';

    /**
     * The human readable name of the pivot dimension.
     */
    public $pivot_dimension_name = false;

    /**
     * Controls whether the footer icon that allows users to switch to the 'normal' DataTable view
     * is shown.
     */
    public $show_table = true;

    /**
     * Controls whether the 'All Columns' footer icon is shown.
     */
    public $show_table_all_columns = true;

    /**
     * Controls whether the entire view footer is shown.
     */
    public $show_footer = true;

    /**
     * Controls whether the row that contains all footer icons & the limit selector is shown.
     */
    public $show_footer_icons = true;

    /**
     * Array property that determines which columns will be shown. Columns not in this array
     * should not appear in ViewDataTable visualizations.
     *
     * Example: `array('label', 'nb_visits', 'nb_uniq_visitors')`
     *
     * If this value is empty it will be defaulted to `array('label', 'nb_visits')` or
     * `array('label', 'nb_uniq_visitors')` if the report contains a nb_uniq_visitors column
     * after data is loaded.
     */
    public $columns_to_display = array();

    /**
     * Controls whether graph and non core viewDataTable footer icons are shown or not.
     */
    public $show_all_views_icons = true;

    /**
     * Controls whether to display a tiny upside-down caret over the currently active view icon.
     */
    public $show_active_view_icon = true;

    /**
     * Related reports are listed below a datatable view. When clicked, the original report will
     * change to the clicked report and the list will change so the original report can be
     * navigated back to.
     */
    public $related_reports = array();

    /**
     * "Related Reports" is displayed by default before listing the Related reports,
     * The string can be changed.
     */
    public $related_reports_title;

    /**
     * The report title. Used with related reports so report headings can be changed when switching
     * reports.
     *
     * This must be set if related reports are added.
     */
    public $title = '';

    /**
     * Controls whether a report's related reports are listed with the view or not.
     */
    public $show_related_reports = true;

    /**
     * Contains the documentation for a report.
     */
    public $documentation = false;

    /**
     * Array property containing custom data to be saved in JSON in the data-params HTML attribute
     * of a data table div. This data can be used by JavaScript DataTable classes.
     *
     * e.g. array('typeReferrer' => ...)
     *
     * It can then be accessed in the twig templates by clientSideParameters.typeReferrer
     */
    public $custom_parameters = array();

    /**
     * Controls whether the limit dropdown (which allows users to change the number of data shown)
     * is always shown or not.
     *
     * Normally shown only if pagination is enabled.
     */
    public $show_limit_control = true;

    /**
     * Controls whether the search box under the datatable is shown.
     */
    public $show_search = true;

    /**
     * Controls whether the user can sort DataTables by clicking on table column headings.
     */
    public $enable_sort = true;

    /**
     * Controls whether the footer icon that allows users to view data as a bar chart is shown.
     */
    public $show_bar_chart = true;

    /**
     * Controls whether the footer icon that allows users to view data as a pie chart is shown.
     */
    public $show_pie_chart = true;

    /**
     * Controls whether the footer icon that allows users to view data as a tag cloud is shown.
     */
    public $show_tag_cloud = true;

    /**
     * Controls whether the user is allowed to export data as an RSS feed or not.
     */
    public $show_export_as_rss_feed = true;

    /**
     * Controls whether the 'Ecoommerce Orders'/'Abandoned Cart' footer icons are shown or not.
     */
    public $show_ecommerce = false;

    /**
     * Stores an HTML message (if any) to display under the datatable view.
     */
    public $show_footer_message = false;

    /**
     * Array property that stores documentation for individual metrics.
     *
     * E.g. `array('nb_visits' => '...', ...)`
     *
     * By default this is set to values retrieved from report metadata (via API.getReportMetadata API method).
     */
    public $metrics_documentation = array();

    /**
     * Row metadata name that contains the tooltip for the specific row.
     */
    public $tooltip_metadata_name = false;

    /**
     * The URL to the report the view is displaying. Modifying this means clicking back to this report
     * from a Related Report will go to a different URL. Can be used to load an entire page instead
     * of a single report when going back to the original report.
     *
     * The URL used to request the report without generic filters.
     */
    public $self_url = '';

    /**
     * CSS class to use in the output HTML div. This is added in addition to the visualization CSS
     * class.
     */
    public $datatable_css_class = false;

    /**
     * The JavaScript class to instantiate after the result HTML is obtained. This class handles all
     * interactive behavior for the DataTable view.
     */
    public $datatable_js_type = 'DataTable';

    /**
     * If true, searching through the DataTable will search through all subtables.
     */
    public $search_recursive = false;

    /**
     * The unit of the displayed column. Valid if only one non-label column is displayed.
     */
    public $y_axis_unit = false;

    /**
     * Controls whether to show the 'Export as Image' footer icon.
     */
    public $show_export_as_image_icon = false;

    /**
     * Array of DataTable filters that should be run before displaying a DataTable. Elements
     * of this array can either be a closure or an array with at most three elements, including:
     * - the filter name (or a closure)
     * - an array of filter parameters
     * - a boolean indicating if the filter is a priority filter or not
     *
     * Priority filters are run before queued filters. These filters should be filters that
     * add/delete rows.
     *
     * If a closure is used, the view is appended as a parameter.
     */
    public $filters = array();

    /**
     * Contains the controller action to call when requesting subtables of the current report.
     *
     * By default, this is set to the controller action used to request the report.
     */
    public $subtable_controller_action = '';

    /**
     * Controls whether the 'prev'/'next' links are shown in the DataTable footer. These links
     * change the 'filter_offset' query parameter, thus allowing pagination.
     */
    public $show_pagination_control = true;

    /**
     * Controls whether offset information (ie, '5-10 of 20') is shown under the datatable.
     */
    public $show_offset_information = true;

    /**
     * Controls whether annotations are shown or not.
     */
    public $hide_annotations_view = true;

    /**
     * The filter_limit query parameter value to use in export links.
     *
     * Defaulted to the value of the `[General] API_datatable_default_limit` INI config option.
     */
    public $export_limit = '';

    /**
     * @ignore
     */
    public $report_id = '';

    /**
     * @ignore
     */
    public $controllerName;

    /**
     * @ignore
     */
    public $controllerAction;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->export_limit = \Piwik\Config::getInstance()->General['API_datatable_default_limit'];
        $this->translations = array_merge(
            Metrics::getDefaultMetrics(),
            Metrics::getDefaultProcessedMetrics()
        );
    }

    /**
     * @ignore
     */
    public function setController($controllerName, $controllerAction)
    {
        $this->controllerName   = $controllerName;
        $this->controllerAction = $controllerAction;
        $this->report_id        = $controllerName . '.' . $controllerAction;

        $this->loadDocumentation();
        $this->setShouldShowPivotBySubtable();
    }

    /** Load documentation from the API */
    private function loadDocumentation()
    {
        $this->metrics_documentation = array();

        $report = API::getInstance()->getMetadata(0, $this->controllerName, $this->controllerAction);
        $report = $report[0];

        if (isset($report['metricsDocumentation'])) {
            $this->metrics_documentation = $report['metricsDocumentation'];
        }

        if (isset($report['documentation'])) {
            $this->documentation = $report['documentation'];
        }
    }

    /**
     * Marks display properties as client side properties. [Read this](#client-side-properties-desc)
     * to learn more.
     *
     * @param array $propertyNames List of property names, eg, `array('show_limit_control', 'show_goals')`.
     */
    public function addPropertiesThatShouldBeAvailableClientSide(array $propertyNames)
    {
        foreach ($propertyNames as $propertyName) {
            $this->clientSideProperties[] = $propertyName;
        }
    }

    /**
     * Marks display properties as overridable. [Read this](#overridable-properties-desc) to
     * learn more.
     *
     * @param array $propertyNames List of property names, eg, `array('show_limit_control', 'show_goals')`.
     */
    public function addPropertiesThatCanBeOverwrittenByQueryParams(array $propertyNames)
    {
        foreach ($propertyNames as $propertyName) {
            $this->overridableProperties[] = $propertyName;
        }
    }

    /**
     * Returns array of all property values in this config object. Property values are mapped
     * by name.
     *
     * @return array eg, `array('show_limit_control' => 0, 'show_goals' => 1, ...)`
     */
    public function getProperties()
    {
        return get_object_vars($this);
    }

    /**
     * @ignore
     */
    public function setDefaultColumnsToDisplay($columns, $hasNbVisits, $hasNbUniqVisitors)
    {
        if ($hasNbVisits || $hasNbUniqVisitors) {
            $columnsToDisplay = array('label');

            // if unique visitors data is available, show it, otherwise just visits
            if ($hasNbUniqVisitors) {
                $columnsToDisplay[] = 'nb_uniq_visitors';
            } else {
                $columnsToDisplay[] = 'nb_visits';
            }
        } else {
            $columnsToDisplay = $columns;
        }

        $this->columns_to_display = array_filter($columnsToDisplay);
    }

    /**
     * @ignore
     */
    public function getFiltersToRun()
    {
        $priorityFilters     = array();
        $presentationFilters = array();

        foreach ($this->filters as $filterInfo) {
            if ($filterInfo instanceof \Closure) {
                $nameOrClosure = $filterInfo;
                $parameters    = array();
                $priority      = false;
            } else {
                @list($nameOrClosure, $parameters, $priority) = $filterInfo;
            }

            if ($priority) {
                $priorityFilters[] = array($nameOrClosure, $parameters);
            } else {
                $presentationFilters[] = array($nameOrClosure, $parameters);
            }
        }

        return array($priorityFilters, $presentationFilters);
    }

    /**
     * Adds a related report to the {@link $related_reports} property. If the report
     * references the one that is currently being displayed, it will not be added to the related
     * report list.
     *
     * @param string $relatedReport The plugin and method of the report, eg, `'UserSettings.getBrowser'`.
     * @param string $title The report's display name, eg, `'Browsers'`.
     * @param array $queryParams Any extra query parameters to set in releated report's URL, eg,
     *                           `array('idGoal' => 'ecommerceOrder')`.
     */
    public function addRelatedReport($relatedReport, $title, $queryParams = array())
    {
        list($module, $action) = explode('.', $relatedReport);

        // don't add the related report if it references this report
        if ($this->controllerName == $module
            && $this->controllerAction == $action) {
            if(empty($queryParams)) {
                return;
            }
        }

        $url = ApiRequest::getBaseReportUrl($module, $action, $queryParams);

        $this->related_reports[$url] = $title;
    }

    /**
     * Adds several related reports to the {@link $related_reports} property. If
     * any of the reports references the report that is currently being displayed, it will not
     * be added to the list. All other reports will still be added though.
     *
     * If you need to make sure the related report URL has some extra query parameters,
     * use {@link addRelatedReport()}.
     *
     * @param array $relatedReports Array mapping report IDs with their internationalized display
     *                              titles, eg,
     *                              ```
     *                              array(
     *                                  'UserSettings.getBrowser' => 'Browsers',
     *                                  'UserSettings.getConfiguration' => 'Configurations'
     *                              )
     *                              ```
     */
    public function addRelatedReports($relatedReports)
    {
        foreach ($relatedReports as $report => $title) {
            $this->addRelatedReport($report, $title);
        }
    }

    /**
     * Associates internationalized text with a metric. Overwrites existing mappings.
     *
     * See {@link $translations}.
     *
     * @param string $columnName The name of a column in the report data, eg, `'nb_visits'` or
     *                           `'goal_1_nb_conversions'`.
     * @param string $translation The internationalized text, eg, `'Visits'` or `"Conversions for 'My Goal'"`.
     */
    public function addTranslation($columnName, $translation)
    {
        $this->translations[$columnName] = $translation;
    }

    /**
     * Associates multiple translations with metrics.
     *
     * See {@link $translations} and {@link addTranslation()}.
     *
     * @param array $translations An array of column name => text mappings, eg,
     *                            ```
     *                            array(
     *                                'nb_visits' => 'Visits',
     *                                'goal_1_nb_conversions' => "Conversions for 'My Goal'"
     *                            )
     *                            ```
     */
    public function addTranslations($translations)
    {
        foreach ($translations as $key => $translation) {
            $this->addTranslation($key, $translation);
        }
    }

    private function setShouldShowPivotBySubtable()
    {
        $report = Report::factory($this->controllerName, $this->controllerAction);

        if (empty($report)) {
            $this->show_pivot_by_subtable = false;
            $this->pivot_by_dimension = false;
        } else {
            $this->show_pivot_by_subtable =  PivotByDimension::isPivotingReportBySubtableSupported($report);

            $subtableDimension = $report->getSubtableDimension();
            if (!empty($subtableDimension)) {
                $this->pivot_by_dimension = $subtableDimension->getId();
                $this->pivot_dimension_name = $subtableDimension->getName();
            }
        }
    }

    public function disablePivotBySubtableIfTableHasNoSubtables(DataTable $table)
    {
        foreach ($table->getRows() as $row) {
            if ($row->getIdSubDataTable() !== null) {
                return;
            }
        }

        $this->show_pivot_by_subtable = false;
    }
}
