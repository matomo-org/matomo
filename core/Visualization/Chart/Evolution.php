<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: ChartVerticalBar.php 168 2008-01-14 05:26:43Z matt $
 * 
 * @package Piwik_Visualization
 */

require_once "Visualization/Chart.php";

/**
 * Customize the Evolution chart style for the flash graph
 * 
 * @package Piwik_Visualization
 */
class Piwik_Visualization_Chart_Evolution extends Piwik_Visualization_Chart 
{
	function customizeChartProperties()
	{
		parent::customizeChartProperties();
		$dataSetsToDisplay = $this->getDataSetsToDisplay();
		if($dataSetsToDisplay === false)
		{
			return;
		}
		
		$colors = array(
			"0x3357A0",
			"0xCC3399",			
			"0x9933CC",
			"0x80a033",
			"0xFD9816",
			"0x246AD2",
			"0xFD16EA",
			"0x49C100",
		);
		
		$i = 0;
		foreach($dataSetsToDisplay as $dataSetToDisplay)
		{
			$color = $colors[$i];
			
			$labelName = $this->yLabels[$dataSetToDisplay];
			$d = new hollow_dot();
			$d->size(3)->halo_size(0)->colour($color); 
		
			$line = new line();
			$line->set_default_dot_style($d);
			$line->set_key($labelName, 11);
			$line->set_width( 1 );
			$line->set_colour( $color );
			
			// Line Values
			// Note: we have to manually create the dot values as the steps feature doens't work on X axis
			// when it's working again, we can remove code below and set generic tooltip above: // ->tooltip('#x_label#<br>#val# '.$labelName) 
			$yValues = $this->yValues[$dataSetToDisplay];
			$labelName = $this->yLabels[$dataSetToDisplay];
			$lineValues = array();
			$j = 0;
			foreach($this->xLabels as $label) {
				$value = (float)$yValues[$j];
				$lineValue = new hollow_dot($value);
				
				$unit = $this->yUnit;
				$lineValue->tooltip("$label<br>$value$unit $labelName");
				if(!empty($this->xOnClick))
				{
					$lineValue->on_click("piwikHelper.redirectToUrl('".$this->xOnClick[$j]."')");
				}
				$lineValues[] = $lineValue;
				$j++;
			}
			$line->set_values( $lineValues );
			$lines[] = $line;
			$i++;
		}
		foreach($lines as $line)
		{
			$this->chart->add_element($line);
		}
		// if one column is a percentage we set the grid accordingly
		// note: it is invalid to plot a percentage dataset along with a numeric dataset
		if($this->yUnit == '%' 
			&& $this->maxValue > 90)
		{
			$this->y->set_range( 0, 100, 50);
		}
	}
}
