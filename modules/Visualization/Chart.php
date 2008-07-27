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

require_once "Visualization/OpenFlashChart.php";

/**
 * Generates the data in the Open Flash Chart format, from the given data.
 * Uses Open flash chart PHP library @see Piwik_Visualization_OpenFlashChart
 * 
 * @package Piwik_Visualization
 */
abstract class Piwik_Visualization_Chart extends Piwik_Visualization_OpenFlashChart
{
	
	protected $dataGraph = array();
	
	function setData($data)
	{
		$this->dataGraph = $data;
	}
	
	function getCount()
	{
		return count($this->dataGraph);
	}
	
	function customizeGraph()
	{
		$this->set_num_decimals ( 0 );
		$this->set_is_decimal_separator_comma( false );
		$this->set_is_thousand_separator_disabled( true );  
		$this->y_axis_colour = '#ffffff';
		$this->x_axis_colour = '#596171'; 
		$this->x_grid_colour = $this->y_grid_colour = '#E0E1E4';
		
		// approx 5 x labels on the graph
		$steps = ceil($this->getCount() / 5);
		$steps = $steps + $steps % 2; // make sure modulo 2
		
		$this->set_x_label_style( 10, $this->x_axis_colour, 0, $steps, $this->x_grid_colour );
		$this->set_x_axis_steps( $steps / 2 );
		
		
		$stepsY = ceil($this->getCount() / 4);
		$this->y_label_steps( $stepsY / 3 );
		$this->y_label_steps( 4 );
		
		$this->bg_colour = '#ffffff';
		$this->set_inner_background('#ffffff');
		
		$this->set_tool_tip( '#x_label# <br>#val# #key# ' );
	}
	
	function prepareData()
	{		
		$label = $data = array();
		$max = 0;
		foreach($this->dataGraph as $row)
		{
			$label[] = $row['label'];
			$data[] = $row['value'];
			
			if($row['value'] > $max) 
			{
				$max = $row['value'];
			}
		}
		$this->arrayData = $data;
		$this->arrayLabel = $label;
		
		$this->arrayLabel = str_replace(","," -",$this->arrayLabel);
		
		$this->maxData = $max;
		if($this->maxData > 10)
		{
			$this->maxData = $max + 10 - $max % 10;
		}
	}
	
}