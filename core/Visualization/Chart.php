<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Visualization
 */

require_once PIWIK_INCLUDE_PATH . '/libs/open-flash-chart/php-ofc-library/open-flash-chart.php';

/**
 * Generates the data in the Open Flash Chart format, from the given data.
 * 
 * @package Piwik_Visualization
 */
abstract class Piwik_Visualization_Chart implements Piwik_iView
{
	/**
	 * @var Piwik_Visualization_OpenFlashChart
	 */
	protected $chart = null;
	
	protected $xLabels = array();
	protected $xOnClick = array();
	protected $xSteps = 2;
	
	protected $yLabels = array();
	protected $yValues = array();
	protected $yUnit = '';
	
	protected $maxValue;
	protected $minValue;
	protected $displayPercentageInTooltip = true;
	
	function __construct()
	{
		$this->chart = new open_flash_chart();
	}
	
	public function setAxisXLabels($xLabels)
	{
		$this->xLabels = $xLabels;
	}

	public function setAxisXOnClick($onClick)
	{
		$this->xOnClick = $onClick;
	}
	
	public function setAxisYValues($values)
	{
		$this->yValues = $values;
	}

	function setAxisYUnit($yUnit)
	{
		if(!empty($yUnit))
		{
			$this->yUnit = $yUnit;
		}
	}
	
	public function setAxisYLabels($labels)
	{
		$this->yLabels = $labels;
	}
	
	public function setDisplayPercentageInTooltip($bool)
	{
		$this->displayPercentageInTooltip = $bool;
	}
	
	public function setXSteps($steps)
	{
		$this->xSteps = $steps;
	}
	
	protected function getDataSetsToDisplay()
	{
		if(empty($this->yValues)) {
			return false;
		}
		return array_keys($this->yValues);
	}
	
	public function getMaxValue()
	{
		$datasetsIds = $this->getDataSetsToDisplay();
		if($datasetsIds === false)
		{
			return 0;
		}
		$maxCrossDataSets = false;
		foreach($datasetsIds as $dataset)
		{
			$maxValue = max($this->yValues[$dataset]);
			if($maxCrossDataSets === false 
				|| $maxValue > $maxCrossDataSets)
			{
				$maxCrossDataSets = $maxValue;
			}
		}
		if($maxCrossDataSets > 10)
		{
			$maxCrossDataSets = $maxCrossDataSets + 10 - $maxCrossDataSets % 10;
		}
		return $maxCrossDataSets;
	}
	
	public function setTitle($text, $css)
	{
		$title = new title($text);
		$title->set_style($css);
		$this->chart->set_title($title);
	}
	
	public function render()
	{
		return $this->chart->toPrettyString();
	}
	
	function customizeChartProperties()
	{
		$this->chart->set_number_format($num_decimals = 0, 
							$is_fixed_num_decimals_forced = true, 
							$is_decimal_separator_comma = false, 
							$is_thousand_separator_disabled = false);
							
		$gridColour = '#E0E1E4';
		$countValues = count($this->xLabels);
		$this->maxValue = $this->getMaxValue();
		$this->minValue = 0;
		
		// X Axis
		$this->x = new x_axis();
		$this->x->set_colour( '#596171' );
		$this->x->set_grid_colour( $gridColour );
		$this->x->set_steps($this->xSteps);
		
		// X Axis Labels
		$this->x_labels = new x_axis_labels();
		$this->x_labels->set_size(11);
		//manually fix the x labels step as this doesn't work in this OFC release..
		$xLabelsStepped = $this->xLabels;
		foreach($xLabelsStepped as $i => &$xLabel)
		{
			if(($i % $this->xSteps) != 0)
			{
				$xLabel = '';
			}
		}
		$this->x_labels->set_labels($xLabelsStepped);
		$this->x_labels->set_steps(2);
		$this->x->set_labels($this->x_labels);
		
		// Y Axis
		$this->y = new y_axis();
		$this->y->set_colour('#ffffff');
		$this->y->set_grid_colour($gridColour);
		$stepsCount = 2;
		$stepsEveryNLabel = ceil(($this->maxValue - $this->minValue) / $stepsCount);
		if($this->maxValue == 0)
		{
			$this->maxValue = 1;
		}
                $this->y->set_range( $this->minValue, (int) $this->maxValue, (int) $stepsEveryNLabel);
		$dataSetsToDisplay = $this->getDataSetsToDisplay();
		if($dataSetsToDisplay != false)
		{
			$dataSetToDisplay = current($dataSetsToDisplay);
			$this->y->set_label_text("#val#".$this->yUnit);
		}
		
		// Tooltip
		$this->tooltip = new tooltip();
		$this->tooltip->set_shadow( true );
		$this->tooltip->set_stroke( 1 );
				
		// Attach elements to the graph
		$this->chart->set_x_axis($this->x);
		$this->chart->set_y_axis($this->y);
		$this->chart->set_tooltip($this->tooltip);
		$this->chart->set_bg_colour('#ffffff');
	}
}
