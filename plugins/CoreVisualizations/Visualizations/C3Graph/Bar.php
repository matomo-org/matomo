<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\C3Graph;

use Piwik\DataTable\Renderer\Json;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;

// TODO: make base graph tile
class Bar extends Graph
{
    const ID = 'graphVerticalBarC3';
    const FOOTER_ICON       = 'icon-chart-bar';
    const FOOTER_ICON_TITLE = 'General_VBarGraph';
    const TEMPLATE_FILE = '@CoreVisualizations/_dataTableViz_c3Graph.twig';

    public function beforeLoadDataTable()
    {
        parent::beforeLoadDataTable();

        $this->checkRequestIsNotForMultiplePeriods();

        $this->config->datatable_js_type = 'C3BarGraphDataTable';
    }

    public function getGraphData($dataTable)
    {
        $renderer = new Json();
        $renderer->setTable($dataTable);
        $result = $renderer->render();
        return $result;
    }

    public static function getDefaultConfig()
    {
        $config = new Config();
        $config->max_graph_elements = 6;

        return $config;
    }
}

/*
milestones:
X show empty page w/ text
X load c3 and use w/ fake data in JS (use angular directive)
- convert datatable data and make it display
- make list of shit to handle, and post in slack w/ list of options in the future

setTimeout(function () {
    chart.load({
        columns: [
            ['data3', 130, -150, 200, 300, -200, 100]
        ]
    });
}, 1000);
 */