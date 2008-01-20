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
 * 
 * @package Piwik_Visualization
 *
 */
class Piwik_Visualization_ChartEvolution extends Piwik_Visualization_Chart
{		
	
	function customizeGraph()
	{
		//TODO add this call in other child
		parent::customizeGraph();
		$this->prepareData();		
		$this->set_y_max( $this->maxData );
		
		$this->bg_colour = '#ffffff';
		$this->set_data( $this->arrayData );
		$this->set_x_labels( $this->arrayLabel );
		$this->area_hollow( 1, 3, 10,'0x3357A0',  ' visits', 10 );
		
		$this->set_tool_tip( '#x_label# <br>#val# #key# ' );
		
		
	}
	
}