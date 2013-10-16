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
 *
 * @property HtmlTable\Config $config
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
        return new HtmlTable\RequestConfig();
    }

    public function configureVisualization()
    {
        if (Common::getRequestVar('idSubtable', false)
            && $this->config->show_embedded_subtable) {

            $this->config->show_visualization_only = true;
        }
    }

}