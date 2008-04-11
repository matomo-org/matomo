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
//		var_dump($this->dataGraph); exit;
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
		
//		var_dump($label);var_dump($data);
	}
	
	function render()
	{
		//some tests data
		/*return '&y_legend=Time of day,#736AFF,12&
			&y_ticks=5,10,6&
			&line_dot=3,0x736AFF,Avg. wave height (cm),10,3&
			&values=1.5,1.6986693307951,1.8894183423087,2.064642473395,2.2173560908995,2.3414709848079,2.4320390859672,2.4854497299885,2.4995736030415,2.4738476308782,2.4092974268257,2.3084964038196,2.1754631805512,2.0155013718215,1.8349881501559,1.6411200080599,1.4416258565724,1.2444588979732,1.0574795567051,0.88814210905728,0.74319750469207,0.62842422758641,0.54839792611048,0.50630899636654,0.50383539116416,0.54107572533686,0.61654534427985,0.72723551244401,0.86873336212768,1.0353978205862,1.2205845018011,1.4169105971825,1.6165492048505,1.8115413635134,1.9941133511386,2.1569865987188,2.2936678638492,2.3987080958116,2.4679196720315,2.4985433453746,2.4893582466234,2.4407305566798,2.3545989080883,2.2343970978741,2.0849171928918&
			&x_labels=2:00am,2:10,2:20,2:30,2:40,2:50,3:00am,3:10,3:20,3:30,3:40,3:50,4:00am,4:10,4:20,4:30,4:40,4:50,5:00am,,,,,,,6:00am,,,,,,,7:00am,,,,,,,8:00am,,,,,,&
			&y_min=0&
			&y_max=3&
			&bg_colour=0xDFFFDF&
			&x_label_style=13,0x9933CC,0,6&
			
			&y_label_style=none&
			';
			*/
		return parent::render();
	}
	
}