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
use Piwik\Config as PiwikConfig;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Site;
use Piwik\View;
use Piwik\Plugin\Visualization;

require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/HtmlTable/AllColumns.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/HtmlTable/Goals.php';

/**
 * DataTable visualization that shows DataTable data in an HTML table.
 */
class HtmlTable extends Visualization
{
    const ID = 'table';
    const TEMPLATE_FILE = "@CoreVisualizations/_dataTableViz_htmlTable.twig";

    public function getDefaultConfig()
    {
        return new HtmlTable\Config();
    }

    public function getDefaultRequestConfig()
    {
        $config = new \Piwik\Visualization\Request();
        $config->filter_limit = PiwikConfig::getInstance()->General['datatable_default_limit'];

        if (Common::getRequestVar('enable_filter_excludelowpop', false) == '1') {
            $config->filter_excludelowpop       = 'nb_visits';
            $config->filter_excludelowpop_value = null;
        }

        $config->addPropertiesThatShouldBeAvailableClientSide(array(
            'search_recursive',
            'filter_limit',
            'filter_offset',
            'filter_sort_column',
            'filter_sort_order',
        ));

        return $config;
    }

    public function configureVisualization()
    {
        if (Common::getRequestVar('idSubtable', false)
            && $this->config->show_embedded_subtable
        ) {
            $this->config->show_visualization_only = true;
        }
    }

}