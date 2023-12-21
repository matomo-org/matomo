<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleUI\Reports;

use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Cloud;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Pie;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetPlanetRatios extends Base
{
    protected function init()
    {
        parent::init();

        $this->name = 'Pie graph';
        $this->documentation = 'This report shows a sample Pie chart';
        $this->subcategoryId = $this->name;
        $this->order = 112;
    }

    public function getDefaultTypeViewDataTable()
    {
        return Pie::ID;
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widgetsList->addWidgetConfig(
            // in this case it will render PIE as configured as default
            $factory->createWidget()
        );

        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                ->setName('Simple tag cloud')
                ->setSubcategoryId('Tag clouds')
                ->forceViewDataTable(Cloud::ID)
                ->setOrder(5)
        );
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->addTranslation('value', 'times the diameter of Earth');

        if ($view->isViewDataTableId(Pie::ID)) {

            $view->config->columns_to_display = array('value');
            $view->config->selectable_columns = array('value');
            $view->config->show_footer_icons = false;
            $view->config->max_graph_elements = 10;

        } else if ($view->isViewDataTableId(Cloud::ID)) {

            $view->config->columns_to_display = array('label', 'value');
            $view->config->show_footer = false;

        }
    }
}
