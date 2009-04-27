<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: ChartPie.php 459 2008-05-06 22:39:42Z matt $
 * 
 * @package Piwik_Visualization
 */
require_once "Visualization/Chart.php";

/**
 * Customize & set values for the Flash Pie chart 
 * 
 * @package Piwik_Visualization
 */
class Piwik_Visualization_Chart_Pie extends Piwik_Visualization_Chart
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
		
		// create the Pie
		$pie = new pie();
		$pie->set_alpha(0.6);
		$pie->set_start_angle( 35 );
		$pie->add_animation( new pie_fade() );
		$pie->set_label_colour('#142448');
		$pie->set_colours( array('#3C5A69','#679BB5','#695A3C','#B58E67','#969696') );

		// create the Pie values
		$yValues = $this->yValues[$dataSetToDisplay];
		$labelName = $this->yLabels[$dataSetToDisplay];
		$sum = array_sum($yValues);
		$pieValues = array();
		$i = 0;
		foreach($this->xLabels as $label) {
			$value = $yValues[$i];
			$i++;
			// we never plot empty pie slices (eg. visits by server time pie chart)
			if($value <= 0) {
				continue;
			}
			$pieValue = new pie_value($value, $label);
			$percentage = round(100 * $value / $sum);
			$pieValue->set_tooltip("$label <br>$percentage% ($value $labelName)");
			$pieValues[] = $pieValue;
		}
		$pie->set_values($pieValues);
		
		$this->chart->add_element($pie);
	}
}
