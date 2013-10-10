<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package TreemapVisualization
 */

namespace Piwik\Plugins\ExampleVisualization;

use Piwik\ViewDataTable\Visualization;

/**
 * Simple Visualization Example.
 */
class SimpleTable extends Visualization
{
    const TEMPLATE_FILE     = '@ExampleVisualization/simpleTable.twig';
    const FOOTER_ICON_TITLE = 'Simple Table';
    const FOOTER_ICON       = 'plugins/ExampleVisualization/images/table.png';

    /**
     * You do not have to implement the init method. It is just an example how to assign view variables.
     */
    public function init()
    {
        $this->vizTitle = 'MyAwesomeTitle';
    }
}