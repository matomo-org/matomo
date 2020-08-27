<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ImageGraph\StaticGraph;

use CpChart\Chart\Pie;
use Piwik\Plugins\ImageGraph\StaticGraph;

/**
 *
 */
abstract class PieGraph extends StaticGraph
{
    const RADIUS_MARGIN = 40;
    const PIE_RIGHT_MARGIN = 20;
    const SECTOR_GAP = 2.5;

    const SLICE_COLOR_KEY = "SLICE_COLOR";

    /**
     * @var Pie
     */
    protected $pieChart;
    protected $xPosition;
    protected $yPosition;
    protected $pieConfig;

    protected function getDefaultColors()
    {
        return array(
            self::SLICE_COLOR_KEY . '1' => '3C5A69',
            self::SLICE_COLOR_KEY . '2' => '679BB5',
            self::SLICE_COLOR_KEY . '3' => '695A3C',
            self::SLICE_COLOR_KEY . '4' => 'B58E67',
            self::SLICE_COLOR_KEY . '5' => '8AA68A',
            self::SLICE_COLOR_KEY . '6' => 'A4D2A6',
        );
    }

    protected function initPieGraph($showLegend)
    {
        $this->truncateSmallValues();
        $this->initpData();
        $this->initpImage();

        if ($this->height > $this->width) {
            $radius = ($this->width / 2) - self::RADIUS_MARGIN;
        } else {
            $radius = ($this->height / 2) - self::RADIUS_MARGIN;
        }

        $this->pieChart = new Pie($this->pImage, $this->pData);

        $numberOfSlices = count($this->abscissaSeries);
        $numberOfAvailableColors = count($this->colors);
        for ($i = 0; $i < $numberOfSlices; $i++) {
            $this->pieChart->setSliceColor($i, $this->colors[self::SLICE_COLOR_KEY . (($i % $numberOfAvailableColors) + 1)]);
        }

        // max abscissa label width is used to set the pie right margin
        list($abscissaMaxWidth, $abscissaMaxHeight) = $this->getMaximumTextWidthHeight($this->abscissaSeries);

        $this->xPosition = $this->width - $radius - $abscissaMaxWidth - self::PIE_RIGHT_MARGIN;
        $this->yPosition = $this->height / 2;

        if ($showLegend) {
            $this->pieChart->drawPieLegend(15, 40, array("Alpha" => 20));
        }

        $this->pieConfig =
            array(
                'Radius'        => $radius,
                'DrawLabels'    => true,
                'DataGapAngle'  => self::SECTOR_GAP,
                'DataGapRadius' => self::SECTOR_GAP,
            );
    }

    /**
     * this method logic is close to Piwik's core filter_truncate.
     * it uses a threshold to determine if an abscissa value should be drawn on the PIE
     * discarded abscissa values are summed in the 'other' abscissa value
     *
     * if this process is not perform, CpChart will draw pie slices that are too small to see
     */
    private function truncateSmallValues()
    {
        $metricColumns = array_keys($this->ordinateSeries);
        $metricColumn = $metricColumns[0];

        $ordinateValuesSum = 0;
        foreach ($this->ordinateSeries[$metricColumn] as $ordinateValue) {
            $ordinateValuesSum += $ordinateValue;
        }

        $truncatedOrdinateSeries[$metricColumn] = array();
        $truncatedAbscissaSeries = array();
        $smallValuesSum = 0;

        $ordinateValuesCount = count($this->ordinateSeries[$metricColumn]);
        for ($i = 0; $i < $ordinateValuesCount - 1; $i++) {
            $ordinateValue = $this->ordinateSeries[$metricColumn][$i];
            if ($ordinateValue / $ordinateValuesSum > 0.01) {
                $truncatedOrdinateSeries[$metricColumn][] = $ordinateValue;
                $truncatedAbscissaSeries[] = $this->abscissaSeries[$i];
            } else {
                $smallValuesSum += $ordinateValue;
            }
        }

        $smallValuesSum += $this->ordinateSeries[$metricColumn][$ordinateValuesCount - 1];
        if (($smallValuesSum / $ordinateValuesSum) > 0.01) {
            $truncatedOrdinateSeries[$metricColumn][] = $smallValuesSum;
            $truncatedAbscissaSeries[] = end($this->abscissaSeries);
        }

        $this->ordinateSeries = $truncatedOrdinateSeries;
        $this->abscissaSeries = $truncatedAbscissaSeries;
    }
}
