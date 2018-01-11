<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ImageGraph\StaticGraph;

/**
 *
 */
class Pie3D extends PieGraph
{
    public function renderGraph()
    {
        $this->initPieGraph(true);

        $this->pieChart->draw3DPie(
            $this->xPosition,
            $this->yPosition,
            $this->pieConfig
        );
    }
}
