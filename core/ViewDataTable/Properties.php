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

use Exception;
use Piwik\Config;

use Piwik\Metrics;
use ReflectionClass;

/**
 * Contains the list of all core DataTable display properties for use with ViewDataTable.
 *
 * @see ViewDataTable - for more info.
 */
class Properties
{
    /**
     * The default viewDataTable ID to use when determining which visualization to use.
     * This property is only valid for reports whose properties are determined by the
     * Visualization.getReportDisplayProperties event. When manually creating ViewDataTables,
     * setting this property will have no effect.
     *
     * Default value: 'table'
     */
    const DEFAULT_VIEW_TYPE = 'default_view_type';

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
    const FOOTER_ICONS = 'footer_icons';

    /**
     * Controls whether the buttons and UI controls around the visualization or shown or
     * if just the visualization alone is shown.
     *
     * Default value: false
     */
    const SHOW_VISUALIZATION_ONLY = 'show_visualization_only';

    /**
     * Controls whether the goals footer icon is shown.
     *
     * Default value: false
     */
    const SHOW_GOALS = 'show_goals';

    /**
     * Array property mapping DataTable column names with their internationalized names.
     *
     * The value you specify for this property is merged with the default value so you
     * don't have to specify translations that already exist in the default value.
     *
     * Default value: Array containing translations of common metrics.
     */
    const COLUMN_NAME_TRANSLATIONS = 'translations';

    /**
     * Controls which column to sort the DataTable by before truncating and displaying.
     *
     * Default value: If the report contains nb_uniq_visitors and nb_uniq_visitors is a
     *                displayed column, then the default value is 'nb_uniq_visitors'.
     *                Otherwise, it is 'nb_visits'.
     */
    const SORTED_COLUMN = 'filter_sort_column';

    /**
     * Controls the sort order. Either 'asc' or 'desc'.
     *
     * Default value: 'desc'
     *
     * @see self::SORTED_COLUMN
     */
    const SORT_ORDER = 'filter_sort_order';

    /**
     * The number of items to truncate the data set to before rendering the DataTable view.
     *
     * Default value: false
     *
     * @see self::OFFSET
     */
    const LIMIT = 'filter_limit';

    /**
     * The number of items from the start of the data set that should be ignored.
     *
     * Default value: 0
     *
     * @see self::LIMIT
     */
    const OFFSET = 'filter_offset';

    /**
     * Controls whether the 'Exclude Low Population' option (visible in the popup that displays after
     * clicking the 'cog' icon) is shown.
     *
     * Default value: true
     */
    const SHOW_EXCLUDE_LOW_POPULATION = 'show_exclude_low_population';

    /**
     * Whether to show the 'Flatten' option (visible in the popup that displays after clicking the
     * 'cog' icon).
     *
     * Default value: true
     */
    const SHOW_FLATTEN_TABLE = 'show_flatten_table';

    /**
     * Controls whether the footer icon that allows user to switch to the 'normal' DataTable view
     * is shown.
     *
     * Default value: true
     */
    const SHOW_NORMAL_TABLE_VIEW = 'show_table';

    /**
     * Controls whether the 'All Columns' footer icon is shown.
     *
     * Default value: true
     */
    const SHOW_ALL_TABLE_VIEW = 'show_table_all_columns';

    /**
     * Controls whether the entire view footer is shown.
     *
     * Default value: true
     */
    const SHOW_FOOTER = 'show_footer';

    /**
     * Controls whether the row that contains all footer icons & the limit selector is shown.
     *
     * Default value: true
     */
    const SHOW_FOOTER_ICONS = 'show_footer_icons';

    /**
     * Array property that determines which columns will be shown. Columns not in this array
     * should not appear in ViewDataTable visualizations.
     *
     * Example: array('label', 'nb_visits', 'nb_uniq_visitors')
     *
     * Default value: array('label', 'nb_visits') or array('label', 'nb_uniq_visitors') if
     *                the report contains a nb_uniq_visitors column.
     */
    const COLUMNS_TO_DISPLAY = 'columns_to_display';

    /**
     * Controls whether the footer icons that change the ViewDataTable type of a view are shown
     * or not.
     *
     * Default value: true
     */
    const SHOW_ALL_VIEW_ICONS = 'show_all_views_icons';

    /**
     * Controls whether to display a tiny upside-down caret over the currently active view icon.
     *
     * Default value: true
     */
    const SHOW_ACTIVE_VIEW_ICON = 'show_active_view_icon';

    /**
     * Related reports are listed below a datatable view. When clicked, the original report will
     * change to the clicked report and the list will change so the original report can be
     * navigated back to.
     *
     * @see also self::TITLE. Both must be set if associating related reports.
     *
     * Default value: array()
     */
    const RELATED_REPORTS = 'related_reports';

    /**
     * The report title. Used with related reports so report headings can be changed when switching
     * reports.
     *
     * @see also self::RELATED_REPORTS. This must be set if related reports are added.
     *
     * Default value: ''
     */
    const TITLE = 'title';

    /**
     * Controls whether a report's related reports are listed with the view or not.
     *
     * Default value: true
     */
    const SHOW_RELATED_REPORTS = 'show_related_reports';

    /**
     * Contains the documentation for a report.
     *
     * Default value: false
     */
    const REPORT_DOCUMENTATION = 'documentation';

    /**
     * An array property that contains query parameter name/value overrides for API requests made
     * by ViewDataTable.
     *
     * E.g. array('idSite' => ..., 'period' => 'month')
     *
     * Default value: array()
     */
    const REQUEST_PARAMETERS_TO_MODIFY = 'request_parameters_to_modify';

    /**
     * A regex pattern to use to filter the DataTable before it is shown.
     *
     * @see also self::FILTER_PATTERN_COLUMN
     *
     * Default value: false
     */
    const FILTER_PATTERN = 'filter_pattern';

    /**
     * The column to apply a filter pattern to.
     *
     * @see also self::FILTER_PATTERN
     *
     * Default value: false
     */
    const FILTER_PATTERN_COLUMN = 'filter_column';

    /**
     * Array property containing custom data to be saved in JSON in the data-params HTML attribute
     * of a data table div. This data can be used by JavaScript DataTable classes.
     *
     * e.g. array('typeReferrer' => ...)
     *
     * Default value: array()
     */
    const CUSTOM_PARAMETERS = 'custom_parameters';

    /**
     * Whether to run generic filters on the DataTable before rendering or not.
     *
     * @see Piwik_API_DataTableGenericFilter
     *
     * Default value: false
     */
    const DISABLE_GENERIC_FILTERS = 'disable_generic_filters';

    /**
     * Whether to run ViewDataTable's list of queued filters or not.
     *
     * NOTE: Priority queued filters are always run.
     *
     * Default value: false
     */
    const DISABLE_QUEUED_FILTERS = 'disable_queued_filters';

    /**
     * Controls whether the limit dropdown (which allows users to change the number of data shown)
     * is always shown or not.
     *
     * Normally shown only if pagination is enabled.
     *
     * Default value: true
     */
    const ALWAYS_SHOW_LIMIT_DROPDOWN = 'show_limit_control';

    /**
     * Controls whether the search box under the datatable is shown.
     *
     * Default value: true
     */
    const SHOW_SEARCH_BOX = 'show_search';

    /**
     * Controls whether the user can sort DataTables by clicking on table column headings.
     *
     * Default value: true
     */
    const ENABLE_SORT = 'enable_sort';

    /**
     * Controls whether the footer icon that allows users to view data as a bar chart is shown.
     *
     * Default value: true
     */
    const SHOW_BAR_CHART_ICON = 'show_bar_chart';

    /**
     * Controls whether the footer icon that allows users to view data as a pie chart is shown.
     *
     * Default value: true
     */
    const SHOW_PIE_CHART_ICON = 'show_pie_chart';

    /**
     * Controls whether the footer icon that allows users to view data as a tag cloud is shown.
     *
     * Default value: true
     */
    const SHOW_TAG_CLOUD = 'show_tag_cloud';

    /**
     * Controls whether the user is allowed to export data as an RSS feed or not.
     *
     * Default value: true
     */
    const SHOW_EXPORT_AS_RSS_FEED = 'show_export_as_rss_feed';

    /**
     * Controls whether the 'Ecoommerce Orders'/'Abandoned Cart' footer icons are shown or not.
     *
     * Default value: false
     */
    const SHOW_ECOMMERCE_FOOTER_ICONS = 'show_ecommerce';

    /**
     * Stores the column name to filter when filtering out rows with low values.
     *
     * @see also self::EXCLUDE_LOW_POPULATION_VALUE
     *
     * Default value: false
     */
    const EXCLUDE_LOW_POPULATION_COLUMN = 'filter_excludelowpop';

    /**
     * Stores the value considered 'low' when filtering out rows w/ low values.
     *
     * @see also self::EXCLUDE_LOW_POPULATION_COLUMN
     *
     * Default value: false
     */
    const EXCLUDE_LOW_POPULATION_VALUE = 'filter_excludelowpop_value';

    /**
     * Stores an HTML message (if any) to display under the datatable view.
     *
     * Default value: false
     */
    const FOOTER_MESSAGE = 'show_footer_message';

    /**
     * Array property that stores documentation for individual metrics.
     *
     * E.g. array('nb_visits' => '...', ...)
     *
     * Default: Set to values retrieved from report metadata (via API.getReportMetadata API method).
     */
    const METRIC_DOCUMENTATION = 'metrics_documentation';

    /**
     * Row metadata name that contains the tooltip for the specific row.
     *
     * Default value: false
     */
    const TOOLTIP_METADATA_NAME = 'tooltip_metadata_name';

    /**
     * The URL to the report the view is displaying. Modifying this means clicking back to this report
     * from a Related Report will go to a different URL. Can be used to load an entire page instead
     * of a single report when going back to the original report.
     *
     * @see also self::RELATED_REPORTS
     *
     * Default value: The URL used to request the report without generic filters.
     */
    const THIS_REPORT_URL = 'self_url';

    /**
     * Special property that holds the properties for DataTable Visualizations.
     *
     * @see Piwik\ViewDataTable\VisualizationProperties
     */
    const VISUALIZATION_PROPERTIES = 'visualization_properties';

    /**
     * CSS class to use in the output HTML div. This is added in addition to the visualization CSS
     * class.
     *
     * Default value: false
     */
    const DATATABLE_CSS_CLASS = 'datatable_css_class';

    /**
     * The JavaScript class to instantiate after the result HTML is obtained. This class handles all
     * interactive behavior for the DataTable view.
     *
     * Default value: 'DataTable'
     */
    const DATATABLE_JS_TYPE = 'datatable_js_type';

    /**
     * If true, searching through the DataTable will search through all subtables.
     *
     * @see also self::FILTER_PATTERN
     *
     * Default value: false
     */
    const DO_RECURSIVE_SEARCH = 'search_recursive';

    /**
     * The unit of the displayed column. Valid if only one non-label column is displayed.
     *
     * Default value: false
     */
    const DISPLAYED_COLUMN_UNIT = 'y_axis_unit';

    /**
     * Controls whether to show the 'Export as Image' footer icon.
     *
     * Default value: false
     */
    const SHOW_EXPORT_AS_IMAGE_ICON = 'show_export_as_image_icon';

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
    const FILTERS = 'filters';

    /**
     * Array of callbacks that are called after the data for a ViewDataTable is successfully
     * loaded. Each callback is invoked with the DataTable instance obtained from the API
     * and the ViewDatable instance that loaded it.
     *
     * Functions can be appended to this array property when it's necessary to configure
     * a ViewDataTable after data has been loaded. If you need to use properties that are
     * only set after data is loaded (like 'columns_to_display'), you'll have to use this
     * property.
     *
     * Default value: array()
     */
    const AFTER_DATA_LOADED_FUNCTIONS = 'after_data_loaded_functions';

    /**
     * Contains the controller action to call when requesting subtables of the current report.
     *
     * Default value: The controller action used to request the report.
     */
    const SUBTABLE_CONTROLLER_ACTION = 'subtable_controller_action';

    /**
     * Controls whether the 'prev'/'next' links are shown in the DataTable footer. These links
     * change the 'filter_offset' query parameter, thus allowing pagination.
     *
     * TODO: pagination/offset is only valid for HtmlTables... should only display for those visualizations.
     *
     * @see self::SHOW_OFFSET_INFORMATION
     *
     * Default value: true
     */
    const SHOW_PAGINATION_CONTROL = 'show_pagination_control';

    /**
     * Controls whether offset information (ie, '5-10 of 20') is shown under the datatable.
     *
     * @see self::SHOW_PAGINATION_CONTROL
     *
     * Default value: true
     */
    const SHOW_OFFSET_INFORMATION = 'show_offset_information';

    /**
     * Controls whether annotations are shown or not.
     *
     * Default value: true
     */
    const HIDE_ANNOTATIONS_VIEW = 'hide_annotations_view';

    /**
     * The filter_limit query parameter value to use in export links.
     *
     * Default value: The value of the 'API_datatable_default_limit' config option.
     */
    const EXPORT_LIMIT = 'export_limit';

    /**
     * Controls whether non-Core DataTable visualizations are shown or not.
     *
     * Default value: true
     */
    const SHOW_NON_CORE_VISUALIZATIONS = 'show_non_core_visualizations';

    /**
     * The list of ViewDataTable properties that are 'Client Side Parameters'.
     *
     * @see Piwik\ViewDataTable\Visualization::getClientSideParameters
     */
    public static $clientSideParameters = array(
        'filter_excludelowpop',
        'filter_excludelowpop_value',
        'filter_pattern',
        'filter_column',
        'filter_offset'
    );

    /**
     * The list of ViewDataTable properties that are 'Client Side Properties'.
     *
     * @see Piwik\ViewDataTable\Visualization::getClientSideProperties
     */
    public static $clientSideProperties = array(
        'show_limit_control'
    );

    /**
     * The list of ViewDataTable properties that can be overriden by query parameters.
     *
     * @see Piwik\ViewDataTable\Visualization::getOverridableProperties
     */
    public static $overridableProperties = array(
        'show_goals',
        'filter_sort_column',
        'filter_sort_order',
        'filter_limit',
        'filter_offset',
        'filter_pattern',
        'filter_column',
        'disable_generic_filters',
        'disable_queued_filters',
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
        'filter_excludelowpop',
        'filter_excludelowpop_value',
        'search_recursive',
        'show_export_as_image_icon',
        'show_pagination_control',
        'show_offset_information',
        'hide_annotations_view',
        'export_limit',
        'show_non_core_visualizations'
    );

    /**
     * Returns the set of all valid ViewDataTable properties. The result is an array with property
     * names as keys. Values of the array are undefined.
     *
     * @return array
     */
    public static function getAllProperties()
    {
        static $propertiesCache = null;

        if ($propertiesCache === null) {
            $propertiesCache = self::getFlippedClassConstantMap(__CLASS__);
        }

        return $propertiesCache;
    }

    /**
     * Returns the set of all valid properties for the given visualization class. The result is an
     * array with property names as keys. Values of the array are undefined.
     *
     * @param string $visualizationClass
     *
     * @return array
     */
    public static function getVisualizationProperties($visualizationClass)
    {
        static $propertiesCache = array();

        if ($visualizationClass === null) {
            return array();
        }

        if (!isset($propertiesCache[$visualizationClass])) {
            $properties = self::getFlippedClassConstantMap($visualizationClass);

            $parentClass = get_parent_class($visualizationClass);
            if ($parentClass != 'Piwik\\ViewDataTable\\Visualization') {
                $properties += self::getVisualizationProperties($parentClass);
            }

            $propertiesCache[$visualizationClass] = $properties;
        }

        return $propertiesCache[$visualizationClass];
    }

    /**
     * Returns true if $name is a core ViewDataTable property, false if not.
     *
     * @param string $name
     * @return bool
     */
    public static function isCoreViewProperty($name)
    {
        $properties = self::getAllProperties();
        return isset($properties[$name]);
    }

    /**
     * Returns true if $name is a valid visualization property for the given visualization class.
     */
    public static function isValidVisualizationProperty($visualizationClass, $name)
    {
        $properties = self::getVisualizationProperties($visualizationClass);
        return isset($properties[$name]);
    }

    /**
     * Checks if a property is a valid ViewDataTable property, and if not, throws an exception.
     *
     * @param string $name The property name.
     * @throws Exception
     */
    public static function checkValidPropertyName($name)
    {
        if (!self::isCoreViewProperty($name)) {
            throw new Exception("Invalid ViewDataTable display property '$name'.");
        }
    }

    /**
     * Checks if a property is a valid visualization property for the given visualization class,
     * and if not, throws an exception.
     *
     * @param string $visualizationClass
     * @param string $name The property name.
     * @throws Exception
     */
    public static function checkValidVisualizationProperty($visualizationClass, $name)
    {
        if (!self::isValidVisualizationProperty($visualizationClass, $name)) {
            throw new Exception("Invalid Visualization display property '$name' for '$visualizationClass'.");
        }
    }

    /**
     * Returns the default values for each core view property.
     *
     * @return array
     */
    public static function getDefaultPropertyValues()
    {
        return array(
            'footer_icons'                 => false,
            'show_visualization_only'      => false,
            'datatable_js_type'            => 'DataTable',
            'show_goals'                   => false,
            'show_ecommerce'               => false,
            'show_search'                  => true,
            'show_table'                   => true,
            'show_table_all_columns'       => true,
            'show_all_views_icons'         => true,
            'show_active_view_icon'        => true,
            'show_bar_chart'               => true,
            'show_pie_chart'               => true,
            'show_tag_cloud'               => true,
            'show_export_as_image_icon'    => false,
            'show_export_as_rss_feed'      => true,
            'show_exclude_low_population'  => true,
            'show_flatten_table'           => true,
            'show_offset_information'      => true,
            'show_pagination_control'      => true,
            'show_limit_control'           => true,
            'show_footer'                  => true,
            'show_footer_icons'            => true,
            'show_footer_message'          => false,
            'show_related_reports'         => true,
            'show_non_core_visualizations' => true,
            'export_limit'                 => Config::getInstance()->General['API_datatable_default_limit'],
            'related_reports'              => array(),
            'title'                        => '',
            'tooltip_metadata_name'        => false,
            'enable_sort'                  => true,
            'disable_generic_filters'      => false,
            'disable_queued_filters'       => false,
            'search_recursive'             => false,
            'filter_excludelowpop'         => false,
            'filter_excludelowpop_value'   => false,
            'filter_pattern'               => false,
            'filter_column'                => false,
            'filter_limit'                 => false,
            'filter_offset'                => 0,
            'filter_sort_column'           => false,
            'filter_sort_order'            => 'desc',
            'custom_parameters'            => array(),
            'translations'                 => array_merge(
                Metrics::getDefaultMetrics(),
                Metrics::getDefaultProcessedMetrics()
            ),
            'request_parameters_to_modify' => array(),
            'documentation'                => false,
            'datatable_css_class'          => false,
            'filters'                      => array(),
            'after_data_loaded_functions'  => array(),
            'hide_annotations_view'        => true,
            'columns_to_display'           => array(),
            'y_axis_unit'                  => false
        );
    }

    private static function getFlippedClassConstantMap($klass)
    {
        $klass = new ReflectionClass($klass);
        $constants = $klass->getConstants();

        unset($constants['ID']);
        unset($constants['FOOTER_ICON']);
        unset($constants['FOOTER_ICON_TITLE']);

        foreach ($constants as $name => $value) {
            if (!is_string($value)) {
                unset($constants[$name]);
            }
        }

        return array_flip($constants);
    }
}