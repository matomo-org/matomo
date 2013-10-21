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

namespace Piwik\ViewDataTable;
use Piwik\API\Request as ApiRequest;
use Piwik\Common;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugins\API\API;

/**
 * Renders a sparkline image given a PHP data array.
 * Using the Sparkline PHP Graphing Library sparkline.org
 *
 * @package Piwik
 * @subpackage Piwik_Visualization
 */
class Config
{

    /**
     * The list of ViewDataTable properties that are 'Client Side Properties'.
     */
    public $clientSideProperties = array(
        'show_limit_control'
    );

    /**
     * The list of ViewDataTable properties that can be overriden by query parameters.
     */
    public $overridableProperties = array(
        'show_goals',
        'show_exclude_low_population',
        'show_flatten_table',
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
     * have set of properties, including an array of arrays describing footer icons. See
     * this example to get a clear idea of what is required:
     *
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
     *     )
     * )
     *
     * By default, when a user clicks on a footer icon, Piwik will assume the 'id' is
     * a viewDataTable ID and try to reload the DataTable w/ the new viewDataTable. You
     * can provide your own footer icon behavior by adding an appropriate handler via
     * DataTable.registerFooterIconHandler in your JavaScript.
     *
     * Default value: The default value will show the 'Normal Table' icon, the 'All Columns'
     * icon, the 'Goals Columns' icon and all jqPlot graph columns, unless other properties
     * tell the view to exclude them.
     */
    public $footer_icons = false;

    /**
     * Controls whether the buttons and UI controls around the visualization or shown or
     * if just the visualization alone is shown.
     *
     * Default value: false
     */
    public $show_visualization_only = false;

    /**
     * Controls whether the goals footer icon is shown.
     *
     * Default value: false
     */
    public $show_goals = false;

    /**
     * Array property mapping DataTable column names with their internationalized names.
     *
     * The value you specify for this property is merged with the default value so you
     * don't have to specify translations that already exist in the default value.
     *
     * Default value: Array containing translations of common metrics.
     */
    public $translations = array();

    /**
     * Controls whether the 'Exclude Low Population' option (visible in the popup that displays after
     * clicking the 'cog' icon) is shown.
     *
     * Default value: true
     */
    public $show_exclude_low_population = true;

    /**
     * Whether to show the 'Flatten' option (visible in the popup that displays after clicking the
     * 'cog' icon).
     *
     * Default value: true
     */
    public $show_flatten_table = true;

    /**
     * Controls whether the footer icon that allows user to switch to the 'normal' DataTable view
     * is shown.
     *
     * Default value: true
     */
    public $show_table = true;

    /**
     * Controls whether the 'All Columns' footer icon is shown.
     *
     * Default value: true
     */
    public $show_table_all_columns = true;

    /**
     * Controls whether the entire view footer is shown.
     *
     * Default value: true
     */
    public $show_footer = true;

    /**
     * Controls whether the row that contains all footer icons & the limit selector is shown.
     *
     * Default value: true
     */
    public $show_footer_icons = true;

    /**
     * Array property that determines which columns will be shown. Columns not in this array
     * should not appear in ViewDataTable visualizations.
     *
     * Example: array('label', 'nb_visits', 'nb_uniq_visitors')
     *
     * Default value: array('label', 'nb_visits') or array('label', 'nb_uniq_visitors') if
     *                the report contains a nb_uniq_visitors column.
     */
    public $columns_to_display = array();

    /**
     * Controls whether graph and non core viewDataTable footer icons are shown or not.
     *
     * Default value: true
     */
    public $show_all_views_icons = true;

    /**
     * Controls whether to display a tiny upside-down caret over the currently active view icon.
     *
     * Default value: true
     */
    public $show_active_view_icon = true;

    /**
     * Related reports are listed below a datatable view. When clicked, the original report will
     * change to the clicked report and the list will change so the original report can be
     * navigated back to.
     *
     * Default value: array()
     */
    public $related_reports = array();

    /**
     * The report title. Used with related reports so report headings can be changed when switching
     * reports.
     *
     * This must be set if related reports are added.
     *
     * Default value: ''
     */
    public $title = '';

    /**
     * Controls whether a report's related reports are listed with the view or not.
     *
     * Default value: true
     */
    public $show_related_reports = true;

    /**
     * Contains the documentation for a report.
     *
     * Default value: false
     */
    public $documentation = false;

    /**
     * Array property containing custom data to be saved in JSON in the data-params HTML attribute
     * of a data table div. This data can be used by JavaScript DataTable classes.
     *
     * e.g. array('typeReferrer' => ...)
     *
     * Default value: array()
     */
    public $custom_parameters = array();

    /**
     * Controls whether the limit dropdown (which allows users to change the number of data shown)
     * is always shown or not.
     *
     * Normally shown only if pagination is enabled.
     *
     * Default value: true
     */
    public $show_limit_control = true;

    /**
     * Controls whether the search box under the datatable is shown.
     *
     * Default value: true
     */
    public $show_search = true;

    /**
     * Controls whether the user can sort DataTables by clicking on table column headings.
     *
     * Default value: true
     */
    public $enable_sort = true;

    /**
     * Controls whether the footer icon that allows users to view data as a bar chart is shown.
     *
     * Default value: true
     */
    public $show_bar_chart = true;

    /**
     * Controls whether the footer icon that allows users to view data as a pie chart is shown.
     *
     * Default value: true
     */
    public $show_pie_chart = true;

    /**
     * Controls whether the footer icon that allows users to view data as a tag cloud is shown.
     *
     * Default value: true
     */
    public $show_tag_cloud = true;

    /**
     * Controls whether the user is allowed to export data as an RSS feed or not.
     *
     * Default value: true
     */
    public $show_export_as_rss_feed = true;

    /**
     * Controls whether the 'Ecoommerce Orders'/'Abandoned Cart' footer icons are shown or not.
     *
     * Default value: false
     */
    public $show_ecommerce = false;

    /**
     * Stores an HTML message (if any) to display under the datatable view.
     *
     * Default value: false
     */
    public $show_footer_message = false;

    /**
     * Array property that stores documentation for individual metrics.
     *
     * E.g. array('nb_visits' => '...', ...)
     *
     * Default: Set to values retrieved from report metadata (via API.getReportMetadata API method).
     */
    public $metrics_documentation = array();

    /**
     * Row metadata name that contains the tooltip for the specific row.
     *
     * Default value: false
     */
    public $tooltip_metadata_name = false;

    /**
     * The URL to the report the view is displaying. Modifying this means clicking back to this report
     * from a Related Report will go to a different URL. Can be used to load an entire page instead
     * of a single report when going back to the original report.
     *
     * Default value: The URL used to request the report without generic filters.
     */
    public $self_url = '';

    /**
     * CSS class to use in the output HTML div. This is added in addition to the visualization CSS
     * class.
     *
     * Default value: false
     */
    public $datatable_css_class = false;

    /**
     * The JavaScript class to instantiate after the result HTML is obtained. This class handles all
     * interactive behavior for the DataTable view.
     *
     * Default value: 'DataTable'
     */
    public $datatable_js_type = 'DataTable';

    /**
     * If true, searching through the DataTable will search through all subtables.
     *
     * Default value: false
     */
    public $search_recursive = false;

    /**
     * The unit of the displayed column. Valid if only one non-label column is displayed.
     *
     * Default value: false
     */
    public $y_axis_unit = false;

    /**
     * Controls whether to show the 'Export as Image' footer icon.
     *
     * Default value: false
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
     *
     * Default value: array()
     */
    public $filters = array();

    /**
     * Contains the controller action to call when requesting subtables of the current report.
     *
     * Default value: The controller action used to request the report.
     */
    public $subtable_controller_action = '';

    /**
     * Controls whether the 'prev'/'next' links are shown in the DataTable footer. These links
     * change the 'filter_offset' query parameter, thus allowing pagination.
     *
     * TODO: pagination/offset is only valid for HtmlTables... should only display for those visualizations.
     *
     * Default value: true
     */
    public $show_pagination_control = true;

    /**
     * Controls whether offset information (ie, '5-10 of 20') is shown under the datatable.
     *
     * Default value: true
     */
    public $show_offset_information = true;

    /**
     * Controls whether annotations are shown or not.
     *
     * Default value: true
     */
    public $hide_annotations_view = true;

    /**
     * The filter_limit query parameter value to use in export links.
     *
     * Default value: The value of the 'API_datatable_default_limit' config option.
     */
    public $export_limit = '';

    public $report_last_updated_message = false;
    public $metadata  = array();
    public $report_id = '';

    public $controllerName;
    public $controllerAction;

    public function __construct()
    {
        $this->export_limit = \Piwik\Config::getInstance()->General['API_datatable_default_limit'];
        $this->translations = array_merge(
            Metrics::getDefaultMetrics(),
            Metrics::getDefaultProcessedMetrics()
        );
    }

    public function setController($controllerName, $controllerAction)
    {
        $this->controllerName   = $controllerName;
        $this->controllerAction = $controllerAction;
        $this->report_id        = $controllerName . '.' . $controllerAction;

        $this->loadDocumentation();
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

    public function addPropertiesThatShouldBeAvailableClientSide(array $propertyNames)
    {
        foreach ($propertyNames as $propertyName) {
            $this->clientSideProperties[] = $propertyName;
        }
    }

    public function addPropertiesThatCanBeOverwrittenByQueryParams(array $propertyNames)
    {
        foreach ($propertyNames as $propertyName) {
            $this->overridableProperties[] = $propertyName;
        }
    }

    public function getProperties()
    {
        return get_object_vars($this);
    }

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

    public function addRelatedReport($relatedReport, $title, $queryParams = array())
    {
        list($module, $action) = explode('.', $relatedReport);

        // don't add the related report if it references this report
        if ($this->controllerName == $module && $this->controllerAction == $action) {
            return;
        }

        $url = ApiRequest::getBaseReportUrl($module, $action, $queryParams);

        $this->related_reports[$url] = $title;
    }

    public function addRelatedReports($relatedReports)
    {
        foreach ($relatedReports as $report => $title) {
            $this->addRelatedReport($report, $title);
        }
    }

    public function addTranslation($key, $translation)
    {
        $this->translations[$key] = $translation;
    }

    public function addTranslations($translations)
    {
        foreach ($translations as $key => $translation) {
            $this->addTranslation($key, $translation);
        }
    }

}
