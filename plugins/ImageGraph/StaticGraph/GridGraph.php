<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_ImageGraph
 */


/**
 *
 * @package Piwik_ImageGraph
 */
abstract class Piwik_ImageGraph_StaticGraph_GridGraph extends Piwik_ImageGraph_StaticGraph
{
	const GRAPHIC_COLOR_KEY = 'GRAPHIC_COLOR';
	const DEFAULT_GRAPHIC_COLOR = '5170AE';
	const VALUE_COLOR_KEY = 'VALUE_COLOR';
	const DEFAULT_VALUE_COLOR = '444444';
	const GRID_COLOR_KEY = 'GRID_COLOR';
	const DEFAULT_GRID_COLOR = 'CCCCCC';

	const LEFT_GRID_MARGIN = 4;
	const BOTTOM_GRID_MARGIN = 10;
	const DEFAULT_TICK_ALPHA = 20;
	const DEFAULT_SERIE_WEIGHT = 0.5;
	const TOP_GRID_MARGIN_HORIZONTAL_GRAPH = 1;
	const RIGHT_GRID_MARGIN_HORIZONTAL_GRAPH = 5;
	const LEGEND_LEFT_MARGIN = 15;
	const LEGEND_TOP_MARGIN = 3;
	const OUTER_TICK_WIDTH = 5;
	const INNER_TICK_WIDTH = 0;
	const LABEL_SPACE_VERTICAL_GRAPH = 10;

	protected function getDefaultColors()
	{
		return array(
			self::GRAPHIC_COLOR_KEY => self::DEFAULT_GRAPHIC_COLOR,
			self::VALUE_COLOR_KEY => self::DEFAULT_VALUE_COLOR,
			self::GRID_COLOR_KEY => self::DEFAULT_GRID_COLOR,
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

		$this->pData->setSerieWeight($this->metricTitle, self::DEFAULT_SERIE_WEIGHT);
		$graphicColor = $this->colors[self::GRAPHIC_COLOR_KEY];
		$this->pData->setPalette($this->metricTitle, $graphicColor);

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
			$abscissaMaxWidthHeight = $this->maxWidthHeight($this->abscissaSerie);
			$abscissaMaxWidth = $abscissaMaxWidthHeight[self::WIDTH_KEY];
			$graphWidth = $bottomRightXValue - $topLeftXValue;
			$maxNumOfLabels = floor($graphWidth / ($abscissaMaxWidth + self::LABEL_SPACE_VERTICAL_GRAPH));

			$abscissaSerieCount = count($this->abscissaSerie);
			if($maxNumOfLabels < $abscissaSerieCount)
			{
				for($candidateSkippedLabels = 1 ; $candidateSkippedLabels < $abscissaSerieCount; $candidateSkippedLabels++)
				{
					$numberOfSegments = $abscissaSerieCount / ($candidateSkippedLabels + 1);
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

		$ordinateAxisLength = $horizontalGraph ? $bottomRightXValue - $topLeftXValue : $this->getGraphHeight($horizontalGraph);

		$maxOrdinateValue = $this->pData->getMax($this->metricTitle);

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

		if($this->showMetricTitle)
		{
			$this->pImage->drawLegend(
				$topLeftXValue + self::LEGEND_LEFT_MARGIN,
				self::LEGEND_TOP_MARGIN,
				array(
					 'Style' => LEGEND_NOBORDER,
					 'FontR' => $graphicColor['R'],
					 'FontG' => $graphicColor['G'],
					 'FontB' => $graphicColor['B'],
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
			$maxWidthHeight = $this->maxWidthHeight($horizontalGraph ? $this->abscissaSerie : $this->ordinateSerie);
			$gridLeftMargin += $maxWidthHeight[self::WIDTH_KEY];
		}

		return $gridLeftMargin;
	}

	protected function getGridTopMargin($horizontalGraph)
	{
		$ordinateMaxWidthHeight = $this->maxWidthHeight($this->ordinateSerie);
		$ordinateMaxHeight = $ordinateMaxWidthHeight[self::HEIGHT_KEY];

		if($horizontalGraph)
		{
			$topMargin = $ordinateMaxHeight + self::TOP_GRID_MARGIN_HORIZONTAL_GRAPH + self::OUTER_TICK_WIDTH;
		}
		else
		{
			$topMargin = $ordinateMaxHeight / 2;
		}

		if($this->showMetricTitle)
		{
			$metricTitleWidthHeight = $this->getTextWidthHeight($this->metricTitle);
			$topMargin += $metricTitleWidthHeight[self::HEIGHT_KEY];
		}

		return $topMargin;
	}

	protected function getGraphHeight($horizontalGraph)
	{
		return $this->getGraphBottom() - $this->getGridTopMargin($horizontalGraph);
	}

	private function getGridBottomMargin()
	{
		$abscissaMaxWidthHeight = $this->maxWidthHeight($this->abscissaSerie);
		return $abscissaMaxWidthHeight[self::HEIGHT_KEY] + self::BOTTOM_GRID_MARGIN;
	}

	protected function getGridRightMargin($horizontalGraph)
	{
		if($horizontalGraph)
		{
			$ordinateMaxWidthHeight = $this->maxWidthHeight($this->ordinateSerie);
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