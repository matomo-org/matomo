<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: ChartVerticalBar.php 459 2008-05-06 22:39:42Z matt $
 * 
 * @package Piwik_Visualization
 */

require_once "Visualization/Chart.php";

/**
 * Customize & set values for the flash Vertical bar chart
 * 
 * @package Piwik_Visualization
 */
class Piwik_Visualization_Chart_VerticalBar extends Piwik_Visualization_Chart
{
	// return the first dataset id from the list
	protected function getDataSetsToDisplay()
	{
		$dataSetsToDisplay = parent::getDataSetsToDisplay();
		if($dataSetsToDisplay === false)
		{
			return false;
		}
		return array_slice($dataSetsToDisplay, 0, 1);
	}
	
	function customizeGraph()
	{
		parent::customizeGraph();
		$dataSetsToDisplay = $this->getDataSetsToDisplay();
		if($dataSetsToDisplay === false)
		{
			return;
		}
		$dataSetToDisplay = current($dataSetsToDisplay);
		
		$this->x->set_grid_colour('#ffffff');
		$this->x_labels->set_steps(2);
		$this->x->set_stroke(1);

		// create the Bar object
		$bar = new bar_filled('#3B5AA9', '#063E7E');
		$bar->set_alpha(0.5);
		$bar->set_key($this->yLabels[$dataSetToDisplay], 12);
		$bar->set_tooltip( '#val# #key#');
		
		// create the bar values
		$yValues = $this->yValues[$dataSetToDisplay];
		$labelName = $this->yLabels[$dataSetToDisplay];
		$sum = array_sum($yValues);
		$barValues = array();
		$i = 0;
		foreach($this->xLabels as $label) {
			$value = $yValues[$i];
			$barValue = new bar_value($value);
			$percentage = round(100 * $value / $sum);
			$barValue->set_tooltip("$label <br>$value $labelName ($percentage%)");
			$barValues[] = $barValue;
			$i++;
		}
		$bar->set_values($barValues);
		$this->chart->add_element($bar);
	}
	
}
