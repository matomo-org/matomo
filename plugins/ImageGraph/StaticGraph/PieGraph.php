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

require_once PIWIK_INCLUDE_PATH . "/libs/pChart2.1.3/class/pPie.class.php";

/**
 *
 * @package Piwik_ImageGraph_StaticGraph
 */
abstract class Piwik_ImageGraph_StaticGraph_PieGraph extends Piwik_ImageGraph_StaticGraph
{
	const RADIUS_MARGIN = 40;
	const PIE_RIGHT_MARGIN = 20;
	const SECTOR_GAP = 2.5;

	protected $pieChart;
	protected $xPosition;
	protected $yPosition;
	protected $pieConfig;

	static private $DEFAULT_SLICE_COLORS = array(
		'SLICE_1' => '3C5A69',
		'SLICE_2' => '679BB5',
		'SLICE_3' => '695A3C',
		'SLICE_4' => 'B58E67',
		'SLICE_5' => '8AA68A',
		'SLICE_6' => 'A4D2A6'
	);

	protected function getDefaultColors()
	{
		return self::$DEFAULT_SLICE_COLORS;
	}

	protected function initPieGraph($showLegend)
	{
		$this->truncateSmallValues();
		$this->initpData();
		$this->initpImage();

		if ($this->height > $this->width)
		{
			$radius = ($this->width / 2) - self::RADIUS_MARGIN;
		}
		else
		{
			$radius = ($this->height / 2) - self::RADIUS_MARGIN;
		}

		$this->pieChart = new pPie($this->pImage, $this->pData);

		$i = 0;
		foreach($this->colors as $color)
		{
			$this->pieChart->setSliceColor($i, $color);
			$i++;
		}

		// max abscissa label width is used to set the pie right margin
		$abscissaMaxWidthHeight = $this->maxWidthHeight($this->abscissaSerie);
		$maxAbscissaLabelWidth = $abscissaMaxWidthHeight[self::WIDTH_KEY];

		$this->xPosition = $this->width - $radius - $maxAbscissaLabelWidth - self::PIE_RIGHT_MARGIN;
		$this->yPosition = $this->height / 2;

		if ($showLegend)
		{
			$this->pieChart->drawPieLegend(15, 40, array("Alpha" => 20));
		}

		$this->pieConfig =
				array(
					'Radius' => $radius,
					'DrawLabels' => true,
					'DataGapAngle' => self::SECTOR_GAP,
					'DataGapRadius' => self::SECTOR_GAP,
				);
	}

	/**
	 * this method logic is close to Piwik's core filter_truncate.
	 * it uses a threshold to determine if an abscissa value should be drawn on the PIE
	 * discarded abscissa values are summed in the 'other' abscissa value
	 *
	 * if this process is not perform, pChart will draw pie slices that are too small to see
	 */
	private function truncateSmallValues()
	{
		$ordinateValuesSum = 0;
		foreach($this->ordinateSerie as $ordinateValue)
		{
			$ordinateValuesSum += $ordinateValue;
		}

		$ordinateValuesCount = count($this->ordinateSerie);
		$truncatedOrdinateSerie = array();
		$truncatedAbscissaSerie = array();
		$smallValuesSum = 0;
		for($i = 0; $i < $ordinateValuesCount - 1 ; $i++)
		{
			$ordinateValue = $this->ordinateSerie[$i];
			if($ordinateValue / $ordinateValuesSum > 0.01)
			{
				$truncatedOrdinateSerie[] = $ordinateValue;
				$truncatedAbscissaSerie[] = $this->abscissaSerie[$i];
			}
			else
			{
				$smallValuesSum += $ordinateValue;
			}
		}

		$smallValuesSum += $this->ordinateSerie[$ordinateValuesCount - 1];
		if(($smallValuesSum / $ordinateValuesSum) > 0.01)
		{
			$truncatedOrdinateSerie[] = $smallValuesSum;
			$truncatedAbscissaSerie[] = $this->abscissaSerie[$ordinateValuesCount - 1];
		}

		$this->ordinateSerie = $truncatedOrdinateSerie;
		$this->abscissaSerie = $truncatedAbscissaSerie;
	}
}
