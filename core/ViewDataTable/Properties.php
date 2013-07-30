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

/**
 * Contains the list of all core DataTable display properties for use with ViewDataTable.
 * 
 * @see Piwik_ViewDataTable for more info.
 * 
 * TODO: change the names of properties to match the const names where appropriate.
 */
class Piwik_ViewDataTable_Properties
{
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
     * The limit used when rendering a jqPlot graph.
     * 
     * TODO: either replace w/ filter_limit, or make it a visualization property.
     */
    const GRAPH_LIMIT = 'graph_limit';

    /**
     * The number of items to truncate the data set to before rendering the DataTable view.
     */
    const LIMIT = 'filter_limit';

    /**
     * Controls whether the 'Exclude Low Population' option (visible in the popup that displays after
     * clicking the 'cog' icon) is shown.
     */
    const SHOW_EXCLUDE_LOW_POPULATION = 'show_exclude_low_population';

    /**
     * Controls whether the 'All Columns' footer icon is shown.
     */
    const SHOW_ALL_TABLES_VIEW = 'show_table_all_columns';

    /**
     * Controls whether the Row Evolution datatable row action icon is shown.
     */
    const DISABLE_ROW_EVOLUTION = 'disable_row_evolution';

    /**
     * The unit to display in jqPlot graphs.
     * 
     * TODO: Either this should be a visualization property, or should be named something different.
     */
    const Y_AXIS_UNIT = 'y_axis_unit';

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
     * Returns the set of all valid ViewDataTable properties. The result is an array with property
     * name as a key. Values of the array are undefined.
     * 
     * @return array
     */
    public static function getAllProperties()
    {
        static $propertiesCache = null;

        if ($propertiesCache === null) {
            $klass = new ReflectionClass(__CLASS__);
            $propertiesCache = array_flip($klass->getConstants());
        }

        return $propertiesCache;
    }

    /**
     * Checks if a property is a valid ViewDataTable property, and if not, throws an exception.
     *
     * @param string $name The property name.
     * @throws Exception
     */
    public static function checkValidPropertyName($name)
    {
        $properties = self::getAllProperties();
        if (!isset($properties[$name])) {
            throw new Exception("Invalid ViewDataTable display property '$name'. Is this a visualization property? "
                              . "If so, set it with \$view->visualization_properties->$name = ...");
        }
    }
}