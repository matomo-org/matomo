<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ImageGraph\StaticGraph;
use Piwik\Piwik;

/**
 *
 */
class HorizontalBar extends GridGraph
{
    const INTERLEAVE = 0.30;
    const PADDING_CHARS = ' ';
    const LEGEND_SQUARE_WIDTH = 11;
    const MIN_SPACE_BETWEEN_HORIZONTAL_VALUES = 5;
    const LOGO_MIN_RIGHT_MARGIN = 3;

    public function renderGraph()
    {
        $verticalLegend = false;

        // determine the maximum logo width & height
        list($maxLogoWidth, $maxLogoHeight) = self::getMaxLogoSize($this->abscissaLogos);

        foreach ($this->abscissaLogos as $logoPath) {
            list($logoWidth, $logoHeight) = self::getLogoSize($logoPath);
            $logoPathToHeight[$logoPath] = $logoHeight;
        }

        // truncate report
        $graphHeight = $this->getGraphBottom($horizontalGraph = true) - $this->getGridTopMargin($horizontalGraph = true, $verticalLegend);

        list($abscissaMaxWidth, $abscissaMaxHeight) = $this->getMaximumTextWidthHeight($this->abscissaSeries);
        list($ordinateMaxWidth, $ordinateMaxHeight) = $this->getMaximumTextWidthHeight($this->ordinateSeries);

        $numberOfSeries = count($this->ordinateSeries);
        $ordinateMaxHeight = $ordinateMaxHeight * $numberOfSeries;

        $textMaxHeight = $abscissaMaxHeight > $ordinateMaxHeight ? $abscissaMaxHeight : $ordinateMaxHeight;

        $minLineWidth = ($textMaxHeight > $maxLogoHeight ? $textMaxHeight : $maxLogoHeight) + (self::MIN_SPACE_BETWEEN_HORIZONTAL_VALUES * $numberOfSeries);
        $maxNumOfValues = floor($graphHeight / $minLineWidth);
        $abscissaSeriesCount = count($this->abscissaSeries);

        if ($maxNumOfValues < $abscissaSeriesCount - 1) {
            $sumOfOthers = array();
            $truncatedOrdinateSeries = array();
            $truncatedAbscissaLogos = array();
            $truncatedAbscissaSeries = array();
            foreach ($this->ordinateSeries as $column => $data) {
                $truncatedOrdinateSeries[$column] = array();
                $sumOfOthers[$column] = 0;
            }

            $i = 0;
            for (; $i < $maxNumOfValues; $i++) {
                foreach ($this->ordinateSeries as $column => $data) {
                    $truncatedOrdinateSeries[$column][] = $data[$i];
                }

                $truncatedAbscissaLogos[] = isset($this->abscissaLogos[$i]) ? $this->abscissaLogos[$i] : null;
                $truncatedAbscissaSeries[] = $this->abscissaSeries[$i];
            }

            for (; $i < $abscissaSeriesCount; $i++) {
                foreach ($this->ordinateSeries as $column => $data) {
                    $sumOfOthers[$column] += $data[$i];
                }
            }

            foreach ($this->ordinateSeries as $column => $data) {
                $truncatedOrdinateSeries[$column][] = $sumOfOthers[$column];
            }

            $truncatedAbscissaSeries[] = Piwik::translate('General_Others');
            $this->abscissaSeries = $truncatedAbscissaSeries;
            $this->ordinateSeries = $truncatedOrdinateSeries;
            $this->abscissaLogos = $truncatedAbscissaLogos;
        }

        // blank characters are used to pad labels so the logo can be displayed
        $paddingText = '';
        $paddingWidth = 0;
        if ($maxLogoWidth > 0) {
            while ($paddingWidth < $maxLogoWidth + self::LOGO_MIN_RIGHT_MARGIN) {
                $paddingText .= self::PADDING_CHARS;
                list($paddingWidth, $paddingHeight) = $this->getTextWidthHeight($paddingText);
            }
        }

        // determine the maximum label width according to the minimum comfortable graph size
        $gridRightMargin = $this->getGridRightMargin($horizontalGraph = true);
        $minGraphSize = ($this->width - $gridRightMargin) / 2;

        $metricLegendWidth = 0;
        foreach ($this->ordinateLabels as $column => $label) {
            list($textWidth, $textHeight) = $this->getTextWidthHeight($label);
            $metricLegendWidth += $textWidth;
        }

        $legendWidth = $metricLegendWidth + ((self::HORIZONTAL_LEGEND_LEFT_MARGIN + self::LEGEND_SQUARE_WIDTH) * $numberOfSeries);
        if ($this->showLegend) {
            if ($legendWidth > $minGraphSize) {
                $minGraphSize = $legendWidth;
            }
        }

        $gridLeftMarginWithoutLabels = $this->getGridLeftMargin($horizontalGraph = true, $withLabel = false);
        $labelWidthLimit =
            $this->width
            - $gridLeftMarginWithoutLabels
            - $gridRightMargin
            - $paddingWidth
            - $minGraphSize;

        // truncate labels if needed
        foreach ($this->abscissaSeries as &$label) {
            $label = $this->truncateLabel($label, $labelWidthLimit);
        }

        $gridLeftMarginBeforePadding = $this->getGridLeftMargin($horizontalGraph = true, $withLabel = true);

        // pad labels for logo space
        foreach ($this->abscissaSeries as &$label) {
            $label .= $paddingText;
        }

        $this->initGridChart(
            $displayVerticalGridLines = false,
            $bulletType = LEGEND_FAMILY_BOX,
            $horizontalGraph = true,
            $showTicks = false,
            $verticalLegend
        );

        $valueColor = $this->textColor;
        $this->pImage->drawBarChart(
            array(
                 'DisplayValues' => true,
                 'Interleave'    => self::INTERLEAVE,
                 'DisplayR'      => $valueColor['R'],
                 'DisplayG'      => $valueColor['G'],
                 'DisplayB'      => $valueColor['B'],
            )
        );

//		// display icons
        $graphData = $this->pData->getData();
        $numberOfRows = count($this->abscissaSeries);
        $logoInterleave = $this->getGraphHeight(true, $verticalLegend) / $numberOfRows;
        for ($i = 0; $i < $numberOfRows; $i++) {
            if (isset($this->abscissaLogos[$i])) {
                $logoPath = $this->abscissaLogos[$i];

                if (isset($logoPathToHeight[$logoPath])) {
                    $logoHeight = $logoPathToHeight[$logoPath];

                    $pathInfo = pathinfo($logoPath);
                    $logoExtension = strtoupper($pathInfo['extension']);
                    $drawingFunction = 'drawFrom' . $logoExtension;

                    $logoYPosition =
                        ($logoInterleave * $i)
                        + $this->getGridTopMargin(true, $verticalLegend)
                        + $graphData['Axis'][1]['Margin']
                        - $logoHeight / 2
                        + 1;

                    if (method_exists($this->pImage, $drawingFunction)) {
                        $this->pImage->$drawingFunction(
                            $gridLeftMarginBeforePadding,
                            $logoYPosition,
                            $logoPath
                        );
                    }
                }
            }
        }
    }
}
