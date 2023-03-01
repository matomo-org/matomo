<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ImageGraph\StaticGraph;

use Piwik\Exception\InvalidDimensionException;
use Piwik\Plugins\ImageGraph\StaticGraph;

/**
 *
 */
abstract class GridGraph extends StaticGraph
{
    const GRAPHIC_COLOR_KEY = 'GRAPHIC_COLOR';

    const TRUNCATION_TEXT = '...';

    const DEFAULT_TICK_ALPHA = 20;
    const DEFAULT_SERIE_WEIGHT = 0.5;
    const LEFT_GRID_MARGIN = 4;
    const BOTTOM_GRID_MARGIN = 10;
    const TOP_GRID_MARGIN_HORIZONTAL_GRAPH = 1;
    const RIGHT_GRID_MARGIN_HORIZONTAL_GRAPH = 4;
    const OUTER_TICK_WIDTH = 5;
    const INNER_TICK_WIDTH = 0;
    const LABEL_SPACE_VERTICAL_GRAPH = 7;

    const HORIZONTAL_LEGEND_TOP_MARGIN = 5;
    const HORIZONTAL_LEGEND_LEFT_MARGIN = 10;
    const HORIZONTAL_LEGEND_BOTTOM_MARGIN = 10;
    const VERTICAL_LEGEND_TOP_MARGIN = 8;
    const VERTICAL_LEGEND_LEFT_MARGIN = 6;
    const VERTICAL_LEGEND_MAX_WIDTH_PCT = 0.70;
    const LEGEND_LINE_BULLET_WIDTH = 14;
    const LEGEND_BOX_BULLET_WIDTH = 5;
    const LEGEND_BULLET_RIGHT_PADDING = 5;
    const LEGEND_ITEM_HORIZONTAL_INTERSTICE = 6;
    const LEGEND_ITEM_VERTICAL_INTERSTICE_OFFSET = 4;
    const LEGEND_SHADOW_OPACITY = 25;
    const LEGEND_VERTICAL_SHADOW_PADDING = 3;
    const LEGEND_HORIZONTAL_SHADOW_PADDING = 2;
    const PCHART_HARD_CODED_VERTICAL_LEGEND_INTERSTICE = 5;

    protected function getDefaultColors()
    {
        return array(
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
        $bulletType,
        $horizontalGraph,
        $showTicks,
        $verticalLegend
    )
    {
        $this->initpData();

        $colorIndex = 1;
        foreach ($this->ordinateSeries as $column => $data) {
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

        $this->drawBackground();

        $this->pImage->setGraphArea(
            $topLeftXValue,
            $topLeftYValue,
            $bottomRightXValue,
            $bottomRightYValue
        );

        // determine how many labels need to be skipped
        $skippedLabels = 0;
        if (!$horizontalGraph) {
            list($abscissaMaxWidth, $abscissaMaxHeight) = $this->getMaximumTextWidthHeight($this->abscissaSeries);
            $graphWidth = $bottomRightXValue - $topLeftXValue;
            $maxNumOfLabels = floor($graphWidth / ($abscissaMaxWidth + self::LABEL_SPACE_VERTICAL_GRAPH));

            $abscissaSeriesCount = count($this->abscissaSeries);
            if ($maxNumOfLabels < $abscissaSeriesCount) {
                for ($candidateSkippedLabels = 1; $candidateSkippedLabels < $abscissaSeriesCount; $candidateSkippedLabels++) {
                    $numberOfSegments = $abscissaSeriesCount / ($candidateSkippedLabels + 1);
                    $numberOfCompleteSegments = floor($numberOfSegments);

                    $numberOfLabels = $numberOfCompleteSegments;
                    if ($numberOfSegments > $numberOfCompleteSegments) {
                        $numberOfLabels++;
                    }

                    if ($numberOfLabels <= $maxNumOfLabels) {
                        $skippedLabels = $candidateSkippedLabels;
                        break;
                    }
                }
            }

            if ($this->forceSkippedLabels
                && $skippedLabels
                && $skippedLabels < $this->forceSkippedLabels
                && $abscissaSeriesCount > $this->forceSkippedLabels + 1
            ) {
                $skippedLabels = $this->forceSkippedLabels;
            }
        }

        $ordinateAxisLength =
            $horizontalGraph ? $bottomRightXValue - $topLeftXValue : $this->getGraphHeight($horizontalGraph, $verticalLegend);

        $maxOrdinateValue = 0;
        foreach ($this->ordinateSeries as $column => $data) {
            $currentMax = $this->pData->getMax($column);

            if ($currentMax > $maxOrdinateValue) {
                $maxOrdinateValue = ceil($currentMax);
            }
        }

        // rounding top scale value to the next multiple of 10
        if ($maxOrdinateValue > 10) {
            $modTen = $maxOrdinateValue % 10;
            if ($modTen) {
                $maxOrdinateValue += 10 - $modTen;
            }
        }

        if ($ordinateAxisLength <= 0 || $bottomRightYValue - $topLeftYValue <= 0) {
            throw new InvalidDimensionException('Error: the graph dimension is not valid. Please try larger width and height values or use 0 for default values.');
        }

        $gridColor = $this->gridColor;
        $this->pImage->drawScale(
            array(
                 'Mode'             => SCALE_MODE_MANUAL,
                 'GridTicks'        => 0,
                 'LabelSkip'        => $skippedLabels,
                 'DrawXLines'       => $displayVerticalGridLines,
                 'Factors'          => array(ceil($maxOrdinateValue / 2)),
                 'MinDivHeight'     => $ordinateAxisLength / 2,
                 'AxisAlpha'        => 0,
                 'SkippedAxisAlpha' => 0,
                 'TickAlpha'        => $showTicks ? self::DEFAULT_TICK_ALPHA : 0,
                 'InnerTickWidth'   => self::INNER_TICK_WIDTH,
                 'OuterTickWidth'   => self::OUTER_TICK_WIDTH,
                 'GridR'            => $gridColor['R'],
                 'GridG'            => $gridColor['G'],
                 'GridB'            => $gridColor['B'],
                 'GridAlpha'        => 100,
                 'ManualScale'      => array(
                     0 => array(
                         'Min' => 0,
                         'Max' => $maxOrdinateValue
                     )
                 ),
                 'Pos'              => $horizontalGraph ? SCALE_POS_TOPBOTTOM : SCALE_POS_LEFTRIGHT,
            )
        );

        if ($this->showLegend) {
            switch ($bulletType) {
                case LEGEND_FAMILY_LINE:
                    $bulletWidth = self::LEGEND_LINE_BULLET_WIDTH;

                    // measured using a picture editing software
                    $iconOffsetAboveLabelSymmetryAxis = -2;
                    break;

                case LEGEND_FAMILY_BOX:
                    $bulletWidth = self::LEGEND_BOX_BULLET_WIDTH;

                    // measured using a picture editing software
                    $iconOffsetAboveLabelSymmetryAxis = 3;
                    break;
            }

            // pChart requires two coordinates to draw the legend $legendTopLeftXValue & $legendTopLeftYValue
            // $legendTopLeftXValue = legend's left padding
            $legendTopLeftXValue = $topLeftXValue + ($verticalLegend ? self::VERTICAL_LEGEND_LEFT_MARGIN : self::HORIZONTAL_LEGEND_LEFT_MARGIN);

            // $legendTopLeftYValue = y coordinate of the top edge of the legend's icons
            // Caution :
            //  - pChart will silently add some value (see $paddingAddedByPChart) to $legendTopLeftYValue depending on multiple criteria
            //  - pChart will not take into account the size of the text. Setting $legendTopLeftYValue = 0 will crop the legend's labels
            // The following section of code determines the value of $legendTopLeftYValue while taking into account the following parameters :
            //  - whether legend items have icons
            //  - whether icons are bigger than the legend's labels
            //  - how much colored shadow padding is required
            list($maxLogoWidth, $maxLogoHeight) = self::getMaxLogoSize(array_values($this->ordinateLogos));
            if ($maxLogoHeight >= $this->legendFontSize) {
                $heightOfTextAboveBulletTop = 0;
                $paddingCreatedByLogo = $maxLogoHeight - $this->legendFontSize;
                $effectiveShadowPadding = $paddingCreatedByLogo < self::LEGEND_VERTICAL_SHADOW_PADDING * 2 ? self::LEGEND_VERTICAL_SHADOW_PADDING - ($paddingCreatedByLogo / 2) : 0;
            } else {
                if ($maxLogoHeight) {
                    // measured using a picture editing software
                    $iconOffsetAboveLabelSymmetryAxis = 5;
                }
                $heightOfTextAboveBulletTop = $this->legendFontSize / 2 - $iconOffsetAboveLabelSymmetryAxis;
                $effectiveShadowPadding = self::LEGEND_VERTICAL_SHADOW_PADDING;
            }

            $effectiveLegendItemVerticalInterstice = $this->legendFontSize + self::LEGEND_ITEM_VERTICAL_INTERSTICE_OFFSET;
            $effectiveLegendItemHorizontalInterstice = self::LEGEND_ITEM_HORIZONTAL_INTERSTICE + self::LEGEND_HORIZONTAL_SHADOW_PADDING;

            $legendTopMargin = $verticalLegend ? self::VERTICAL_LEGEND_TOP_MARGIN : self::HORIZONTAL_LEGEND_TOP_MARGIN;
            $requiredPaddingAboveItemBullet = $legendTopMargin + $heightOfTextAboveBulletTop + $effectiveShadowPadding;

            $paddingAddedByPChart = 0;
            if ($verticalLegend) {
                if ($maxLogoHeight) {
                    // see line 1691 of pDraw.class.php
                    if ($maxLogoHeight < $effectiveLegendItemVerticalInterstice) {
                        $paddingAddedByPChart = ($effectiveLegendItemVerticalInterstice / 2) - ($maxLogoHeight / 2);
                    }
                } else {
                    // see line 1711 of pDraw.class.php ($Y+$IconAreaHeight/2)
                    $paddingAddedByPChart = $effectiveLegendItemVerticalInterstice / 2;
                }
            }

            $legendTopLeftYValue = $paddingAddedByPChart < $requiredPaddingAboveItemBullet ? $requiredPaddingAboveItemBullet - $paddingAddedByPChart : 0;

            // add colored background to each legend item
            if (count($this->ordinateLabels) > 1) {
                $currentPosition = $verticalLegend ? $legendTopMargin : $legendTopLeftXValue;
                $colorIndex = 1;
                foreach ($this->ordinateLabels as $metricCode => &$label) {
                    $color = $this->colors[self::GRAPHIC_COLOR_KEY . $colorIndex++];

                    $paddedBulletWidth = $bulletWidth;
                    if (isset($this->ordinateLogos[$metricCode])) {
                        $paddedBulletWidth = $maxLogoWidth;
                    }
                    $paddedBulletWidth += self::LEGEND_BULLET_RIGHT_PADDING;

                    // truncate labels if required
                    if ($verticalLegend) {
                        $label = $this->truncateLabel($label, ($this->width * self::VERTICAL_LEGEND_MAX_WIDTH_PCT) - $legendTopLeftXValue - $paddedBulletWidth, $this->legendFontSize);
                        $this->pData->setSerieDescription($metricCode, $label);
                    }

                    $rectangleTopLeftXValue = ($verticalLegend ? $legendTopLeftXValue : $currentPosition) + $paddedBulletWidth - self::LEGEND_HORIZONTAL_SHADOW_PADDING;
                    $rectangleTopLeftYValue = $verticalLegend ? $currentPosition : $legendTopMargin;

                    list($labelWidth, $labelHeight) = $this->getTextWidthHeight($label, $this->legendFontSize);
                    $legendItemWidth = $paddedBulletWidth + $labelWidth + $effectiveLegendItemHorizontalInterstice;
                    $rectangleBottomRightXValue = $rectangleTopLeftXValue + $labelWidth + (self::LEGEND_HORIZONTAL_SHADOW_PADDING * 2);

                    $legendItemHeight = max($maxLogoHeight, $this->legendFontSize) + ($effectiveShadowPadding * 2);
                    $rectangleBottomRightYValue = $rectangleTopLeftYValue + $legendItemHeight;

                    $this->pImage->drawFilledRectangle(
                        $rectangleTopLeftXValue,
                        $rectangleTopLeftYValue,
                        $rectangleBottomRightXValue,
                        $rectangleBottomRightYValue,
                        array(
                             'Alpha' => self::LEGEND_SHADOW_OPACITY,
                             'R'     => $color['R'],
                             'G'     => $color['G'],
                             'B'     => $color['B'],
                        )
                    );

                    if ($verticalLegend) {
                        $currentPositionIncrement = max($maxLogoHeight, $effectiveLegendItemVerticalInterstice, $this->legendFontSize) + self::PCHART_HARD_CODED_VERTICAL_LEGEND_INTERSTICE;
                    } else {
                        $currentPositionIncrement = $legendItemWidth;
                    }

                    $currentPosition += $currentPositionIncrement;
                }
            }

            // draw legend
            $legendColor = $this->textColor;
            $this->pImage->drawLegend(
                $legendTopLeftXValue,
                $legendTopLeftYValue,
                array(
                     'Style'     => LEGEND_NOBORDER,
                     'FontSize'  => $this->legendFontSize,
                     'BoxWidth'  => $bulletWidth,
                     'XSpacing'  => $effectiveLegendItemHorizontalInterstice, // not effective when vertical
                     'Mode'      => $verticalLegend ? LEGEND_VERTICAL : LEGEND_HORIZONTAL,
                     'BoxHeight' => $verticalLegend ? $effectiveLegendItemVerticalInterstice : null,
                     'Family'    => $bulletType,
                     'FontR'     => $legendColor['R'],
                     'FontG'     => $legendColor['G'],
                     'FontB'     => $legendColor['B'],
                )
            );
        }
    }

    protected static function getMaxLogoSize($logoPaths)
    {
        $maxLogoWidth = 0;
        $maxLogoHeight = 0;
        foreach ($logoPaths as $logoPath) {
            list($logoWidth, $logoHeight) = self::getLogoSize($logoPath);

            if ($logoWidth > $maxLogoWidth) {
                $maxLogoWidth = $logoWidth;
            }
            if ($logoHeight > $maxLogoHeight) {
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

        if ($withLabel) {
            list($maxTextWidth, $maxTextHeight) = $this->getMaximumTextWidthHeight($horizontalGraph ? $this->abscissaSeries : $this->ordinateSeries);
            $gridLeftMargin += $maxTextWidth;
        }

        return $gridLeftMargin;
    }

    protected function getGridTopMargin($horizontalGraph, $verticalLegend)
    {
        list($ordinateMaxWidth, $ordinateMaxHeight) = $this->getMaximumTextWidthHeight($this->ordinateSeries);

        if ($horizontalGraph) {
            $topMargin = $ordinateMaxHeight + self::TOP_GRID_MARGIN_HORIZONTAL_GRAPH + self::OUTER_TICK_WIDTH;
        } else {
            $topMargin = $ordinateMaxHeight / 2;
        }

        if ($this->showLegend && !$verticalLegend) {
            $topMargin += $this->getHorizontalLegendHeight();
        }

        return $topMargin;
    }

    private function getHorizontalLegendHeight()
    {
        list($maxMetricLegendWidth, $maxMetricLegendHeight) =
            $this->getMaximumTextWidthHeight(array_values($this->ordinateLabels), $this->legendFontSize);

        return $maxMetricLegendHeight + self::HORIZONTAL_LEGEND_BOTTOM_MARGIN + self::HORIZONTAL_LEGEND_TOP_MARGIN;
    }

    protected function getGraphHeight($horizontalGraph, $verticalLegend)
    {
        return $this->getGraphBottom($horizontalGraph) - $this->getGridTopMargin($horizontalGraph, $verticalLegend);
    }

    private function getGridBottomMargin($horizontalGraph)
    {
        $gridBottomMargin = self::BOTTOM_GRID_MARGIN;
        if (!$horizontalGraph) {
            list($abscissaMaxWidth, $abscissaMaxHeight) = $this->getMaximumTextWidthHeight($this->abscissaSeries);
            $gridBottomMargin += $abscissaMaxHeight;
        }
        return $gridBottomMargin;
    }

    protected function getGridRightMargin($horizontalGraph)
    {
        if ($horizontalGraph) {
            // in horizontal graphs, metric values are displayed on the far right of the bar
            list($ordinateMaxWidth, $ordinateMaxHeight) = $this->getMaximumTextWidthHeight($this->ordinateSeries);
            return self::RIGHT_GRID_MARGIN_HORIZONTAL_GRAPH + $ordinateMaxWidth;
        } else {
            return 0;
        }
    }

    protected function getGraphBottom($horizontalGraph)
    {
        return $this->height - $this->getGridBottomMargin($horizontalGraph);
    }

    protected function truncateLabel($label, $labelWidthLimit, $fontSize = false)
    {
        list($truncationTextWidth, $truncationTextHeight) = $this->getTextWidthHeight(self::TRUNCATION_TEXT, $fontSize);
        list($labelWidth, $labelHeight) = $this->getTextWidthHeight($label, $fontSize);

        if ($labelWidth > $labelWidthLimit) {
            $averageCharWidth = $labelWidth / strlen($label);
            $charsToKeep = floor(($labelWidthLimit - $truncationTextWidth) / $averageCharWidth);
            $label = substr($label, 0, $charsToKeep) . self::TRUNCATION_TEXT;
        }
        return $label;
    }
    // display min & max values
    // can not currently be used because pChart's label design is not flexible enough
    // e.g: it is not possible to remove the box border & the square icon
    // it would require modifying pChart code base which we try to avoid
    // see https://github.com/piwik/piwik/issues/3396
//	protected function displayMinMaxValues()
//	{
//		if ($displayMinMax)
//		{
//			// when plotting multiple metrics, display min & max on both series
//			// to fix: in vertical bars, labels are hidden when multiple metrics are plotted, hence the restriction on count($this->ordinateSeries) == 1
//			if ($this->multipleMetrics && count($this->ordinateSeries) == 1)
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
//			if ($value > $maxValue)
//			{
//				$maxValue = $value;
//				$maxValueIndex = $index;
//			}
//
//			if ($value < $minValue)
//			{
//				$minValue = $value;
//				$minValueIndex = $index;
//			}
//		}
//
//		return array($minValueIndex, $maxValueIndex);
//	}
}
