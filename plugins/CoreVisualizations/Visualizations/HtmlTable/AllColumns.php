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

namespace Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Visualization\Config;

/**
 * DataTable Visualization that derives from HtmlTable and sets show_extra_columns to true.
 */
class AllColumns extends HtmlTable
{
    const ID = 'tableAllColumns';

    public function configureVisualization(Config $properties)
    {
        $properties->visualization_properties->show_extra_columns = true;

        parent::configureVisualization($properties);
    }
}