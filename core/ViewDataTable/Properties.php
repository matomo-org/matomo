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
use ReflectionClass;
use Piwik\Piwik;
use Piwik\Config;
use Piwik\Metrics;
use Piwik\Common;

/**
 * Contains the list of all core DataTable display properties for use with ViewDataTable.
 *
 * @see ViewDataTable - for more info.
 *
 * TODO: list default value for each property
 */
class Properties
{
    /**
     * The default viewDataTable ID to use when determining which visualization to use.
     * This property is only valid for reports whose properties are determined by the
     * ViewDataTable.getReportDisplayProperties event. When manually creating ViewDataTables,
     * setting this property will have no effect.
     */
    const DEFAULT_VIEW_TYPE = 'default_view_type';

    /**
     * This property determines which Twig template to use when rendering a ViewDataTable.
     *
     * TODO: shouldn't have this property. should only use visualization classes.
     */
    const DATATABLE_TEMPLATE = 'datatable_template';

    /**
     * Controls whether the goals footer icon is shown.
     */
    const SHOW_GOALS = 'show_goals';

    /**
     * Array property mapping DataTable column names with their internationalized names.
     */
    const COLUMN_NAME_TRANSLATIONS = 'translations';

    /**
     * Controls which column to sort the DataTable by before truncating and displaying.
     */
    const SORTED_COLUMN = 'filter_sort_column';

    /**
     * Controls the sort order. Either 'asc' or 'desc'.
     *
     * @see self::SORTED_COLUMN
     */
    const SORT_ORDER = 'filter_sort_order';

    /**
     * The number of items to truncate the data set to before rendering the DataTable view.
     * 
     * @see self::OFFSET
     */
    const LIMIT = 'filter_limit';

    /**
     * The number of items from the start of the data set that should be ignored.
     * 
     * @see self::LIMIT
     */
    const OFFSET = 'filter_offset';

    /**
     * Controls whether the 'Exclude Low Population' option (visible in the popup that displays after
     * clicking the 'cog' icon) is shown.
     */
    const SHOW_EXCLUDE_LOW_POPULATION = 'show_exclude_low_population';

    /**
     * Controls whether the footer icon that allows user to switch to the 'normal' DataTable view
     * is shown.
     */
    const SHOW_NORMAL_TABLE_VIEW = 'show_table';

    /**
     * Controls whether the 'All Columns' footer icon is shown.
     */
    const SHOW_ALL_TABLE_VIEW = 'show_table_all_columns';

    /**
     * Controls whether the entire view footer is shown.
     */
    const SHOW_FOOTER = 'show_footer';

    /**
     * Controls whether the row that contains all footer icons & the limit selector is shown.
     */
    const SHOW_FOOTER_ICONS = 'show_footer_icons';

    /**
     * Array property that determines which columns will be shown. Columns not in this array
     * should not appear in ViewDataTable visualizations.
     *
     * Example: array('label', 'nb_visits', 'nb_uniq_visitors')
     */
    const COLUMNS_TO_DISPLAY = 'columns_to_display';

    /**
     * Whether to display the logo assocatied with a DataTable row (stored as 'logo' row metadata)
     * isntead of the label in Tag Clouds.
     */
    const DISPLAY_LOGO_INSTEAD_OF_LABEL = 'display_logo_instead_of_label';

    /**
     * Controls whether the footer icons that change the ViewDataTable type of a view are shown
     * or not.
     */
    const SHOW_ALL_VIEW_ICONS = 'show_all_views_icons';

    /**
     * Controls whether to display a tiny upside-down caret over the currently active view icon.
     */
    const SHOW_ACTIVE_VIEW_ICON = 'show_active_view_icon';

    /**
     * Related reports are listed below a datatable view. When clicked, the original report will
     * change to the clicked report and the list will change so the original report can be
     * navigated back to.
     * 
     * @see also self::TITLE. Both must be set if associating related reports.
     */
    const RELATED_REPORTS = 'related_reports';

    /**
     * The report title. Used with related reports so report headings can be changed when switching
     * reports.
     * 
     * @see also self::RELATED_REPORTS. This must be set if related reports are added.
     */
    const TITLE = 'title';

    /**
     * Controls whether a report's related reports are listed with the view or not.
     */
    const SHOW_RELATED_REPORTS = 'show_related_reports';

    /**
     * Contains the documentation for a report.
     */
    const REPORT_DOCUMENTATION = 'documentation';

    /**
     * An array property that contains query parameter name/value overrides for API requests made
     * by ViewDataTable.
     * 
     * E.g. array('idSite' => ..., 'period' => 'month')
     */
    const REQUEST_PARAMETERS_TO_MODIFY = 'request_parameters_to_modify';

    /**
     * A regex pattern to use to filter the DataTable before it is shown.
     * 
     * @see also self::FILTER_PATTERN_COLUMN
     */
    const FILTER_PATTERN = 'filter_pattern';

    /**
     * The column to apply a filter pattern to.
     * 
     * @see also self::FILTER_PATTERN
     */
    const FILTER_PATTERN_COLUMN = 'filter_column';

    /**
     * Array property containing custom data to be saved in JSON in the data-params HTML attribute
     * of a data table div. This data can be used by JavaScript DataTable classes.
     * 
     * e.g. array('typeReferer' => ...)
     */
    const CUSTOM_PARAMETERS = 'custom_parameters';

    /**
     * Contains the column (if any) of the values used in the Row Picker.
     * 
     * @see self::ROW_PICKER_VISIBLE_VALUES
     */
    const ROW_PICKER_VALUE_COLUMN = 'row_picker_match_rows_by';

    /**
     * Contains the list of values available for the Row Picker.
     * 
     * @see self::ROW_PICKER_VALUE_COLUMN
     */
    const ROW_PICKER_VISIBLE_VALUES = 'row_picker_visible_rows';

    /**
     * Whether to run generic filters on the DataTable before rendering or not.
     * 
     * @see Piwik_API_DataTableGenericFilter
     */
    const DISABLE_GENERIC_FILTERS = 'disable_generic_filters';

    /**
     * Whether to run ViewDataTable's list of queued filters or not.
     * 
     * NOTE: Priority queued filters are always run.
     */
    const DISABLE_QUEUED_FILTERS = 'disable_queued_filters';

    /**
     * Controls whether the limit dropdown (which allows users to change the number of data shown)
     * is always shown or not.
     * 
     * Normally shown only if pagination is enabled.
     */
    const ALWAYS_SHOW_LIMIT_DROPDOWN = 'show_limit_control';

    /**
     * Controls whether the search box under the datatable is shown.
     */
    const SHOW_SEARCH_BOX = 'show_search';

    /**
     * Controls whether the user can sort DataTables by clicking on table column headings.
     */
    const ENABLE_SORT = 'enable_sort';

    /**
     * Controls whether the footer icon that allows users to view data as a bar chart is shown.
     */
    const SHOW_BAR_CHART_ICON = 'show_bar_chart';

    /**
     * Controls whether the footer icon that allows users to view data as a pie chart is shown.
     */
    const SHOW_PIE_CHART_ICON = 'show_pie_chart';

    /**
     * Controls whether the footer icon that allows users to view data as a tag cloud is shown.
     */
    const SHOW_TAG_CLOUD = 'show_tag_cloud';

    /**
     * Controls whether the user is allowed to export data as an RSS feed or not.
     */
    const SHOW_EXPORT_AS_RSS_FEED = 'show_export_as_rss_feed';

    /**
     * Controls whether the 'Ecoommerce Orders'/'Abandoned Cart' footer icons are shown or not.
     */
    const SHOW_ECOMMERCE_FOOTER_ICONS = 'show_ecommerce';

    /**
     * Controls whether the summary row is displayed on every page of the datatable view or not.
     * If false, the summary row will be treated as the last row of the dataset and will only visible
     * when viewing the last rows.
     */
    const KEEP_SUMMARY_ROW = 'keep_summary_row';

    /**
     * Stores the column name to filter when filtering out rows with low values.
     * 
     * @see also self::EXCLUDE_LOW_POPULATION_VALUE
     */
    const EXCLUDE_LOW_POPULATION_COLUMN = 'filter_excludelowpop';

    /**
     * Stores the value considered 'low' when filtering out rows w/ low values.
     * 
     * @see also self::EXCLUDE_LOW_POPULATION_COLUMN
     */
    const EXCLUDE_LOW_POPULATION_VALUE = 'filter_excludelowpop_value';

    /**
     * Stores an HTML message (if any) to display under the datatable view.
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
     * If true, the summary row will be colored differently than all other DataTable rows.
     * 
     * @see also self::KEEP_SUMMARY_ROW
     */
    const HIGHLIGHT_SUMMARY_ROW = 'highlight_summary_row';

    /**
     * Row metadata name that contains the tooltip for the specific row.
     */
    const TOOLTIP_METADATA_NAME = 'tooltip_metadata_name';

    /**
     * The URL to the report the view is displaying. Modifying this means clicking back to this report
     * from a Related Report will go to a different URL. Can be used to load an entire page instead
     * of a single report when going back to the original report.
     * 
     * @see also self::RELATED_REPORTS
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
     */
    const DATATABLE_CSS_CLASS = 'datatable_css_class';

    /**
     * The JavaScript class to instantiate after the result HTML is obtained. This class handles all
     * interactive behavior for the DataTable view.
     */
    const DATATABLE_JS_TYPE = 'datatable_js_type';

    /**
     * If true, searching through the DataTable will search through all subtables.
     * 
     * @see also self::FILTER_PATTERN
     */
    const DO_RECURSIVE_SEARCH = 'search_recursive';

    /**
     * The unit of the displayed column. Valid if only one non-label column is displayed.
     */
    const DISPLAYED_COLUMN_UNIT = 'y_axis_unit';

    /**
     * Controls whether to show the 'Export as Image' footer icon.
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
     */
    const FILTERS = 'filters';

    /**
     * Contains the controller action to call when requesting subtables of the current report.
     */
    const SUBTABLE_CONTROLLER_ACTION = 'subtable_controller_action';

    /**
     * Controls whether the 'prev'/'next' links are shown in the DataTable footer. These links
     * change the 'filter_offset' query parameter, thus allowing pagination.
     * 
     * @see self::SHOW_OFFSET_INFORMATION
     */
    const SHOW_PAGINATION_CONTROL = 'show_pagination_control';

    /**
     * Controls whether offset information (ie, '5-10 of 20') is shown under the datatable. 
     * 
     * @see self::SHOW_PAGINATION_CONTROL
     */
    const SHOW_OFFSET_INFORMATION = 'show_offset_information';

    /**
     * Controls whether annotations are shown or not.
     */
    const HIDE_ANNOTATIONS_VIEW = 'hide_annotations_view';

    /**
     * The filter_limit query parameter value to use in export links.
     */
    const EXPORT_LIMIT = 'export_limit';

    /**
     * Controls whether non-Core DataTable visualizations are shown or not.
     */
    const SHOW_NON_CORE_VISUALIZATIONS = 'show_non_core_visualizations';

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
     * @param string  $visualizationClass
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
            if ($parentClass != 'Piwik\\DataTableVisualization') {
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
        $result = array(
            'datatable_template' => '@CoreHome/_dataTable',
            'show_goals' => false,
            'show_ecommerce' => false,
            'show_search' => true,
            'show_table' => true,
            'show_table_all_columns' => true,
            'show_all_views_icons' => true,
            'show_active_view_icon' => true,
            'show_bar_chart' => true,
            'show_pie_chart' => true,
            'show_tag_cloud' => true,
            'show_export_as_image_icon' => false,
            'show_export_as_rss_feed' => true,
            'show_exclude_low_population' => true,
            'show_offset_information' => true,
            'show_pagination_control' => true,
            'show_limit_control' => false,
            'show_footer' => true,
            'show_related_reports' => true,
            'show_non_core_visualizations' => true,
            'export_limit' => Config::getInstance()->General['API_datatable_default_limit'],
            'highlight_summary_row' => false,
            'related_reports' => array(),
            'title' => '',
            'tooltip_metadata_name' => false,
            'enable_sort' => true,
            'disable_generic_filters' => false,
            'disable_queued_filters' => false,
            'keep_summary_row' => false,
            'filter_excludelowpop' => false,
            'filter_excludelowpop_value' => false,
            'filter_pattern' => false,
            'filter_column' => false,
            'filter_limit' => false,
            'filter_offset' => 0,
            'filter_sort_column' => false,
            'filter_sort_order' => 'desc',
            'custom_parameters' => array(),
            'translations' => array_merge(
                Metrics::getDefaultMetrics(),
                Metrics::getDefaultProcessedMetrics()
            ),
            'request_parameters_to_modify' => array(),
            'documentation' => false,
            'datatable_css_class' => false,
            'filters' => array(),
            'hide_annotations_view' => true,
            'columns_to_display' => array(),
        );

        $columns = Common::getRequestVar('columns', false);
        if ($columns !== false) {
            $result['columns_to_display'] = Piwik::getArrayFromApiParameter($columns);
            array_unshift($result['columns_to_display'], 'label');
        }

        return $result;
    }

    private static function getFlippedClassConstantMap($klass)
    {
        $klass = new ReflectionClass($klass);
        $constants = $klass->getConstants();
        unset($constants['ID']);
        unset($constants['FOOTER_ICON']);
        unset($constants['FOOTER_ICON_TITLE']);
        return array_flip($constants);
    }
}