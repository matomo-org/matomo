<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Live\Visualizations\VisitorLog;

use Piwik\ViewDataTable\Config as VisualizationConfig;

/**
 * DataTable Visualization that derives from HtmlTable and sets show_extra_columns to true.
 */
class Config extends VisualizationConfig
{
    /**
     * Controls whether any DataTable Row Action icons are shown. If true, no icons are shown.
     *
     * Default value: false
     */
    public $disable_row_actions = false;

    public function __construct()
    {
        parent::__construct();

        $this->addPropertiesThatShouldBeAvailableClientSide(array(
            'disable_row_actions',
        ));

        $this->addPropertiesThatCanBeOverwrittenByQueryParams(array(
            'disable_row_actions',
        ));
    }

}
