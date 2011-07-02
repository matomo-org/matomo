<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Generates the data in the Open Flash Chart format, from the given data.
 *
 * @package Piwik
 * @subpackage Piwik_Visualization
 */
abstract class Piwik_Visualization_Chart implements Piwik_iView
{
	
	// the data kept here conforms to the jqplot data layout
	// @see http://www.jqplot.com/docs/files/jqPlotOptions-txt.html
	protected $series = array();
	protected $data = array();
	protected $axes = array();
	protected $tooltip = array();
	protected $seriesColors = array('#000000');
	
	// other attributes (not directly used for jqplot)
	protected $maxValue;
	protected $yUnit = '';
	protected $displayPercentageInTooltip = true;
	protected $xSteps = 2;
	
	public function setAxisXLabels(&$xLabels)
	{
		$this->axes['xaxis']['ticks'] = &$xLabels;
	}

	public function setAxisXOnClick(&$onClick)
	{
		$this->axes['xaxis']['onclick'] = &$onClick;
	}
	
	public function setAxisYValues(&$values)
	{
		foreach ($values as $label => &$data)
		{
			$this->series[] = array(
				'label' => $label,
				'internalLabel' => $label
			);
			
			array_walk($data, create_function('&$v', '$v = (float)$v;'));
			$this->data[] = &$data;
		}
	}
	
	protected function addTooltipToValue($seriesIndex, $valueIndex, $tooltipTitle, $tooltipText)
	{
		$this->tooltip[$seriesIndex][$valueIndex] = array($tooltipTitle, $tooltipText);
	}

	public function setAxisYUnit($yUnit)
	{
		if (!empty($yUnit))
		{
			$this->yUnit = $yUnit;
			$this->axes['yaxis']['tickOptions']['formatString'] = '%s'.$yUnit;
			$this->tooltip['yUnit'] = $yUnit;
		}
	}
	
	public function setAxisYLabels($labels)
	{
		foreach ($this->series as &$series)
		{
			$label = $series['internalLabel'];
			if (isset($labels[$label]))
			{
				$series['label'] = $labels[$label];
			}
		}
	}
	
	public function setDisplayPercentageInTooltip($display)
	{
		$this->displayPercentageInTooltip = $display;
	}
	
	public function setXSteps($steps)
	{
		$this->xSteps = $steps;
	}

	public function getMaxValue()
	{
		if (count($this->data) == 0)
		{
			return 0;
		}
		
		$maxCrossDataSets = 0;
		foreach ($this->data as &$data)
		{
			$maxValue = max($data);
			if($maxValue > $maxCrossDataSets)
			{
				$maxCrossDataSets = $maxValue;
			}
		}
		
		$maxCrossDataSets += round($maxCrossDataSets * .02);
		
		if ($maxCrossDataSets > 10)
		{
			$maxCrossDataSets = $maxCrossDataSets + 10 - $maxCrossDataSets % 10;
		}
		return $maxCrossDataSets;
	}
	
	public function render()
	{
		Piwik::overrideCacheControlHeaders();
		
		$data = array(
			'params' => array(
				'axes' => &$this->axes,
				'series' => &$this->series,
				'seriesColors' => &$this->seriesColors
			),
			'data' => &$this->data,
			'tooltip' => $this->tooltip
		);
		
		return json_encode($data);
	}
	
	public function customizeChartProperties()
	{
		$this->maxValue = $this->getMaxValue();
		if ($this->maxValue == 0)
		{
			$this->maxValue = 1;
		}
		
		// x axis labels with steps
		if (isset($this->axes['xaxis']['ticks']))
		{
			foreach ($this->axes['xaxis']['ticks'] as $i => &$xLabel)
			{
				$this->axes['xaxis']['labels'][$i] = $xLabel;
				if (($i % $this->xSteps) != 0)
				{
					$xLabel = ' ';
				}
			}
		}
		
		// y axis labels
		$ticks = array();
		$numberOfTicks = 2;
		$tickDistance = ceil($this->maxValue / $numberOfTicks);
		for ($i = 0; $i <= $numberOfTicks; $i++)
		{
			$ticks[] = $i * $tickDistance;
		}
		$this->axes['yaxis']['ticks'] = &$ticks;
	}
	
}
