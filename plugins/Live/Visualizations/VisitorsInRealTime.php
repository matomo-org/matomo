<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\Visualizations;

use Piwik\Plugin\Visualization;

class VisitorsInRealTime extends Visualization
{
    const ID = 'VisitorLogWidget';
    const TEMPLATE_FILE = '@Live/getLastVisitsStart.twig';
    const FOOTER_ICON_TITLE = '';
    const FOOTER_ICON = '';


    /**
     * Configure visualization.
     */
    public function beforeRender()
    {
        $this->config->show_visualization_only = true;
    }

    public function beforeLoadDataTable()
    {
        $this->requestConfig->addPropertiesThatShouldBeAvailableClientSide(array(
            'filter_limit',
            'filter_offset',
            'filter_sort_column',
            'filter_sort_order',
        ));

        if (!is_numeric($this->requestConfig->filter_limit)) {
            $this->requestConfig->filter_limit = 10;
        }

        $this->requestConfig->disable_generic_filters = true;
        $this->requestConfig->filter_sort_column      = false;
    }

    public static function canDisplayViewDataTable(ViewDataTable $view)
    {
        return ($view->requestConfig->getApiModuleToRequest() === 'Live');
    }
}