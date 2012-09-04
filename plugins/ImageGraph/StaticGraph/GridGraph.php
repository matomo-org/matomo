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
abstract class Piwik_ImageGraph_StaticGraph_GridGraph extends Piwik_ImageGraph_StaticGraph
{
	const GRAPHIC_COLOR_KEY = 'GRAPHIC_COLOR';
	const VALUE_COLOR_KEY = 'VALUE_COLOR';
	const GRID_COLOR_KEY = 'GRID_COLOR';

	const DEFAULT_TICK_ALPHA = 20;
	const DEFAULT_SERIE_WEIGHT = 0.5;
	const LEFT_GRID_MARGIN = 4;
	const BOTTOM_GRID_MARGIN = 10;
	const TOP_GRID_MARGIN_HORIZONTAL_GRAPH = 1;
	const RIGHT_GRID_MARGIN_HORIZONTAL_GRAPH = 5;
	const LEGEND_LEFT_MARGIN = 4;
	const LEGEND_BOTTOM_MARGIN = 10;
	const OUTER_TICK_WIDTH = 5;
	const INNER_TICK_WIDTH = 0;
	const LABEL_SPACE_VERTICAL_GRAPH = 10;

	protected function getDefaultColors()
	{
		return array(
			self::VALUE_COLOR_KEY => '444444',
			self::GRID_COLOR_KEY => 'CCCCCC',
			self::GRAPHIC_COLOR_KEY . '1' => '5170AE',
			self::GRAPHIC_COLOR_KEY . '2' => 'F29007',
			self::GRAPHIC_COLOR_KEY . '3' => 'CC3399',
			self::GRAPHIC_COLOR_KEY . '4' => '9933CC',
			self::GRAPHIC_COLOR_KEY . '5' => '80A033',
			self::GRAPHIC_COLOR_KEY . '6' => '246AD2'
		);
	}

	protected function initGridChart(
		$displayVerticalGridLines,
		$drawCircles,
		$horizontalGraph,
		$showTicks
	)
	{
		$this->initpData();

		$colorIndex = 1;
		foreach($this->ordinateSeries as $column => $data)
		{
			$this->pData->setSerieWeight($column, self::DEFAULT_SERIE_WEIGHT);
			$graphicColor = $this->colors[self::GRAPHIC_COLOR_KEY . $colorIndex++];
			$this->pData->setPalette($column, $graphicColor);
		}

		$this->initpImage();

		// graph area coordinates
		$topLeftXValue = $this->getGridLeftMargin($horizontalGraph, $withLabel = true);
		$topLeftYValue = $this->getGridTopMargin($horizontalGraph);
		$bottomRightXValue = $this->width - $this->getGridRightMargin($horizontalGraph);
		$bottomRightYValue = $this->getGraphBottom();

		$this->pImage->setGraphArea(
			$topLeftXValue,
			$topLeftYValue,
			$bottomRightXValue,
			$bottomRightYValue
		);

		// determine how many labels need to be skipped
		$skippedLabels = 0;
		if(!$horizontalGraph)
		{
			$abscissaMaxWidthHeight = $this->maxWidthHeight($this->abscissaSeries);
			$abscissaMaxWidth = $abscissaMaxWidthHeight[self::WIDTH_KEY];
			$graphWidth = $bottomRightXValue - $topLeftXValue;
			$maxNumOfLabels = floor($graphWidth / ($abscissaMaxWidth + self::LABEL_SPACE_VERTICAL_GRAPH));

			$abscissaSeriesCount = count($this->abscissaSeries);
			if($maxNumOfLabels < $abscissaSeriesCount)
			{
				for($candidateSkippedLabels = 1 ; $candidateSkippedLabels < $abscissaSeriesCount; $candidateSkippedLabels++)
				{
					$numberOfSegments = $abscissaSeriesCount / ($candidateSkippedLabels + 1);
					$numberOfCompleteSegments = floor($numberOfSegments);

					$numberOfLabels = $numberOfCompleteSegments;
					if($numberOfSegments > $numberOfCompleteSegments)
					{
						$numberOfLabels++;
					}

					if($numberOfLabels <= $maxNumOfLabels )
					{
						$skippedLabels = $candidateSkippedLabels;
						break;
					}
				}
			}
		}

		$ordinateAxisLength =
			$horizontalGraph ? $bottomRightXValue - $topLeftXValue : $this->getGraphHeight($horizontalGraph);

		$maxOrdinateValue = 0;
		foreach($this->ordinateSeries as $column => $data)
		{
			$currentMax = $this->pData->getMax($column);

			if($currentMax > $maxOrdinateValue)
			{
				$maxOrdinateValue = $currentMax;
			}
		}

		$gridColor = $this->colors[self::GRID_COLOR_KEY];

		$this->pImage->drawScale(
			array(
				 'Mode' => SCALE_MODE_MANUAL,
				 'GridTicks' => 0,
				 'LabelSkip' => $skippedLabels,
				 'DrawXLines' => $displayVerticalGridLines,
				 'Factors' => array(ceil($maxOrdinateValue / 2)),
				 'MinDivHeight' => $ordinateAxisLength / 2,
				 'AxisAlpha' => 0,
				 'SkippedAxisAlpha' => 0,
				 'TickAlpha' => $showTicks ? self::DEFAULT_TICK_ALPHA : 0,
				 'InnerTickWidth' => self::INNER_TICK_WIDTH,
				 'OuterTickWidth' => self::OUTER_TICK_WIDTH,
				 'GridR' => $gridColor['R'],
				 'GridG' => $gridColor['G'],
				 'GridB' => $gridColor['B'],
				 'GridAlpha' => 100,
				 'ManualScale' => array(
					 0 => array(
						 'Min' => 0,
						 'Max' => $maxOrdinateValue
					 )
				 ),
				 'Pos' => $horizontalGraph ? SCALE_POS_TOPBOTTOM : SCALE_POS_LEFTRIGHT,
			)
		);

		if($this->showLegend)
		{
			$legendColor = $this->colors[self::VALUE_COLOR_KEY];
			$this->pImage->drawLegend(
				$topLeftXValue + self::LEGEND_LEFT_MARGIN,
				$this->getLegendHeight() / 2,
				array(
					 'Style' => LEGEND_NOBORDER,
					 'Mode' => LEGEND_HORIZONTAL,
					 'FontR' => $legendColor['R'],
					 'FontG' => $legendColor['G'],
					 'FontB' => $legendColor['B'],
				)
			);
		}

		if($drawCircles)
		{
			$this->pImage->drawPlotChart();
		}
	}

	protected function getGridLeftMargin($horizontalGraph, $withLabel)
	{
		$gridLeftMargin = self::LEFT_GRID_MARGIN + self::OUTER_TICK_WIDTH;

		if($withLabel)
		{
			$maxWidthHeight = $this->maxWidthHeight($horizontalGraph ? $this->abscissaSeries : $this->ordinateSeries);
			$gridLeftMargin += $maxWidthHeight[self::WIDTH_KEY];
		}

		return $gridLeftMargin;
	}

	protected function getGridTopMargin($horizontalGraph)
	{
		$ordinateMaxWidthHeight = $this->maxWidthHeight($this->ordinateSeries);
		$ordinateMaxHeight = $ordinateMaxWidthHeight[self::HEIGHT_KEY];

		if($horizontalGraph)
		{
			$topMargin = $ordinateMaxHeight + self::TOP_GRID_MARGIN_HORIZONTAL_GRAPH + self::OUTER_TICK_WIDTH;
		}
		else
		{
			$topMargin = $ordinateMaxHeight / 2;
		}

		if($this->showLegend)
		{
			$topMargin += $this->getLegendHeight() + self::LEGEND_BOTTOM_MARGIN;
		}

		return $topMargin;
	}

	private function getLegendHeight()
	{
		$maxMetricLegendHeight = 0;
		foreach($this->ordinateLabels as $column => $label)
		{
			$metricTitleWidthHeight = $this->getTextWidthHeight($label);
			$metricTitleHeight = $metricTitleWidthHeight[self::HEIGHT_KEY];
			if($metricTitleHeight > $maxMetricLegendHeight)
			{
				$maxMetricLegendHeight = $metricTitleHeight;
			}
		}

		return $maxMetricLegendHeight;
	}

	protected function getGraphHeight($horizontalGraph)
	{
		return $this->getGraphBottom() - $this->getGridTopMargin($horizontalGraph);
	}

	private function getGridBottomMargin()
	{
		$abscissaMaxWidthHeight = $this->maxWidthHeight($this->abscissaSeries);
		return $abscissaMaxWidthHeight[self::HEIGHT_KEY] + self::BOTTOM_GRID_MARGIN;
	}

	protected function getGridRightMargin($horizontalGraph)
	{
		if($horizontalGraph)
		{
			$ordinateMaxWidthHeight = $this->maxWidthHeight($this->ordinateSeries);
			return self::RIGHT_GRID_MARGIN_HORIZONTAL_GRAPH + $ordinateMaxWidthHeight[self::WIDTH_KEY];
		}
		else
		{
			return 0;
		}
	}

	protected function getGraphBottom()
	{
		return $this->height - $this->getGridBottomMargin();
	}
}