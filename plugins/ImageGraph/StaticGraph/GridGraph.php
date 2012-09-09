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
	const RIGHT_GRID_MARGIN_HORIZONTAL_GRAPH = 4;
	const OUTER_TICK_WIDTH = 5;
	const INNER_TICK_WIDTH = 0;
	const LABEL_SPACE_VERTICAL_GRAPH = 10;

	const HORIZONTAL_LEGEND_TOP_MARGIN = 5;
	const LEGEND_LEFT_MARGIN = 5;
	const HORIZONTAL_LEGEND_BOTTOM_MARGIN = 10;
	const HORIZONTAL_LEGEND_FONT_SIZE_OFFSET = 2;
	const LEGEND_BULLET_SIZE = 5;
	const LEGEND_BULLET_RIGHT_PADDING = 5;
	const LEGEND_ITEM_HORIZONTAL_INTERSTICE = 6;
	const LEGEND_ITEM_VERTICAL_INTERSTICE = 12;
	const LEGEND_SHADOW_OPACITY = 20;
	const LEGEND_SHADOW_PADDING = 2;
	const PCHART_HARD_CODED_VERTICAL_LEGEND_INTERSTICE = 5;

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
		$showTicks,
		$verticalLegend
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
		$topLeftYValue = $this->getGridTopMargin($horizontalGraph, $verticalLegend);
		$bottomRightXValue = $this->width - $this->getGridRightMargin($horizontalGraph);
		$bottomRightYValue = $this->getGraphBottom($horizontalGraph);

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
			$abscissaMaxWidth = $this->getMaximumTextWidth($this->abscissaSeries);
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

		if($this->forceSkippedLabels && $skippedLabels && $skippedLabels < $this->forceSkippedLabels)
		{
			$skippedLabels = $this->forceSkippedLabels;
		}

		$ordinateAxisLength =
			$horizontalGraph ? $bottomRightXValue - $topLeftXValue : $this->getGraphHeight($horizontalGraph, $verticalLegend);

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
			$legendFontSize = $this->getLegendFontSize($verticalLegend);
			$maxLegendTextHeight = $this->getMaximumTextHeight($legendFontSize);
			$legendTopLeftXValue = $topLeftXValue + self::LEGEND_LEFT_MARGIN;

			// this value is used by pChart to position the top edge of item bullets
			$legendTopLeftYValue = ($verticalLegend ? 0 : self::HORIZONTAL_LEGEND_TOP_MARGIN) + ($maxLegendTextHeight / 4);

			// maximum logo width & height
			list($maxLogoWidth, $maxLogoHeight) = self::getMaxLogoSize(array_values($this->ordinateLogos));

			// add colored background to each legend item
			if(count($this->ordinateLabels) > 1)
			{
				$currentPosition = $verticalLegend ? $legendTopLeftYValue - self::LEGEND_SHADOW_PADDING: $legendTopLeftXValue;
				$colorIndex = 1;
				foreach($this->ordinateLabels as $metricCode => $label)
				{
					$color = $this->colors[self::GRAPHIC_COLOR_KEY . $colorIndex++];

					$bulletSize = self::LEGEND_BULLET_SIZE;
					if(isset($this->ordinateLogos[$metricCode]))
					{
						$bulletSize = $maxLogoWidth;
					}

					$rectangleTopLeftXValue = $verticalLegend ? $legendTopLeftXValue : $currentPosition;
					$rectangleTopLeftYValue = $verticalLegend ? $currentPosition : self::HORIZONTAL_LEGEND_TOP_MARGIN - self::LEGEND_SHADOW_PADDING;

					$rectangleWidth = $bulletSize + self::LEGEND_BULLET_RIGHT_PADDING + $this->getTextWidth($label, $legendFontSize);
					$legendItemWidth = $rectangleWidth + self::LEGEND_ITEM_HORIZONTAL_INTERSTICE;
					$rectangleBottomRightXValue = $rectangleTopLeftXValue + $rectangleWidth;

					$legendItemHeight = ($maxLogoHeight > $maxLegendTextHeight ? $maxLogoHeight : $maxLegendTextHeight) + self::LEGEND_SHADOW_PADDING;
					$rectangleBottomRightYValue = $rectangleTopLeftYValue + $legendItemHeight;

					$this->pImage->drawFilledRectangle(
						$rectangleTopLeftXValue,
						$rectangleTopLeftYValue,
						$rectangleBottomRightXValue,
						$rectangleBottomRightYValue,
						array(
							'Alpha' => self::LEGEND_SHADOW_OPACITY,
							'R' => $color['R'],
							'G' => $color['G'],
							'B' => $color['B'],
						)
					);

					$currentPosition +=
						$verticalLegend
							? ($maxLogoHeight > self::LEGEND_ITEM_VERTICAL_INTERSTICE ? $maxLogoHeight : self::LEGEND_ITEM_VERTICAL_INTERSTICE) + self::PCHART_HARD_CODED_VERTICAL_LEGEND_INTERSTICE
							: $legendItemWidth;
				}
			}

			// draw legend
			$legendColor = $this->colors[self::VALUE_COLOR_KEY];
			$this->pImage->drawLegend(
				$legendTopLeftXValue,
				$legendTopLeftYValue,
				array(
					'Style' => LEGEND_NOBORDER,
					'FontSize' => $legendFontSize,
					'BoxWidth' => self::LEGEND_BULLET_SIZE,
					'XSpacing' => self::LEGEND_ITEM_HORIZONTAL_INTERSTICE, // not effective when vertical
					'Mode' => $verticalLegend ? LEGEND_VERTICAL : LEGEND_HORIZONTAL,
					'BoxHeight' => $verticalLegend ? self::LEGEND_ITEM_VERTICAL_INTERSTICE : null,
					'Family' => $drawCircles ? LEGEND_FAMILY_LINE : LEGEND_FAMILY_BOX,
					'FontR' => $legendColor['R'],
					'FontG' => $legendColor['G'],
					'FontB' => $legendColor['B'],
				)
			);
		}

		if($drawCircles)
		{
			// drawPlotChart uses series pictures when they are specified
			// remove series pictures (ie. logos) so that drawPlotChart draws simple dots
			foreach($this->ordinateSeries as $column => $data)
			{
				if(isset($this->ordinateLogos[$column]))
				{
					$this->pData->setSeriePicture($column,null);
				}
			}

			$this->pImage->drawPlotChart();
		}
	}

	protected function getLegendFontSize($verticalLegend)
	{
		$legendFontSize = $this->fontSize;
		if(count($this->ordinateLabels) == 1 || !$verticalLegend)
		{
			$legendFontSize += self::HORIZONTAL_LEGEND_FONT_SIZE_OFFSET;
		}
		return $legendFontSize;
	}

	protected static function getMaxLogoSize($logoPaths)
	{
		$maxLogoWidth = 0;
		$maxLogoHeight = 0;
		foreach($logoPaths as $logoPath)
		{
			list($logoWidth, $logoHeight) = self::getLogoSize($logoPath);

			if($logoWidth > $maxLogoWidth)
			{
				$maxLogoWidth = $logoWidth;
			}
			if($logoHeight > $maxLogoHeight)
			{
				$maxLogoHeight = $logoHeight;
			}
		}

		return array($maxLogoWidth, $maxLogoHeight);
	}

	protected static function getLogoSize($logoPath)
	{
		$pathInfo = getimagesize($logoPath);
		return array($pathInfo[0], $pathInfo[1]);
	}

	protected function getGridLeftMargin($horizontalGraph, $withLabel)
	{
		$gridLeftMargin = self::LEFT_GRID_MARGIN + self::OUTER_TICK_WIDTH;

		if($withLabel)
		{
			$gridLeftMargin += $this->getMaximumTextWidth($horizontalGraph ? $this->abscissaSeries : $this->ordinateSeries);
		}

		return $gridLeftMargin;
	}

	protected function getGridTopMargin($horizontalGraph, $verticalLegend)
	{
		$ordinateMaxHeight = $this->getMaximumTextHeight();

		if($horizontalGraph)
		{
			$topMargin = $ordinateMaxHeight + self::TOP_GRID_MARGIN_HORIZONTAL_GRAPH + self::OUTER_TICK_WIDTH;
		}
		else
		{
			$topMargin = $ordinateMaxHeight / 2;
		}

		if($this->showLegend && !$verticalLegend)
		{
			$topMargin += $this->getHorizontalLegendHeight();
		}

		return $topMargin;
	}

	private function getHorizontalLegendHeight()
	{
		return $this->getMaximumTextHeight($this->getLegendFontSize($verticalLegend = false)) + self::HORIZONTAL_LEGEND_BOTTOM_MARGIN + self::HORIZONTAL_LEGEND_TOP_MARGIN;
	}

	protected function getGraphHeight($horizontalGraph, $verticalLegend)
	{
		return $this->getGraphBottom($horizontalGraph) - $this->getGridTopMargin($horizontalGraph, $verticalLegend);
	}

	private function getGridBottomMargin($horizontalGraph)
	{
		$gridBottomMargin = self::BOTTOM_GRID_MARGIN;
		if(!$horizontalGraph)
		{
			$gridBottomMargin += $this->getMaximumTextHeight();
		}
		return $gridBottomMargin;
	}

	protected function getGridRightMargin($horizontalGraph)
	{
		if($horizontalGraph)
		{
			// in horizontal graphs, metric values are displayed on the far right of the bar
			return self::RIGHT_GRID_MARGIN_HORIZONTAL_GRAPH + $this->getMaximumTextWidth($this->ordinateSeries);
		}
		else
		{
			return 0;
		}
	}

	protected function getGraphBottom($horizontalGraph)
	{
		return $this->height - $this->getGridBottomMargin($horizontalGraph);
	}

	// display min & max values
	// can not currently be used because pChart's label design is not flexible enough
	// e.g: it is not possible to remove the box border & the square icon
	// it would require modifying pChart code base which we try to avoid
//	protected function displayMinMaxValues()
//	{
//		if($displayMinMax)
//		{
//			// when plotting multiple metrics, display min & max on both series
//			// to fix: in vertical bars, labels are hidden when multiple metrics are plotted, hence the restriction on count($this->ordinateSeries) == 1
//			if($this->multipleMetrics && count($this->ordinateSeries) == 1)
//			{
//				$colorIndex = 1;
//				foreach($this->ordinateSeries as $column => $data)
//				{
//					$color = $this->colors[self::GRAPHIC_COLOR_KEY . $colorIndex++];
//
//					$this->pImage->writeLabel(
//						$column,
//						self::locateMinMaxValue($data),
//						$Format = array(
//							'NoTitle' => true,
//							'DrawPoint' => false,
//							'DrawSerieColor' => true,
//							'TitleMode' => LABEL_TITLE_NOBACKGROUND,
//							'GradientStartR' => $color['R'],
//							'GradientStartG' => $color['G'],
//							'GradientStartB' => $color['B'],
//							'GradientEndR' => 255,
//							'GradientEndG' => 255,
//							'GradientEndB' => 255,
//							'BoxWidth' => 0,
//							'VerticalMargin' => 9,
//							'HorizontalMargin' => 7,
//						)
//					);
//				}
//			}
//			else
//			{
//				// display only one min & max label
//			}
//		}
//	}

//	protected static function locateMinMaxValue($data)
//	{
//		$firstValue = $data[0];
//		$minValue = $firstValue;
//		$minValueIndex = 0;
//		$maxValue = $firstValue;
//		$maxValueIndex = 0;
//		foreach($data as $index => $value)
//		{
//			if($value > $maxValue)
//			{
//				$maxValue = $value;
//				$maxValueIndex = $index;
//			}
//
//			if($value < $minValue)
//			{
//				$minValue = $value;
//				$minValueIndex = $index;
//			}
//		}
//
//		return array($minValueIndex, $maxValueIndex);
//	}
}