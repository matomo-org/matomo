<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_ImageGraph_StaticGraph
 */


/**
 *
 * @package Piwik_ImageGraph_StaticGraph
 */
class Piwik_ImageGraph_StaticGraph_VerticalBar extends Piwik_ImageGraph_StaticGraph_GridGraph
{
	const INTERLEAVE = 0.10;

	public function renderGraph()
	{
		$this->initGridChart(
			$displayVerticalGridLines = false, 
			$drawCircles = false,
			$horizontalGraph = false,
			$showTicks = true,
			$verticalLegend = false
		);

		$this->pImage->drawBarChart(
			array(
				 'Interleave' => self::INTERLEAVE,
			)
		);
	}
}
