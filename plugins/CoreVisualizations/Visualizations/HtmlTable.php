<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreVisualizations
 */
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Site;
use Piwik\View;
use Piwik\ViewDataTable\Visualization;
use Piwik\Visualization\Config as VizConfig;

require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/HtmlTable/AllColumns.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/HtmlTable/Goals.php';

/**
 * DataTable visualization that shows DataTable data in an HTML table.
 */
class HtmlTable extends Visualization
{
    const ID = 'table';

    const TEMPLATE_FILE = "@CoreVisualizations/_dataTableViz_htmlTable.twig";

    /**
     * If this property is set to true, subtables will be shown as embedded in the original table.
     * If false, subtables will be shown as whole tables between rows.
     *
     * Default value: false
     */
    const SHOW_EMBEDDED_SUBTABLE = 'show_embedded_subtable';

    /**
     * Controls whether the entire DataTable should be rendered (including subtables) or just one
     * specific table in the tree.
     *
     * Default value: false
     */
    const SHOW_EXPANDED = 'show_expanded';

    /**
     * When showing an expanded datatable, this property controls whether rows with subtables are
     * replaced with their subtables, or if they are shown alongside their subtables.
     *
     * Default value: false
     */
    const REPLACE_ROW_WITH_SUBTABLE = 'replace_row_with_subtable';

    /**
     * Controls whether any DataTable Row Action icons are shown. If true, no icons are shown.
     *
     * @see also self::DISABLE_ROW_EVOLUTION
     *
     * Default value: false
     */
    const DISABLE_ROW_ACTIONS = 'disable_row_actions';

    /**
     * Controls whether the row evolution DataTable Row Action icon is shown or not.
     *
     * @see also self::DISABLE_ROW_ACTIONS
     *
     * Default value: false
     */
    const DISABLE_ROW_EVOLUTION = 'disable_row_evolution';

    /**
     * If true, the 'label', 'nb_visits', 'nb_uniq_visitors' (if present), 'nb_actions',
     * 'nb_actions_per_visit', 'avg_time_on_site', 'bounce_rate' and 'conversion_rate' (if
     * goals view is not allowed) are displayed.
     *
     * Default value: false
     */
    const SHOW_EXTRA_COLUMNS = 'show_extra_columns';

    /**
     * If true, conversions for each existing goal will be displayed for the visits in
     * each row.
     *
     * Default value: false
     */
    const SHOW_GOALS_COLUMNS = 'show_goals_columns';

    /**
     * If true, subtables will not be loaded when rows are clicked, but only if the
     * 'show_goals_columns' property is also true.
     *
     * @see also self::SHOW_GOALS_COLUMNS
     *
     * Default value: false
     */
    const DISABLE_SUBTABLE_IN_GOALS_VIEW = 'disable_subtable_when_show_goals';

    /**
     * Controls whether the summary row is displayed on every page of the datatable view or not.
     * If false, the summary row will be treated as the last row of the dataset and will only visible
     * when viewing the last rows.
     *
     * Default value: false
     */
    const KEEP_SUMMARY_ROW = 'keep_summary_row';

    /**
     * If true, the summary row will be colored differently than all other DataTable rows.
     *
     * @see also self::KEEP_SUMMARY_ROW
     *
     * Default value: false
     */
    const HIGHLIGHT_SUMMARY_ROW = 'highlight_summary_row';

    static public $clientSideRequestParameters = array(
        'search_recursive',
        'filter_limit',
        'filter_offset',
        'filter_sort_column',
        'filter_sort_order',
    );

    static public $clientSideConfigProperties = array(
        'show_extra_columns',
        'show_goals_columns',
        'disable_row_evolution',
        'disable_row_actions',
        'enable_sort',
        'keep_summary_row',
        'subtable_controller_action',
    );

    public static $overridableProperties = array(
        'show_expanded',
        'disable_row_actions',
        'disable_row_evolution',
        'show_extra_columns',
        'show_goals_columns',
        'disable_subtable_when_show_goals',
        'keep_summary_row',
        'highlight_summary_row',
    );

    public function configureVisualization(VizConfig $properties)
    {
        if (Common::getRequestVar('idSubtable', false)
            && $properties->visualization_properties->show_embedded_subtable
        ) {
            $properties->show_visualization_only = true;
        }
    }

    public static function getDefaultPropertyValues()
    {
        $defaults = array(
            'enable_sort'              => true,
            'datatable_js_type'        => 'DataTable',
            'filter_limit'             => Config::getInstance()->General['datatable_default_limit'],
            'visualization_properties' => array(
                'table' => array(
                    'disable_row_evolution'            => false,
                    'disable_row_actions'              => false,
                    'show_extra_columns'               => false,
                    'show_goals_columns'               => false,
                    'disable_subtable_when_show_goals' => false,
                    'keep_summary_row'                 => false,
                    'highlight_summary_row'            => false,
                    'show_expanded'                    => false,
                    'show_embedded_subtable'           => false
                ),
            ),
        );

        if (Common::getRequestVar('enable_filter_excludelowpop', false) == '1') {
            $defaults['filter_excludelowpop'] = 'nb_visits';
            $defaults['filter_excludelowpop_value'] = null;
        }

        return $defaults;
    }

}